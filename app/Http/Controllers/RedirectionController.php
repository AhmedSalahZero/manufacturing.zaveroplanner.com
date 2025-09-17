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
use App\Http\Requests\StoreProductsRequest;
use App\Http\Requests\StoreRawMaterialPaymentsRequest;
use App\Product;
use App\Project;
use App\RawMaterial;
use App\ReadyFunctions\CollectionPolicyService;
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
        return view('products.form',$product->getViewVars() );
    }

    public function productsPost(StoreProductsRequest $request, Project $project, Product $product)
    {

		$request->merge([
			'fg_inventory_quantity'=>number_unformat($request->get('fg_inventory_quantity')),
			'fg_inventory_value'=>number_unformat($request->get('fg_inventory_value')),
		]);
        $product->syncSeasonality($request->get('seasonality'));
        $request['project_id'] = $project->id;
		$product->update($request->except(['rawMaterials','submit_button','seasonality']));
        
       
        $request->merge([
            'fg_inventory_quantity'=>number_unformat($request->get('fg_inventory_quantity'))
        ]);
        
        /**
         * monthly_sal target value
         */
		$rawMaterials = $request->get('rawMaterials');
		 $product->rawMaterials()->detach();
        foreach ($rawMaterials as $rawMaterialArr) {
            unset($rawMaterialArr['id']);
            $rawMaterialArr['project_id'] = $project->id;
            $rawMaterialId = $rawMaterialArr['raw_material_id'];
            $rawMaterialArr['percentages'] = json_encode($project->repeatArr($rawMaterialArr['percentages'],false));
            $product->rawMaterials()->attach($rawMaterialId, $rawMaterialArr);
        }
		
        $monthlySalesTargetValueBeforeVat  = $product->calculateMonthlySalesTargetValue();
		
		// here 
        $localMonthlySalesTargetValueBeforeVat  = $monthlySalesTargetValueBeforeVat['localMonthlySalesTargetValue'];
        $exportMonthlySalesTargetValueBeforeVat  = $monthlySalesTargetValueBeforeVat['exportMonthlySalesTargetValue'];
		$localCollectionStatement = $product->calculateMultiYearsCollectionPolicy($localMonthlySalesTargetValueBeforeVat,'local',true);
		$exportCollectionStatement = $product->calculateMultiYearsCollectionPolicy($exportMonthlySalesTargetValueBeforeVat,'export');
		$collectionStatement = HArr::sumTwoIntervalArrays($localCollectionStatement,$exportCollectionStatement);

        $product->update([
			'local_collection_statement'=> $localCollectionStatement ,
			'export_collection_statement'=> $exportCollectionStatement,
			'collection_statement'=>$collectionStatement
		]);
       
        $project->recalculateFgInventoryValueStatement();
		$project->recalculateVatStatements();
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
        return view('raw-material-payments.form', $project->getRawMaterialViewVars());
    }

    public function rawMaterialPaymentsPost(StoreRawMaterialPaymentsRequest $request, Project $project)
    {
		$project->update([
			'comment_for_raw_materials'=>$request->get('comment_for_raw_materials')
		]);
		
        foreach ($request->get('rawMaterials') as $rawMaterialId => $dataArr) {
			/**
			 * @var RawMaterial $rawMaterial
			 */
            $rawMaterial = RawMaterial::find($rawMaterialId);
            $rawMaterial->update($dataArr);
        }
		RawMaterial::calculateInventoryQuantityStatement($project->id);
		$project->recalculateVatStatements();
		
		// foreach($project)
		unset($rawMaterial);
        if ($request->get('submit_button') != 'next') {
            return redirect()->route('main.project.page', ['project'=>$project->id]);
        }
        return redirect()->route('manPower.form', $project) ;
        
    }
    
    /**************** Man Power **********************/
    public function manPowerGet(Project $project)
    {
        //Years Contract
      
        return view('manPower.form', $project->getManpowerViewVars() );
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
                if (isset($items['position']) && isset($items['avg_salary'])) {
					$items['existing_count'] = isset($items['existing_count']) ? $items['existing_count'] : 0;
                    foreach ($items as $name => $value) {
                        $manpowers['manpowers'][$index][$name] = $value ;
                    }
                    $existingCount = $items['existing_count'];
                    $hiringCounts  = $items['hirings']??[];
                    
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
        $project->recalculateFgInventoryValueStatement();

        if ($request->get('submit_button') != 'next') {
            return redirect()->route('main.project.page', ['project'=>$project->id]);
        }
        return redirect()->route('expenses.form', ['project'=>$project->id]);
     
    }

    /**************** Expenses **********************/
    public function expensesGet(Project $project)
    {

        return view('expenses.form', $project->getExpensesViewVars());
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
				
                $loopEndDate = $loopEndDate ==  0 && $tableId == 'one_time_expense' ? $studyEndDateAsIndex : $loopEndDate ;
	

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
                $expenseAsPercentageResult = [];
             
                if (isset($tableDataArr['amount']) && $tableId == 'fixed_monthly_repeating_amount') {
					$amount = $tableDataArr['amount']??0 ;
                    $increaseInterval = $tableDataArr['increase_interval'] ?? 'annually' ;
					$increaseRate = $tableDataArr['increase_rate'] ?? 0 ;
                    $monthlyFixedRepeatingResults = $monthlyFixedRepeatingAmountEquation->calculate($amount, $tableDataArr['start_date'], $loopEndDate, $increaseInterval, $increaseRate, $isDeductible, $vatRate, $withholdRate);
				
                    $withholdAmounts  = $monthlyFixedRepeatingResults['withhold_amounts'];
                    $tableDataArr['monthly_repeating_amounts']  = $monthlyFixedRepeatingResults['total_before_vat'];
                    $tableDataArr['total_vat']  = $monthlyFixedRepeatingResults['total_vat'];
                    $tableDataArr['total_after_vat']  = $monthlyFixedRepeatingResults['total_after_vat'];
					$expenseAsPercentageResult['expense_allocations'] = Product::multiplyWithAllocation($productAllocations,$project->products,$tableDataArr['total_after_vat']);
					
					
                    $payments = $this->calculateCollectionOrPaymentAmounts($tableDataArr['payment_terms'], $tableDataArr['total_after_vat'], $datesAsIndexAndString, $customCollectionPolicy,true) ;
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
                if ($tableId =='expense_as_percentage' && $name) {
         
                    $expenseAsPercentageResult = $expenseAsPercentageEquation->calculate($expenseCategoryId, $expenseType, $name, $project->products, $productAllocations, $tableDataArr['start_date'], $loopEndDate, $tableDataArr['monthly_percentage'], $vatRate, $isDeductible, $tableDataArr['withhold_tax_rate']) ;
                    $expenseAmounts = $expenseAsPercentageResult['expense_amounts'];
                    
                    
                
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
                    $amountBeforeVat = $tableDataArr['amount']??0 ;
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
					$expenseAsPercentageResult['expense_allocations'] = Product::multiplyWithAllocation($productAllocations,$project->products,$oneTimeExpenses['monthly_one_time']??[]);
					
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
					// $tableDataArr['prepaid_expense_statement'] = Expense::calculatePrepaidExpenseStatement();
                }
                $tableDataArr['project_id']  = $project->id ;
                $tableDataArr['model_id']   = $project->id ;
                $tableDataArr['model_name']   = $modelName ;
                //	$tableDataArr['collection_statements'] = [];
                if ($name) {
					if($project->expenseHasAllocation($expenseCategoryId)){
						$expenseAllocations[$expenseType][$expenseCategoryId][$name] = $expenseAsPercentageResult['expense_allocations'];
					}
                    $project->generateRelationDynamically($tableId, $expenseType)->create($tableDataArr);
                    
                }
                    
                
            }
            
        }
        $expenseAllocationFormatted = [];
        foreach ($expenseAllocations as $expenseType => $arr1) {
			foreach ($arr1 as $expenseCategoryId => $expenseCategoryNameAndValues) {
				$hasAllocation = $project->getExpenseCategories()[$expenseCategoryId]['has_allocation'];
				if($hasAllocation){
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
        }
        DB::table('product_expense_allocations')->where('is_expense', 1)->where('project_id', $project->id)->delete();
        DB::table('product_expense_allocations')->insert($expenseAllocationFormatted);
        $project->recalculateAllProductsOverheadExpenses();
        
        if ($request->get('submit_button') != 'next') {
            return redirect()->route('main.project.page', ['project'=>$project->id]);
        }
        return redirect()->route('fixed.assets.form', $project->id);
    }

    /**************** Assets **********************/
    public function fixedAssetsGet(Project $project)
    {
      

        return view(
            'fixed-assets.form',
            $project->getFixedAssetsViewVars()
        );
    }

    public function fixedAssetsPost(StoreFixedAssetsRequest $request, Project $project)
    {
        $project->storeRepeaterRelations($request, ['fixedAssets'], ['project_id'=>$project->id]);
        $project->recalculateFixedAsset();
        
        if ($request->get('submit_button') != 'next') {
            return redirect()->route('main.project.page', ['project'=>$project->id]);
        }
		if($project->isNewCompany())
		{
			        return redirect()->route('financial.result', ['project'=>$project->id]);
		}
        return redirect()->route('openingBalances.form', $project->id);
        // return $project->new_company == 0 ? redirect()->route('openingBalances.form',$project->id) : redirect()->route('dashboard.index',$project->id);
        

    }
    
    
    public function openingBalancesGet(Project $project)
    {
		if($project->isNewCompany()){
			abort(404);
		}       
        return view(
            'openingBalances.form',
            $project->getOpeningBalancesViewVars()
        );
    }

    public function openingBalancesPost(StoreOpeningBalancesRequest $request, Project $project)
    {
		$project->storeRepeaterRelations($request, ['fixedAssetOpeningBalances','cashAndBankOpeningBalances','otherDebtorsOpeningBalances','vatAndCreditWithholdTaxesOpeningBalances'
        ,'supplierPayableOpeningBalances','otherCreditorsOpeningBalances','otherLongTermLiabilitiesOpeningBalances','equityOpeningBalances','longTermLoanOpeningBalances'
    ], ['project_id'=>$project->id]);
	
	
		$project->recalculateOpeningBalances();
	
		if($request->get('total_liabilities_and_equity_minus_total_assets') != 0){
			$errorMessage = __('Total Assets Must Be Equal To Total Liabilities + Owners Equity') . ' [ ' . number_format($request->get('total_liabilities_and_equity_minus_total_assets'))  . ' ]';
			return redirect()->back()->with('errors',collect([$errorMessage]));
		}
        if ($request->get('submit_button') != 'next') {
            return redirect()->route('main.project.page', ['project'=>$project->id]);
        }
        return redirect()->route('financial.result', ['project'=>$project->id]);
     
    }
    
    /**************** financialResultsGet **********************/
    public function financialResultsGet(Project $project)
    {
      
        
        
  
        
        
        return view(
            'financial-results.index',
            $project->calculateIncomeStatement()
        );
    }
	
	
	  public function cashInOutFlowGet(Project $project)
    {
        
        
        return view(
            'financial-results.index',
			$project->getCashInOutFlowViewVars()
        );
    } 
	public function balanceSheetGet(Project $project)
    {
        
        return view(
            'financial-results.index',
			$project->getBalanceSheetViewVars()
        );
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
        return ($request->submit_button == "next") ?  redirect()->route('view.results.dashboard', $project->id) : redirect()->route('main.project.page', $project->id);
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
        }elseif($collectionPolicyType == 'system_default'){
			$collectionPolicyValue = $paymentTerm;
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
