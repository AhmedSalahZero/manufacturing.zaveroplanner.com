<?php

namespace App;

use App\Helpers\HArr;
use App\ReadyFunctions\CalculateFixedLoanAtEndService;
use App\ReadyFunctions\ProjectsUnderProgress;
use App\Traits\HasBasicStoreRequest;
use App\Traits\HasFixedAsset;
use App\Traits\HasIndexedDates;
use App\Traits\HasManpowerExpense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    protected $connection = 'mysql';
    use SoftDeletes,HasBasicStoreRequest,HasIndexedDates,HasManpowerExpense,HasFixedAsset;
    protected $casts = [
        'manpower_allocations'=>'array',
        'extended_study_dates'=>'array'
    ];
    protected $guarded = [];
    public function owner()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    public function sharingLinks()
    {
        return $this->hasMany(Sharing::class);
    }
    public function product($type)
    {
        return $this->hasOne('App\Product')->where('type', $type)->first();
    }
    public function sensitivity()
    {
        return $this->hasOne('App\Sensitivity');
    }
    public function fixedAssets()
    {
        return $this->hasMany(FixedAsset::class, 'project_id', 'id');
    }

    // public function manPower()
    // {
    //     return $this->hasOne('App\ManPower');
    // }
    // public function expense()
    // {
    //     return $this->hasOne('App\Expense');
    // }
    // public function assets()
    // {
    //     return $this->hasOne('App\Assets');
    // }
    // public function openingBalance()
    // {
    //     return $this->hasOne('App\OpeningBalance');
    // }
    public function backlog()
    {
        return $this->hasOne('App\Backlog', 'project_id');
    }
    public function sector()
    {
        return $this->hasOne('App\BusinessSector', 'id', 'business_sector_id');
    }
    public function products():HasMany
    {
        return $this->hasMany(Product::class, 'project_id', 'id');
    }
    public function manpowers():HasMany
    {
        return $this->hasMany(ManPower::class, 'project_id', 'id');
    }
    public function expenses():HasMany
    {
        return $this->hasMany(Expense::class, 'project_id', 'id');
    }
    public function getViewStudyDates():array
    {
        return generateDatesBetweenTwoDatesWithoutOverflow(Carbon::make($this->start_date), Carbon::make($this->end_date));
    }
    public function getExtendedStudyDates():array
    {
        return generateDatesBetweenTwoDatesWithoutOverflow(Carbon::make($this->start_date), Carbon::make($this->extended_end_date));
    }
    public function getYears():array
    {
        return $this->getYearIndexWithYear();
        
    }
    public function getManpowerAllocationForType(string $manpowerTypeId)
    {
        return $this->manpower_allocations[$manpowerTypeId]??[];
    }
    public function getSalaryIncreaseRate():float
    {
        return $this->salaries_annual_increase?:0;
    }
    public function getSalaryTaxRate():float
    {
        return $this->salaries_tax_rate?:0;
    }public function getSocialInsuranceRate():float
    {
        return $this->social_insurance_rate?:0;
    }
    public function rawMaterials():HasMany
    {
        return $this->hasMany(RawMaterial::class, 'project_id', 'id');
    }
    public function getStudyStartDateAttribute()
    {
        return $this->start_date;
    }
    public function getStudyEndDateAttribute()
    {
        return $this->end_date;
    }
    public function getStudyStartDateYearAndMonth()
    {
        $studyStartDate = $this->getStudyStartDate() ;
        if (is_null($studyStartDate)) {
            return now()->format('Y-m');
        }
        return Carbon::make($studyStartDate)->format('Y-m');
    }
    
    public function getStudyEndDateYearAndMonth()
    {
        $studyEndDate = $this->getStudyEndDate() ;
        if (is_null($studyEndDate)) {
            return now()->format('Y-m');
        }
        return Carbon::make($studyEndDate)->format('Y-m');
    }
    
    public function getOperationStartDateYearAndMonth()
    {
        $operationStartDate = $this->getOperationStartDate() ;
        if (is_null($operationStartDate)) {
            return now()->format('Y-m');
        }
        return Carbon::make($operationStartDate)->format('Y-m');
    }
    public function recalculateForSensitivity()
    {
        foreach ($this->products as $product) {
            $product->calculateMonthlySalesTargetValue(true);
        }
    }
    public function getFirstYearUntilLast($startDateAsIndex):array
    {
        // ['first_year'=>'2025','else_years'=>'2026 till 2029'];
        $sellingYear = $this->getYearFromDateIndex($startDateAsIndex);
        $projectEndYear = explode('-', $this->end_date)[0];
        if ($sellingYear == $projectEndYear) {
            return [
             __('Year [ ' . $sellingYear . ' ]') ,
            ];
        }
        if ($sellingYear+1 == $projectEndYear) {
            return [
             __('Year [ ' . $sellingYear . ' ]') ,
             __('Year [ ' . $projectEndYear . ' ]') ,
            ];
        }
        return [
             __('Year [ ' . $sellingYear . ' ]') ,
             'Year [ '. ($sellingYear+1) . __(' Till ' . $projectEndYear)  . ' ]',
            ];
    }
    /**
     * !!
     */
    public function isMonthlyStudy()
    {
        return true ;
        // return $this->duration_in_years < 2 ;
    }
    public function reallocateProductsOnManpowers()
    {
        $operationDates = $this->getOperationDatesAsDateAndDateAsIndex();
        foreach (getManpowerTypes() as $id => $manpowerOptionArr) {
            if ($manpowerOptionArr['has_allocation']) {
                $allocationColumnName= $manpowerOptionArr['allocation_column_name'];
                $salaryExpenses = ManPower::where('type', $id)->where('project_id', $this->id)->pluck('salary_expenses')->toArray();
                $allocationForManpowerType = $this->getManpowerAllocationForType($id);
            
                $totalManpowerSalaries = HArr::sumAtDates($salaryExpenses, $operationDates);
                foreach ($allocationForManpowerType as $productId => $allocationPercentage) {
                    $product = Product::find($productId);
                    $product->update([
                        $allocationColumnName => HArr::MultiplyWithNumber($totalManpowerSalaries, $allocationPercentage/100)
                    ]);
                    
                }
                
            }
        }
        $this->recalculateFgInventoryValueStatement();
    }
    public function getExpenseCategories():array
    {
        $expenseCategories = [
            'manufacturing-expenses'=>[
                'title'=>__('Manufacturing Exp.'),
                'has_allocation'=>1
            ],
            'other-operation-expenses'=>[
                'title'=>__('Other Operation Exp.'),
                'has_allocation'=>1 ,
            ],
            'marketing'=>[
                'title'=>__('Marketing Exp.') ,
                'has_allocation'=>0
            ],
            'sales'=>[
                'title'=>__('Sales Exp.'),
                'has_allocation'=> 0
            ],
            'general-expenses'=>[
                'title'=>__('General Exp.'),
                'has_allocation'=> 0
            ],
        ] ;
        $isNewCompany = $this->isNewCompany() ;
        if ($isNewCompany) {
            $expenseCategories = array_merge($expenseCategories, [
                    'pre-operating-expenses'=>[
                        'title'=>__('Pre-operating Exp.') ,
                        'has_allocation'=>0
                    ],
                        'start-up-expenses'=>[
                        'title'=>__('Start-up Cost'),
                        'has_allocation'=>0
                     ]
            ]);
               
        }
        return $expenseCategories;
    }
    public function isNewCompany():bool
    {
        return $this->new_company == 1 ;
    }
    public function generateRelationDynamically(string $relationName, string $expenseType = 'Expense')
    {
        return $this->hasMany(Expense::class, 'model_id', 'id')->where('model_name', 'Project')
        ->where('expense_type', $expenseType)->where('relation_name', $relationName);
    }
    

    public function recalculateFixedAsset(bool $isSensitivity = false)
    {
        $loanTableName = $isSensitivity ? 'sensitivity_loan_schedule_payments' : 'loan_schedule_payments';
        $studyEndDateAsIndex = $this->getStudyEndDateAsIndex();
        $calculateFixedLoanAtEndService = new CalculateFixedLoanAtEndService();
        $projectUnderProgressService = new ProjectsUnderProgress();
        $datesAsStringAndIndex = $this->getDateWithDateIndex();
        $dateIndexWithDate = $this->getDateIndexWithDate();
        $operationStartDateFormatted = $this->getOperationStartDateFormatted();
        $datesIndexWithYearIndex = $this->getDatesIndexWithYearIndex();
        $dateWithMonthNumber = $this->getDateWithMonthNumber();
        $operationStartDateAsIndex = $this->getOperationStartDateAsIndex($datesAsStringAndIndex, $operationStartDateFormatted);
        $yearIndexWithYear = $this->getYearIndexWithYear();
        $studyDurationPerYear = $this->getStudyDurationPerYear($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber, true, true, false);
        $studyDates=$this->getOnlyDatesOfActiveStudy($studyDurationPerYear, $dateIndexWithDate);
        DB::table('product_expense_allocations')->where('project_id', $this->id)->where('is_depreciation', 1)->delete();
        DB::table($loanTableName)->where('project_id', $this->id)->delete();
        foreach ($this->fixedAssets as $fixedAsset) {
            $fixedAssetCalculationResultArr = $calculateFixedLoanAtEndService->calculateFixedAssetsLoans($fixedAsset);
            $loanArr = $fixedAssetCalculationResultArr['ffeLoanCalculations'] ?? [];
            if (count($loanArr)) {
                $loanArr['project_id'] = $this->id ;
                $loanArr['fixed_asset_id'] = $fixedAsset->id ;
                
            }
            unset($loanArr['totals']);
            if (count($loanArr)) {
                DB::table($loanTableName)->insert(HArr::encodeArr($loanArr));
            }
            $ffeExecutionAndPayment = $fixedAssetCalculationResultArr['ffeExecutionAndPayment'];
            $ffeLoanInterestAmounts = $fixedAssetCalculationResultArr['ffeLoanInterestAmounts'];
            $ffeLoanWithdrawalInterestAmounts = $fixedAssetCalculationResultArr['ffeLoanWithdrawalInterest'];
            $projectUnderProgressFFE = $projectUnderProgressService->calculateForFFE($fixedAsset->getEndDateAsIndex(), $ffeExecutionAndPayment, $ffeLoanInterestAmounts, $ffeLoanWithdrawalInterestAmounts, $this, $operationStartDateAsIndex, $datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber);
        
            // 	$ffeLoanWithdrawalInterest= $fixedAssetCalculationResultArr['ffeLoanWithdrawalInterest'];
            // $ffeLoanPricing= $fixedAssetCalculationResultArr['ffeLoanPricing'];
            // $ffeLoanEndBalanceAtStudyEndDate = $fixedAssetCalculationResultArr['ffeLoanEndBalanceAtStudyEndDate'];
            // $projectUnderProgressService = new ProjectsUnderProgress();
            // $projectUnderProgressFFE = $projectUnderProgressService->calculateForFFE($ffeExecutionAndPayment,$ffeLoanInterestAmounts,$ffeLoanWithdrawalInterestAmounts, $hospitalitySector,$operationStartDateAsIndex,$datesAsStringAndIndex,$datesIndexWithYearIndex,$yearIndexWithYear,$dateIndexWithDate,$dateWithMonthNumber);
            $transferredDateForFFEAsIndex = array_key_last($projectUnderProgressFFE['transferred_date_and_vales']??[]);
            $ffeAssetItems = [];
            //   $totalOfFFEItemForFFE = [];
            // if($fixedAsset ){
            $ffeAssetItems = $fixedAsset->calculateFFEAssetsForFFE($transferredDateForFFEAsIndex, Arr::last($projectUnderProgressFFE['transferred_date_and_vales']??[], null, 0), $studyDates, $studyEndDateAsIndex, $this);
            
            $totalMonthlyDepreciation = $ffeAssetItems['total_monthly_depreciation'] ?? [];
            $adminDepreciationPercentage = $fixedAsset->getAdminDepreciationPercentage() /100;
            $manufacturingDepreciationPercentage = $fixedAsset->getManufacturingDepreciationPercentage() /100;
            $manufacturingDepreciations = [];
            $adminDepreciations = [];
            foreach ($totalMonthlyDepreciation as $dateAsIndex => $monthDepreciationValue) {
                $manufacturingDepreciations[$dateAsIndex]  =	$manufacturingDepreciationPercentage * $monthDepreciationValue;
            }
            foreach ($totalMonthlyDepreciation as $dateAsIndex => $monthDepreciationValue) {
                $adminDepreciations[$dateAsIndex]  =	$adminDepreciationPercentage * $monthDepreciationValue;
            }
            $fixedAsset->update([
                'capitalization_statement'=>$projectUnderProgressFFE,
                'depreciation_statement'=>$ffeAssetItems,
                'admin_depreciations'=>$adminDepreciations
            ]);
            $fixedAssetsAllocations =[ ];
            $allocationsPercentages = $fixedAsset->getProductAllocations();
            foreach ($allocationsPercentages as $productId => $allocationPercentage) {
                foreach ($manufacturingDepreciations as $dateAsIndex => $depreciationValue) {
                    $fixedAssetsAllocations[$productId][$dateAsIndex]  = $depreciationValue * $allocationPercentage /100 ;
                }
                
                DB::table('product_expense_allocations')->insert([
                    'project_id'=>$this->id ,
                    'product_id'=>$productId,
                    'expense_name'=>$fixedAsset->getName(),
                    'is_expense'=>0,
                    'is_depreciation'=>1 ,
                    'payload'=>json_encode($fixedAssetsAllocations[$productId])
                ]);
            }
            $this->recalculateAllProductsOverheadExpenses();
        }
        $this->recalculateFgInventoryValueStatement();
        
    }
    public function recalculateAllProductsOverheadExpenses()
    {
        DB::table('products')->where('project_id', $this->id)->update([
            'product_overheads_allocation'=>null
        ]);
        $result = [];
        $productExpenseAllocations = DB::table('product_expense_allocations')->where('project_id', $this->id)->pluck('payload', 'product_id')->toArray();
        $result = [];
        foreach ($productExpenseAllocations as $productId => $payload) {
            $payload = json_decode($payload);
            foreach ($payload as $dateAsIndex => $value) {
                $result[$productId][$dateAsIndex] = isset($result[$productId][$dateAsIndex]) ? $result[$productId][$dateAsIndex] + $value : $value ;
            }
        }
    
        foreach ($result as $productId => $totals) {
            DB::table('products')->where('id', $productId)->update([
                'product_overheads_allocation'=>json_encode($totals)
            ]);
        }
        
        $this->recalculateFgInventoryValueStatement();
        
    }
    public function calculateManufacturingExpensesPerUnit(array $items, array $totalAvailableQuantity):array
    {
        $result = [];
        foreach ($totalAvailableQuantity as $dateAsIndex => $availableQuantityValue) {
            $currentValue = $items[$dateAsIndex] ?? 0 ;
            $result[$dateAsIndex] =$availableQuantityValue ? $currentValue / $availableQuantityValue  : 0 ;
        }
        return $result;
    }
    public function recalculateFgInventoryValueStatement():void
    {
        $products = $this->products ;
        
        $fgStatementValues = [];
        $fgValueStatement = [];
        $columnNameMapping = [
            'direct_labor_value'=>'product_manpower_allocation',
            'raw_material_value'=>'product_raw_material_consumed',
            'manufacturing_overheads_value'=>'product_overheads_allocation',
        ];
        foreach ($products as $product) {
            $productId = $product->id ;
            $quantityStatement = $product->product_inventory_qt_statement;
            $totalAvailableQuantity = $quantityStatement['total_quantity_available']??[];
            $salesQuantity = $quantityStatement['sales_quantity']??[];
            $fgBeginningInventoryBreakdowns = $product->getFgBeginningInventoryBreakdowns() ;
            foreach ($fgBeginningInventoryBreakdowns as $inventoryItemType => $inventoryItemValues) {
                $fgBeginningInventoryBreakdownValue = 	$product->getFgBeginningInventoryBreakdownValueForType($inventoryItemType);
                $currentColumnMapping = $columnNameMapping[$inventoryItemType];
                $fgStatementValues[$productId][$inventoryItemType]['beginning_value'] = $fgBeginningInventoryBreakdownValue;
                $currentManufacturingExpenseArr = $currentColumnMapping == 'product_raw_material_consumed' ?  $product->{$currentColumnMapping}['total'] :   $product->{$currentColumnMapping} ;
                
                foreach ($currentManufacturingExpenseArr as $dateAsIndex => $currentManufacturingExpenseVal) {
                    $fgStatementValues[$productId][$inventoryItemType]['total_available_manufacturing_expenses'][$dateAsIndex] = $currentManufacturingExpenseVal+$fgBeginningInventoryBreakdownValue;
                    $totalAvailableValueAtCurrentDateIndex = $fgStatementValues[$productId][$inventoryItemType]['total_available_manufacturing_expenses'][$dateAsIndex];
                    $fgStatementValues[$productId][$inventoryItemType]['addition_manufacturing_expenses'][$dateAsIndex] = $currentManufacturingExpenseVal;
                    $totalAvailableQuantityAtDate  = $totalAvailableQuantity[$dateAsIndex] ??0 ;
                    $fgStatementValues[$productId][$inventoryItemType]['manufacturing_expenses_per_unit'][$dateAsIndex] =$totalAvailableQuantityAtDate ?  $totalAvailableValueAtCurrentDateIndex /$totalAvailableQuantityAtDate: 0 ;
                    $manufacturingExpensesPerUnit = $fgStatementValues[$productId][$inventoryItemType]['manufacturing_expenses_per_unit'][$dateAsIndex];
                    $salesQuantityAtCurrentDate = $salesQuantity[$dateAsIndex] ?? 0 ;
                    $fgStatementValues[$productId][$inventoryItemType]['cogs'][$dateAsIndex] = $manufacturingExpensesPerUnit * $salesQuantityAtCurrentDate ;
                    $currentCogsAtDate = $fgStatementValues[$productId][$inventoryItemType]['cogs'][$dateAsIndex]??0 ;
                    $fgStatementValues[$productId][$inventoryItemType]['end_balance'][$dateAsIndex] = $totalAvailableValueAtCurrentDateIndex - $currentCogsAtDate ;
                    $currentEndBalanceAtDate = $fgStatementValues[$productId][$inventoryItemType]['end_balance'][$dateAsIndex] ?? [];
                    $fgBeginningInventoryBreakdowns = $currentEndBalanceAtDate;
                    
                    
                    $fgValueStatement[$productId]['cogs'][$dateAsIndex] = isset($fgValueStatement[$productId]['cogs'][$dateAsIndex]) ? $fgValueStatement[$productId]['cogs'][$dateAsIndex] +  $currentCogsAtDate : $currentCogsAtDate ;
                    $fgValueStatement[$productId]['end_balance'][$dateAsIndex] = isset($fgValueStatement[$productId]['end_balance'][$dateAsIndex]) ? $fgValueStatement[$productId]['end_balance'][$dateAsIndex] +  $currentCogsAtDate : $currentEndBalanceAtDate;
                    
                    // $fgStatementValues[$productId][$inventoryItemType]['total_available_manufacturing_expenses'][$dateAsIndex] / $totalAvailableAtDate
                    
                    // $fgStatementValues[$productId][$inventoryItemType]['manufacturing_expenses_per_unit'] =  $this->calculateManufacturingExpensesPerUnit(,)  ;
                
                    //  =  HArr::subtractAtDates($fgStatementValues[$productId][$inventoryItemType]['total_available_manufacturing_expenses'] , $fgStatementValues[$productId][$inventoryItemType]['cogs']) ;
                
                    
                }
                
                // $currentManufacturingExpenseArr = HArr::sumWithNumber($currentManufacturingExpenseArr,$fgBeginningInventoryBreakdownValue);
                
            }
            $product->update([
                'product_manpower_statement'=>$fgStatementValues[$productId]['direct_labor_value']??[],
                'raw_material_statement'=>$fgStatementValues[$productId]['raw_material_value']??[],
                'product_overheads_statement'=>$fgStatementValues[$productId]['manufacturing_overheads_value']??[],
                'product_inventory_value_statement'=>$fgValueStatement
            ]);
        }
        //
        //
        // foreach($fgStatementValues as $productId => $statementArr){
        // 	$prodc
        // 	$productManpowerStatement = $statementArr['direct_labor_value']??[];
            
            
        // }
        // dd('lol',$fgStatementValues);
    }
    public function getFinancialYearEndMonthNumber():int
    {
        $financialYearStartMonthName = $this->financialYearStartMonth();
        if ($financialYearStartMonthName =='january') {
            return 12;
        }
        if ($financialYearStartMonthName =='april') {
            return 3;
        }
        if ($financialYearStartMonthName =='july') {
            return 6;
        }
        
    }
    public function fixedAssetOpeningBalances()
    {
        return $this->hasMany(FixedAssetOpeningBalance::class, 'project_id', 'id');
    }
    public function cashAndBankOpeningBalances():HasMany
    {
        return $this->hasMany(CashAndBankOpeningBalance::class, 'project_id', 'id');
    }
    public function otherDebtorsOpeningBalances():HasMany
    {
        return $this->hasMany(OtherDebtorsOpeningBalance::class, 'project_id', 'id');
    }
    public function supplierPayableOpeningBalances():HasMany
    {
        return $this->hasMany(SupplierPayableOpeningBalance::class, 'project_id', 'id');
    }
    public function otherCreditorsOpeningBalances():HasMany
    {
        return $this->hasMany(OtherCreditsOpeningBalance::class, 'project_id', 'id');
    }
    public function otherLongTermLiabilitiesOpeningBalances():HasMany
    {
        return $this->hasMany(OtherLongTermLiabilitiesOpeningBalance::class, 'project_id', 'id');
    }
    public function equityOpeningBalances():HasMany
    {
        return $this->hasMany(EquityOpeningBalance::class, 'project_id', 'id');
    }
    public function longTermLoanOpeningBalances():HasMany
    {
        return $this->hasMany(LongTermLoanOpeningBalance::class, 'project_id', 'id');
    }
    public function calculateFixedAssetOpeningBalances()
    {
        $fixedAssetOpeningBalances  = $this->fixedAssetOpeningBalances;
        $operationDates  = array_values($this->getOperationDatesAsDateAndDateAsIndex());
		 DB::table('product_expense_allocations')->where('is_opening_depreciation', 1)->where('project_id', $this->id)->delete();
	$productExpensesForAllFixedAssets = [];
        foreach ($fixedAssetOpeningBalances as $fixedAssetOpeningBalance) {
            $openingId  = $fixedAssetOpeningBalance->id ;
            $monthlyDepreciation = $fixedAssetOpeningBalance->getMonthlyDepreciation();
            $allocationPercentage = $fixedAssetOpeningBalance->getProductAllocations();
            $monthlyCount = $fixedAssetOpeningBalance->getMonthlyCounts();
            for ($i = 0 ; $i<= $monthlyCount ; $i++) {
                $currentDateAsIndex = $operationDates[$i]??null;
                if (!is_null($currentDateAsIndex)) {
                    $result[$openingId][$currentDateAsIndex] = $monthlyDepreciation  ;
                }
            }
            $adminDepreciationPercentage = $fixedAssetOpeningBalance->getAdminDepreciationPercentage();
            $manufacturingDepreciationPercentage = $fixedAssetOpeningBalance->getManufacturingDepreciationPercentage();
            $adminAllocationPercentages = [];
            $manufacturingAllocationPercentages = [];
            foreach ($result as $openingId => $dateIndexWithMonthlyDepreciation) {
                $adminAllocationPercentages[$openingId]=HArr::MultiplyWithNumber($dateIndexWithMonthlyDepreciation, $adminDepreciationPercentage/100);
                $manufacturingAllocationPercentages[$openingId]=HArr::MultiplyWithNumber($dateIndexWithMonthlyDepreciation, $manufacturingDepreciationPercentage/100);
            }
            $productExpenses = [];
            foreach ($manufacturingAllocationPercentages as $openingId => $dateAsIndexAndValue) {
                foreach ($allocationPercentage as $productId => $percentage) {
                    foreach ($dateAsIndexAndValue as $dateIndex => $value) {
                        $productExpenses[$openingId][$productId][$dateIndex] = $value  * $percentage/100;
                    }
                }
            }
            // foreach($productExpenses as $openingId => $productIdWithArr){
			// 	foreach($productIdWithArr as $productId => $dateAsIndexWithValue){
			// 		foreach($dateAsIndexWithValue as $dateAsIndex => $value){
			// 			$productExpensesForAllFixedAssets[][$productId][$dateIndex] = isset($productExpensesForAllFixedAssets[$dateIndex]) ? $productExpensesForAllFixedAssets[$dateIndex] + $value : $value;
			// 		}
			// 	}
			// }
			
        }
		$finalResult =[];
		foreach($productExpenses as $fixedAssetOpeningBalanceId => $hisProductAllocations){
			$fixedAssetOpeningBalance  = FixedAssetOpeningBalance::find($fixedAssetOpeningBalanceId);
			foreach($hisProductAllocations as $productId => $dateAndValues){
				$finalResult[] = [
					'product_id'=>$productId,
					'expense_name'=>$fixedAssetOpeningBalance->getName() ,
					'is_opening_depreciation'=>1 ,
					'is_expense'=>0,
					'is_depreciation'=>0,
					'payload'=>json_encode($dateAndValues),
					'project_id'=>$this->id
				];
			}
		}
	
		DB::table('product_expense_allocations')->insert($finalResult);
        
    }
    public function recalculateOpeningBalances()
    {
        $this->calculateFixedAssetOpeningBalances();
        
    }
}
