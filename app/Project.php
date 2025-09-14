<?php

namespace App;

use App\Helpers\HArr;
use App\ReadyFunctions\CalculateFixedLoanAtEndService;
use App\ReadyFunctions\FixedAssetsPayableEndBalance;
use App\ReadyFunctions\ProjectsUnderProgress;
use App\Traits\HasBasicStoreRequest;
use App\Traits\HasCollectionOrPaymentStatement;
use App\Traits\HasFixedAsset;
use App\Traits\HasIndexedDates;
use App\Traits\HasManpowerExpense;
use App\Traits\Redirects;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use MathPHP\Finance;

class Project extends Model
{
    protected $connection = 'mysql';
    use SoftDeletes,HasBasicStoreRequest,HasIndexedDates,HasManpowerExpense,HasFixedAsset,HasCollectionOrPaymentStatement;
    
    public function setMonthAttribute($value)
    {
        return ;
    }
    public function setYearAttribute($value)
    {
        return ;
    }
    protected $casts = [
        'manpower_allocations'=>'array',
        'extended_study_dates'=>'array',
        'vat_statements'=>'array',
        'corporate_taxes_statement'=>'array'
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
		// direct_labor
		// manufacturing_overheads
		$totalIndirectManpowers = [];
        $operationDates = $this->getOperationDatesAsDateAndDateAsIndex();
		  DB::table('product_expense_allocations')->where('project_id', $this->id)->where('is_indirect_manpower', 1)->delete();
		// product_expense_allocations
        foreach (getManpowerTypes() as $id => $manpowerOptionArr) {
            if ($manpowerOptionArr['has_allocation']) {
				$isIndirectManpower = $manpowerOptionArr['is_indirect_manpower'];
                $allocationColumnName= $manpowerOptionArr['allocation_column_name'];
                $salaryExpenses = ManPower::where('type', $id)->where('project_id', $this->id)->pluck('salary_expenses')->toArray();
                $allocationForManpowerType = $this->getManpowerAllocationForType($id);
				
                $totalManpowerSalaries = HArr::sumAtDates($salaryExpenses, $operationDates);
                foreach ($allocationForManpowerType as $productId => $allocationPercentage) {
					$product = Product::find($productId);
				
						$currentValue = HArr::MultiplyWithNumber($totalManpowerSalaries, $allocationPercentage/100) ;
						if($isIndirectManpower){
							$totalIndirectManpowers[$id][$productId]= $currentValue ; 
						}
                    $product->update([
                        $allocationColumnName => $currentValue 
                    ]);
                    
                }
                
            }
        }
		foreach($totalIndirectManpowers as $typeId => $productIdWithValues){
			foreach($productIdWithValues as $productId => $payload){
				DB::table('product_expense_allocations')->insert([
						'project_id'=>$this->id ,
						'product_id'=>$productId,
						'expense_name'=>$typeId,
						'is_expense'=>0,
						'is_depreciation'=>0 ,
						'is_indirect_manpower'=>1 ,
						'payload'=>json_encode($payload)
					]);
			}
			
			
		}
				
		$this->recalculateAllProductsOverheadExpenses();
        
    }
	public function expenseHasAllocation(string $expenseTypeId):bool
	{
		return $this->getExpenseCategories()[$expenseTypeId]['has_allocation'];
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
            $ffeEquityPayment = $fixedAssetCalculationResultArr['ffeEquityPayment']['FFE Equity Injection'] ?? [];
            $ffeLoanWithdrawal = $fixedAssetCalculationResultArr['ffeLoanWithdrawal']['FFE Loan Withdrawal'] ?? [];
            $ffePayment = $fixedAssetCalculationResultArr['contractPayments']['FFE Payment'] ?? [];
        
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
            $ffeLoanWithdrawalEndBalance = $fixedAssetCalculationResultArr['ffeLoanWithdrawalEndBalance'];
			// dd($ffeLoanWithdrawalEndBalance);
            $projectUnderProgressFFE = $projectUnderProgressService->calculateForFFE($fixedAsset->getStartDateAsIndex(), $fixedAsset->getEndDateAsIndex(), $ffeExecutionAndPayment, $ffeLoanInterestAmounts, $ffeLoanWithdrawalInterestAmounts, $this, $operationStartDateAsIndex, $datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber);
    
        
            $transferredDateForFFEAsIndex = array_key_last($projectUnderProgressFFE['transferred_date_and_vales']??[]);
            $ffeAssetItems = [];
            //   $totalOfFFEItemForFFE = [];
            // if($fixedAsset ){
            $ffeAssetItems = $fixedAsset->calculateFFEAssetsForFFE($transferredDateForFFEAsIndex, Arr::last($projectUnderProgressFFE['transferred_date_and_vales']??[], null, 0), $studyDates, $studyEndDateAsIndex, $this);
            
            $fixedAssetAddition = $ffeAssetItems['additions']??[];
            
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
            
            
            $datesAsStringAndIndex = $this->getDatesAsStringAndIndex();
            $dateIndexWithDate = $this->getDateIndexWithDate();
            $ffeAcquisitionDatesAndAmounts =  $fixedAsset->getFfeExecutionAndPayment();
            $ffeAcquisitionDatesAndAmounts = $this->convertArrayOfIndexKeysToIndexAsDateStringWithItsOriginalValue($ffeAcquisitionDatesAndAmounts, $datesAsStringAndIndex);
            $ffeAcquisitionPayments = $fixedAsset->getFfePayment() ;
            $ffePayable = [];
            if (count($ffeAcquisitionDatesAndAmounts)) {
				$ffePayable=(new FixedAssetsPayableEndBalance())->calculateEndBalance($ffeAcquisitionDatesAndAmounts, $ffeAcquisitionPayments, $dateIndexWithDate);
                $ffePayable = $ffePayable['monthly']['end_balance'] ?? [];
            }
			// dd($ffeLoanWithdrawalEndBalance);
            $fixedAsset->update([
                'capitalization_statement'=>$projectUnderProgressFFE,
                'depreciation_statement'=>$ffeAssetItems,
                'admin_depreciations'=>$adminDepreciations,
                'ffe_equity_payment'=>$ffeEquityPayment,
                'ffe_loan_withdrawal'=>$ffeLoanWithdrawal,
				'ffe_loan_withdrawal_end_balance'=>$ffeLoanWithdrawalEndBalance,
                'ffe_payment'=>$ffePayment,
                'statement'=>$ffeAssetItems,
                'ffe_execution_and_payment'=>$ffeExecutionAndPayment,
                'ffe_payable'=>$ffePayable
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
        }
        $this->recalculateAllProductsOverheadExpenses();
        
    }
    public function recalculateAllProductsOverheadExpenses()
    {
        DB::table('products')->where('project_id', $this->id)->update([
            'product_overheads_allocation'=>null
        ]);
        $productExpenseAllocations = DB::table('product_expense_allocations')->where('project_id', $this->id)->get();
        $result = [];
        $depreciationResult = [];
        foreach ($productExpenseAllocations as $index => $productExpenseAllocation) {
			$productId = $productExpenseAllocation->product_id;
            $payload = $productExpenseAllocation->payload;
            $payload = (array)json_decode($payload);
            $isDepreciation = $productExpenseAllocation->is_depreciation || $productExpenseAllocation->is_opening_depreciation;
			
            foreach ($payload as $dateAsIndex => $value) {
                $result[$productId][$dateAsIndex] = isset($result[$productId][$dateAsIndex]) ? $result[$productId][$dateAsIndex] + $value : $value ;
                if ($isDepreciation) {
                    $depreciationResult[$productId][$dateAsIndex] = isset($depreciationResult[$productId][$dateAsIndex]) ? $depreciationResult[$productId][$dateAsIndex] + $value : $value ;
                }
            }
        }
		
		
		
        foreach ($result as $productId => $totals) {
            DB::table('products')->where('id', $productId)->update([
                'product_overheads_allocation'=>json_encode($totals),
                'product_depreciation_allocation'=> isset($depreciationResult[$productId]) ? json_encode($depreciationResult[$productId]) : null
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
		$allKeys = [
			'direct_labor_value'=>[],
			'raw_material_value'=>[],
			'manufacturing_overheads_value'=>[],
			'product_depreciation_allocation'=>[]
		];
        $columnNameMapping = [
            'direct_labor_value'=>'product_manpower_allocation',
            'raw_material_value'=>'product_raw_material_consumed',
            'manufacturing_overheads_value'=>'product_overheads_allocation',
            'product_depreciation_allocation'=>'product_depreciation_allocation'
        ];
        foreach ($products as $product) {
            $productId = $product->id ;
            $quantityStatement = $product->product_inventory_qt_statement;
            $totalAvailableQuantity = $quantityStatement['total_quantity_available']??[];
            $salesQuantity = $quantityStatement['sales_quantity']??[];
            $fgBeginningInventoryBreakdowns = count($product->getFgBeginningInventoryBreakdowns()) ? $product->getFgBeginningInventoryBreakdowns() : $columnNameMapping  ;
		
            $fgBeginningInventoryBreakdowns['product_depreciation_allocation'] = [];

            foreach ($fgBeginningInventoryBreakdowns as $inventoryItemType => $inventoryItemValues) {
                $fgBeginningInventoryBreakdownValue =  $inventoryItemValues['value']??0	;
                $currentColumnMapping = $columnNameMapping[$inventoryItemType];
                $currentManufacturingExpenseArr = $currentColumnMapping == 'product_raw_material_consumed' ?  ($product->{$currentColumnMapping}['total']??[]) :   (array)$product->{$currentColumnMapping} ;
				ksort($currentManufacturingExpenseArr);
             
                foreach ($currentManufacturingExpenseArr as $dateAsIndex => $currentManufacturingExpenseVal) {
                    $fgStatementValues[$productId][$inventoryItemType]['beginning_value'][$dateAsIndex] = $fgBeginningInventoryBreakdownValue;
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
				
                    $fgBeginningInventoryBreakdownValue = $currentEndBalanceAtDate;
                    
                    if ($inventoryItemType =='product_depreciation_allocation') {
                        $currentCogsAtDate = 0 ;
                        $currentEndBalanceAtDate = 0 ;
                    }
                    $fgValueStatement[$productId]['cogs'][$dateAsIndex] = isset($fgValueStatement[$productId]['cogs'][$dateAsIndex]) ? $fgValueStatement[$productId]['cogs'][$dateAsIndex] +  $currentCogsAtDate : $currentCogsAtDate ;
                    $fgValueStatement[$productId]['end_balance'][$dateAsIndex] = isset($fgValueStatement[$productId]['end_balance'][$dateAsIndex]) ? $fgValueStatement[$productId]['end_balance'][$dateAsIndex] +  $currentEndBalanceAtDate : $currentEndBalanceAtDate;
                    
                }
                // $currentManufacturingExpenseArr = HArr::sumWithNumber($currentManufacturingExpenseArr,$fgBeginningInventoryBreakdownValue);
                
            }
			
            $product->update([
				'product_manpower_statement'=>$fgStatementValues[$productId]['direct_labor_value']??[],
                'raw_material_statement'=>$fgStatementValues[$productId]['raw_material_value']??[],
                'product_overheads_statement'=>$fgStatementValues[$productId]['manufacturing_overheads_value']??[],
                'product_depreciation_statement'=>$fgStatementValues[$productId]['product_depreciation_allocation']??[],
                'product_inventory_value_statement'=>$fgValueStatement[$productId]??[]
            ]);
        }
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
    public function vatAndCreditWithholdTaxesOpeningBalances():HasMany
    {
        return $this->hasMany(VatAndCreditWithholdTaxOpeningBalance::class, 'project_id', 'id');
    }
    public function getVatOpeningBalanceAmount():float
    {
        $vatOpening = $this->vatAndCreditWithholdTaxesOpeningBalances->first();
        return $vatOpening ? $vatOpening->getVatAmount() : 0 ;
    }
    public function getCreditWithholdOpeningBalanceAmount():float
    {
        $vatOpening = $this->vatAndCreditWithholdTaxesOpeningBalances->first();
        return $vatOpening ? $vatOpening->getCreditWithholdTaxes() : 0 ;
    }
    public function longTermLoanOpeningBalances():HasMany
    {
        return $this->hasMany(LongTermLoanOpeningBalance::class, 'project_id', 'id');
    }
    public function calculateFixedAssetOpeningBalances()
    {
        $fixedAssetOpeningBalances  = $this->fixedAssetOpeningBalances;
		    $datesAsStringAndIndex = $this->getDateWithDateIndex();
        $operationStartDateFormatted = $this->getOperationStartDateFormatted();
		$operationStartDateAsIndex = $this->getOperationStartDateAsIndex($datesAsStringAndIndex,$operationStartDateFormatted);
	
        $operationDates  = array_values($this->getOperationDatesAsDateAndDateAsIndex());
        DB::table('product_expense_allocations')->where('is_opening_depreciation', 1)->where('project_id', $this->id)->delete();
        $productExpensesForAllFixedAssets = [];
        foreach ($fixedAssetOpeningBalances as $fixedAssetOpeningBalance) {
            $openingId  = $fixedAssetOpeningBalance->id ;
            $monthlyDepreciation = $fixedAssetOpeningBalance->getMonthlyDepreciation();
            $allocationPercentage = $fixedAssetOpeningBalance->getProductAllocations();
            $monthlyCount = $fixedAssetOpeningBalance->getMonthlyCounts();
            for ($i = $operationStartDateAsIndex ; $i<= $monthlyCount ; $i++) {
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
                        $productExpenses[$openingId][$productId][$dateIndex] = $value  * ($percentage/100);
                    }
                }
            }
            
        }
        $finalResult =[];
        foreach ($productExpenses as $fixedAssetOpeningBalanceId => $hisProductAllocations) {
            $fixedAssetOpeningBalance  = FixedAssetOpeningBalance::find($fixedAssetOpeningBalanceId);
            $adminDepreciation = $adminAllocationPercentages[$fixedAssetOpeningBalanceId]??[];
            $manufacturingDepreciation = $manufacturingAllocationPercentages[$fixedAssetOpeningBalanceId]??[] ;
        
            $monthlyAccumulatedDepreciations = HArr::sumAtDates([$adminDepreciation,$manufacturingDepreciation]) ;
            $monthlyAccumulatedDepreciations[0] = ($monthlyAccumulatedDepreciations[0]??0) +  $fixedAssetOpeningBalance->getAccumulatedDepreciation();
            $monthlyAccumulatedDepreciations = HArr::accumulateArray($monthlyAccumulatedDepreciations);
            $fixedAssetOpeningBalance->update([
                'admin_depreciations'=>$adminDepreciation	,
                'manufacturing_depreciations'=>$manufacturingDepreciation,
                'monthly_accumulated_depreciations'=>$monthlyAccumulatedDepreciations
            ]);
            foreach ($hisProductAllocations as $productId => $dateAndValues) {
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
    public function getViewVars():array
    {
        $step_data =(new Redirects)->steps($this, 'edit');
        $this->month = isset($this->start_date) ? date("m", strtotime($this->start_date)) : null;
        $this->year = isset($this->start_date) ? date("Y", strtotime($this->start_date)) : null;
        $products  = $this->products ;
        $rawMaterials  = $this->rawMaterials ;
        $sectors = BusinessSector::all();
        $duration_data =  $this->duration ;
        $start_date = $this->start_date ;
        $max_length = 7;
        return [
            'project'=>$this,
            'sectors'=>$sectors,
            'step_data'=>$step_data,
            'products'=>$products,
            'rawMaterials'=>$rawMaterials,
            'duration_data'=>$duration_data,
            'start_date'=>$start_date,
            'max_length'=>$max_length
        ];
    }
    public function getRawMaterialViewVars():array
    {
        $uniqueDistributedRawMaterialNames =RawMaterial::where('project_id', $this->id)->has('products')->get();
        $currentStepNumber = 3 ;
        $totalSteps = getTotalSteps() ;
        
        return [
            'project'=>$this ,
            'uniqueDistributedRawMaterialNames'=>$uniqueDistributedRawMaterialNames,
            'currentStepNumber'=>$currentStepNumber,
            'totalSteps'=>$totalSteps,
        ];
        
    }
    public function getManpowerViewVars()
    {
        $step_data =(new Redirects)->steps($this, 'manPower');
        $years  = $this->getYears();
        $products = $this->products;
        $manpowers = $this->manpowers;
        return
        ['years'=>$years,'project'=>$this,'manpowers'=>$manpowers,'step_data'=>$step_data,'products'=>$products];
        
    }
    public function getExpensesViewVars():array
    {
        $step_data =(new Redirects)->steps($this, 'expenses');
        $years  = $this->getYears();
        $expenses = $this->expenses;
        $products = $this->products;
        return ['project'=>$this,'years'=>$years,'expenses'=>$expenses,'step_data'=>$step_data,'products'=>$products];
    }
    public function getFixedAssetsViewVars():array
    {
        $step_data =(new Redirects)->steps($this, 'assets');
        $fixedAssets = $this->fixedAssets;
        $products = $this->products;
        $years =$this->getYears();
        return ['project'=>$this,'years'=>$years,'fixedAssets'=>$fixedAssets,'step_data'=>$step_data,'products'=>$products];
        
    }
    public function getOpeningBalancesViewVars():array
    {
        $step_data =(new Redirects)->steps($this, 'openingBalances');
        $fixedAssetOpeningBalances = $this->fixedAssetOpeningBalances;
        $cashAndBankOpeningBalances = $this->cashAndBankOpeningBalances;
        $otherDebtorsOpeningBalances = $this->otherDebtorsOpeningBalances;
        $supplierPayableOpeningBalances = $this->supplierPayableOpeningBalances;
        $otherCreditorsOpeningBalances = $this->otherCreditorsOpeningBalances;
        $vatAndCreditWithholdTaxesOpeningBalances = $this->vatAndCreditWithholdTaxesOpeningBalances;
        $otherLongTermLiabilitiesOpeningBalances = $this->otherLongTermLiabilitiesOpeningBalances;
        $equityOpeningBalances = $this->equityOpeningBalances;
        $longTermLoanOpeningBalances = $this->longTermLoanOpeningBalances;
        $products = $this->products;
        $years =$this->getYears();
        return ['project'=>$this,'vatAndCreditWithholdTaxesOpeningBalances'=>$vatAndCreditWithholdTaxesOpeningBalances,'years'=>$years,'longTermLoanOpeningBalances'=>$longTermLoanOpeningBalances,'equityOpeningBalances'=>$equityOpeningBalances,'otherLongTermLiabilitiesOpeningBalances'=>$otherLongTermLiabilitiesOpeningBalances,'otherCreditorsOpeningBalances'=>$otherCreditorsOpeningBalances,'supplierPayableOpeningBalances'=>$supplierPayableOpeningBalances,'otherDebtorsOpeningBalances'=>$otherDebtorsOpeningBalances,'fixedAssetOpeningBalances'=>$fixedAssetOpeningBalances,'cashAndBankOpeningBalances'=>$cashAndBankOpeningBalances,'step_data'=>$step_data,'products'=>$products];
        
    }
    public function calculateIncomeStatement():array
    {
        $project = $this;
        $step_data =(new Redirects)->steps($project, 'assets');
        $products = $project->products;
        $financialYearEndMonthNumber = '12';
        $defaultNumericInputClasses = [
            'number-format-decimals'=>0,
            'is-percentage'=>false,
            'classes'=>'repeater-with-collapse-input',
            'formatted-input-classes'=>'custom-input-numeric-width ',
        ];
        $defaultPercentageInputClasses = [
            'classes'=>'',
            'formatted-input-classes'=>'',
            'is-percentage'=>true ,
            'number-format-decimals'=> 2,
        ];
        $defaultClasses = [
            $defaultNumericInputClasses,
            $defaultPercentageInputClasses
        ];
        $orderIndexPerExpenseCategory = [
            'sales-revenue'=>0,
            'cost-of-service'=>1 ,
            'gross-profit'=>2 ,
            'other-operation-expense'=>3,
            'marketing'=>4 ,
            'sales'=>5,
            'general-expenses'=>6,
            'ebitda'=>7,
            'depreciation'=>8,
            'ebit'=>9,
            'finance-expense'=>10,
            'ebt'=>11,
            'corporate-taxes'=>12,
            'net-profit'=>13
        ];
        // $financialYearsEndMonths = $project->getFinancialYearsEndMonths();
        
        
        
        
        $salesRevenueOrderIndex = $orderIndexPerExpenseCategory['sales-revenue'];
        $costOfServiceOrderIndex = $orderIndexPerExpenseCategory['cost-of-service'];
        $grossProfitOrderIndex = $orderIndexPerExpenseCategory['gross-profit'];
        $salesOrderIndex = $orderIndexPerExpenseCategory['sales'];
        $ebitdaOrderIndex = $orderIndexPerExpenseCategory['ebitda'];
        $depreciationOrderIndex = $orderIndexPerExpenseCategory['depreciation'];
        $financeExpenseExpenseOrderIndex = $orderIndexPerExpenseCategory ['finance-expense'];
        $ebitOrderIndex = $orderIndexPerExpenseCategory['ebit'];
        $ebtOrderIndex = $orderIndexPerExpenseCategory['ebt'];
        $corporateTaxesOrderIndex = $orderIndexPerExpenseCategory['corporate-taxes'];
        $netProfitOrderIndex = $orderIndexPerExpenseCategory['net-profit'];
        
        $studyMonthsForViews = array_flip($project->getOperationDatesAsDateAndDateAsIndexToStudyEndDate());
        $sumKeys = array_keys($studyMonthsForViews);
        
        
        
        
        
        
        /**
         * * First Tab Sales Revenue
        */
        $tableDataFormatted[$salesRevenueOrderIndex]['main_items']['sales-revenue']['options'] = array_merge([
           'title'=>__('Sales Revenue')
        ], $defaultNumericInputClasses);
        $yearWithItsMonths=$project->getYearIndexWithItsMonthsAsIndexAndString();
        $tableDataFormatted[$salesRevenueOrderIndex]['main_items']['growth-rate']['options'] = array_merge($defaultPercentageInputClasses, ['title'=>__('Growth Rate %')]);
        $productsTotals = [];
        foreach ($products as $product) {
            $tableDataFormatted[$salesRevenueOrderIndex]['sub_items'][$product->getName()]['options'] =array_merge([
                'title'=>$product->getName(),
            ], $defaultNumericInputClasses);
            $monthlySalesTargetValues = $product->monthly_sales_target_values?:[];
            $tableDataFormatted[$salesRevenueOrderIndex]['sub_items'][$product->getName()]['data'] = $monthlySalesTargetValues;
            $tableDataFormatted[$salesRevenueOrderIndex]['sub_items'][$product->getName()]['year_total'] = HArr::sumPerYearIndex($monthlySalesTargetValues, $yearWithItsMonths);
            $productsTotals = HArr::sumAtDates([$monthlySalesTargetValues?:[],$productsTotals], $sumKeys);
        }
        
        $tableDataFormatted[$salesRevenueOrderIndex]['main_items']['sales-revenue']['data'] = $productsTotals;
        $tableDataFormatted[$salesRevenueOrderIndex]['main_items']['sales-revenue']['year_total'] = $salesRevenueYearTotal = HArr::sumPerYearIndex($productsTotals, $yearWithItsMonths);
        $tableDataFormatted[$salesRevenueOrderIndex]['main_items']['growth-rate']['data'] = HArr::calculateGrowthRate($productsTotals);
        $tableDataFormatted[$salesRevenueOrderIndex]['main_items']['growth-rate']['year_total'] = $annuallySalesRevenueGrowthRates = HArr::calculateGrowthRate($salesRevenueYearTotal);
        
        /**
         * * Second Tab Cost Of Goods Sold
         */
        
        
        $tableDataFormatted[$costOfServiceOrderIndex]['main_items']['cost-of-goods-sold']['options'] = array_merge([
           'title'=>__('Cost Of Goods Sold')
        ], $defaultNumericInputClasses);
        
        
        $tableDataFormatted[$costOfServiceOrderIndex]['main_items']['revenues-percentage']['options'] = array_merge([
            'title'=>__('%/Revenues')
        ], $defaultPercentageInputClasses);
        $products = DB::table('products')->where('project_id', $project->id)->get();
        // $rawMaterialStatements  = $products->pluck('raw_material_statement');
        // $manpowerStatement   = $products->pluck('product_manpower_statement');
        $totalCogs = [
            'raw_material'=>[],
            'direct_labor'=>[],
            'manufacturing-overheads'=>[]
        ];
    
        foreach ($products as $product) {
            
            $rawMaterialCogs = $product->raw_material_statement ? (array)(@json_decode($product->raw_material_statement)->cogs) : [];
            $directLaborCogs =$product->product_manpower_statement ?  (array)(@json_decode($product->product_manpower_statement)->cogs) : [] ;
            $manufacturingCogs = $product->product_overheads_statement ? (array)(@json_decode($product->product_overheads_statement)->cogs) : [] ;
            $totalCogs['raw_material'] = HArr::sumAtDates([$rawMaterialCogs,$totalCogs['raw_material']], $sumKeys);
            $totalCogs['direct_labor'] = HArr::sumAtDates([$directLaborCogs,$totalCogs['direct_labor']], $sumKeys);
            $totalCogs['manufacturing-overheads'] = HArr::sumAtDates([$manufacturingCogs,$totalCogs['manufacturing-overheads']], $sumKeys);
            
        }
    
        $totalCogsPerYear = [];
        $totalCogsPercentageOfRevenues = [];
        foreach (['raw_material'=>__('Raw Materials') , 'direct_labor'=>__('Direct Labors') , 'manufacturing-overheads'=>__('Manufacturing Overheads')] as $id => $title) {
            
            $tableDataFormatted[$costOfServiceOrderIndex]['sub_items'][$id]['options'] =array_merge([
                'title'=>$title,
            ], $defaultNumericInputClasses);
            
            $currentTotalCogs = $totalCogs[$id]??[];
            $tableDataFormatted[$costOfServiceOrderIndex]['sub_items'][$id]['data'] = $currentTotalCogs ;
            $tableDataFormatted[$costOfServiceOrderIndex]['sub_items'][$id]['year_total'] = $currentTotalPerYear = HArr::sumPerYearIndex($currentTotalCogs, $yearWithItsMonths);
            $totalCogsPerYear[$id] = array_values($currentTotalPerYear);
            $totalCogsPercentageOfRevenues[$id]  = array_values(HArr::calculatePercentageOf($salesRevenueYearTotal, $currentTotalPerYear)) ;
        }
        $totalCostOfGoodsSold = HArr::sumAtDates(array_values($totalCogs), $sumKeys) ;
        $tableDataFormatted[$costOfServiceOrderIndex]['main_items']['cost-of-goods-sold']['data'] =  $totalCostOfGoodsSold ;
        $tableDataFormatted[$costOfServiceOrderIndex]['main_items']['cost-of-goods-sold']['year_total'] =$costOfGodSoldTotalPerYear = HArr::sumPerYearIndex($totalCostOfGoodsSold, $yearWithItsMonths);
        $tableDataFormatted[$costOfServiceOrderIndex]['main_items']['revenues-percentage']['data'] =  HArr::calculatePercentageOf($productsTotals, $totalCostOfGoodsSold) ;
        $tableDataFormatted[$costOfServiceOrderIndex]['main_items']['revenues-percentage']['year_total'] = HArr::calculatePercentageOf($salesRevenueYearTotal, $costOfGodSoldTotalPerYear);
        
        
    
        
        /**
         * Three Items
        */
        
        $totalGrossProfit = HArr::subtractAtDates([$productsTotals,$totalCostOfGoodsSold], $sumKeys) ;
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['gross-profit']['data'] =  $totalGrossProfit ;
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['gross-profit']['year_total'] = $grossProfitTotalPerYear = HArr::sumPerYearIndex($totalGrossProfit, $yearWithItsMonths);
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['gross-profit']['options']['title'] = __('Gross Profit');
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['% Of Revenue']['data'] =  HArr::calculatePercentageOf($productsTotals, $totalGrossProfit) ;
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['% Of Revenue']['year_total'] = HArr::calculatePercentageOf($salesRevenueYearTotal, $grossProfitTotalPerYear);

        /**
         * * Four Item
         */
        // general-expenses
        // sales
        // marketing
        $tableDataFormatted[$salesOrderIndex]['main_items']['sganda']['options'] = array_merge([
           'title'=>__('Sales, Marketing & General Exp.')
        ], $defaultNumericInputClasses);
        
        $expenses = DB::table('expenses')->whereIn('category_id', ['general-expenses','sales','marketing'])->where('project_id', $project->id)->get();
        $columnName = [
            'expense_as_percentage'=>'expense_as_percentages',
            'fixed_monthly_repeating_amount'=>'monthly_repeating_amounts',
            'one_time_expense'=>'payload.monthly_one_time'
        ];
        $resultPerCategory = [];
        foreach ($expenses as $index=>$expense) {
            $categoryId = $expense->category_id;
            $relationName  = $expense->relation_name;
            $currentColumnName = $columnName[$relationName];
            $amount = $relationName == 'one_time_expense' ? json_decode($expense->payload)->monthly_one_time :  json_decode($expense->{$currentColumnName});
            $currentAmount = (array)$amount ;
            $resultPerCategory[$categoryId] = isset($resultPerCategory[$categoryId]) ? HArr::sumAtDates([$currentAmount,$resultPerCategory[$categoryId]], $sumKeys):$currentAmount ;
        }
        $manpowers = DB::table('manpowers')->whereIn('type', ['sales','general'])->where('project_id', $this->id)->get();
        foreach ($manpowers as $manpower) {
            $categoryId = $manpower->type ;
            if ($categoryId == 'general') {
                $categoryId = 'general-expenses';
            }
            $currentAmount =  (array)json_decode($manpower->salary_expenses);
			
            $resultPerCategory[$categoryId] = isset($resultPerCategory[$categoryId]) ? HArr::sumAtDates([$currentAmount,$resultPerCategory[$categoryId]], $sumKeys):$currentAmount;
        }
        foreach ($this->fixedAssets as $fixedAsset) {
            $currentAmount = $fixedAsset->admin_depreciations;
            $categoryId = 'general-expenses';
            $resultPerCategory[$categoryId] = isset($resultPerCategory[$categoryId]) ? HArr::sumAtDates([$currentAmount,$resultPerCategory[$categoryId]], $sumKeys):$currentAmount;
            
        }
        $openingFixedAssets = DB::table('fixed_asset_opening_balances')->where('project_id', $this->id)->get();
        foreach ($openingFixedAssets as $openingFixedAsset) {
            $currentAmount =(array) json_decode($openingFixedAsset->admin_depreciations?:'');
            $categoryId = 'general-expenses';
            $resultPerCategory[$categoryId] = isset($resultPerCategory[$categoryId]) ? HArr::sumAtDates([$currentAmount,$resultPerCategory[$categoryId]], $sumKeys):$currentAmount;
        }
        $resultTotalPerCategoryPerYear = [];
        $resultTotalPercentagesPerCategoryPerYear = [];
        foreach ($resultPerCategory as $categoryId => $values) {
            
            $currentItems=HArr::sumPerYearIndex($values, $yearWithItsMonths);
            $resultTotalPerCategoryPerYear[$categoryId]=array_values($currentItems);
            $resultTotalPercentagesPerCategoryPerYear[$categoryId]=array_values(HArr::calculatePercentageOf($salesRevenueYearTotal, $currentItems));
        }
        
       
            
        
        $tableDataFormatted[$salesOrderIndex]['main_items']['revenues-percentage']['options'] = array_merge([
            'title'=>__('%/Revenues')
        ], $defaultPercentageInputClasses);
        foreach (['sales'=>__('Sales Expenses') , 'marketing'=>__('Marketing Expenses') , 'general-expenses'=>__('General Expenses')] as $id => $title) {
            $orderId = $orderIndexPerExpenseCategory[$id];
            $tableDataFormatted[$salesOrderIndex]['sub_items'][$id]['options'] =array_merge([
                'title'=>$title,
            ], $defaultNumericInputClasses);
            $currentTotalForCategory = $resultPerCategory[$id] ?? [];
            $tableDataFormatted[$salesOrderIndex]['sub_items'][$id]['data'] =$currentTotalForCategory;
            $tableDataFormatted[$salesOrderIndex]['sub_items'][$id]['year_total'] = HArr::sumPerYearIndex($currentTotalForCategory, $yearWithItsMonths);
        }
        $totalSGANDA = HArr::sumAtDates(array_values($resultPerCategory), $sumKeys);
        $tableDataFormatted[$salesOrderIndex]['main_items']['sganda']['data'] =$totalSGANDA;
        $tableDataFormatted[$salesOrderIndex]['main_items']['sganda']['year_total'] =$sgandaTotalPerYear = HArr::sumPerYearIndex($totalSGANDA, $yearWithItsMonths);
        $tableDataFormatted[$salesOrderIndex]['main_items']['revenues-percentage']['data'] =  HArr::calculatePercentageOf($productsTotals, $totalSGANDA) ;
        $tableDataFormatted[$salesOrderIndex]['main_items']['revenues-percentage']['year_total'] = HArr::calculatePercentageOf($salesRevenueYearTotal, $sgandaTotalPerYear);
          
          
        /**
         * * Five Item
         */
          
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['ebitda']['options']['title'] = __('EBITDA');
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $depreciationCogs =DB::table('products')->where('project_id', $project->id)->pluck('product_depreciation_statement')->toArray();
        $formattedDepreciationCogs = [];
        foreach ($depreciationCogs as $index => $depreciationCogArr) {
            $formattedDepreciationCogs[$index] = isset(json_decode($depreciationCogArr)->cogs) ?  (array)json_decode($depreciationCogArr)->cogs : [];
        }
        $formattedDepreciationCogs = HArr::sumAtDates($formattedDepreciationCogs, $sumKeys);
        $fixedAssetAdminDepreciations = DB::table('fixed_assets')->where('project_id', $project->id)->pluck('admin_depreciations')->toArray();
        array_walk($fixedAssetAdminDepreciations, function (&$value) {
            $value = (array)json_decode($value);
        });
        $fixedAssetOpeningBalancesAdminDepreciations = DB::table('fixed_asset_opening_balances')->where('project_id', $project->id)->pluck('admin_depreciations')->toArray();
        array_walk($fixedAssetOpeningBalancesAdminDepreciations, function (&$value) {
            $value = (array)json_decode($value);
        });
        $totalFixedAssetAdminDepreciation = HArr::sumAtDates(array_merge($fixedAssetAdminDepreciations, $fixedAssetOpeningBalancesAdminDepreciations), $sumKeys);
        $totalDepreciation = HArr::sumAtDates([$formattedDepreciationCogs,$totalFixedAssetAdminDepreciation], $sumKeys);
        $sum = HArr::sumAtDates([$totalDepreciation,$totalGrossProfit], $sumKeys);
        $editda = HArr::subtractAtDates([$sum,$totalSGANDA], $sumKeys) ;
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['ebitda']['data'] = $editda;
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['ebitda']['year_total'] =$ebitdaTotalPerYear= HArr::sumPerYearIndex($editda, $yearWithItsMonths);
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['% Of Revenue']['data'] =  HArr::calculatePercentageOf($productsTotals, $editda);
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['% Of Revenue']['year_total'] = $editdaRevenuePercentage = HArr::calculatePercentageOf($salesRevenueYearTotal, $ebitdaTotalPerYear);
        /**
         * * End Five Item
         */
        
        
        /**
         * * Start Sixth Item
         */
        $tableDataFormatted[$depreciationOrderIndex]['main_items']['total-depreciation']['options']['title'] = __('Total Depreciation');
        $tableDataFormatted[$depreciationOrderIndex]['main_items']['total-depreciation']['data'] = $totalDepreciation ;
        $tableDataFormatted[$depreciationOrderIndex]['main_items']['total-depreciation']['year_total'] =$totalDepreciationTotalPerYear = HArr::sumPerYearIndex($totalDepreciation, $yearWithItsMonths);
        $tableDataFormatted[$depreciationOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[$depreciationOrderIndex]['main_items']['% Of Revenue']['data'] = HArr::calculatePercentageOf($productsTotals, $totalDepreciation);
        $tableDataFormatted[$depreciationOrderIndex]['main_items']['% Of Revenue']['year_total'] = HArr::calculatePercentageOf($salesRevenueYearTotal, $totalDepreciationTotalPerYear);
        /**
         * * End  Sixth Item
         */
        
        
        /**
         * * Start Sixth Item
         */
        $tableDataFormatted[$ebitOrderIndex]['main_items']['ebit']['options']['title'] = __('EBIT');
        $ebit = HArr::subtractAtDates([$totalGrossProfit,$totalSGANDA], $sumKeys) ;
        $tableDataFormatted[$ebitOrderIndex]['main_items']['ebit']['data'] = $ebit ;
        $tableDataFormatted[$ebitOrderIndex]['main_items']['ebit']['year_total'] =$ebitTotalPerYear= HArr::sumPerYearIndex($ebit, $yearWithItsMonths);
        $tableDataFormatted[$ebitOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[$ebitOrderIndex]['main_items']['% Of Revenue']['data'] = HArr::calculatePercentageOf($productsTotals, $ebit);
        $tableDataFormatted[$ebitOrderIndex]['main_items']['% Of Revenue']['year_total'] = $grossProfitRevenuePercentages =$editRevenuePercentage= HArr::calculatePercentageOf($salesRevenueYearTotal, $ebitTotalPerYear);
        /**
         * * End  Sixth Item
         */
        
        
        
        
        /**
         * * Start Seven Item
         */
        
        $tableDataFormatted[$financeExpenseExpenseOrderIndex]['main_items']['finance_exp']['options'] = array_merge([
           'title'=>__('Finance Expense')
        ], $defaultNumericInputClasses);
        $loanSchedulePayments = DB::table('loan_schedule_payments')->where('loan_schedule_payments.project_id', $project->id)->join('fixed_assets', 'fixed_assets.id', '=', 'fixed_asset_id')->selectRaw('interestAmount,name')->get();
        $openingLoans = DB::table('long_term_loan_opening_balances')->where('project_id', $project->id)->pluck('interests')->toArray();
        $openingLoansTotal=[];
        foreach ($openingLoans as $openingLoanInterest) {
            $openingLoansTotal= HArr::sumAtDates([(array)json_decode($openingLoanInterest),$openingLoansTotal], $sumKeys);
        }
        // $loanSchedulePaymentsFormatted = [];
        foreach ($loanSchedulePayments as $loanSchedulePayment) {
            $tableDataFormatted[$financeExpenseExpenseOrderIndex]['sub_items'][$loanSchedulePayment->name]['options'] =array_merge([
               'title'=>__('Interest Expense') . ' '.$loanSchedulePayment->name,
            ], $defaultNumericInputClasses);
            $currentInterestAmounts = (array)json_decode($loanSchedulePayment->interestAmount);
            $tableDataFormatted[$financeExpenseExpenseOrderIndex]['sub_items'][$loanSchedulePayment->name]['data'] = $currentInterestAmounts;
            $tableDataFormatted[$financeExpenseExpenseOrderIndex]['sub_items'][$loanSchedulePayment->name]['year_total'] = HArr::sumPerYearIndex($currentInterestAmounts, $yearWithItsMonths);
        }
        if (count($openingLoansTotal)) {
            $tableDataFormatted[$financeExpenseExpenseOrderIndex]['sub_items'][__('Opening Balance Loans Interests')]['data'] = $openingLoansTotal;
            $tableDataFormatted[$financeExpenseExpenseOrderIndex]['sub_items'][__('Opening Balance Loans Interests')]['year_total'] = HArr::sumPerYearIndex($openingLoansTotal, $yearWithItsMonths);
        }
        $totalFinanceExpense = HArr::sumAtDates(array_column($tableDataFormatted[$financeExpenseExpenseOrderIndex]['sub_items']??[], 'data'), $sumKeys);
        $tableDataFormatted[$financeExpenseExpenseOrderIndex]['main_items']['finance_exp']['data'] = $totalFinanceExpense;
        $tableDataFormatted[$financeExpenseExpenseOrderIndex]['main_items']['finance_exp']['year_total'] = $financeExpenseTotalPerYear = HArr::sumPerYearIndex($totalFinanceExpense, $yearWithItsMonths);
        
        $tableDataFormatted[$financeExpenseExpenseOrderIndex]['main_items']['revenues-percentage']['options'] = array_merge([
            'title'=>__('%/Revenues')
        ], $defaultPercentageInputClasses);
        
        $tableDataFormatted[$financeExpenseExpenseOrderIndex]['main_items']['revenues-percentage']['data'] =  HArr::calculatePercentageOf($productsTotals, $totalFinanceExpense) ;
        $tableDataFormatted[$financeExpenseExpenseOrderIndex]['main_items']['revenues-percentage']['year_total'] = HArr::calculatePercentageOf($salesRevenueYearTotal, $financeExpenseTotalPerYear);
        /**
         * * End Seven Item
         */
        
        /**
         * * Start Eight Item
         */
        
        
        $ebt = HArr::subtractAtDates([$ebit,$totalFinanceExpense], $sumKeys);
        $tableDataFormatted[$ebtOrderIndex]['main_items']['ebt']['options']['title'] = __('EBT');
        $tableDataFormatted[$ebtOrderIndex]['main_items']['ebt']['data'] = $ebt;
        $tableDataFormatted[$ebtOrderIndex]['main_items']['ebt']['year_total'] =$ebtTotalPerYear = HArr::sumPerYearIndex($ebt, $yearWithItsMonths);
        $tableDataFormatted[$ebtOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[$ebtOrderIndex]['main_items']['% Of Revenue']['data']=  HArr::calculatePercentageOf($productsTotals, $ebt);
        $tableDataFormatted[$ebtOrderIndex]['main_items']['% Of Revenue']['year_total'] = $ebtRevenuePercentagePerYear= HArr::calculatePercentageOf($salesRevenueYearTotal, $ebtTotalPerYear);
           
        
        /**
         * * End Eight Item
         */
        
            
        /**
         * * Start Nine Item
         */
        
        $corporateTaxesRate = $project->tax_rate/100;
        $annuallyCorporateTaxes =  HArr::MultiplyWithNumberIfPositive($ebt, $corporateTaxesRate);
		$annuallyCorporateTaxes = HArr::sumPerYearIndex($annuallyCorporateTaxes, $yearWithItsMonths);
        $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['corporate-taxes']['options']['title'] = __('Corporate Taxes');
        $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['corporate-taxes']['data'] = $annuallyCorporateTaxes;
        $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['corporate-taxes']['year_total'] = $annuallyCorporateTaxes;
        $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['% Of Revenue']['data']=  [];
        $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['% Of Revenue']['year_total'] = $corporateTaxesRevenuePercentage=HArr::calculatePercentageOf($salesRevenueYearTotal, $annuallyCorporateTaxes);
        
        $totalProductsWithholdAmounts = [];
        foreach ($this->products as $product) {
            $withholdAmounts = $product->getCollectionStatement()['monthly']['withhold_amount']??[];
            $totalProductsWithholdAmounts = HArr::sumAtDates([$withholdAmounts,$totalProductsWithholdAmounts], $sumKeys);
        }
        $dateIndexWithDate = $this->getDateIndexWithDate();
        $calculatedCorporateTaxesPerYear = HArr::sumPerYearIndex($annuallyCorporateTaxes, $yearWithItsMonths) ;
        foreach ($calculatedCorporateTaxesPerYear as $dateIndex => &$value) {
            if ($value < 0) {
                $value =0 ;
            }
        }
        $corporateTaxesPayable = $this->getCorporateTaxesPayable();
        $studyStartDateAsMonthNumber = array_values($this->getDateWithMonthNumber())[0];
        $corporateTaxesStatement  = Project::calculateCorporateTaxesStatement($totalProductsWithholdAmounts, $calculatedCorporateTaxesPerYear, $corporateTaxesPayable, $dateIndexWithDate, $studyStartDateAsMonthNumber);
        $this->update([
            'corporate_taxes_statement'=>$corporateTaxesStatement
        ]);
        /**
         * * End Nine Item
         */
        
            
        /**
         * * Start  Sixth Item
         */
        
		$annuallyNetProfit = HArr::subtractAtDates([$ebt,$annuallyCorporateTaxes],$sumKeys);
        $netProfit = $annuallyNetProfit;
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['net-profit']['options']['title'] = __('Net Profit');
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['net-profit']['data'] = $netProfit;
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['net-profit']['year_total'] = $netProfitTotalPerYear = HArr::sumPerYearIndex($annuallyNetProfit, $yearWithItsMonths);
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['% Of Revenue']['data'] = HArr::calculatePercentageOf($productsTotals, $netProfit);
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['% Of Revenue']['year_total'] = $netProfitRevenuePercentage = HArr::calculatePercentageOf($salesRevenueYearTotal, $netProfitTotalPerYear);
        
        
        $retainedEarningOpening = DB::table('equity_opening_balances')->where('project_id', $this->id)->first();
        $retainedEarningOpening = $retainedEarningOpening ? $retainedEarningOpening->retained_earnings : 0;
        // $retainedEarning = HArr::calculateRetainEarning($retainedEarningOpening,$ebt);
        $retainedEarning = HArr::calculateRetainEarning($retainedEarningOpening, $netProfit);
        $data = [
            'total_sales_revenues'=>array_values($salesRevenueYearTotal) ,
            'annually_sales_revenues_growth_rates'=>array_values($annuallySalesRevenueGrowthRates) ,
            'gross_profit'=>array_values($grossProfitTotalPerYear),
            'annually_gross_profit_revenue_percentages'=>array_values($grossProfitRevenuePercentages),
            'ebitda'=>array_values($ebitdaTotalPerYear),
            'annually_ebitda_revenue_percentages'=>array_values($editdaRevenuePercentage),
            'ebit'=>array_values($ebitTotalPerYear),
            'annually_ebit_revenue_percentages'=>array_values($editRevenuePercentage),
            'monthly_ebt'=>$ebt,
            'ebt'=>array_values($ebtTotalPerYear),
            'annually_ebt_revenue_percentages'=>array_values($ebtRevenuePercentagePerYear),
            'net_profit'=>$netProfit,
            'annually_net_profit'=>$netProfitTotalPerYear,
            'annually_net_profit_revenue_percentages'=>array_values($netProfitRevenuePercentage),
            'total_cogs'=>$totalCogsPerYear,
            'total_percentages_cogs'=>$totalCogsPercentageOfRevenues,
            'accumulated_retained_earnings'=>$retainedEarning,
            'total_depreciation'=>array_values($totalDepreciationTotalPerYear),
            'sganda'=>$resultTotalPerCategoryPerYear,
            'sganda_revenues_percentages'=>$resultTotalPercentagesPerCategoryPerYear
        ];
        $this->incomeStatement ? $this->incomeStatement->update($data)  : $this->incomeStatement()->create($data);
        return [
            'step_data'=>$step_data,
            'financialYearEndMonthNumber'=>$financialYearEndMonthNumber,
            'years','studyMonthsForViews'=>$studyMonthsForViews,
            'project'=>$this,
            'tableDataFormatted'=>$tableDataFormatted,
            'defaultClasses'=>$defaultClasses,
            'title'=>__('Income Statement'),
            'nextRoute'=>route('cash.in.out.flow.result', ['project'=>$project->id]),
        ];
        /**
         * * End  Sixth Item
         */
        
        
           
     
        
        
     
    }
    public function getCashInOutFlowViewVars():array
    {
        $project = $this ;
        $step_data =(new Redirects)->steps($project, 'assets');
        // $fixedAssets = $project->fixedAssets;
        $products = $project->products;
        // $years =$project->getYears();
        $financialYearEndMonthNumber = '12';
        $defaultNumericInputClasses = [
            'number-format-decimals'=>0,
            'is-percentage'=>false,
            'classes'=>'repeater-with-collapse-input readonly',
            'formatted-input-classes'=>'custom-input-numeric-width readonly',
        ];
        $defaultPercentageInputClasses = [
            'classes'=>'',
            'formatted-input-classes'=>'ddd',
            'is-percentage'=>true ,
            'number-format-decimals'=> 2,
        ];
        $defaultClasses = [
            $defaultNumericInputClasses,
            $defaultPercentageInputClasses
        ];
        $studyMonthsForViews = $project->getStudyDates();
		$studyMonthsForViews = array_slice($studyMonthsForViews,0,$project->getViewStudyEndDateAsIndex()+1);
        $yearWithItsMonths=$project->getYearIndexWithItsMonths();
		unset($yearWithItsMonths[array_key_last($yearWithItsMonths)]);
        
        
        
        
        
        /**
         * * First Tab Sales Revenue
        */
        $cashAndBankAmount = $this->getCashAndBanksAmount();
        $tableDataFormatted[-1]['main_items']['cash-and-banks']['options'] = array_merge([
           'title'=>__('Cash And Banks')
        ], $defaultNumericInputClasses);
        $tableDataFormatted[-1]['main_items']['cash-and-banks']['data'] = [];
        
        
        
    
            
        
        $tableDataFormatted[0]['main_items']['cash-in-flow']['options'] = array_merge([
           'title'=>__('Total CashIn Flow')
        ], $defaultNumericInputClasses);
        $totalCashIn = [];
        
        
        
        $sumKeys = array_keys($studyMonthsForViews);
        
        /**
         * * Cash And Bank
         */
    
        $totalCollectionPayment = [];
        foreach ($products as $product) {
            $collectionPayment = $product->collection_statement['monthly']['payment']??[];
            $tableDataFormatted[0]['sub_items'][$product->getName()]['options'] =array_merge([
                'title'=>$product->getName(),
            ], $defaultNumericInputClasses);
            $tableDataFormatted[0]['sub_items'][$product->getName()]['data'] = $collectionPayment;
            $totalCollectionPayment = HArr::sumAtDates([$collectionPayment,$totalCollectionPayment], $sumKeys);
            $tableDataFormatted[0]['sub_items'][$product->getName()]['year_total'] = HArr::sumPerYearIndex($collectionPayment, $yearWithItsMonths);
        
        }
        $totalFixedAssetEquity = [];
        $totalFixedAssetLoanWithdrawal = [];
        foreach ($project->fixedAssets as $fixedAsset) {
            $totalFixedAssetEquity = HArr::sumAtDates([$fixedAsset->getFfeEquityPayment(),$totalFixedAssetEquity], $sumKeys);
            $totalFixedAssetLoanWithdrawal = HArr::sumAtDates([$fixedAsset->getFfeLoanWithdrawal(),$totalFixedAssetLoanWithdrawal], $sumKeys);
        }
        $otherDebtorsOpeningBalances = DB::table('other_debtors_opening_balances')->where('project_id', $project->id)->pluck('payload');
        $totalOtherDebtorsOpeningBalances = [];
        foreach ($otherDebtorsOpeningBalances as $otherDebtorsOpeningBalance) {
            $otherDebtorsOpeningBalance= (array)json_decode($otherDebtorsOpeningBalance);
            $totalOtherDebtorsOpeningBalances = HArr::sumAtDates([$totalOtherDebtorsOpeningBalances,$otherDebtorsOpeningBalance], $sumKeys);
        }
        $openingBalanceCollection = DB::table('cash_and_bank_opening_balances')->where('project_id', $this->id)->pluck('payload')->toArray()[0]??'';
            
        $openingBalanceCollection = (array) json_decode($openingBalanceCollection);
        if (count($openingBalanceCollection)) {
            $tableDataFormatted[0]['sub_items'][__('Opening Balance Collection')]['data'] = $openingBalanceCollection;
            $tableDataFormatted[0]['sub_items'][__('Opening Balance Collection')]['year_total'] = HArr::sumPerYearIndex($openingBalanceCollection, $yearWithItsMonths);
        }
        if (count($totalOtherDebtorsOpeningBalances)) {
            $tableDataFormatted[0]['sub_items'][__('Other Debtors')]['data'] = $totalOtherDebtorsOpeningBalances;
            $tableDataFormatted[0]['sub_items'][__('Other Debtors')]['year_total'] = HArr::sumPerYearIndex($totalOtherDebtorsOpeningBalances, $yearWithItsMonths);
        }
        $customerCollection = HArr::sumAtDates([$openingBalanceCollection,$totalCollectionPayment,$totalOtherDebtorsOpeningBalances], $sumKeys);
        if (count($totalFixedAssetEquity)) {
            $tableDataFormatted[0]['sub_items'][__('Equity Injection')]['data'] = $totalFixedAssetEquity;
            $tableDataFormatted[0]['sub_items'][__('Equity Injection')]['year_total'] = HArr::sumPerYearIndex($totalFixedAssetEquity, $yearWithItsMonths);
        }
        if (count($totalFixedAssetLoanWithdrawal)) {
            $tableDataFormatted[0]['sub_items'][__('Loan Withdrawals')]['data'] = $totalFixedAssetLoanWithdrawal;
            $tableDataFormatted[0]['sub_items'][__('Loan Withdrawals')]['year_total'] = HArr::sumPerYearIndex($totalFixedAssetLoanWithdrawal, $yearWithItsMonths);
        }
            
        $totalCashIn = HArr::sumAtDates(array_column($tableDataFormatted[0]['sub_items']??[], 'data'), $sumKeys);
            
        
        $tableDataFormatted[0]['main_items']['cash-in-flow']['data'] = $totalCashIn;
        $tableDataFormatted[0]['main_items']['cash-in-flow']['year_total'] = HArr::sumPerYearIndex($totalCashIn, $yearWithItsMonths);
        
        
        
        
        
        
        
        
        
        
        ////////////////////////////////////////////////////////////////////////////////
        
        
        
        
        /**
         * * Second Tab Cash Out
        */
        $currentTabIndex = 1 ;
        $currentTabId = 'cash-out-flow';
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>__('Total CashOut Flow')
        ], $defaultNumericInputClasses);

        $sumKeys = array_keys($studyMonthsForViews);
        $rawMaterials = $project->rawMaterials;
        $totalCreditWithholdTaxPayments = [];
        $supplierPayments = [];
        foreach ($rawMaterials as $rawMaterial) {
            $collectionPayment = $rawMaterial->collection_statement['monthly']['payment']??[];
            $tableDataFormatted[$currentTabIndex]['sub_items'][$rawMaterial->getName()]['options'] =array_merge([
                'title'=>$rawMaterial->getName(),
            ], $defaultNumericInputClasses);
            $tableDataFormatted[$currentTabIndex]['sub_items'][$rawMaterial->getName()]['data'] = $collectionPayment;
            $supplierPayments = HArr::sumAtDates([$supplierPayments,$collectionPayment]);
            $tableDataFormatted[$currentTabIndex]['sub_items'][$rawMaterial->getName()]['year_total'] = HArr::sumPerYearIndex($collectionPayment, $yearWithItsMonths);
            $creditWithholdPayment = $rawMaterial->credit_withhold_statement['monthly']['payment']??[];
            $totalCreditWithholdTaxPayments = HArr::sumAtDates([$totalCreditWithholdTaxPayments,$creditWithholdPayment], $sumKeys);
        }
        
        $openingBalancePayment = DB::table('supplier_payable_opening_balances')->where('project_id', $this->id)->pluck('payload')->toArray()[0]??'';
        $openingBalancePayment = (array) json_decode($openingBalancePayment);
        if (count($openingBalancePayment)) {
            
            $tableDataFormatted[$currentTabIndex]['sub_items'][__('Opening Balance Payment')]['data'] = $openingBalancePayment;
            $tableDataFormatted[$currentTabIndex]['sub_items'][__('Opening Balance Payment')]['year_total'] = HArr::sumPerYearIndex($openingBalancePayment, $yearWithItsMonths);
        }
            
            
        $otherCreditorsOpeningBalances = DB::table('other_credits_opening_balances')->where('project_id', $project->id)->pluck('payload');
        $totalOtherCreditorsOpeningBalances = [];
        foreach ($otherCreditorsOpeningBalances as $otherCreditorsOpeningBalance) {
            $otherCreditorsOpeningBalance= (array)json_decode($otherCreditorsOpeningBalance);
            $totalOtherCreditorsOpeningBalances = HArr::sumAtDates([$totalOtherCreditorsOpeningBalances,$otherCreditorsOpeningBalance], $sumKeys);
        }
        $supplierPayments = HArr::sumAtDates([$supplierPayments,$openingBalancePayment,$totalOtherCreditorsOpeningBalances], $sumKeys);
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('Other Creditors Payments')]['data'] = $totalOtherCreditorsOpeningBalances;
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('Other Creditors Payments')]['year_total'] = HArr::sumPerYearIndex($totalOtherCreditorsOpeningBalances, $yearWithItsMonths);
            
            
            
        if ($this->getCreditWithholdOpeningBalanceAmount() > 0) {
            $firstItemValue = $totalCreditWithholdTaxPayments[0] ?? 0 ;
            $totalCreditWithholdTaxPayments[0] = $firstItemValue + $this->getCreditWithholdOpeningBalanceAmount();
        }
        $totalSalaryPayments = [];
        $totalExpenses = [];
        $totalTaxAndSocialInsurances = [];
        $salaryPayments = DB::table('manpowers')->where('project_id', $project->id)->pluck('salary_payments')->toArray();
        $salaryTaxAndSocialInsurances = DB::table('manpowers')->where('project_id', $project->id)->pluck('tax_and_social_insurance_statement')->toArray();
        foreach ($salaryPayments as $index=>$manpowerSalaryPayment) {
            $manpowerSalaryPayment = (array)json_decode($manpowerSalaryPayment);
            $salaryTaxAndSocialInsurance = ((array)json_decode($salaryTaxAndSocialInsurances[$index]))['monthly']??[];
            $salaryTaxAndSocialInsurance = (array)$salaryTaxAndSocialInsurance->payment;
            $totalSalaryPayments  = HArr::sumAtDates([$totalSalaryPayments,$manpowerSalaryPayment], $sumKeys);
            $totalTaxAndSocialInsurances  = HArr::sumAtDates([$totalTaxAndSocialInsurances,$salaryTaxAndSocialInsurance], $sumKeys);
        }
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('Salaries Payments')]['data'] = $totalSalaryPayments;
        $totalExpenses =  HArr::sumAtDates([$totalExpenses,$totalSalaryPayments], $sumKeys);
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('Salaries Payments')]['year_total'] = HArr::sumPerYearIndex($totalSalaryPayments, $yearWithItsMonths);
        
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('Salary Taxes & Social Insurance')]['data'] = $totalTaxAndSocialInsurances;
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('Salary Taxes & Social Insurance')]['year_total'] = HArr::sumPerYearIndex($totalTaxAndSocialInsurances, $yearWithItsMonths);
        
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('Credit Withhold Taxes Payments')]['data'] = $totalCreditWithholdTaxPayments;
		
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('Credit Withhold Taxes Payments')]['year_total'] = HArr::sumPerYearIndex($totalCreditWithholdTaxPayments, $yearWithItsMonths);
        
