<?php

namespace App\Http\Controllers;

use App\Assets;
use App\Equations\ExpenseAsPercentageEquation;
use App\Equations\MonthlyFixedRepeatingAmountEquation;
use App\Equations\OneTimeExpenseEquation;
use App\Expense;
use App\Helpers\HArr;
use App\Http\Requests\StoreExpensesRequest;
use App\Http\Requests\StoreFixedAssetsRequest;
use App\Http\Requests\StoreManpowerRequest;
use App\Http\Requests\StoreOpeningBalancesRequest;
use App\ManPower;
use App\OpeningBalance;
use App\Product;
use App\Project;
use App\RawMaterial;
use App\ReadyFunctions\CollectionPolicyService;
use App\ReadyFunctions\SeasonalityService;
use App\Sensitivity;
use App\Traits\Redirects;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RedirectionController extends Controller
{
    /**************** products **********************/
    public function productsGet(Project $project, Product $product)
    {
        // $step_data =(new Redirects)->steps($project,$type);
        // (new Redirects)->redirectFun($project);
        // $product = $project->product($type);
        $years = $product->getViewYearIndexWithYear();
        $currentStepNumber = 2 ;
        $totalSteps = getTotalSteps() ;
        $rawMaterials  = $product->rawMaterials;
        $rawMaterialNames = $project->rawMaterials;
        
        return view('products.form', [
            'years'=>$years ,
            'project'=>$project ,
            'product'=>$product,
            'currentStepNumber'=>$currentStepNumber,
            'totalSteps'=>$totalSteps,
            'rawMaterials'=>$rawMaterials,
            'rawMaterialNames'=>$rawMaterialNames
        ]);
    }

    public function productsPost(Request $request, Project $project, Product $product)
    {

        $product->syncSeasonality($request->get('seasonality'));
        // $request['user_id'] = auth()->user()->id;
        $request['project_id'] = $project->id;
        // $request['type'] = $type;
        $rawMaterials = [];
        foreach ($request->get('rawMaterials') as $index => &$rawMaterialArr) {
            $rawMaterialArr['product_id'] = $product->id;
            if ($rawMaterialArr['raw_material_id']) {
                $rawMaterials[$index] = $rawMaterialArr;
            }
        }
        $request->merge([
            'rawMaterials'=>$rawMaterials
        ]);
        $request->merge([
            'fg_inventory_quantity'=>number_unformat($request->get('fg_inventory_quantity'))
        ]);
        $product->update($request->except(['rawMaterials','submit_button','seasonality']));
        /**
         * monthly_sal target value
         */
        $monthlySalesTargetValueBeforeVat  = $product->calculateMonthlySalesTargetValue();
		$collectionStatement = $product->calculateMultiYearsCollectionPolicy($monthlySalesTargetValueBeforeVat);
        
        $product->update([
			'collection_statement'=>$collectionStatement
		]);
        $product->rawMaterials()->detach();
        foreach ($rawMaterials as $rawMaterialArr) {
            unset($rawMaterialArr['id']);
            $rawMaterialArr['project_id'] = $project->id;
            $rawMaterialId = $rawMaterialArr['raw_material_id'];
            $rawMaterialArr['percentages'] = json_encode($rawMaterialArr['percentages']);
            $product->rawMaterials()->attach($rawMaterialId, $rawMaterialArr);
        }
        $project->recalculateFgInventoryValueStatement();
        $nextProduct = $product->getNextProduct();
        
        if ($request->get('submit_button') != 'next') {
            return redirect()->route('main.project.page', ['project'=>$project->id]);
        }
        if ($nextProduct) {
            return redirect()->route('products.form', ['product'=>$nextProduct->id,'project'=>$project->id]);
        }
        return redirect()->route('raw.material.payments.form', $project) ;
        
    }

    
    
    /**************** raw material payments **********************/
    public function rawMaterialPaymentsGet(Project $project)
    {
        $uniqueDistributedRawMaterialNames =RawMaterial::where('project_id', $project->id)->has('products')->get();
        
        
     
        // $years = $product->getViewYearIndexWithYear();
        $currentStepNumber = 3 ;
        $totalSteps = getTotalSteps() ;
    
        
        return view('raw-material-payments.form', [
            // 'years'=>$years ,
            'project'=>$project ,
            'uniqueDistributedRawMaterialNames'=>$uniqueDistributedRawMaterialNames,
            'currentStepNumber'=>$currentStepNumber,
            'totalSteps'=>$totalSteps,
        ]);
    }

    public function rawMaterialPaymentsPost(Request $request, Project $project)
    {
        foreach ($request->get('rawMaterials') as $rawMaterialId => $dataArr) {
            $rawMaterial = RawMaterial::find($rawMaterialId);
            $rawMaterial->update($dataArr);
        }
		$rawMaterial->calculateInventoryQuantityStatement($project->id);
        $project->update([
            'comment_for_raw_materials'=>$request->get('comment_for_raw_materials')
        ]);

        if ($request->get('submit_button') != 'next') {
            return redirect()->route('main.project.page', ['project'=>$project->id]);
        }
        return redirect()->route('manPower.form', $project) ;
        
    }
    
    /**************** Man Power **********************/
    public function manPowerGet(Project $project)
    {
        // $m = ManPower::all();
        // foreach ($m as $row) {
        //     $old_general_first_capacity = $row->old_general_first_capacity;
        //     $old_sales_first_capacity = $row->old_sales_first_capacity;
        //     $old_operational_salaries_first_capacity = $row->old_operational_salaries_first_capacity;

        //     $row->general_first_capacity = ['one'=>$old_general_first_capacity,'two'=>$old_general_first_capacity,'three'=>$old_general_first_capacity,'four'=>$old_general_first_capacity];
        //     $row->sales_first_capacity = ['one'=>$old_sales_first_capacity,'two'=>$old_sales_first_capacity,'three'=>$old_sales_first_capacity,'four'=>$old_sales_first_capacity];
        //     $row->operational_salaries_first_capacity = ['one'=>$old_operational_salaries_first_capacity,'two'=>$old_operational_salaries_first_capacity,'three'=>$old_operational_salaries_first_capacity,'four'=>$old_operational_salaries_first_capacity];
        //     $row->save();
        // }
        //Years Contract
        $step_data =(new Redirects)->steps($project, 'manPower');
        $years  = $project->getYears();
        // $years = (new ProjectController)->years($project,($project->new_company == 0 ?'project' : 'min_selling_date'));
        $products = $project->products;
        $manpowers = $project->manpowers;
        return view('manPower.form', ['years'=>$years,'project'=>$project,'manpowers'=>$manpowers,'step_data'=>$step_data,'products'=>$products]);
    }

    public function manPowerPost(StoreManpowerRequest $request, Project $project)
    {
        // $result = (new ValidationsController)->manPowerValidation($request,$project);
        $manpowers = [];
        $index=   0 ;
        $manpowerAllocations = [];
        foreach ($request->get('manpower_allocations') as $manpowerType => $allocations) {
            foreach ($allocations['products']??[] as $productId=>$productName) {
                $manpowerAllocations[$manpowerType][$productId] = $allocations['percentages'][$productId]??0;
            }
            
        }
    
        $project->update([
            'manpower_allocations'=>$manpowerAllocations,
            'salaries_annual_increase'=>$request->get('salaries_annual_increase'),
            'salaries_tax_rate'=>$request->get('salaries_tax_rate'),
            'social_insurance_rate'=>$request->get('social_insurance_rate')
        ]);
        $dateAsIndexes = $project->getDateWithDateIndex();
        // $existingCount =
        foreach (getManpowerTypes() as $id => $manpowerOptionArr) {
            foreach ($request->get($id) as $i => $items) {
                if ($items['position'] && $items['avg_salary'] && $items['existing_count']) {
                    foreach ($items as $name => $value) {
                        $manpowers['manpowers'][$index][$name] = $value ;
                    }
                    $existingCount = $items['existing_count'];
                    $hiringCounts  = $items['hirings'];
                    
                    $monthlyNetSalary = $items['avg_salary'] ;
                    $salaryTaxesRate = $project->getSalaryTaxRate() / 100;
                    $socialInsuranceRate = $project->getSocialInsuranceRate() /100;
                    $manpowers['manpowers'][$index]['type'] = $id;
                    $salaryExpenses=$project->calculateManpowerResult($dateAsIndexes, $existingCount, $hiringCounts, $monthlyNetSalary, $salaryTaxesRate, $socialInsuranceRate);
                    foreach ($salaryExpenses as $columnName => $resultArr) {
                        $manpowers['manpowers'][$index][$columnName] = $resultArr;
                    }
                            
                }
                $index++;
            }
        }
        $request->merge($manpowers);
        $project->storeRepeaterRelations($request, ['manpowers'], ['project_id'=>$project->id]);
        $project->reallocateProductsOnManpowers();
        

        if ($request->get('submit_button') != 'next') {
            return redirect()->route('main.project.page', ['project'=>$project->id]);
        }
        return redirect()->route('expenses.form', ['project'=>$project->id]);
     
    }

    /**************** Expenses **********************/
    public function expensesGet(Project $project)
    {
        $step_data =(new Redirects)->steps($project, 'expenses');
        // $expense = $project->expense;
        $years  = $project->getYears();
        // $years = (new ProjectController)->years($project,$project->new_company == 0 ?'project' : 'min_selling_date');
        $expenses = $project->expenses;
        $products = $project->products;
        return view('expenses.form', ['project'=>$project,'years'=>$years,'expenses'=>$expenses,'step_data'=>$step_data,'products'=>$products]);
    }

    public function expensesPost(
        StoreExpensesRequest $request,
        Project $project,
        MonthlyFixedRepeatingAmountEquation $monthlyFixedRepeatingAmountEquation,
        ExpenseAsPercentageEquation $expenseAsPercentageEquation,
        OneTimeExpenseEquation $oneTimeExpenseEquation
    ) {
        $expenseTypes =getExpensesTypes();
        $modelName = 'Project';
        $dateIndexWithDate = $project->getDateIndexWithDate();
        $datesAsStringDateIndex = $project->getDatesAsStringAndIndex();
        $datesAsIndexAndString = array_flip($datesAsStringDateIndex);
        $operationStartDateAsIndex = $datesAsStringDateIndex[$project->getOperationStartDate()];
        $studyEndDateAsIndex = $project->getStudyEndDateAsIndex();
        $datesAsStringDateIndex = $project->getDatesAsStringAndIndex();
        $studyExtendedEndDateAsIndex = Arr::last($datesAsStringDateIndex);
        $expenseAllocations = [];
        foreach ($expenseTypes as $expenseType) {
            $project->generateRelationDynamically($expenseType)->delete();
            $tableId = $expenseType;
             
            foreach ((array)$request->get($tableId) as $tableDataArr) {
            
                $withholdRate = $tableDataArr['withhold_tax_rate']??0;
                $tableDataArr['withhold_tax_rate'] = $withholdRate;
                $productIds = $tableDataArr['product_id'];
                $allocationPercentages = $tableDataArr['percentage'];
                $tableDataArr['product_allocations'] = array_combine($productIds, $allocationPercentages);
                $productAllocations = $tableDataArr['product_allocations'] ;
                $expenseCategoryId =$tableDataArr['category_id'];
                if (isset($tableDataArr['start_date']) && count(explode('-', $tableDataArr['start_date'])) == 2) {
                    $tableDataArr['start_date'] = $tableDataArr['start_date'].'-01';
                    
                }if (isset($tableDataArr['end_date']) && count(explode('-', $tableDataArr['end_date'])) == 2) {
                    $tableDataArr['end_date'] = $tableDataArr['end_date'].'-01';
                    
                }
                $tableDataArr['expense_type'] = 'Expense';
                $name = $tableDataArr['name']??null;
                    
                if (isset($tableDataArr['start_date'])) {
                    $tableDataArr['start_date'] = $datesAsStringDateIndex[$tableDataArr['start_date']];
                } else {
                    $tableDataArr['start_date'] = $operationStartDateAsIndex;
                }
                if (isset($tableDataArr['end_date'])) {
                    $tableDataArr['end_date'] = $datesAsStringDateIndex[$tableDataArr['end_date']];
                } else {
                    $tableDataArr['end_date'] = $operationStartDateAsIndex;
                }
                /**
                 * * to repeat 2 years inside json
                 */
                $loopEndDate = $tableDataArr['end_date'] >=  $studyEndDateAsIndex ? $studyExtendedEndDateAsIndex : $tableDataArr['end_date'];
                $loopEndDate = $loopEndDate ==  0 ? $studyEndDateAsIndex : $loopEndDate ;

                //        $monthsAsIndexes = range(0, $studyEndDateAsIndex) ;
                $tableDataArr['relation_name']  = $expenseType ;
                /**
                 * * Fixed Repeating
                 */
                $vatRate = $tableDataArr['vat_rate']??0;
                $isDeductible = $tableDataArr['is_deductible'] ?? false;
            
                if ($tableDataArr['payment_terms'] == 'customize') {
                    $tableDataArr['custom_collection_policy'] = sumDueDayWithPayment($tableDataArr['payment_rate'], $tableDataArr['due_days']);
                }
                $customCollectionPolicy = $tableDataArr['custom_collection_policy']??[];
                if (is_array($isDeductible)) {
                    $tableDataArr['is_deductible'] = $isDeductible[0];
                    $isDeductible= $isDeductible[0];
                }
                
             
                if (isset($tableDataArr['amount']) && $tableId == 'fixed_monthly_repeating_amount') {
                    $amount = $tableDataArr['amount']??0 ;
                    $increaseInterval = $tableDataArr['increase_interval'] ?? 'annually' ;
                    $monthlyFixedRepeatingResults = $monthlyFixedRepeatingAmountEquation->calculate($amount, $tableDataArr['start_date'], $loopEndDate, $increaseInterval, $tableDataArr['increase_rate'], $isDeductible, $vatRate, $withholdRate);
                    $withholdAmounts  = $monthlyFixedRepeatingResults['withhold_amounts'];
                    $tableDataArr['monthly_repeating_amounts']  = $monthlyFixedRepeatingResults['total_before_vat'];
                    $tableDataArr['total_vat']  = $monthlyFixedRepeatingResults['total_vat'];
                    $tableDataArr['total_after_vat']  = $monthlyFixedRepeatingResults['total_after_vat'];
                    $payments = $this->calculateCollectionOrPaymentAmounts($tableDataArr['payment_terms'], $tableDataArr['total_after_vat'], $datesAsIndexAndString, $customCollectionPolicy) ;
                    $withholdPayments = $this->calculateCollectionOrPaymentAmounts($tableDataArr['payment_terms'], $withholdAmounts, $datesAsIndexAndString, $customCollectionPolicy) ;
                    $netPaymentsAfterWithhold = HArr::subtractAtDates([$payments,$withholdPayments], array_keys($payments));
                    $tableDataArr['withhold_amounts'] = $withholdAmounts ;
                    $tableDataArr['withhold_payments']=$withholdPayments;
                    $tableDataArr['payment_amounts'] = $payments;
                    $tableDataArr['net_payments_after_withhold']=$netPaymentsAfterWithhold;
                    $tableDataArr['collection_statements']   =Expense::calculateStatement($tableDataArr['monthly_repeating_amounts'], $tableDataArr['total_vat'], $netPaymentsAfterWithhold, $withholdPayments, $dateIndexWithDate);
        
                }
                /**
                 * * Expense As Percentage
                 */
                /**
                 * 	$beginning = 0 ;
                 * $expense
                 * $vat
                 * $total due = $begiining + $expense + $vat
                 * $collection
                 * $withhold
                 * $endBalance = $totalDie - $collection - $withhold
                 * $begiinign = $endBalance

                 */
                if ($tableId =='expense_as_percentage') {
                    
                    $expenseAsPercentageResult = $expenseAsPercentageEquation->calculate($expenseCategoryId, $expenseType, $name, $project->products, $productAllocations, $tableDataArr['start_date'], $loopEndDate, $tableDataArr['monthly_percentage'], $vatRate, $isDeductible, $tableDataArr['withhold_tax_rate']) ;
                    $expenseAmounts = $expenseAsPercentageResult['expense_amounts'];
                    $expenseAllocations[$expenseType][$expenseCategoryId][$name] = $expenseAsPercentageResult['expense_allocations'];
                    
                
                    $tableDataArr['expense_as_percentages']  =$expenseAmounts  ;
                    // expense_as_percentages
                    $tableDataArr['total_vat']  =[]  ;
                    $tableDataArr['total_after_vat']  =$expenseAmounts  ;
                    $withholdAmounts  = [];
                    $tableDataArr['payment_amounts'] = $this->calculateCollectionOrPaymentAmounts($tableDataArr['payment_terms'], $tableDataArr['total_after_vat'], $datesAsIndexAndString, $customCollectionPolicy) ;
                    $payments = $this->calculateCollectionOrPaymentAmounts($tableDataArr['payment_terms'], $tableDataArr['total_after_vat'], $datesAsIndexAndString, $customCollectionPolicy) ;
                    $withholdPayments = $this->calculateCollectionOrPaymentAmounts($tableDataArr['payment_terms'], $withholdAmounts, $datesAsIndexAndString, $customCollectionPolicy) ;
                    $netPaymentsAfterWithhold = HArr::subtractAtDates([$payments,$withholdPayments], array_keys($payments));
                    $tableDataArr['withhold_amounts'] = $withholdAmounts ;
                    $tableDataArr['withhold_payments']=$withholdPayments;
                    $tableDataArr['payment_amounts'] = $payments;
                    $tableDataArr['net_payments_after_withhold']=$netPaymentsAfterWithhold;
                    $tableDataArr['collection_statements']   =Expense::calculateStatement($tableDataArr['expense_as_percentages'], $tableDataArr['total_vat'], $netPaymentsAfterWithhold, $withholdPayments, $dateIndexWithDate);
                }
                
                /**
                 * * One Time Expense
                */
                if ($tableId == 'one_time_expense') {
                    $startDateAsIndex = $tableDataArr['start_date'] ;
                    $amountBeforeVat = $tableDataArr['amount'] ;
                    $withholdAmount = $tableDataArr['withhold_tax_rate'] / 100 * $amountBeforeVat ;
                    $oneTimeExpenses = $oneTimeExpenseEquation->calculate($amountBeforeVat, $startDateAsIndex, $isDeductible, $vatRate);
                    $tableDataArr['payload']  = $oneTimeExpenses ;
                    $amountBeforeVatPayload = [$startDateAsIndex=>$amountBeforeVat] ;
                    $vatRate = $tableDataArr['vat_rate'] ??0 ;
                    $vatRate =  $vatRate / 100 ;
                    $vats = [$startDateAsIndex=>$amountBeforeVat * $vatRate];
                    
                    $tableDataArr['total_vat']  =$vats  ;
                    $amountAfterVat = [$startDateAsIndex => $amountBeforeVat + $amountBeforeVat * $vatRate ];
                    $tableDataArr['total_after_vat']  =$amountAfterVat  ;
                    $withholdAmount = $tableDataArr['withhold_tax_rate']/100 ;
                    $withholdAmounts  = [$startDateAsIndex =>  $amountBeforeVat * $withholdAmount ] ;
                    $payments = $this->calculateCollectionOrPaymentAmounts($tableDataArr['payment_terms'], $amountAfterVat, $datesAsIndexAndString, $customCollectionPolicy, true) ;
                    $withholdPayments = $this->calculateCollectionOrPaymentAmounts($tableDataArr['payment_terms'], $withholdAmounts, $datesAsIndexAndString, $customCollectionPolicy) ;
                    $netPaymentsAfterWithhold = HArr::subtractAtDates([$payments,$withholdPayments], array_keys($payments));
                    $tableDataArr['withhold_amounts'] = $withholdAmounts ;
                    $tableDataArr['withhold_payments']=$withholdPayments;
                    $tableDataArr['payment_amounts'] = $payments;
                    $tableDataArr['net_payments_after_withhold']=$netPaymentsAfterWithhold;
                    $tableDataArr['collection_statements']   =Expense::calculateStatement($amountBeforeVatPayload, $tableDataArr['total_vat'], $netPaymentsAfterWithhold, $withholdPayments, $dateIndexWithDate);
                }
              
                $tableDataArr['project_id']  = $project->id ;
                $tableDataArr['model_id']   = $project->id ;
                $tableDataArr['model_name']   = $modelName ;
                //	$tableDataArr['collection_statements'] = [];
                if ($name) {
                    $project->generateRelationDynamically($tableId, $expenseType)->create($tableDataArr);
                    
                }
                    
                
            }
            
        }
        $expenseAllocationFormatted = [];
    
        foreach ($expenseAllocations as $expenseType => $arr1) {
            foreach ($arr1 as $expenseCategoryId => $expenseCategoryNameAndValues) {
                foreach ($expenseCategoryNameAndValues as $expenseName => $productIdWithPayload) {
                    foreach ($productIdWithPayload as $productId => $payload) {
                        $expenseAllocationFormatted[] = [
                            'expense_type'=>$expenseType,
                            'expense_category'=>$expenseCategoryId,
                            'expense_name'=>$expenseName,
                            'product_id'=>$productId,
                            'payload'=>json_encode($payload),
                            'is_expense'=>1,
                            'project_id'=>$project->id
                        ];
                    }
                }
            }
        }
        DB::table('product_expense_allocations')->where('is_expense', 1)->where('project_id', $project->id)->delete();
        DB::table('product_expense_allocations')->insert($expenseAllocationFormatted);
        $project->recalculateAllProductsOverheadExpenses();
        
        if ($request->get('submit_button') != 'next') {
            return redirect()->route('main.project.page', ['project'=>$project->id]);
        }
        return redirect()->route('assets.form', $project->id);
    }

    /**************** Assets **********************/
    public function assetsGet(Project $project)
    {
        $step_data =(new Redirects)->steps($project, 'assets');
        $fixedAssets = $project->fixedAssets;
        $products = $project->products;
        $years =$project->getYears();

        return view(
            'fixed-assets.form',
            ['project'=>$project,'years'=>$years,'fixedAssets'=>$fixedAssets,'step_data'=>$step_data,'products'=>$products]
        );
        // return view('assets.form',compact('assets','project','step_data','years'));
    }

    public function assetsPost(StoreFixedAssetsRequest $request, Project $project)
    {
        $project->storeRepeaterRelations($request, ['fixedAssets'], ['project_id'=>$project->id]);
        $project->recalculateFixedAsset();
        
        if ($request->get('submit_button') != 'next') {
            return redirect()->route('main.project.page', ['project'=>$project->id]);
        }
        return redirect()->route('openingBalances.form', $project->id);
        // return $project->new_company == 0 ? redirect()->route('openingBalances.form',$project->id) : redirect()->route('dashboard.index',$project->id);
        

    }
    
    
    public function openingBalancesGet(Project $project)
    {
        $step_data =(new Redirects)->steps($project, 'openingBalances');
        $fixedAssetOpeningBalances = $project->fixedAssetOpeningBalances;
        $cashAndBankOpeningBalances = $project->cashAndBankOpeningBalances;
        $otherDebtorsOpeningBalances = $project->otherDebtorsOpeningBalances;
        $supplierPayableOpeningBalances = $project->supplierPayableOpeningBalances;
        $otherCreditorsOpeningBalances = $project->otherCreditorsOpeningBalances;
        $otherLongTermLiabilitiesOpeningBalances = $project->otherLongTermLiabilitiesOpeningBalances;
        $equityOpeningBalances = $project->equityOpeningBalances;
        $longTermLoanOpeningBalances = $project->longTermLoanOpeningBalances;
        $products = $project->products;
        $years =$project->getYears();

        return view(
            'openingBalances.form',
            ['project'=>$project,'years'=>$years,'longTermLoanOpeningBalances'=>$longTermLoanOpeningBalances,'equityOpeningBalances'=>$equityOpeningBalances,'otherLongTermLiabilitiesOpeningBalances'=>$otherLongTermLiabilitiesOpeningBalances,'otherCreditorsOpeningBalances'=>$otherCreditorsOpeningBalances,'supplierPayableOpeningBalances'=>$supplierPayableOpeningBalances,'otherDebtorsOpeningBalances'=>$otherDebtorsOpeningBalances,'fixedAssetOpeningBalances'=>$fixedAssetOpeningBalances,'cashAndBankOpeningBalances'=>$cashAndBankOpeningBalances,'step_data'=>$step_data,'products'=>$products]
        );
        // return view('assets.form',compact('assets','project','step_data','years'));
    }

    public function openingBalancesPost(StoreOpeningBalancesRequest $request, Project $project)
    {
        $project->storeRepeaterRelations($request, ['fixedAssetOpeningBalances','cashAndBankOpeningBalances','otherDebtorsOpeningBalances'
        ,'supplierPayableOpeningBalances','otherCreditorsOpeningBalances','otherLongTermLiabilitiesOpeningBalances','equityOpeningBalances','longTermLoanOpeningBalances'
    ], ['project_id'=>$project->id]);
	
	
		$project->recalculateOpeningBalances();

        if ($request->get('submit_button') != 'next') {
            return redirect()->route('main.project.page', ['project'=>$project->id]);
        }
        return redirect()->route('financial.result', ['project'=>$project->id]);
     
    }
    
    /**************** financialResultsGet **********************/
    public function financialResultsGet(Project $project)
    {
        $step_data =(new Redirects)->steps($project, 'assets');
        $fixedAssets = $project->fixedAssets;
        $products = $project->products;
        $years =$project->getYears();
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
            'net-profit'=>11
        ];
        
        $financialYearsEndMonths = $project->getFinancialYearsEndMonths();
        
        
        
        
        $grossProfitOrderIndex = $orderIndexPerExpenseCategory['gross-profit'];
        $ebitdaOrderIndex = $orderIndexPerExpenseCategory['ebitda'];
        $ebitOrderIndex = $orderIndexPerExpenseCategory['ebit'];
        $ebtOrderIndex = $orderIndexPerExpenseCategory['ebt'];
        $netProfitOrderIndex = $orderIndexPerExpenseCategory['net-profit'];
        
        // $studyMonthsForViews=$project->getStudyDurationPerYearFromIndexesForView();
		$studyMonthsForViews = array_flip($project->getOperationDatesAsDateAndDateAsIndexToStudyEndDate());
         
        
        
        
        
        
		
        
        /**
		 * * First Tab Sales Revenue
		*/
		$tableDataFormatted[0]['main_items']['sales-revenue']['options'] = array_merge([
		   'title'=>__('Sales Revenue')
		], $defaultNumericInputClasses);
		
		$tableDataFormatted[0]['main_items']['growth-rate']['options'] = array_merge($defaultPercentageInputClasses, ['title'=>__('Growth Rate %')]);
		$productsTotals = [];
		$sumKeys = array_keys($studyMonthsForViews);
        foreach ($products as $product) {
            $tableDataFormatted[0]['sub_items'][$product->getName()]['options'] =array_merge([
                'title'=>$product->getName(),
            ], $defaultNumericInputClasses);
            $tableDataFormatted[0]['sub_items'][$product->getName()]['data'] = $product->monthly_sales_target_values;
			$productsTotals = HArr::sumAtDates([$product->monthly_sales_target_values,$productsTotals],$sumKeys);
        }
		$tableDataFormatted[0]['main_items']['sales-revenue']['data'] = $productsTotals;
		$tableDataFormatted[0]['main_items']['growth-rate']['data'] = HArr::calculateGrowthRate($productsTotals);
		/**
		 * * Second Tab Cost Of Goods Sold 
		 */
		
		
        $tableDataFormatted[1]['main_items']['cost-of-goods-sold']['options'] = array_merge([
           'title'=>__('Cost Of Goods Sold')
        ], $defaultNumericInputClasses);
        
        
        $tableDataFormatted[1]['main_items']['revenues-percentage']['options'] = array_merge([
            'title'=>__('%/Revenues')
        ], $defaultNumericInputClasses);
		$products = DB::table('products')->where('project_id',$project->id)->get();
		// $rawMaterialStatements  = $products->pluck('raw_material_statement');
		// $manpowerStatement   = $products->pluck('product_manpower_statement');
		$totalCogs = [
			'raw_material'=>[],
			'direct_labor'=>[],
			'manufacturing-overheads'=>[]
		];
		foreach($products as $product){
			$rawMaterialCogs = $product->raw_material_statement ? (array)json_decode($product->raw_material_statement)->cogs : [];
			$directLaborCogs =$product->product_manpower_statement ?  (array)json_decode($product->product_manpower_statement)->cogs : [] ;
			$manufacturingCogs = $product->product_overheads_statement ? (array)json_decode($product->product_overheads_statement)->cogs : [] ;
			$totalCogs['raw_material'] = HArr::sumAtDates([$rawMaterialCogs,$totalCogs['raw_material']],$sumKeys);
			$totalCogs['direct_labor'] = HArr::sumAtDates([$directLaborCogs,$totalCogs['direct_labor']],$sumKeys);
			$totalCogs['manufacturing-overheads'] = HArr::sumAtDates([$manufacturingCogs,$totalCogs['manufacturing-overheads']],$sumKeys);
			
		}
		
        foreach (['raw_material'=>__('Raw Materials') , 'direct_labor'=>__('Direct Labors') , 'manufacturing-overheads'=>__('Manufacturing Overheads')] as $id => $title) {
			
            $tableDataFormatted[1]['sub_items'][$id]['options'] =array_merge([
                'title'=>$title,
            ], $defaultNumericInputClasses); 
			$tableDataFormatted[1]['sub_items'][$id]['data'] = $totalCogs[$id]??[] ;
        }
		$totalCostOfGoodsSold = HArr::sumAtDates(array_values($totalCogs),$sumKeys) ;
		$tableDataFormatted[1]['main_items']['cost-of-goods-sold']['data'] =  $totalCostOfGoodsSold ; 
		
        /**
		 * Three Items 
		*/
        
		$totalGrossProfit = HArr::subtractAtDates([$productsTotals,$totalCostOfGoodsSold],$sumKeys) ;
		$tableDataFormatted[2]['main_items']['gross-profit']['data'] =  $totalGrossProfit ; 
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['gross-profit']['options']['title'] = __('Gross Profit');
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        
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
		// dd();
		
        
            
        
        $tableDataFormatted[5]['main_items']['revenues-percentage']['options'] = array_merge([
            'title'=>__('%/Revenues')
        ], $defaultNumericInputClasses);
        foreach (['sales'=>__('Sales Expenses') , 'marketing'=>__('Marketing Expenses') , 'general-expenses'=>__('General Expenses')] as $id => $title) {
            $orderId = $orderIndexPerExpenseCategory[$id];
            $tableDataFormatted[5]['sub_items'][$id]['options'] =array_merge([
                'title'=>$title,
            ], $defaultNumericInputClasses);  
			$tableDataFormatted[5]['sub_items'][$id]['data'] =$resultPerCategory[$id];
        }
		$tableDataFormatted[5]['main_items']['sganda']['data'] =HArr::sumAtDates(array_values($resultPerCategory),$sumKeys);
        
        
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['ebitda']['options']['title'] = __('EBITDA');
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        
        $tableDataFormatted[$ebitOrderIndex]['main_items']['ebit']['options']['title'] = __('EBIT');
        $tableDataFormatted[$ebitOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        
        
        $tableDataFormatted[9]['main_items']['finance_exp']['options'] = array_merge([
           'title'=>__('Finance Expense')
        ], $defaultNumericInputClasses);
        
        $tableDataFormatted[9]['main_items']['revenues-percentage']['options'] = array_merge([
            'title'=>__('%/Revenues')
        ], $defaultNumericInputClasses);
        // foreach (['sales-expense'=>__('Sales Expenses') , 'marketing-expense'=>__('Marketing Expenses') , 'general-expense'=>__('General Expenses')] as $id => $title) {
        //     $orderId = $orderIndexPerExpenseCategory[$id];
        //     $tableDataFormatted[9]['sub_items'][$id]['options'] =array_merge([
        //         'title'=>$title,
        //     ], $defaultNumericInputClasses);
        // }
        
        
        $tableDataFormatted[$ebtOrderIndex]['main_items']['ebt']['options']['title'] = __('EBT');
        $tableDataFormatted[$ebtOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['net-profit']['options']['title'] = __('Net Profit');
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        
        
        return view(
            'financial-results.index',
            [
            'step_data'=>$step_data,
            'financialYearEndMonthNumber'=>$financialYearEndMonthNumber,
            'years','studyMonthsForViews'=>$studyMonthsForViews,
            'project'=>$project,
            'tableDataFormatted'=>$tableDataFormatted,
            'defaultClasses'=>$defaultClasses
            
            ]
        );
        // return view('assets.form',compact('assets','project','step_data','years'));
    }
    
    /**************** Opening Balances **********************/
    // public function openingBalancesGet(Project $project)
    // {
    //     $step_data =(new Redirects)->steps($project,'openingBalances');
    //     $openning = $project->openingBalance;
    //     $years = (new ProjectController)->years($project);

    //     $products = (new Redirects)->productsforms($project,'without-backlog');
    //     $total_begining_inventory = $project->new_company == 1 ? 0 :  Product::whereIn('type',$products)->where('user_id',auth()->user()->id)->where('project_id',$project->id)->sum(DB::raw('rm_inventory_value + fg_inventory_value'));
    //     $begining_inventory_value = $total_begining_inventory;
    //     // annual_price_increase
    //     return view('openingBalances.form',compact('project','years','openning','begining_inventory_value','step_data'));
    // }

    // public function openingBalancesPost(Request $request ,Project $project )
    // {
    //     $result = (new ValidationsController)->openingBalancesPostValidation($request,$project);

    //     $request['user_id'] = auth()->user()->id;
    //     $request['project_id'] = $project->id;
    //     $openingBalance = $project->openingBalance;
    //     isset($openingBalance) ?   $openingBalance->update($request->except(['submit_button'])) : OpeningBalance::create($request->except(['submit_button']));
    //     if(isset($result['redirect_route'])){
    //         return $result['redirect_route'] ;
    //     }
    //     return redirect()->route('dashboard.index',$project->id);
    // }

    public function sensitivityGet(Project $project)
    {
        $sensitivity = $project->sensitivity;
        $products = (new Redirects)->forms($project, $backlog=null);
        return view('sensitivity', compact('project', 'products', 'sensitivity'));
    }
    public function sensitivityPost(Request $request, Project $project)
    {

        $request['user_id'] = auth()->user()->id;
        $request['project_id'] = $project->id;
        $sensitivity = $project->sensitivity;
        isset($sensitivity) ?   $sensitivity->update($request->except(['submit_button'])) : Sensitivity::create($request->except(['submit_button']));
        return ($request->submit_button == "next") ?  redirect()->route('dashboard.index', $project->id) : redirect()->route('main.project.page', $project->id);
    }
    private function calculateCollectionOrPaymentAmounts(string $paymentTerm, array $totalAfterVat, array $datesAsIndexAndString, array $customCollectionPolicy, $debug=false)
    {
        $collectionPolicyType  = $paymentTerm == 'customize' ? 'customize':'system_default';
        $collectionPolicyValue = $collectionPolicyType ;
        $dateValue = $totalAfterVat;
        if ($collectionPolicyType == 'customize') {
            $collectionPolicyValue = $customCollectionPolicy ;
        } elseif ($collectionPolicyType == 'system_default' && $paymentTerm=='cash') {
            $collectionPolicyValue = 'monthly';
        }
        $dateValue = convertIndexKeysToString($dateValue, $datesAsIndexAndString);
        $collectionPolicyValue = is_array($collectionPolicyValue) ?  $this->formatDues($collectionPolicyValue) : $collectionPolicyValue;
        $result = (new CollectionPolicyService())->applyCollectionPolicy(true, $collectionPolicyType, $collectionPolicyValue, $dateValue) ;
        
        return convertStringKeysToIndexes($result, $datesAsIndexAndString);
    }
    private function formatDues(array $duesAndDays)
    {
        $result = [];
        foreach ($duesAndDays as $day => $due) {
            $result['due_in_days'][]=$day;
            $result['rate'][]=$due;
        }
        return $result;
    }

}
