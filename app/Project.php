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
use Symfony\Component\Console\Helper\Helper;

class Project extends Model
{
    protected $connection = 'mysql';
    use SoftDeletes,HasBasicStoreRequest,HasIndexedDates,HasManpowerExpense,HasFixedAsset,HasCollectionOrPaymentStatement;
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
            $projectUnderProgressFFE = $projectUnderProgressService->calculateForFFE($fixedAsset->getEndDateAsIndex(), $ffeExecutionAndPayment, $ffeLoanInterestAmounts, $ffeLoanWithdrawalInterestAmounts, $this, $operationStartDateAsIndex, $datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber);
        
            $transferredDateForFFEAsIndex = array_key_last($projectUnderProgressFFE['transferred_date_and_vales']??[]);
            $ffeAssetItems = [];
            //   $totalOfFFEItemForFFE = [];
            // if($fixedAsset ){
            $ffeAssetItems = $fixedAsset->calculateFFEAssetsForFFE($transferredDateForFFEAsIndex, Arr::last($projectUnderProgressFFE['transferred_date_and_vales']??[], null, 0), $studyDates, $studyEndDateAsIndex, $this);
			
			$fixedAssetAddition = $ffeAssetItems['additions']??[];
			
            // dd($ffePayment,$fixedAssetAddition);
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
			$ffeAcquisitionDatesAndAmounts = $this->convertArrayOfIndexKeysToIndexAsDateStringWithItsOriginalValue($ffeAcquisitionDatesAndAmounts,$datesAsStringAndIndex);
			$ffeAcquisitionPayments = $fixedAsset->getFfePayment() ;
			$ffePayable = [];
			if(count($ffeAcquisitionDatesAndAmounts)){
				$ffePayable=(new FixedAssetsPayableEndBalance())->calculateEndBalance($ffeAcquisitionDatesAndAmounts,$ffeAcquisitionPayments,$dateIndexWithDate);
				$ffePayable = $ffePayable['monthly']['end_balance'] ?? [];
			}
		
			
            $fixedAsset->update([
                'capitalization_statement'=>$projectUnderProgressFFE,
                'depreciation_statement'=>$ffeAssetItems,
                'admin_depreciations'=>$adminDepreciations,
				'ffe_equity_payment'=>$ffeEquityPayment,
				'ffe_loan_withdrawal'=>$ffeLoanWithdrawal,
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
        $productExpenseAllocations = DB::table('product_expense_allocations')->where('project_id', $this->id)->get()->toArray();
        $result = [];
        $depreciationResult = [];
        foreach ($productExpenseAllocations as $index => $productExpenseAllocation) {
			$productId = $productExpenseAllocation->product_id;
			$payload = $productExpenseAllocation->payload;
			$isDepreciation = $productExpenseAllocation->is_depreciation || $productExpenseAllocation->is_opening_depreciation;
            $payload = json_decode($payload);
            foreach ($payload as $dateAsIndex => $value) {
                $result[$productId][$dateAsIndex] = isset($result[$productId][$dateAsIndex]) ? $result[$productId][$dateAsIndex] + $value : $value ;
				if($isDepreciation){
					                $depreciationResult[$productId][$dateAsIndex] = isset($depreciationResult[$productId][$dateAsIndex]) ? $depreciationResult[$productId][$dateAsIndex] + $value : $value ;
				}
            }
        }
        foreach ($result as $productId => $totals) {
            DB::table('products')->where('id', $productId)->update([
                'product_overheads_allocation'=>json_encode($totals),
				'product_depreciation_allocation'=>json_encode($depreciationResult[$productId])
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
			'product_depreciation_allocation'=>'product_depreciation_allocation'
        ];
        foreach ($products as $product) {
            $productId = $product->id ;
            $quantityStatement = $product->product_inventory_qt_statement; 
            $totalAvailableQuantity = $quantityStatement['total_quantity_available']??[];
            $salesQuantity = $quantityStatement['sales_quantity']??[];
            $fgBeginningInventoryBreakdowns = $product->getFgBeginningInventoryBreakdowns() ;
			$fgBeginningInventoryBreakdowns['product_depreciation_allocation'] = [];
            foreach ($fgBeginningInventoryBreakdowns as $inventoryItemType => $inventoryItemValues) {
                $fgBeginningInventoryBreakdownValue =  $inventoryItemValues['value']??0	;
                $currentColumnMapping = $columnNameMapping[$inventoryItemType];
                $fgStatementValues[$productId][$inventoryItemType]['beginning_value'] = $fgBeginningInventoryBreakdownValue;
                $currentManufacturingExpenseArr = $currentColumnMapping == 'product_raw_material_consumed' ?  $product->{$currentColumnMapping}['total'] :   (array)$product->{$currentColumnMapping} ;
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
                    
                    if($inventoryItemType =='product_depreciation_allocation'){
						$currentCogsAtDate = 0 ;
						$currentEndBalanceAtDate = 0 ;
					}
					
                    $fgValueStatement[$productId]['cogs'][$dateAsIndex] = isset($fgValueStatement[$productId]['cogs'][$dateAsIndex]) ? $fgValueStatement[$productId]['cogs'][$dateAsIndex] +  $currentCogsAtDate : $currentCogsAtDate ;
                    $fgValueStatement[$productId]['end_balance'][$dateAsIndex] = isset($fgValueStatement[$productId]['end_balance'][$dateAsIndex]) ? $fgValueStatement[$productId]['end_balance'][$dateAsIndex] +  $currentCogsAtDate : $currentEndBalanceAtDate;
                    
              
                    
                }
                
                // $currentManufacturingExpenseArr = HArr::sumWithNumber($currentManufacturingExpenseArr,$fgBeginningInventoryBreakdownValue);
                
            }
            $product->update([
                'product_manpower_statement'=>$fgStatementValues[$productId]['direct_labor_value']??[],
                'raw_material_statement'=>$fgStatementValues[$productId]['raw_material_value']??[],
                'product_overheads_statement'=>$fgStatementValues[$productId]['manufacturing_overheads_value']??[],
				'product_depreciation_statement'=>$fgStatementValues[$productId]['product_depreciation_allocation']??[],
                'product_inventory_value_statement'=>$fgValueStatement
            ]);
        }
        //
        //
        // foreach($fgStatementValues as $productId => $statementArr){
        // 	$prodc
        // 	$productManpowerStatement = $statementArr['direct_labor_value']??[];
            
            
        // }
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
			
        }
		$finalResult =[];
		foreach($productExpenses as $fixedAssetOpeningBalanceId => $hisProductAllocations){
			$fixedAssetOpeningBalance  = FixedAssetOpeningBalance::find($fixedAssetOpeningBalanceId);
			$fixedAssetOpeningBalance->update([
				'admin_depreciations'=>$adminAllocationPercentages[$fixedAssetOpeningBalanceId]??[]	
			]);
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
	public function getViewVars():array
	{
		$step_data =(new Redirects)->steps($this,'edit');
        $this->month = isset($this->start_date) ? date("m",strtotime($this->start_date)) : null;
        $this->year = isset($this->start_date) ? date("Y",strtotime($this->start_date)) : null;
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
	public  function getRawMaterialViewVars():array
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
            // 'sales_revenue'=>0,
            'cost-of-service'=>1 ,
            'gross-profit'=>2 ,
            'other-operation-expense'=>3,
            'marketing'=>4 ,
            'sales'=>5,
            'general-expenses'=>6,
            'ebitda'=>7,
            'ebit'=>8,
            
            'ebt'=>10,
            'corporate-taxes'=>11,
            'net-profit'=>12
        ];
        
        // $financialYearsEndMonths = $project->getFinancialYearsEndMonths();
        
        
        
        
        $grossProfitOrderIndex = $orderIndexPerExpenseCategory['gross-profit'];
        $ebitdaOrderIndex = $orderIndexPerExpenseCategory['ebitda'];
        $ebitOrderIndex = $orderIndexPerExpenseCategory['ebit'];
        $ebtOrderIndex = $orderIndexPerExpenseCategory['ebt'];
        $corporateTaxesOrderIndex = $orderIndexPerExpenseCategory['corporate-taxes'];
        $netProfitOrderIndex = $orderIndexPerExpenseCategory['net-profit'];
        
        // $studyMonthsForViews=$project->getStudyDurationPerYearFromIndexesForView();
		$studyMonthsForViews = array_flip($project->getOperationDatesAsDateAndDateAsIndexToStudyEndDate());
         
        
        
        
        
        
		
        
        /**
		 * * First Tab Sales Revenue
		*/
		$tableDataFormatted[0]['main_items']['sales-revenue']['options'] = array_merge([
		   'title'=>__('Sales Revenue')
		], $defaultNumericInputClasses);
		$yearWithItsMonths=$project->getYearIndexWithItsMonthsAsIndexAndString();
		$tableDataFormatted[0]['main_items']['growth-rate']['options'] = array_merge($defaultPercentageInputClasses, ['title'=>__('Growth Rate %')]);
		$productsTotals = [];
		$sumKeys = array_keys($studyMonthsForViews);
        foreach ($products as $product) {
            $tableDataFormatted[0]['sub_items'][$product->getName()]['options'] =array_merge([
                'title'=>$product->getName(),
            ], $defaultNumericInputClasses);
			$monthlySalesTargetValues = $product->monthly_sales_target_values;
            $tableDataFormatted[0]['sub_items'][$product->getName()]['data'] = $monthlySalesTargetValues;
			$tableDataFormatted[0]['sub_items'][$product->getName()]['year_total'] = HArr::sumPerYearIndex($monthlySalesTargetValues,$yearWithItsMonths);
			$productsTotals = HArr::sumAtDates([$monthlySalesTargetValues?:[],$productsTotals],$sumKeys);
        }
		
		$tableDataFormatted[0]['main_items']['sales-revenue']['data'] = $productsTotals;
		$tableDataFormatted[0]['main_items']['sales-revenue']['year_total'] = $salesRevenueYearTotal = HArr::sumPerYearIndex($productsTotals,$yearWithItsMonths);
		$tableDataFormatted[0]['main_items']['growth-rate']['data'] = HArr::calculateGrowthRate($productsTotals);
		
		
		/**
		 * * Second Tab Cost Of Goods Sold 
		 */
		
		
        $tableDataFormatted[1]['main_items']['cost-of-goods-sold']['options'] = array_merge([
           'title'=>__('Cost Of Goods Sold')
        ], $defaultNumericInputClasses);
        
        
        $tableDataFormatted[1]['main_items']['revenues-percentage']['options'] = array_merge([
            'title'=>__('%/Revenues')
        ], $defaultPercentageInputClasses);
		$products = DB::table('products')->where('project_id',$project->id)->get();
		// $rawMaterialStatements  = $products->pluck('raw_material_statement');
		// $manpowerStatement   = $products->pluck('product_manpower_statement');
		$totalCogs = [
			'raw_material'=>[],
			'direct_labor'=>[],
			'manufacturing-overheads'=>[]
		];
		foreach($products as $product){
			
			$rawMaterialCogs = $product->raw_material_statement ? (array)(@json_decode($product->raw_material_statement)->cogs) : [];
			$directLaborCogs =$product->product_manpower_statement ?  (array)(@json_decode($product->product_manpower_statement)->cogs) : [] ;
			$manufacturingCogs = $product->product_overheads_statement ? (array)(@json_decode($product->product_overheads_statement)->cogs) : [] ;
			$totalCogs['raw_material'] = HArr::sumAtDates([$rawMaterialCogs,$totalCogs['raw_material']],$sumKeys);
			$totalCogs['direct_labor'] = HArr::sumAtDates([$directLaborCogs,$totalCogs['direct_labor']],$sumKeys);
			$totalCogs['manufacturing-overheads'] = HArr::sumAtDates([$manufacturingCogs,$totalCogs['manufacturing-overheads']],$sumKeys);
			
		}
		
        foreach (['raw_material'=>__('Raw Materials') , 'direct_labor'=>__('Direct Labors') , 'manufacturing-overheads'=>__('Manufacturing Overheads')] as $id => $title) {
			
            $tableDataFormatted[1]['sub_items'][$id]['options'] =array_merge([
                'title'=>$title,
            ], $defaultNumericInputClasses); 
			$currentTotalCogs = $totalCogs[$id]??[];
			$tableDataFormatted[1]['sub_items'][$id]['data'] = $currentTotalCogs ;
			$tableDataFormatted[1]['sub_items'][$id]['year_total'] = HArr::sumPerYearIndex($currentTotalCogs,$yearWithItsMonths);
        }
		$totalCostOfGoodsSold = HArr::sumAtDates(array_values($totalCogs),$sumKeys) ;
		$tableDataFormatted[1]['main_items']['cost-of-goods-sold']['data'] =  $totalCostOfGoodsSold ; 
		$tableDataFormatted[1]['main_items']['cost-of-goods-sold']['year_total'] =$costOfGodSoldTotalPerYear = HArr::sumPerYearIndex($totalCostOfGoodsSold,$yearWithItsMonths);
		$tableDataFormatted[1]['main_items']['revenues-percentage']['data'] =  HArr::calculatePercentageOf($productsTotals,$totalCostOfGoodsSold) ; 
		$tableDataFormatted[1]['main_items']['revenues-percentage']['year_total'] = HArr::calculatePercentageOf($salesRevenueYearTotal,$costOfGodSoldTotalPerYear);
		
		
        /**
		 * Three Items 
		*/
        
		$totalGrossProfit = HArr::subtractAtDates([$productsTotals,$totalCostOfGoodsSold],$sumKeys) ;
		$tableDataFormatted[2]['main_items']['gross-profit']['data'] =  $totalGrossProfit ; 
		$tableDataFormatted[2]['main_items']['gross-profit']['year_total'] = $grossProfitTotalPerYear = HArr::sumPerYearIndex($totalGrossProfit,$yearWithItsMonths);
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['gross-profit']['options']['title'] = __('Gross Profit');
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[2]['main_items']['% Of Revenue']['data'] =  HArr::calculatePercentageOf($productsTotals,$totalGrossProfit) ; 
		$tableDataFormatted[2]['main_items']['% Of Revenue']['year_total'] = HArr::calculatePercentageOf($salesRevenueYearTotal,$grossProfitTotalPerYear);

        /**
		 * * Four Item
		 */
        // general-expenses
		// sales
		// marketing
        $tableDataFormatted[5]['main_items']['sganda']['options'] = array_merge([
           'title'=>__('Sales, Marketing & General Exp.')
        ], $defaultNumericInputClasses);
        
		$expenses = DB::table('expenses')->whereIn('category_id',['general-expenses','sales','marketing'])->where('project_id',$project->id)->get();
		$columnName = [
			'expense_as_percentage'=>'expense_as_percentages',
			'fixed_monthly_repeating_amount'=>'monthly_repeating_amounts',
			'one_time_expense'=>'payload.monthly_one_time'
		];
		$resultPerCategory = [];
		foreach($expenses as $index=>$expense){
			$categoryId = $expense->category_id;
			$relationName  = $expense->relation_name;
			$currentColumnName = $columnName[$relationName];
			$amount = $relationName == 'one_time_expense' ? json_decode($expense->payload)->monthly_one_time :  json_decode($expense->{$currentColumnName});
			$currentAmount = (array)$amount ;
			$resultPerCategory[$categoryId] = isset($resultPerCategory[$categoryId]) ? HArr::sumAtDates([$currentAmount,$resultPerCategory[$categoryId]],$sumKeys):$currentAmount ;
		}
		
        
            
        
        $tableDataFormatted[5]['main_items']['revenues-percentage']['options'] = array_merge([
            'title'=>__('%/Revenues')
        ], $defaultPercentageInputClasses);
        foreach (['sales'=>__('Sales Expenses') , 'marketing'=>__('Marketing Expenses') , 'general-expenses'=>__('General Expenses')] as $id => $title) {
            $orderId = $orderIndexPerExpenseCategory[$id];
            $tableDataFormatted[5]['sub_items'][$id]['options'] =array_merge([
                'title'=>$title,
            ], $defaultNumericInputClasses);  
			$currentTotalForCategory = $resultPerCategory[$id] ?? [];
			$tableDataFormatted[5]['sub_items'][$id]['data'] =$currentTotalForCategory;
			$tableDataFormatted[5]['sub_items'][$id]['year_total'] = HArr::sumPerYearIndex($currentTotalForCategory,$yearWithItsMonths);
        }
		$totalSGANDA = HArr::sumAtDates(array_values($resultPerCategory),$sumKeys);
		$tableDataFormatted[5]['main_items']['sganda']['data'] =$totalSGANDA;
		$tableDataFormatted[5]['main_items']['sganda']['year_total'] =$sgandaTotalPerYear = HArr::sumPerYearIndex($totalSGANDA,$yearWithItsMonths);
          $tableDataFormatted[5]['main_items']['revenues-percentage']['data'] =  HArr::calculatePercentageOf($productsTotals,$totalSGANDA) ; 
        		$tableDataFormatted[5]['main_items']['revenues-percentage']['year_total'] = HArr::calculatePercentageOf($salesRevenueYearTotal,$sgandaTotalPerYear);
		  
		  
		  /**
		   * * Five Item
		   */
		  
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['ebitda']['options']['title'] = __('EBITDA');
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
		$depreciationCogs =DB::table('products')->where('project_id',$project->id)->pluck('product_depreciation_statement')->toArray();
		$formattedDepreciationCogs = [];
		foreach($depreciationCogs as $index => $depreciationCogArr){
			$formattedDepreciationCogs[$index] = ((array)json_decode($depreciationCogArr))['cogs']??[];
		}
		$formattedDepreciationCogs = HArr::sumAtDates($formattedDepreciationCogs,$sumKeys);
		$fixedAssetAdminDepreciations = DB::table('fixed_assets')->where('project_id',$project->id)->pluck('admin_depreciations')->toArray();
		 array_walk($fixedAssetAdminDepreciations,function(&$value){
			$value = (array)json_decode($value);
		});
		$fixedAssetOpeningBalancesAdminDepreciations = DB::table('fixed_asset_opening_balances')->where('project_id',$project->id)->pluck('admin_depreciations')->toArray();
		 array_walk($fixedAssetOpeningBalancesAdminDepreciations,function(&$value){
			$value = (array)json_decode($value);
		});
		$totalFixedAssetAdminDepreciation = HArr::sumAtDates(array_merge($fixedAssetAdminDepreciations,$fixedAssetOpeningBalancesAdminDepreciations),$sumKeys);
		$sum = HArr::sumAtDates([$formattedDepreciationCogs,$totalFixedAssetAdminDepreciation,$totalGrossProfit],$sumKeys);
		$editda = HArr::subtractAtDates([$sum,$totalSGANDA],$sumKeys) ;
		
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['ebitda']['data'] = $editda;
		$tableDataFormatted[$ebitdaOrderIndex]['main_items']['ebitda']['year_total'] =$ebitdaTotalPerYear= HArr::sumPerYearIndex($editda,$yearWithItsMonths);
		$tableDataFormatted[$ebitdaOrderIndex]['main_items']['% Of Revenue']['data'] =  HArr::calculatePercentageOf($productsTotals,$editda);
			$tableDataFormatted[$ebitdaOrderIndex]['main_items']['% Of Revenue']['year_total'] = HArr::calculatePercentageOf($salesRevenueYearTotal,$ebitdaTotalPerYear);
		/**
		 * * End Five Item
		 */
		
		
		/**
		 * * Start Sixth Item 
		 */
		   $tableDataFormatted[$ebitOrderIndex]['main_items']['ebit']['options']['title'] = __('EBIT');
		   $ebit = HArr::subtractAtDates([$totalGrossProfit,$totalSGANDA],$sumKeys) ;
		   $tableDataFormatted[$ebitOrderIndex]['main_items']['ebit']['data'] = $ebit ;
		   $tableDataFormatted[$ebitOrderIndex]['main_items']['ebit']['year_total'] =$ebitTotalPerYear= HArr::sumPerYearIndex($ebit,$yearWithItsMonths);
		   $tableDataFormatted[$ebitOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
		   $tableDataFormatted[$ebitOrderIndex]['main_items']['% Of Revenue']['data'] = HArr::calculatePercentageOf($productsTotals,$ebit);
				$tableDataFormatted[$ebitOrderIndex]['main_items']['% Of Revenue']['year_total'] = HArr::calculatePercentageOf($salesRevenueYearTotal,$ebitTotalPerYear);
		/**
		 * * End  Sixth Item 
		 */
		
		
		
		
		/**
		 * * Start Seven Item 
		 */
		
		$tableDataFormatted[9]['main_items']['finance_exp']['options'] = array_merge([
           'title'=>__('Finance Expense')
        ], $defaultNumericInputClasses);
		$loanSchedulePayments = DB::table('loan_schedule_payments')->where('loan_schedule_payments.project_id',$project->id)->join('fixed_assets','fixed_assets.id','=','fixed_asset_id')->selectRaw('interestAmount,name')->get();
		$openingLoans = DB::table('long_term_loan_opening_balances')->where('project_id',$project->id)->pluck('interests')->toArray();
		$openingLoansTotal=[];
		foreach($openingLoans as $openingLoanInterest){
			$openingLoansTotal= HArr::sumAtDates([(array)json_decode($openingLoanInterest),$openingLoansTotal],$sumKeys);
		}
		// $loanSchedulePaymentsFormatted = [];
		foreach($loanSchedulePayments as $loanSchedulePayment){
			 $tableDataFormatted[9]['sub_items'][$loanSchedulePayment->name]['options'] =array_merge([
                'title'=>__('Interest Expense') . ' '.$loanSchedulePayment->name,
            ], $defaultNumericInputClasses);
			$currentInterestAmounts = (array)json_decode($loanSchedulePayment->interestAmount);
            $tableDataFormatted[9]['sub_items'][$loanSchedulePayment->name]['data'] = $currentInterestAmounts;
			$tableDataFormatted[9]['sub_items'][$loanSchedulePayment->name]['year_total'] = HArr::sumPerYearIndex($currentInterestAmounts,$yearWithItsMonths);
		}
		if(count($openingLoansTotal)){
			$tableDataFormatted[9]['sub_items'][__('Opening Balance Loans Interests')]['data'] = $openingLoansTotal;
			$tableDataFormatted[9]['sub_items'][__('Opening Balance Loans Interests')]['year_total'] = HArr::sumPerYearIndex($openingLoansTotal,$yearWithItsMonths);
		}
		$totalFinanceExpense = HArr::sumAtDates(array_column($tableDataFormatted[9]['sub_items'],'data'),$sumKeys);
		$tableDataFormatted[9]['main_items']['finance_exp']['data'] = $totalFinanceExpense;
		$tableDataFormatted[9]['main_items']['finance_exp']['year_total'] = $financeExpenseTotalPerYear = HArr::sumPerYearIndex($totalFinanceExpense,$yearWithItsMonths);
        
        $tableDataFormatted[9]['main_items']['revenues-percentage']['options'] = array_merge([
            'title'=>__('%/Revenues')
        ], $defaultPercentageInputClasses);
		
		$tableDataFormatted[9]['main_items']['revenues-percentage']['data'] =  HArr::calculatePercentageOf($productsTotals,$totalFinanceExpense) ;
			$tableDataFormatted[9]['main_items']['revenues-percentage']['year_total'] = HArr::calculatePercentageOf($salesRevenueYearTotal,$financeExpenseTotalPerYear);
		/**
		 * * End Seven Item 
		 */
		
		/**
		 * * Start Eight Item 
		 */
		
		
		   $ebt = HArr::subtractAtDates([$ebit,$totalFinanceExpense],$sumKeys);
		 $tableDataFormatted[$ebtOrderIndex]['main_items']['ebt']['options']['title'] = __('EBT');
		 $tableDataFormatted[$ebtOrderIndex]['main_items']['ebt']['data'] = $ebt;
		 $tableDataFormatted[$ebtOrderIndex]['main_items']['ebt']['year_total'] =$ebtTotalPerYear = HArr::sumPerYearIndex($ebt,$yearWithItsMonths);
        $tableDataFormatted[$ebtOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[$ebtOrderIndex]['main_items']['% Of Revenue']['data']=  HArr::calculatePercentageOf($productsTotals,$ebt);
			$tableDataFormatted[$ebtOrderIndex]['main_items']['% Of Revenue']['year_total'] = HArr::calculatePercentageOf($salesRevenueYearTotal,$ebtTotalPerYear);
		   
		
			/**
		 * * End Eight Item 
		 */
		
			
				/**
		 * * Start Nine Item 
		 */
		
		$corporateTaxesRate = $project->tax_rate/100;
		   $corporateTaxes =  HArr::MultiplyWithNumber($ebt,$corporateTaxesRate);
		 $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['corporate-taxes']['options']['title'] = __('Corporate Taxes');
		  $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['corporate-taxes']['data'] = $corporateTaxes;
		  $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['corporate-taxes']['year_total'] = $corporateTaxesTotalPerYear = HArr::sumPerYearIndex($corporateTaxes,$yearWithItsMonths);
        $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['% Of Revenue']['data']=  HArr::calculatePercentageOf($productsTotals,$corporateTaxes);
      $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['% Of Revenue']['year_total'] = HArr::calculatePercentageOf($salesRevenueYearTotal,$corporateTaxesTotalPerYear);
		
	  $totalProductsWithholdAmounts = [];
	  foreach($this->products as $product){
		  $withholdAmounts = $product->getCollectionStatement()['monthly']['withhold_amount']??[];
		  $totalProductsWithholdAmounts = HArr::sumAtDates([$withholdAmounts,$totalProductsWithholdAmounts],$sumKeys);
		}
		$dateIndexWithDate = $this->getDateIndexWithDate();
		$calculatedCorporateTaxesPerYear = HArr::sumPerYearIndex($corporateTaxes,$yearWithItsMonths) ;
		foreach($calculatedCorporateTaxesPerYear as $dateIndex => &$value){
			if($value < 0 ){
				$value =0 ;
			}
		}
		// dd($totalProductsWithholdAmounts);
		$corporateTaxesStatement  = Project::calculateCorporateTaxesStatement($totalProductsWithholdAmounts,$calculatedCorporateTaxesPerYear,0,$dateIndexWithDate);
		$this->update([
			'corporate_taxes_statement'=>$corporateTaxesStatement
		]);
			/**
		 * * End Nine Item 
		 */
		
			
			/**
		 * * Start  Sixth Item 
		 */
		
			$netProfit = HArr::subtractAtDates([$ebt,$corporateTaxes],$sumKeys);
			     $tableDataFormatted[$netProfitOrderIndex]['main_items']['net-profit']['options']['title'] = __('Net Profit');
			     $tableDataFormatted[$netProfitOrderIndex]['main_items']['net-profit']['data'] = $netProfit;
				 $tableDataFormatted[$netProfitOrderIndex]['main_items']['net-profit']['year_total'] = $netProfitTotalPerYear = HArr::sumPerYearIndex($netProfit,$yearWithItsMonths);
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['% Of Revenue']['data'] = HArr::calculatePercentageOf($productsTotals,$netProfit);
		$tableDataFormatted[$netProfitOrderIndex]['main_items']['% Of Revenue']['year_total'] = HArr::calculatePercentageOf($salesRevenueYearTotal,$netProfitTotalPerYear);
		
		return [
            'step_data'=>$step_data,
            'financialYearEndMonthNumber'=>$financialYearEndMonthNumber,
            'years','studyMonthsForViews'=>$studyMonthsForViews,
            'project'=>$this,
            'tableDataFormatted'=>$tableDataFormatted,
            'defaultClasses'=>$defaultClasses
            
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
      
        // $studyMonthsForViews=$project->getStudyDurationPerYearFromIndexesForView();
		$studyMonthsForViews = array_flip($project->getOperationDatesAsDateAndDateAsIndexToStudyEndDate());
         
        $yearWithItsMonths=$project->getYearIndexWithItsMonthsAsIndexAndString();
        
        
        
        
		
        
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
	
			
			
        foreach ($products as $product) {
			$collectionPayment = $product->collection_statement['monthly']['payment']??[];
            $tableDataFormatted[0]['sub_items'][$product->getName()]['options'] =array_merge([
                'title'=>$product->getName(),
            ], $defaultNumericInputClasses);
            $tableDataFormatted[0]['sub_items'][$product->getName()]['data'] = $collectionPayment;
			$tableDataFormatted[0]['sub_items'][$product->getName()]['year_total'] = HArr::sumPerYearIndex($collectionPayment,$yearWithItsMonths);
        
		}
		$totalFixedAssetEquity = [];
		$totalFixedAssetLoanWithdrawal = [];
		foreach($project->fixedAssets as $fixedAsset)
			{
				$totalFixedAssetEquity = HArr::sumAtDates([$fixedAsset->getFfeEquityPayment(),$totalFixedAssetEquity],$sumKeys);
				$totalFixedAssetLoanWithdrawal = HArr::sumAtDates([$fixedAsset->getFfeLoanWithdrawal(),$totalFixedAssetLoanWithdrawal],$sumKeys);
			}
			$otherDebtorsOpeningBalances = DB::table('other_debtors_opening_balances')->where('project_id',$project->id)->pluck('payload');
			$totalOtherDebtorsOpeningBalances = [];
			foreach($otherDebtorsOpeningBalances as $otherDebtorsOpeningBalance){
				$otherDebtorsOpeningBalance= (array)json_decode($otherDebtorsOpeningBalance);
				$totalOtherDebtorsOpeningBalances = HArr::sumAtDates([$totalOtherDebtorsOpeningBalances,$otherDebtorsOpeningBalance],$sumKeys);
			}
			if(count($totalOtherDebtorsOpeningBalances)){
				$tableDataFormatted[0]['sub_items'][__('Other Debtors')]['data'] = $totalOtherDebtorsOpeningBalances;
				$tableDataFormatted[0]['sub_items'][__('Other Debtors')]['year_total'] = HArr::sumPerYearIndex($totalOtherDebtorsOpeningBalances,$yearWithItsMonths);
			}
			if(count($totalFixedAssetEquity)){
				$tableDataFormatted[0]['sub_items'][__('Equity Injection')]['data'] = $totalFixedAssetEquity;
				$tableDataFormatted[0]['sub_items'][__('Equity Injection')]['year_total'] = HArr::sumPerYearIndex($totalFixedAssetEquity,$yearWithItsMonths);
			}
			if(count($totalFixedAssetLoanWithdrawal)){
				$tableDataFormatted[0]['sub_items'][__('Loan Withdrawals')]['data'] = $totalFixedAssetLoanWithdrawal;
				$tableDataFormatted[0]['sub_items'][__('Loan Withdrawals')]['year_total'] = HArr::sumPerYearIndex($totalFixedAssetLoanWithdrawal,$yearWithItsMonths);
			}
			
			$totalCashIn = HArr::sumAtDates(array_column($tableDataFormatted[0]['sub_items'],'data'),$sumKeys);
			
		
		$tableDataFormatted[0]['main_items']['cash-in-flow']['data'] = $totalCashIn;
		$tableDataFormatted[0]['main_items']['cash-in-flow']['year_total'] = HArr::sumPerYearIndex($totalCashIn,$yearWithItsMonths);
		
		
		
		
		
		
		
		
		
		
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
        foreach ($rawMaterials as $rawMaterial) {
			$collectionPayment = $rawMaterial->collection_statement['monthly']['payment']??[];
            $tableDataFormatted[$currentTabIndex]['sub_items'][$rawMaterial->getName()]['options'] =array_merge([
                'title'=>$rawMaterial->getName(),
            ], $defaultNumericInputClasses);
            $tableDataFormatted[$currentTabIndex]['sub_items'][$rawMaterial->getName()]['data'] = $collectionPayment;
			$tableDataFormatted[$currentTabIndex]['sub_items'][$rawMaterial->getName()]['year_total'] = HArr::sumPerYearIndex($collectionPayment,$yearWithItsMonths);
			$creditWithholdPayment = $rawMaterial->credit_withhold_statement['monthly']['payment']??[];
			$totalCreditWithholdTaxPayments = HArr::sumAtDates([$totalCreditWithholdTaxPayments,$creditWithholdPayment],$sumKeys);
        }
		if($this->getCreditWithholdOpeningBalanceAmount() > 0){
			$firstItemValue = $totalCreditWithholdTaxPayments[0] ?? 0 ; 
			$totalCreditWithholdTaxPayments[0] = $firstItemValue + $this->getCreditWithholdOpeningBalanceAmount();
		}
		$totalSalaryPayments = [];
		$totalTaxAndSocialInsurances = [];
		$salaryPayments = DB::table('manpowers')->where('project_id',$project->id)->pluck('salary_payments')->toArray();
		$salaryTaxAndSocialInsurances = DB::table('manpowers')->where('project_id',$project->id)->pluck('tax_and_social_insurance_statement')->toArray();
		foreach($salaryPayments as $index=>$manpowerSalaryPayment){
			$manpowerSalaryPayment = (array)json_decode($manpowerSalaryPayment);
			$salaryTaxAndSocialInsurance = ((array)json_decode($salaryTaxAndSocialInsurances[$index]))['monthly']??[];
			$salaryTaxAndSocialInsurance = $salaryTaxAndSocialInsurance->payment;
			$totalSalaryPayments  = HArr::sumAtDates([$totalSalaryPayments,$manpowerSalaryPayment],$sumKeys);
			$totalTaxAndSocialInsurances  = HArr::sumAtDates([$totalTaxAndSocialInsurances,$salaryTaxAndSocialInsurance],$sumKeys);
		}
		$tableDataFormatted[$currentTabIndex]['sub_items'][__('Salaries Payments')]['data'] = $totalSalaryPayments;
		$tableDataFormatted[$currentTabIndex]['sub_items'][__('Salaries Payments')]['year_total'] = HArr::sumPerYearIndex($totalSalaryPayments,$yearWithItsMonths);
		
		$tableDataFormatted[$currentTabIndex]['sub_items'][__('Salary Taxes & Social Insurance')]['data'] = $totalTaxAndSocialInsurances;
		$tableDataFormatted[$currentTabIndex]['sub_items'][__('Salary Taxes & Social Insurance')]['year_total'] = HArr::sumPerYearIndex($totalTaxAndSocialInsurances,$yearWithItsMonths);		
		
		$tableDataFormatted[$currentTabIndex]['sub_items'][__('Credit Withhold Taxes Payments')]['data'] = $totalCreditWithholdTaxPayments;
		$tableDataFormatted[$currentTabIndex]['sub_items'][__('Credit Withhold Taxes Payments')]['year_total'] = HArr::sumPerYearIndex($totalCreditWithholdTaxPayments,$yearWithItsMonths);
		
		$totalVatsPayments = $this->vat_statements['monthly']['payment']??[];
		
		$tableDataFormatted[$currentTabIndex]['sub_items'][__('VAT Payments')]['data'] = $totalVatsPayments;
		$tableDataFormatted[$currentTabIndex]['sub_items'][__('VAT Payments')]['year_total'] = HArr::sumPerYearIndex($totalVatsPayments,$yearWithItsMonths);
		
		
		$totalCorporateTaxesPayments = $this->corporate_taxes_statement['monthly']['payment']??[];
		$tableDataFormatted[$currentTabIndex]['sub_items'][__('Corporate Taxes Payments')]['data'] = $totalCorporateTaxesPayments;
		$tableDataFormatted[$currentTabIndex]['sub_items'][__('Corporate Taxes Payments')]['year_total'] = HArr::sumPerYearIndex($totalCorporateTaxesPayments,$yearWithItsMonths);
		
		
		
		
		
		$expenses = Expense::where('project_id',$project->id)->get();
		
		 foreach ($expenses as $expense) {
			$paymentAmounts = $expense->payment_amounts;
			$expenseName = $expense->name;
			$currentItemId = $expense->id.$expenseName ;
            $tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['options'] =array_merge([
                'title'=>$expenseName,
            ], $defaultNumericInputClasses);
            $tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['data'] = $paymentAmounts;
			$tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['year_total'] = HArr::sumPerYearIndex($paymentAmounts,$yearWithItsMonths);
        }
		$totalReplaceCost = [];
		foreach ($project->fixedAssets as $fixedAsset) {
			$ffePayment = $fixedAsset->getFfePayment();
			$currentItemId = $fixedAsset->id ;
            $tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['options'] =array_merge([
                'title'=>$fixedAsset->getName(),
            ], $defaultNumericInputClasses);
            $tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['data'] = $ffePayment;
			$tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['year_total'] = HArr::sumPerYearIndex($ffePayment,$yearWithItsMonths);
			$totalReplaceCost = HArr::sumAtDates([$totalReplaceCost,$fixedAsset->getReplaceCost()],$sumKeys);
        }
		$currentItemId = 'fixed-asset-replacement-cost';
		$tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['options'] =array_merge([
                'title'=>__('Fixed Asset Replacement Cost'),
            ], $defaultNumericInputClasses);
            $tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['data'] = $totalReplaceCost;
			$tableDataFormatted[$currentTabIndex]['sub_items'][$currentItemId]['year_total'] = HArr::sumPerYearIndex($totalReplaceCost,$yearWithItsMonths);
			
		
		$loanSchedulePayments = DB::table('loan_schedule_payments')->where('loan_schedule_payments.project_id',$project->id)->join('fixed_assets','fixed_assets.id','=','loan_schedule_payments.fixed_asset_id')->selectRaw('schedulePayment,name')->get();
		foreach($loanSchedulePayments as $loanSchedulePayments){
			$schedulePayment = (array)json_decode($loanSchedulePayments->schedulePayment);
			$name = $loanSchedulePayments->name .' '. __('Loan Installment') ;
            $tableDataFormatted[$currentTabIndex]['sub_items'][$name]['options'] =array_merge([
                'title'=>$name,
            ], $defaultNumericInputClasses);
            $tableDataFormatted[$currentTabIndex]['sub_items'][$name]['data'] = $schedulePayment;
			$tableDataFormatted[$currentTabIndex]['sub_items'][$name]['year_total'] = HArr::sumPerYearIndex($schedulePayment,$yearWithItsMonths);
		}
		
		$openingLoans = DB::table('long_term_loan_opening_balances')->where('long_term_loan_opening_balances.project_id',$project->id)->get();
		$totalLoanInstallments =[]; 
		$totalLoanInterests =[]; 
		foreach($openingLoans as $openingLoan){
			$installment = (array)json_decode($openingLoan->installments);
			$interest = (array)json_decode($openingLoan->interests);
			$totalLoanInstallments = HArr::sumAtDates([$totalLoanInstallments,$installment],$sumKeys);
			$totalLoanInterests = HArr::sumAtDates([$totalLoanInterests,$interest],$sumKeys);
		}
		$totalLoansAmounts = HArr::sumAtDates([$totalLoanInterests,$totalLoanInstallments],$sumKeys);
            $tableDataFormatted[$currentTabIndex]['sub_items'][__('Opening Loan Installments')]['options'] =array_merge([
                'title'=>__('Opening Loan Installments'),
            ], $defaultNumericInputClasses);
            $tableDataFormatted[$currentTabIndex]['sub_items'][__('Opening Loan Installments')]['data'] = $totalLoansAmounts;
			$tableDataFormatted[$currentTabIndex]['sub_items'][__('Opening Loan Installments')]['year_total'] = HArr::sumPerYearIndex($totalLoansAmounts,$yearWithItsMonths);
		
			
		
		
		$totalCashOut = HArr::sumAtDates(array_column($tableDataFormatted[$currentTabIndex]['sub_items'],'data'),$sumKeys);
			
		$tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $totalCashOut;
		$tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =  HArr::sumPerYearIndex($totalCashOut,$yearWithItsMonths);
		
		$workingCapitalStatement = HArr::calculateWorkingCapital($cashAndBankAmount,$totalCashIn,$totalCashOut,$sumKeys);
		/**
		 * * Start Net Cash Before Working Capital;
		*/
		$currentTabIndex = 2 ; 
		$currentTabId = 'net-cash-before-working-capital';
		
		$tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
		   'title'=>__('Net Cash Before Working Capital')
		], $defaultNumericInputClasses);
		
		$tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentData = $workingCapitalStatement['net_cash_before_working_capital']??[];
		$tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =  HArr::sumPerYearIndex($currentData,$yearWithItsMonths);
		
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
		
		$tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentData = $workingCapitalStatement['working_capital_injection']??[];
		$tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =  HArr::sumPerYearIndex($currentData,$yearWithItsMonths);
		
			/**
		 * * End Net Cash Before Working Capital;
		 */
		
			
				/**
		 * * Start Cash And Bank End Balance;
		*/
		$currentTabIndex = 4 ; 
		$currentTabId = 'cash-and-bank-end-balance';
		
		$tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['options'] = array_merge([
		   'title'=>__('Cash And Bank End Balance')
		], $defaultNumericInputClasses);
		$tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['data'] = $currentData = $workingCapitalStatement['cash_end_balance']??[];
		$tableDataFormatted[$currentTabIndex]['main_items'][$currentTabId]['year_total'] =  HArr::sumPerYearIndex($currentData,$yearWithItsMonths);
		
			/**
		 * * End Cash And Bank End Balance;
		 */
			$tableDataFormatted[-1]['main_items']['cash-and-banks']['data'] = $workingCapitalStatement['beginning_balance'] ??[];
		
		return  [
            'step_data'=>$step_data,
            'financialYearEndMonthNumber'=>$financialYearEndMonthNumber,
            'years','studyMonthsForViews'=>$studyMonthsForViews,
            'project'=>$project,
            'tableDataFormatted'=>$tableDataFormatted,
            'defaultClasses'=>$defaultClasses
            
		];
	}
	public function recalculateVatStatements():void
	{
		$extendedSumKeys  = array_keys($this->getExtendedStudyDurationPerYears());
		$totalProductsVatAmounts = [];
		$dateIndexWithDate = $this->getDateIndexWithDate();
		$totalRawMaterialsVatAmounts = [];
		foreach($this->products as $product){
			$vatAmounts = $product->getCollectionStatement()['monthly']['vat']??[];
			$totalProductsVatAmounts = HArr::sumAtDates([$vatAmounts,$totalProductsVatAmounts],$extendedSumKeys);
		}
		foreach($this->rawMaterials as $rawMaterial ){
			$vatAmounts = $rawMaterial->getCollectionStatement()['monthly']['vat']??[] ;
			$totalRawMaterialsVatAmounts = HArr::sumAtDates([$vatAmounts,$totalRawMaterialsVatAmounts],$extendedSumKeys);
		}
		$additions = HArr::subtractAtDates([$totalProductsVatAmounts,$totalRawMaterialsVatAmounts],$extendedSumKeys);
		$vatOpeningBalance = $this->getVatOpeningBalanceAmount();
		$vatStatements = Project::calculateVatStatement($additions,$vatOpeningBalance,$dateIndexWithDate);
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
}