        $totalVatsPayments = $this->vat_statements['monthly']['payment']??[];
        
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('VAT Payments')]['data'] = $totalVatsPayments;
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('VAT Payments')]['year_total'] = HArr::sumPerYearIndex($totalVatsPayments, $yearWithItsMonths);
        
        
        $totalCorporateTaxesPayments = $this->corporate_taxes_statement['monthly']['payment']??[];
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('Corporate Taxes Payments')]['data'] = $totalCorporateTaxesPayments;
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('Corporate Taxes Payments')]['year_total'] = HArr::sumPerYearIndex($totalCorporateTaxesPayments, $yearWithItsMonths);
        
        $taxes = HArr::sumAtDates([$totalTaxAndSocialInsurances,$totalCreditWithholdTaxPayments,$totalVatsPayments,$totalCorporateTaxesPayments], $sumKeys);
        
        
        
        $expenses = Expense::where('project_id', $project->id)->get();

        foreach ($expenses as $expense) {
            $paymentAmounts = $expense->payment_amounts;
            $expenseName = $expense->name;
            $currentItemId = $expense->id.$expenseName ;
            $tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['options'] =array_merge([
                'title'=>$expenseName,
            ], $defaultNumericInputClasses);
            $tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['data'] = $paymentAmounts;
            $totalExpenses =  HArr::sumAtDates([$totalExpenses,$paymentAmounts], $sumKeys);
            $tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['year_total'] = HArr::sumPerYearIndex($paymentAmounts, $yearWithItsMonths);
        }
        $totalReplaceCost = [];
        $fixedAssetPayments = [];
        foreach ($project->fixedAssets as $fixedAsset) {
            $ffePayment = $fixedAsset->getFfePayment();
            $currentItemId = $fixedAsset->id ;
            $tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['options'] =array_merge([
                'title'=>$fixedAsset->getName(),
            ], $defaultNumericInputClasses);
            $tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['data'] = $ffePayment;
            $fixedAssetPayments = HArr::sumAtDates([$fixedAssetPayments,$ffePayment], $sumKeys);
            $tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['year_total'] = HArr::sumPerYearIndex($ffePayment, $yearWithItsMonths);
            $totalReplaceCost = HArr::sumAtDates([$totalReplaceCost,$fixedAsset->getReplaceCost()], $sumKeys);
        }
        $currentItemId = 'fixed-asset-replacement-cost';
        $tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['options'] =array_merge([
                'title'=>__('Fixed Asset Replacement Cost'),
            ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['data'] = $totalReplaceCost;
        $tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['year_total'] = HArr::sumPerYearIndex($totalReplaceCost, $yearWithItsMonths);
        $fixedAssetPayments = HArr::sumAtDates([$fixedAssetPayments,$totalReplaceCost], $sumKeys);
            
        
        $loanSchedulePayments = DB::table('loan_schedule_payments')->where('loan_schedule_payments.project_id', $project->id)->join('fixed_assets', 'fixed_assets.id', '=', 'loan_schedule_payments.fixed_asset_id')->selectRaw('schedulePayment,name')->get();
        $totalSchedulePayments = [];
        foreach ($loanSchedulePayments as $loanSchedulePayments) {
            $schedulePayment = (array)json_decode($loanSchedulePayments->schedulePayment);
            $name = $loanSchedulePayments->name .' '. __('Loan Installment') ;
            $tableDataFormatted[$currentTabIndex]['sub_items'][$name]['options'] =array_merge([
                'title'=>$name,
            ], $defaultNumericInputClasses);
            $tableDataFormatted[$currentTabIndex]['sub_items'][$name]['data'] = $schedulePayment;
            $totalSchedulePayments = HArr::sumAtDates([$schedulePayment,$totalSchedulePayments], $sumKeys);
            $tableDataFormatted[$currentTabIndex]['sub_items'][$name]['year_total'] = HArr::sumPerYearIndex($schedulePayment, $yearWithItsMonths);
        }
        
		
        $openingLoans = DB::table('long_term_loan_opening_balances')->where('long_term_loan_opening_balances.project_id', $project->id)->get();
        $totalLoanInstallments =[];
        $totalLoanInterests =[];
        foreach ($openingLoans as $openingLoan) {
            $installment = (array)json_decode($openingLoan->installments);
            $interest = (array)json_decode($openingLoan->interests);
            $totalLoanInstallments = HArr::sumAtDates([$totalLoanInstallments,$installment], $sumKeys);
            $totalLoanInterests = HArr::sumAtDates([$totalLoanInterests,$interest], $sumKeys);
        }
        $totalLoansAmounts = HArr::sumAtDates([$totalLoanInterests,$totalLoanInstallments], $sumKeys);
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('Opening Loan Installments')]['options'] =array_merge([
            'title'=>__('Opening Loan Installments'),
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('Opening Loan Installments')]['data'] = $totalLoansAmounts;
        $totalLoanWithSchedulePayments =  HArr::sumAtDates([$totalLoansAmounts,$totalSchedulePayments], $sumKeys);
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('Opening Loan Installments')]['year_total'] = HArr::sumPerYearIndex($totalLoansAmounts, $yearWithItsMonths);
        
        
		
		
		
		     $openingLoans = DB::table('other_long_term_liabilities_opening_balances')->where('project_id', $project->id)->get();
        $totalLoanTermLiabilities =[];

        foreach ($openingLoans as $openingLoan) {
            $payload = (array)json_decode($openingLoan->payload);
            $totalLoanTermLiabilities = HArr::sumAtDates([$totalLoanTermLiabilities,$payload], $sumKeys);
        }
    
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('Opening Long Term Liabilities')]['options'] =array_merge([
            'title'=>__('Opening Long Term Liabilities'),
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('Opening Long Term Liabilities')]['data'] = $totalLoanTermLiabilities;
        $tableDataFormatted[$currentTabIndex]['sub_items'][__('Opening Long Term Liabilities')]['year_total'] = HArr::sumPerYearIndex($totalLoanTermLiabilities, $yearWithItsMonths);
        
		
		
		
		
		
        
        
        $totalCashOut = HArr::sumAtDates(array_column($tableDataFormatted[$currentTabIndex]['sub_items']??[], 'data'), $sumKeys);
            
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $totalCashOut;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =  HArr::sumPerYearIndex($totalCashOut, $yearWithItsMonths);
        
        $workingCapitalStatement = HArr::calculateWorkingCapital($cashAndBankAmount, $totalCashIn, $totalCashOut, $sumKeys);
        /**
         * * Start Net Cash Before Working Capital;
        */
        $currentTabIndex = 2 ;
        $currentTabId = 'net-cash-before-working-capital';
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>__('Net Cash Before Working Capital')
        ], $defaultNumericInputClasses);
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentData = $workingCapitalStatement['net_cash_before_working_capital']??[];
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =  HArr::sumPerYearIndex($currentData, $yearWithItsMonths);
        
        /**
         * * End Net Cash Before Working Capital;
         */
    
            
        /**
         * * Start Net Cash Before Working Capital;
        */
        $currentTabIndex = 3 ;
        $currentTabId = 'working-capital-injection';
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>__('Working Capital Injection')
        ], $defaultNumericInputClasses);
        
        $workingCapitalInjection = $workingCapitalStatement['working_capital_injection']??[] ;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentData = $workingCapitalInjection;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =  HArr::sumPerYearIndex($currentData, $yearWithItsMonths);
        
        /**
         * * End Net Cash Before Working Capital;
         */
        
            
        /**
         * * Start Cash And Bank End Balance;
        */
        $currentTabIndex = 4 ;
        $currentTabId = 'cash-and-bank-end-balance';
        
        $cashEndBalance = $workingCapitalStatement['cash_end_balance']??[] ;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>__('Cash And Bank End Balance')
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentData = $cashEndBalance;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =  HArr::sumPerYearIndex($currentData, $yearWithItsMonths);
        
        /**
         * * End Cash And Bank End Balance;
         */
        $tableDataFormatted[-1]['main_items']['cash-and-banks']['data'] = $workingCapitalStatement['beginning_balance'] ??[];
        $tableDataFormatted[-1]['main_items']['cash-and-banks']['year_total'] = HArr::getPerYearIndexForCashAndBank($workingCapitalStatement['beginning_balance'] ??[], $yearWithItsMonths);
        $totalCashOut = HArr::sumAtDates([$supplierPayments,$taxes,$totalExpenses,$fixedAssetPayments,$totalLoanWithSchedulePayments], $sumKeys);
        $statementData = [
            'cash_end_balance'=>$cashEndBalance,
            'working_capital_injection'=>$workingCapitalInjection,
            'equity_injection'=>$totalFixedAssetEquity,
            'loan_withdrawal'=>$totalFixedAssetLoanWithdrawal,
            'customer_collection'=>$customerCollection,
            'supplier_payments'=>$supplierPayments,
            'taxes'=>$taxes,
            'expenses'=>$totalExpenses,
            'fixed_asset_payments'=>$fixedAssetPayments,
            'loan_installments'=>$totalLoanWithSchedulePayments,
            'total_cash_out'=>$totalCashOut
        ];
        $this->cashInOutStatement ? $this->cashInOutStatement->update($statementData) : $this->cashInOutStatement()->create($statementData);
            
        return  [
            'step_data'=>$step_data,
            'financialYearEndMonthNumber'=>$financialYearEndMonthNumber,
            'years','studyMonthsForViews'=>$studyMonthsForViews,
            'project'=>$project,
            'tableDataFormatted'=>$tableDataFormatted,
            'defaultClasses'=>$defaultClasses,
            'title'=>__('Cash In Out Flow'),
            'nextRoute'=>route('balance.sheet.result', ['project'=>$project->id]),
            
        ];
    }
    
    
    
    
    public function getBalanceSheetViewVars():array
    {
        $project = $this ;
        //   $step_data =(new Redirects)->steps($project, 'assets');
        // $fixedAssets = $project->fixedAssets;
        $products = $project->products;
        // $years =$project->getYears();
        $financialYearEndMonthNumber = '12';
        $defaultNumericInputClasses = [
            'number-format-decimals'=>0,
            'is-percentage'=>false,
            'classes'=>'repeater-with-collapse-input',
            'formatted-input-classes'=>'custom-input-numeric-width ',
        ];
        $defaultPercentageInputClasses = [
            'classes'=>'',
            'formatted-input-classes'=>'',
            'is-percentage'=>true ,
            'number-format-decimals'=> 2,
        ];
        $defaultClasses = [
            $defaultNumericInputClasses,
            $defaultPercentageInputClasses
        ];
      
	      $studyMonthsForViews = $project->getStudyDates();
		$studyMonthsForViews = array_slice($studyMonthsForViews,0,$project->getViewStudyEndDateAsIndex()+1);
        $yearWithItsMonths=$project->getYearIndexWithItsMonths();
		unset($yearWithItsMonths[array_key_last($yearWithItsMonths)]);
		
        // $studyMonthsForViews=$project->getStudyDurationPerYearFromIndexesForView();
        // $studyMonthsForViews = array_flip($project->getOperationDatesAsDateAndDateAsIndexToStudyEndDate());
        $projectId = $this->id;
        // $yearWithItsMonths=$project->getYearIndexWithItsMonthsAsIndexAndString();
        
        $sumKeys = array_keys($studyMonthsForViews);
        
        
        
        
        /**
         * * First Tab
        */
    
        $totalFixedAssetOpening = [];
        $fixedAssetOpeningBalanceEndBalances =  DB::table('fixed_asset_opening_balances')->where('project_id', $projectId)->pluck('statement')->toArray();
        foreach ($fixedAssetOpeningBalanceEndBalances as $fixedAssetOpeningBalanceEndBalance) {
            $fixedAssetOpeningBalanceEndBalance= ((array)((array)json_decode($fixedAssetOpeningBalanceEndBalance))['monthly']??[])['end_balance']??[];
            $totalFixedAssetOpening = HArr::sumAtDates([$totalFixedAssetOpening,$fixedAssetOpeningBalanceEndBalance], $sumKeys);
        }
        
        $totalFixedAsset = [];
        $fixedAssetBalanceEndBalances =  DB::table('fixed_assets')->where('project_id', $projectId)->pluck('statement')->toArray();
        foreach ($fixedAssetBalanceEndBalances as $fixedAssetBalanceEndBalance) {
            $fixedAssetBalanceEndBalance= ((array)((array)json_decode($fixedAssetBalanceEndBalance))['end_balance']??[]);
            $totalFixedAsset = HArr::sumAtDates([$totalFixedAsset,$fixedAssetBalanceEndBalance], $sumKeys);
        }
        $netFixedAsset =  HArr::sumAtDates([$totalFixedAssetOpening,$totalFixedAsset], $sumKeys) ;
        $currentDataArr =$netFixedAsset;
        $title = __('Net Fixed Asset');
        $currentTabId = $title ;
        $currentTabIndex = -1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        
        
        /**
         * * End First Key
         */
        
        
        
        /**
        * * Second Tab
        */
    
        $totalProjectUnderProgress = [];
        $capitalizedStatements =  DB::table('fixed_assets')->where('project_id', $projectId)->pluck('capitalization_statement')->toArray();
        foreach ($capitalizedStatements as $capitalizedStatement) {
            $capitalizedStatement= @((array)((array)json_decode($capitalizedStatement))['end_balance']??[]);
            $totalProjectUnderProgress = HArr::sumAtDates([$totalProjectUnderProgress,$capitalizedStatement], $sumKeys);
        }
        $currentDataArr = $totalProjectUnderProgress  ;
        $title = __('Projects Under Progress');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        
        
        /**
         * * End Second Key
         */
        
            
        
        /**
        * * Third Tab
        */
        $cashInOutStatement = $this->cashInOutStatement ;
        $cashEndBalance = $cashInOutStatement['cash_end_balance']??[] ;
        $currentDataArr = $cashEndBalance;
        $title = __('Cash & Banks');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        
        
        /**
         * * End Third Key
         */
        
        
        
        
        /**
         * * Fourth Tab
        */
        
        
        
        $totalCustomerReceivablesOpening = [];
        $customerReceivablesOpeningBalanceEndBalances =  DB::table('cash_and_bank_opening_balances')->where('project_id', $projectId)->pluck('statement')->toArray();
        foreach ($customerReceivablesOpeningBalanceEndBalances as $customerReceivablesOpeningBalanceEndBalance) {
            $customerReceivablesOpeningBalanceEndBalance= ((array)((array)json_decode($customerReceivablesOpeningBalanceEndBalance))['monthly']??[])['end_balance']??[];
            $totalCustomerReceivablesOpening = HArr::sumAtDates([$totalCustomerReceivablesOpening,$customerReceivablesOpeningBalanceEndBalance], $sumKeys);
        }
        
        $totalCustomerReceivables = [];
        $collectionStatements =  DB::table('products')->where('project_id', $projectId)->pluck('collection_statement')->toArray();
        foreach ($collectionStatements as $collectionStatementEndBalance) {
			$collectionStatementEndBalance = (array)json_decode($collectionStatementEndBalance) ;
			$collectionStatementEndBalance = $collectionStatementEndBalance ? (array)$collectionStatementEndBalance['monthly'] : '';
			$collectionStatementEndBalance = (array)($collectionStatementEndBalance['end_balance'] ?? []);
			$collectionStatementEndBalance = (array) ($collectionStatementEndBalance);
            $totalCustomerReceivables = HArr::sumAtDates([$totalCustomerReceivables,$collectionStatementEndBalance], $sumKeys);
        }
        $customerReceivables = HArr::sumAtDates([$totalCustomerReceivablesOpening,$totalCustomerReceivables], $sumKeys);
        $currentDataArr = $customerReceivables;
        $title = __('Customer Receivables');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        
        
        /**
         * * End Fourth Key
         */
        
        
        
        /**
         * * Fifth Tab
        */
        
        
        
    
        $totalFGs = [];
        $fgInventories =  DB::table('products')->where('project_id', $projectId)->pluck('product_inventory_value_statement')->toArray();
	
        foreach ($fgInventories as $fgInventory) {
            $fgInventory= @((array)((array)json_decode($fgInventory))['end_balance']??[]);
		
            $fgInventory = $fgInventory?:[];
            $totalFGs = HArr::sumAtDates([$fgInventory,$totalFGs], $sumKeys);
        }
        $currentDataArr = $totalFGs ;
        $title = __('Finished Goods Inventory');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        
        
        /**
         * * End Fifth Key
         */
        
        
        /**
        * * Sixth Tab
        */
        
        
        
    
        $totalRawMaterials = [];
        $rmInventories =  DB::table('raw_materials')->where('project_id', $projectId)->pluck('inventory_value_statement')->toArray();
        foreach ($rmInventories as $rmInventory) {
            $rmInventory= @((array)((array)json_decode($rmInventory))['end_balance']??[]);
            $rmInventory = $rmInventory?:[];
            $totalRawMaterials = HArr::sumAtDates([$rmInventory,$totalRawMaterials], $sumKeys);
        }
        $currentDataArr = $totalRawMaterials   ;
        $title = __('Raw Material Inventory');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        
        
        /**
         * * End Sixth Key
         */
        
        
        
        /**
        * * Seventh Tab
        */
        
        
        $totalDebtorsOpening = [];
        $debtorsOpeningBalances =  DB::table('other_debtors_opening_balances')->where('project_id', $projectId)->get();
        $debtorsOpeningBalanceEndBalances = $debtorsOpeningBalances->pluck('statement')->toArray();
        $totalDebtorsAmounts = 0 ;
        foreach ($debtorsOpeningBalanceEndBalances as $index=>$debtorsOpeningBalanceEndBalance) {
            $debtorsOpeningBalanceEndBalance= ((array)((array)json_decode($debtorsOpeningBalanceEndBalance))['monthly']??[])['end_balance']??[];
            $totalDebtorsOpening = HArr::sumAtDates([$totalDebtorsOpening,$debtorsOpeningBalanceEndBalance], $sumKeys);
            $totalDebtorsAmounts += $debtorsOpeningBalances[$index]->amount;
        }
        $oneTimeExpenses = DB::table('expenses')->where('project_id',$projectId)->where('expense_type','Expense')->where('relation_name','one_time_expense')->pluck('payload')->toArray();
		$totalOneTimeExpenses = [];
		foreach($oneTimeExpenses as $oneTimeExpense){
			$oneTimeExpense = $oneTimeExpense ?  (array) json_decode($oneTimeExpense) : [];
			$oneTimeExpense = (array)$oneTimeExpense['end_balance']??[];
			$totalOneTimeExpenses = HArr::sumAtDates([$totalOneTimeExpenses,$oneTimeExpense],$sumKeys);
		}
        
        $totalOtherDebtors = HArr::sumAtDates([$totalDebtorsOpening,$totalOneTimeExpenses],$sumKeys);
        $currentDataArr = $totalOtherDebtors ;
        $title = __('Other Debtors');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        
        
        /**
         * * End Seventh Key
         */
        
        /**
         * * Start Key
         */
        
        $totalCurrentAssets = HArr::sumAtDates([$cashEndBalance,$customerReceivables,$totalRawMaterials , $totalFGs,$totalOtherDebtors], $sumKeys);
        $currentDataArr = $totalCurrentAssets;
        ;
        $title = __('Total Current Assets');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        /**
             * * End Key
             */
        /**
         * * Start Key
         */
        
        
        $totalAssets = HArr::sumAtDates([$totalCurrentAssets,$totalProjectUnderProgress ,$netFixedAsset], $sumKeys);
        $currentDataArr = $totalAssets ;
        $title = __('Total Assets');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        /**
             * * End Key
             */
        
        
        /**
         * * Start Key
         */
        
        $supplierOpening = DB::table('supplier_payable_opening_balances')->where('project_id', $this->id)->pluck('statement')->toArray()[0]??'';
        $supplierOpeningEndBalance = @((array)(((array) json_decode($supplierOpening))['monthly']))['end_balance']??[];
        $supplierOpeningEndBalance = $supplierOpeningEndBalance?:[];
            
            
        $totalRawMaterialsEndBalance = [];
        $rawMaterialEndBalances =  DB::table('raw_materials')->where('project_id', $projectId)->pluck('collection_statement')->toArray();
        foreach ($rawMaterialEndBalances as $rawMaterial) {
            $rawMaterial= @((array)(((array)((array)json_decode($rawMaterial))['monthly']??[]))['end_balance']??[]);
            $rawMaterial = $rawMaterial?:[];
            $totalRawMaterialsEndBalance = HArr::sumAtDates([$rawMaterial,$totalRawMaterialsEndBalance], $sumKeys);
        }
        $supplierPayables = HArr::sumAtDates([$supplierOpeningEndBalance,$totalRawMaterialsEndBalance], $sumKeys);
        $currentDataArr = $supplierPayables ;
        $title = __('Supplier Payables');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
    
        /**
         * * End Key
         */
        
        /**
         * * Start Key
         */
        
        $ffePayables = DB::table('fixed_assets')->where('project_id', $projectId)->pluck('ffe_payable')->toArray();
        $totalFfePayables  = [];
        foreach ($ffePayables as $ffePayable) {
            $ffePayable= (array)json_decode($ffePayable);
            $totalFfePayables = HArr::sumAtDates([$ffePayable,$totalFfePayables], $sumKeys);
        }
        
        
        
        
        $currentDataArr = $totalFfePayables ;
        $title = __('Fixed Assets Creditors');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        /**
             * * End Key
             */
        
    
        /**
         * * Start Key
         */
        
        $totalVatPayables = $this->vat_statements['monthly']['end_balance']??[];
        $currentDataArr = $totalVatPayables ;
        $title = __('VAT Payables');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
    
        /**
         * * End Key
         */
        
        /**
         * * Start Key
         */
        $totalCreditWithholdsEndBalance = [];
        $creditWithholdEndBalances =  DB::table('raw_materials')->where('project_id', $projectId)->pluck('credit_withhold_statement')->toArray();
        foreach ($creditWithholdEndBalances as $creditWithhold) {
            $creditWithhold= @((array)(((array)((array)json_decode($creditWithhold))['monthly']??[]))['end_balance']??[]);
            $creditWithhold = $creditWithhold?:[];
            $totalCreditWithholdsEndBalance = HArr::sumAtDates([$creditWithhold,$totalCreditWithholdsEndBalance], $sumKeys);
        }
        
        $currentDataArr = $totalCreditWithholdsEndBalance ;
        $title = __('Credit Withhold Taxes');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        /**
             * * End Key
             */
    
        /**
         * * Start Key
         */
    
        $totalSalaryTaxesEndBalance = [];
        $salaryTaxesEndBalances =  DB::table('manpowers')->where('project_id', $projectId)->pluck('tax_and_social_insurance_statement')->toArray();
        foreach ($salaryTaxesEndBalances as $salaryTaxes) {
            $salaryTaxes= ((array)(((array)((array)json_decode($salaryTaxes))['monthly']??[]))['end_balance']??[]);
            $totalSalaryTaxesEndBalance = HArr::sumAtDates([$salaryTaxes,$totalSalaryTaxesEndBalance], $sumKeys);
        }
        
        $currentDataArr = $totalSalaryTaxesEndBalance ;
        $title = __('Salary Taxes & Social Insurances');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        /**
             * * End Key
             */
        
        /**
             * * Start Key
             */
        
        
        
        $corporateTaxesEndBalance = $this->corporate_taxes_statement['monthly']['end_balance']??[];
        $currentDataArr = $corporateTaxesEndBalance ;
        $title = __('Corporate Taxes Payables');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
    
        
        
        
        
        
        
        /**
         * * End Key
         */
        
        
        
        
        /**
        * * Start Tab
        */
        
        
        
        
        $totalCreditorsOpening = [];
        $totalCreditorsAmount = 0 ;
        $creditorsOpeningBalances =  DB::table('other_credits_opening_balances')->where('project_id', $projectId)->get();
        $creditorsOpeningBalanceEndBalances = $creditorsOpeningBalances->pluck('statement')->toArray();
        foreach ($creditorsOpeningBalanceEndBalances as $index=>$creditorsOpeningBalanceEndBalance) {
            $creditorsOpeningBalanceEndBalance= ((array)((array)json_decode($creditorsOpeningBalanceEndBalance))['monthly']??[])['end_balance']??[];
            $totalCreditorsOpening = HArr::sumAtDates([$totalCreditorsOpening,$creditorsOpeningBalanceEndBalance], $sumKeys);
            $totalCreditorsAmount += ($creditorsOpeningBalances[$index]->amount);
        }
    
        $totalExpenses = [];
        $expenses =  DB::table('expenses')->where('project_id', $projectId)->pluck('collection_statements')->toArray();
        foreach ($expenses as $expenseArr) {
            $expenseArr= ((array)(((array)((array)json_decode($expenseArr))['monthly']??[]))['end_balance']??[]);
            $totalExpenses = HArr::sumAtDates([$expenseArr,$totalExpenses], $sumKeys);
        }
        
        $otherCreditors  = HArr::sumAtDates([$totalExpenses,$totalCreditorsOpening], $sumKeys) ;
        $currentDataArr = $otherCreditors ;
        $title = __('Other Creditors');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        
        
        /**
         * * End Key
         */
        
        /**
         * * Start Key
         */
        
        $totalCurrentLiabilities = HArr::sumAtDates([$supplierPayables,  $totalFfePayables, $totalVatPayables, $totalCreditWithholdsEndBalance, $totalSalaryTaxesEndBalance, $corporateTaxesEndBalance, $otherCreditors], $sumKeys);
        $currentDataArr = $totalCurrentLiabilities;
        ;
        $title = __('Total Current Liabilities');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        /**
         * * End Key
         */
        
        
        
        
        
        /**
        * * Start Tab
        */
        
        
        
        
        $totalLoansOpening = [];
        $loansOpeningBalanceEndBalances =  DB::table('long_term_loan_opening_balances')->where('project_id', $projectId)->pluck('statement')->toArray();
        foreach ($loansOpeningBalanceEndBalances as $loansOpeningBalanceEndBalance) {
            $loansOpeningBalanceEndBalance= ((array)((array)json_decode($loansOpeningBalanceEndBalance))['monthly']??[])['end_balance']??[];
            $totalLoansOpening = HArr::sumAtDates([$totalLoansOpening,$loansOpeningBalanceEndBalance], $sumKeys);
        }
    
        $totalLoanBalances = [];
        $loanEndBalances =  DB::table('loan_schedule_payments')->where('project_id', $projectId)->pluck('endBalance')->toArray();
        foreach ($loanEndBalances as $loanEndBalance) {
            $loanEndBalance= (array)json_decode($loanEndBalance);
            $totalLoanBalances = HArr::sumAtDates([$loanEndBalance,$totalLoanBalances], $sumKeys);
        }
		// foreach($this->fixedAssets as $fixedAsset){
		// 	dd($fixedAsset->capitalization_statement['capitalized_interest']);
		// }
		
        // dd($loanEndBalances);

        $mediumTermLoans  = HArr::sumAtDates([$totalLoanBalances,$totalLoansOpening], $sumKeys) ;
		foreach($this->fixedAssets as $fixedAsset)
		{
			$loanWithdrawalEndBalance = $fixedAsset->ffe_loan_withdrawal_end_balance?:[];
			array_pop($loanWithdrawalEndBalance);
			$mediumTermLoans = HArr::sumAtDates([$mediumTermLoans,$loanWithdrawalEndBalance],$sumKeys); 
		}
        $currentDataArr = $mediumTermLoans ;
        $title = __('Medium Term Loan');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =$mediumTermLoanPerYear = HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        
        
        /**
         * * End Key
         */
        
        
        
        /**
        * * Start Tab
        */
        
        
        
        
        $totalOtherLongTermsOpening = [];
        $otherLongTermsOpeningBalanceEndBalances =  DB::table('other_long_term_liabilities_opening_balances')->where('project_id', $projectId)->pluck('statement')->toArray();
        foreach ($otherLongTermsOpeningBalanceEndBalances as $otherLongTermsOpeningBalanceEndBalance) {
            $otherLongTermsOpeningBalanceEndBalance= ((array)((array)json_decode($otherLongTermsOpeningBalanceEndBalance))['monthly']??[])['end_balance']??[];
            $totalOtherLongTermsOpening = HArr::sumAtDates([$totalOtherLongTermsOpening,$otherLongTermsOpeningBalanceEndBalance], $sumKeys);
        }
        
        $currentDataArr = $totalOtherLongTermsOpening ;
        $title = __('Other Long Term Liabilities');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        
        
        /**
         * * End Key
         */
        
        
        /**
         * * Start Key
         */
        
        $totalLongTermLiabilities = HArr::sumAtDates([$mediumTermLoans,$totalOtherLongTermsOpening], $sumKeys);
        $currentDataArr = $totalLongTermLiabilities;
        ;
        $title = __('Total Long Term Liabilities');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        /**
         * * End Key
         */
        
        
        
        /**
         * * Start Key
         */
        $equityOpeningBalance = count($this->equityOpeningBalances) ? $this->equityOpeningBalances[0] : null;
        
        $paidUpCapitals = $equityOpeningBalance ? $equityOpeningBalance->getExtendedPaidUpCapitalAmount() : [];
        $currentDataArr =$paidUpCapitals ;
        ;
        $title = __('Paid Up Capital');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        /**
         * * End Key
         */
        
        
        /**
         * * Start Key
         */
        $workingCapitalInjection = $cashInOutStatement ? $cashInOutStatement->working_capital_injection : [];
        $equityInjection = $cashInOutStatement ? $cashInOutStatement->equity_injection : [];
        $additionalPaidUpCapital = HArr::accumulateArray(HArr::sumAtDates([$workingCapitalInjection,$equityInjection], $sumKeys)) ;
        $currentDataArr =$additionalPaidUpCapital;
        ;
        $title = __('Additional Paid Up Capital');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        /**
         * * End Key
         */
        
        /**
         * * Start Key
         */

        
        $legalReserve = $equityOpeningBalance ? $equityOpeningBalance->getExtendedLegalReserveAmount() : [];
        $currentDataArr =$legalReserve ;
        ;
        $title = __('Legal Reserve');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($currentDataArr, $yearWithItsMonths) ;
        /**
         * * End Key
         */
        
        /**
         * * Start Key
         */

        $incomeStatement  = $this->incomeStatement;
        $accumulatedRetainedEarnings = $incomeStatement ? $incomeStatement->accumulated_retained_earnings : [];
        
        $currentDataArr =$accumulatedRetainedEarnings ;
        ;
        $title = __('Retained Earnings');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentDataArr;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForFirstMonthInYear($currentDataArr, $yearWithItsMonths) ;
        /**
         * * End Key
         */
        
        /**
         * * Start Key
         */

        $incomeStatement  = $this->incomeStatement;
        $netProfits = $incomeStatement ? $incomeStatement->net_profit : [];
  //      $monthlyEbt = $incomeStatement ? $incomeStatement->monthly_ebt : [];
        
        $annuallyNetProfits = $incomeStatement ? $incomeStatement->annually_net_profit : [];
        $currentDataArr =$netProfits ;
        ;
        $title = __('Profit Of The Period');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $netProfits;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =$annuallyNetProfits ;
        /**
         * * End Key
         */
        
        
        /**
         * * Start Key
         */
        
        $monthlyTotalOwnersEquity = HArr::sumAtDates([$paidUpCapitals,$additionalPaidUpCapital,$legalReserve,$accumulatedRetainedEarnings,$netProfits], $sumKeys);
        $annuallyTotalOwnersEquity = HArr::sumAtDates([$paidUpCapitals,$additionalPaidUpCapital,$legalReserve,$accumulatedRetainedEarnings,$netProfits], $sumKeys);
        // $currentDataArr = $totalOwnersEquity; ;
        $title = __('Total Owners Equity');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $monthlyTotalOwnersEquity;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] = $totalOwnerEquity = HArr::getPerYearIndexForEndBalance($annuallyTotalOwnersEquity, $yearWithItsMonths) ;
        /**
         * * End Key
         */
        
        
        /**
         * * Start Key
         */
        
        $monthlyCheckErrors = HArr::subtractAtDates([$totalAssets,$totalCurrentLiabilities,$totalLongTermLiabilities,$monthlyTotalOwnersEquity], $sumKeys);
        $annuallyCheckErrors = HArr::subtractAtDates([$totalAssets,$totalCurrentLiabilities,$totalLongTermLiabilities,$annuallyTotalOwnersEquity], $sumKeys);
        // $currentDataArr = $checkErrors; ;
        $title = __('Check Errors');
        $currentTabId = $title ;
        $currentTabIndex +=1;
        
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
           'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $monthlyCheckErrors;
        $tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =HArr::getPerYearIndexForEndBalance($annuallyCheckErrors, $yearWithItsMonths) ;
        /**
         * * End Key
         */
        
        $yearIndexWithLastMonth = HArr::getLastMonthOfYear($yearWithItsMonths);
        $customerReceivablesOpeningBalance = DB::table('cash_and_bank_opening_balances')->where('project_id', $this->id)->first();
        $customerReceivablesOpeningBalance = $customerReceivablesOpeningBalance ? $customerReceivablesOpeningBalance->customer_receivable_amount : 0;
        $changeInCustomerReceivables = HArr::calculateChangeInAfter($customerReceivables, $customerReceivablesOpeningBalance, $yearIndexWithLastMonth);
    
        $fgInventoryAmount = $this->getTotalFgInventoryValue();
        $totalRawMaterialInventoryValue = $this->getTotalRawMaterialInventoryValue();
        
        $changeInFg  = HArr::calculateChangeInAfter($totalFGs, $fgInventoryAmount, $yearIndexWithLastMonth);
        $changeInRawMaterial  = HArr::calculateChangeInAfter($totalRawMaterials, $totalRawMaterialInventoryValue, $yearIndexWithLastMonth);
        $changeInOtherDebtors  = HArr::calculateChangeInAfter($totalOtherDebtors, $totalDebtorsAmounts, $yearIndexWithLastMonth);
        
        
        $supplierPayablesOpeningBalance = DB::table('supplier_payable_opening_balances')->where('project_id', $this->id)->first();
        $supplierPayablesOpeningBalance = $supplierPayablesOpeningBalance ? $supplierPayablesOpeningBalance->amount : 0;
        $changeInSupplierPayables  = HArr::calculateChangeInBefore($supplierPayables, $supplierPayablesOpeningBalance, $yearIndexWithLastMonth);
        $changeInOtherCreditors  = HArr::calculateChangeInAfter($otherCreditors, $totalCreditorsAmount, $yearIndexWithLastMonth);
		//   $years = range(0, $this->duration-1);
	
        $netChangeInWorkingCapital = HArr::sumAtDates([$changeInCustomerReceivables,$changeInFg,$changeInRawMaterial,$changeInOtherDebtors,$changeInSupplierPayables,$changeInOtherCreditors]);
        $totalCapital = HArr::sumAtDates([$totalOwnerEquity,$mediumTermLoanPerYear]);
        $equityFundingPercentages = HArr::divideTwoArrAtSameIndex($totalOwnerEquity, $totalCapital);
        $debitFundingPercentages = HArr::divideTwoArrAtSameIndex($mediumTermLoanPerYear, $totalCapital);

        
        $data = [
            'change_in_customer_receivables'=>$changeInCustomerReceivables,
            'change_in_fg_inventory'=>$changeInFg,
            'change_in_raw_material_inventory'=>$changeInRawMaterial,
            'change_in_other_debtors'=>$changeInOtherDebtors,
            'change_in_supplier_payables'=>$changeInSupplierPayables,
            'change_in_other_creditors'=>$changeInOtherCreditors,
            'net_change_in_working_capital'=>$netChangeInWorkingCapital,
            'debit_funding_percentages'=>$debitFundingPercentages,
            'equity_funding_percentages'=>$equityFundingPercentages,
        ];
        $this->balanceSheet ? $this->balanceSheet->update($data) : $this->balanceSheet()->create($data);
        
        
        return  [
            // 'step_data'=>$step_data,
            'financialYearEndMonthNumber'=>$financialYearEndMonthNumber,
            'years','studyMonthsForViews'=>$studyMonthsForViews,
            'project'=>$project,
            'tableDataFormatted'=>$tableDataFormatted,
            'defaultClasses'=>$defaultClasses,
            'title'=>__('Balance Sheet'),
            'nextRoute'=>route('view.results.dashboard', ['project'=>$project->id]),
            
        ];
    }
    protected function formatTwoLineChartWithGrowthRate(array $items)
    {
        $lineChart = [];
        foreach ($items as $key => $arrayItems) {
            $previous = 0 ;
            $hasSalesGrowth = $key == 'revenue-streams';
            foreach ($arrayItems as $year => $value) {
                $currentGrowthRate = $previous ? (($value / $previous)-1)*100 : 0   ;
                $previous = $value ;
                if ($hasSalesGrowth) {
                    $lineChart[$key][] = [
                        'date'=> $year.'-01-01' ,
                        'revenue_value'=>number_format($value/getDivisionNumber(), 2) ,
                        'growth_rate'=>number_format($currentGrowthRate, 2)
                    ];
                    
                } else {
                    $lineChart[$key][] = [
                        'date'=> $year.'-01-01' ,
                        'revenue_value'=>number_format($value/getDivisionNumber(), 2) ,
                    
                    ];
                }
                
            }
        }

        return $lineChart ;
    }
    protected function formatTwoLineChartWithPercentageOfSales(array $items)
    {
        $lineChart = [];
        foreach ($items as $key => $arrayItems) {
            $previous = 0 ;
            foreach ($arrayItems['value'] as $year => $value) {
                $previous = $value ;
                $lineChart[$key][] = [
                    'date'=> $year.'-01-01' ,
                    'value'=>number_format($value/getDivisionNumber(), 2) ,
                    'percentage'=>number_format($arrayItems['percentage'][$year]??0, 2)
                ];
                    
                
            }
        }

        return $lineChart ;
    }
    protected function formatOneLineChart(array $items)
    {
        $lineChart = [];
        foreach ($items as $key => $arrayItems) {
            $previous = 0 ;
        
            foreach ($arrayItems as $year => $value) {
                //	$currentGrowthRate = $previous ? (($value / $previous)-1)*100 : 0   ;
                $previous = $value ;
            
                $lineChart[$key][] = [
                    'date'=> $year.'-01-01' ,
                    'revenue_value'=>number_format($value/getDivisionNumber(), 2) ,
                    
                ];
                
            }
        }

        return $lineChart ;
    }
	protected function formatBarChart(array $items):array
	{
		$barChart = [];
		foreach($items  as $key => $arrayItems){
			foreach($arrayItems as $year => $value){
					// $value = $value / getDivisionNumber();
					$barChart[$year][$key] =  isset($barChart[$year][$key]) ? $barChart[$year][$key] + $value : $value;
					$barChart[$year]['year'] = strval($year);
			}
		}
		return array_values($barChart);
					
	}
    public function getDashboardViewVars():array
    {
        $project = $this ;
        
        $withSensitivity = false ;
        $incomeStatement = $project->incomeStatement;
        $balanceSheet = $project->balanceSheet;
        $cashflow = $project->cashInOutStatement;
        $formattedResult['sales_revenue'] = $incomeStatement ? $incomeStatement->total_sales_revenues : [];
        $formattedResult['growth_rate'] = $incomeStatement ? $incomeStatement->annually_sales_revenues_growth_rates : [];
        $formattedResult['gross_profit'] = $incomeStatement ? $incomeStatement->gross_profit : [];
        $formattedResult['gross_profit_percentage_of_sales'] = $incomeStatement ? $incomeStatement->annually_gross_profit_revenue_percentages : [];
        $formattedResult['ebitda'] =  $incomeStatement ? $incomeStatement->ebitda : [];
        $formattedResult['ebitda_percentage_of_sales'] = $incomeStatement ? $incomeStatement->annually_ebitda_revenue_percentages : [];
        $formattedResult['ebit'] = $incomeStatement ? $incomeStatement->ebit : [];
        $formattedResult['ebit_percentage_of_sales'] = $incomeStatement ? $incomeStatement->annually_ebit_revenue_percentages : [];
        $formattedResult['ebt'] =  $incomeStatement ? $incomeStatement->ebt : [];
        $formattedResult['ebt_percentage_of_sales'] = $incomeStatement ? $incomeStatement->annually_ebt_revenue_percentages : [];
        $formattedResult['net_profit'] =  $incomeStatement ? $incomeStatement->net_profit : [];
        $formattedResult['net_profit_percentage_of_sales'] = $incomeStatement ? $incomeStatement->annually_net_profit_revenue_percentages : [];
        $formattedExpenses['raw-material-cost'] = $incomeStatement ? $incomeStatement->total_cogs['raw_material']??[] : [];
        $formattedExpenses['labor-cost'] = $incomeStatement ? $incomeStatement->total_cogs['direct_labor']??[] : [];
        $formattedExpenses['manufacturing-overheads'] = $incomeStatement ? $incomeStatement->total_cogs['manufacturing-overheads']??[] : [];
        $formattedExpenses['marketing-expense'] = $incomeStatement ? $incomeStatement->sganda['marketing']??[] : [];
        $formattedExpenses['sales-expense'] = $incomeStatement ? $incomeStatement->sganda['sales']??[] : [];
        $formattedExpenses['general-expense'] = $incomeStatement ? $incomeStatement->sganda['general-expenses']??[] : [];
		$formattedExpensesPercentages = [];
		$formattedExpensesPercentages = $formattedExpenses ; 
		foreach($formattedExpensesPercentages as $key => $values){
			$formattedExpensesPercentages[$key] = HArr::calculatePercentageOf($formattedResult['sales_revenue'] , $values);
		}
        $years = range(0, $this->duration-1);
        $lineCharts = [
            'two-line-with-growth-rates'=>[
                'revenue-streams'=>$project->replaceYearIndexWithYear($formattedResult['sales_revenue'], $years)
            ],
            'one-line-chart'=>[
                'accumulated-revenue-streams'=>HArr::accumulateArray($project->replaceYearIndexWithYear($formattedResult['sales_revenue'], $years))
            ],
            'two-line-with-percentage'=>[
                'gross-profit'=>[
                    'value'=>$project->replaceYearIndexWithYear($formattedResult['gross_profit'], $years) ,
                    'percentage'=>$project->replaceYearIndexWithYear($formattedResult['gross_profit_percentage_of_sales'], $years)
                ],
                'EBITDA'=>[
                        'value'=>$project->replaceYearIndexWithYear($formattedResult['ebitda'], $years) ,
                    'percentage'=>$project->replaceYearIndexWithYear($formattedResult['ebitda_percentage_of_sales'], $years)
                ],
                'EBIT'=>[
                        'value'=>$project->replaceYearIndexWithYear($formattedResult['ebit'], $years) ,
                    'percentage'=>$project->replaceYearIndexWithYear($formattedResult['ebit_percentage_of_sales'], $years)
                ]
				// ,
                //     'EBT'=>[
                //         'value'=>$project->replaceYearIndexWithYear($formattedResult['ebt'], $years) ,
                //     'percentage'=>$project->replaceYearIndexWithYear($formattedResult['ebt_percentage_of_sales'], $years)
                //     ]
					,'net-profit'=>[
                        'value'=>$project->replaceYearIndexWithYear($formattedResult['net_profit'], $years) ,
                    'percentage'=>$project->replaceYearIndexWithYear($formattedResult['net_profit_percentage_of_sales'], $years)
                    ],
            ]
        ];
		$barCharts = $this->formatBarChart($this->replaceYearIndexWithYearInTwoDimArr($formattedExpensesPercentages,$years));
        $chartTitleMapping = [
            'revenue-streams'=>__('Revenue Streams'),
            'accumulated-revenue-streams'=>__('Accumulated Revenue Streams'),
            'gross-profit'=>__('Gross Profit'),
            'EBITDA'=>__('EBITDA'),
            'EBIT'=>__('EBIT'),
            'EBT'=>__('EBT'),
            'net-profit'=>__('Net Profit')
        ];
        $twoLineChartWithGrowthRates = $this->formatTwoLineChartWithGrowthRate($lineCharts['two-line-with-growth-rates']);
        $oneLineChart = $this->formatOneLineChart($lineCharts['one-line-chart']);
        $twoLineChartWithPercentageOfSales = $this->formatTwoLineChartWithPercentageOfSales($lineCharts['two-line-with-percentage']);
        
        
        $formattedDcfMethod['ebit'] = $ebit = $incomeStatement ? $incomeStatement->ebit : [];
        
        
        $taxRate = $project->tax_rate / 100 ;
        $formattedDcfMethod['taxes'] = $taxes =  HArr::MultiplyWithNumberIfPositive($formattedDcfMethod['ebit'], $taxRate);
        $formattedDcfMethod['depreciation'] = $depreciation =  $incomeStatement ? $incomeStatement->total_depreciation : [];
        $formattedDcfMethod['net-change-in-working-capital'] = $netChangeInWorkingCapital = $balanceSheet ? array_values($balanceSheet->net_change_in_working_capital) : [];
        $formattedDcfMethod['capex'] = $capex =  $cashflow ? $cashflow->fixed_asset_payments : [];
        $sum = HArr::sumAtDates([$ebit,$depreciation,$netChangeInWorkingCapital], $years);
        $minus = HArr::sumAtDates([$taxes,$capex], $years);
        $freeCashflow = HArr::subtractAtDates([$sum,$minus], $years);
        $formattedDcfMethod['free-cashflow'] = $freeCashflow ;
        $lastValueFreeCashflow = $freeCashflow[array_key_last($freeCashflow)] ??0;
        $perptual = $this->perpetual_growth_rate/100;
        $lastValueFreeCashflow = $lastValueFreeCashflow * (1+$perptual);
        $returnRate = $this->return_rate/100;
        $total =  0 ;
        $fixedAssetAmounts = [];
        foreach ($this->fixedAssets as $fixedAsset) {
            $debitFundingRate = (100-($fixedAsset->equity_funding_rate/100)) ;
            $amount = $fixedAsset->getAmount();
            $total += 	($amount*$debitFundingRate);
            $fixedAssetAmounts[$fixedAsset->id] = $amount*$debitFundingRate ;
        }
        $totalAfterInterest = [];
        foreach ($fixedAssetAmounts as $fixedAssetId => &$currentTotal) {
            $fixedAsset = FixedAsset::find($fixedAssetId);
            if ($total != 0) {
                $totalAfterInterest[$fixedAssetId] = ($currentTotal / $total) * ($fixedAsset->interest_rate/100);
                
            } else {
                $totalAfterInterest[$fixedAssetId] = 0;
            }
        }
        $costOfDebit = array_sum($totalAfterInterest);
        $debitFundingPercentages = $balanceSheet ? $balanceSheet->debit_funding_percentages  : [];
        $equityFundingPercentages = $balanceSheet ? $balanceSheet->equity_funding_percentages  : [];
        $debitFundingPercentages = HArr::MultiplyWithNumber($debitFundingPercentages, $costOfDebit);
        $equityFundingPercentages = HArr::MultiplyWithNumber($equityFundingPercentages, $returnRate);
        $wacc = HArr::sumAtDates([$equityFundingPercentages,$debitFundingPercentages]);
        unset($wacc[array_key_last($wacc)]);
        $lastKeyInWacc  = $wacc[array_key_last($wacc)] ?? 0;
        $terminalValues = [];
        foreach ($years as $index => $yearIndex) {
            $terminalValues[$yearIndex] = 0 ;
            if ($index == count($years)-1) {
                $terminalValues[$yearIndex] = $lastValueFreeCashflow /($lastKeyInWacc-$perptual);
            }
        }
        $formattedDcfMethod['terminal-value'] = $terminalValues ;
        $formattedDcfMethod['free-cashflow-with-terminal'] = $freeCashflowWithTerminal = HArr::sumAtDates([$terminalValues,$freeCashflow]) ;
        $newWacc=[];
        $index = 1 ;
        foreach ($wacc as $yearAsIndex => $wacc) {
            $newWacc[$yearAsIndex] = pow(1+$wacc, $index);
            $index++;
        }
        $formattedDcfMethod['discount-factor'] = array_values($newWacc) ;
        $formattedDcfMethod['npv'] = [0=>array_sum(HArr::divideTwoArrAtSameIndex($freeCashflowWithTerminal, array_values($newWacc)))] ;
        $formattedDcfMethod['irr'] = [Finance::irr($freeCashflowWithTerminal)*100] ;
		
        
        
        
        $yearWithItsIndexes = $project->getOperationDurationPerYearFromIndexes();
        $sensitivityFormattedResult = [];
        $sensitivityFormattedExpenses=[];
        // if($withSensitivity){
        // 	$sensitivityDashboardData = $this->generateDashboardData($project,true );
        // 	$sensitivityFormattedResult = $sensitivityDashboardData['formattedResult'];
        // 	$sensitivityFormattedExpenses = $sensitivityDashboardData['formattedExpenses'];
        // }
        $yearOrMonthsIndexes = $project->getYearOrMonthIndexes();
    
        $isYearsStudy = true;
        
        return [
        'yearsWithItsMonths' => $project->getOperationDurationPerYearFromIndexes(),
        'model'=>$project,
        'project'=>$project,
        'twoLineChartWithGrowthRates'=>$twoLineChartWithGrowthRates,
        'oneLineChart'=>$oneLineChart,
        'twoLineChartWithPercentageOfSales'=>$twoLineChartWithPercentageOfSales,
        'chartTitleMapping'=>$chartTitleMapping,
		'barChart'=>$barCharts,
        'formattedResult'=>$formattedResult,
        'formattedExpenses'=>$formattedExpenses,
		'formattedExpensesPercentages'=>$formattedExpensesPercentages,
        // 'lineChart'=>$lineChart,
        // 'lineChart'=>$lineChart,
        // 'barChart'=>$barChart,
        'yearWithItsIndexes'=>$yearWithItsIndexes,
        'sensitivityFormattedResult'=>$sensitivityFormattedResult,
        'sensitivityFormattedExpenses'=>$sensitivityFormattedExpenses,
        'withSensitivity'=>$withSensitivity,
        'yearOrMonthsIndexes'=>$yearOrMonthsIndexes,
        'isYearsStudy'=>$isYearsStudy,
        'formattedDcfMethod'=>$formattedDcfMethod
        ];
    }
    
    public function recalculateVatStatements():void
    {
        $extendedSumKeys  = array_keys($this->getExtendedStudyDurationPerYears());
        $totalProductsVatAmounts = [];
        $dateIndexWithDate = $this->getDateIndexWithDate();
        $totalRawMaterialsVatAmounts = [];
        foreach ($this->products as $product) {
            $vatAmounts = $product->getCollectionStatement()['monthly']['vat']??[];
            $totalProductsVatAmounts = HArr::sumAtDates([$vatAmounts,$totalProductsVatAmounts], $extendedSumKeys);
        }
        foreach ($this->rawMaterials as $rawMaterial) {
            $vatAmounts = $rawMaterial->getCollectionStatement()['monthly']['vat']??[] ;
            $totalRawMaterialsVatAmounts = HArr::sumAtDates([$vatAmounts,$totalRawMaterialsVatAmounts], $extendedSumKeys);
        }
        $additions = HArr::subtractAtDates([$totalProductsVatAmounts,$totalRawMaterialsVatAmounts], $extendedSumKeys);
        $vatOpeningBalance = $this->getVatOpeningBalanceAmount();
        $vatStatements = Project::calculateVatStatement($additions, $vatOpeningBalance, $dateIndexWithDate);
        $this->update([
            'vat_statements'=>$vatStatements
        ]);
    }
    // public function calculateWorkingCapitalInjection(array $accumulatedNetCash, array $datesAsStringAndIndex,array $datesIndexWithYearIndex,array $yearIndexWithYear,array $dateIndexWithDate,$debug = false)
    // {

    // 	$workingCapital = [];
    
    // 	$accumulatedNetCashPerYear = $this->arrayPerYear($accumulatedNetCash,$datesIndexWithYearIndex,$dateIndexWithDate);
    // 	$accumulatedNetCashPerYear = getMinAtEveryIndex($accumulatedNetCashPerYear);
        
    // 	$accumulatedNetCashPerYear = eachIndexMinusPreviousIfNegative($accumulatedNetCashPerYear,$debug);
    // 	foreach ($accumulatedNetCashPerYear as $yearIndex=>$newValue) {
            
    // 		$workingCapitalDate = $this->getFirstDateIndexInYearIndex($datesAsStringAndIndex, $yearIndex,$datesIndexWithYearIndex,$yearIndexWithYear,$dateIndexWithDate);
    // 		if (!is_null($workingCapitalDate)) {
    // 			$workingCapital[$workingCapitalDate] = $newValue;
    // 		}
    // 	}
    // 	return $workingCapital;
    // }
    // public function getFirstDateIndexInYearIndex(array $datesAsStringAndIndex, int $yearIndex,array $datesIndexWithYearIndex,array $yearIndexWithYear,array $dateIndexWithDate)
    // {
        
    // 	$studyDates = $this->getStudyDateFormatted($datesAsStringAndIndex,$datesIndexWithYearIndex,$yearIndexWithYear,$dateIndexWithDate);
    // 	foreach ($studyDates as $studyDateAsString=>$studyDateAsIndex) {
    // 		$currentYearIndex = $datesIndexWithYearIndex[$studyDateAsIndex];
    // 		if ($currentYearIndex == $yearIndex) {
    // 			return $studyDateAsIndex;
    // 		}
    // 	}

    // 	return null;
    // }
    // public function arrayPerYear(array $keyAndValue,array $datesIndexWithYearIndex,array $dateIndexWithDate)
    // {
    // 	$result = [];
    // 	foreach ($keyAndValue as $dateAsIndex=>$value) {
    // 		$fullDate = $dateIndexWithDate[$dateAsIndex];
    // 		$yearAsIndex = $datesIndexWithYearIndex[$dateAsIndex];
    // 		$result[$yearAsIndex][$fullDate] = $value;
    // 	}

    // 	return $result;
    // }
    
    public function getCashAndBanksAmount():float
    {
        $cashAndBankAmount   = $this->cashAndBankOpeningBalances->first();
        return $cashAndBankAmount ? $cashAndBankAmount->cash_and_bank_amount : 0 ;
    }
    public function convertArrayOfIndexKeysToIndexAsDateStringWithItsOriginalValue(array $items, array $datesAsStringAndIndex)
    {
        $newItems = [];

        foreach ($items as $dateAsIndex=>$value) {
            if (is_numeric($dateAsIndex)) {
                $newItems[$dateAsIndex]=$value;
            } else {
                $newItems[$datesAsStringAndIndex[$dateAsIndex]]=$value;
            }
        }

        return $newItems;
    }
    public function isCompleted():bool
    {
        return $this->is_completed;
    }
    public function getDefaultStartDateAsYearAndMonth()
    {
        $operationStartDate = $this->getOperationStartDate() ;
        return Carbon::make($operationStartDate)->format('Y-m');
    }
    public function getDefaultEndDateAsYearAndMonth()
    {
        $operationEndDate = $this->end_date;
        return Carbon::make($operationEndDate)->format('Y-m');
    }
    public function getInventoryAmount():float
    {
        $fgInventoryAmount = $this->getTotalFgInventoryValue();
        $totalRawMaterialInventoryValue = $this->getTotalRawMaterialInventoryValue();
        return $fgInventoryAmount +  $totalRawMaterialInventoryValue ;
    }
    public function getTotalFgInventoryValue():float
    {
        $fgInventoryAmount = 0 ;
        foreach ($this->products as $product) {
            $fgInventoryAmount+=$product->getFgInventoryValue();
        }
        return $fgInventoryAmount;
    }
    public function getTotalRawMaterialInventoryValue():float
    {
        $totalRawMaterialInventoryValue = 0;
        foreach ($this->rawMaterials as $rawMaterial) {
            $totalRawMaterialInventoryValue+= $rawMaterial->getBeginningInventoryValue();
        }
        return $totalRawMaterialInventoryValue;
    }
    public function cashInOutStatement():HasOne
    {
        return $this->hasOne(CashInOutStatement::class,'project_id','id');
    }
    public function incomeStatement():HasOne
    {
        return $this->hasOne(IncomeStatement::class,'project_id','id');
    }
    public function balanceSheet():HasOne
    {
        return $this->hasOne(BalanceSheet::class,'project_id','id');
    }
    public function getCorporateTaxesPayable():float
    {
        $corporateTaxesPayable = DB::table('vat_and_credit_withhold_tax_opening_balances')->where('project_id',$this->id)->first();
        return $corporateTaxesPayable ? $corporateTaxesPayable->corporate_taxes_payable  : 0 ;
    }
	


}
