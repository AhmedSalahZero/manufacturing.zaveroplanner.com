<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\Study;
use App\Project;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

	protected function formatForTheeLineChart(array $items){
		$lineChart = [];
		$barChart = [];
		foreach($items as $key => $arrayItems){
			$previous = 0 ;
			foreach($arrayItems as $year => $value){
				$currentGrowthRate = $previous ? (($value / $previous)-1)*100 : 0   ; 
				$previous = $value ;
				$lineChart[$key][] = [
					'date'=> $year.'-01-01' , 
					'revenue_value'=>number_format($value/1000000,2) ,
					'growth_rate'=>number_format($currentGrowthRate,2)
				];
				if($key != 'all'){
					$value = $value / 1000000;
					$barChart[$year][$key] =  isset($barChart[$year][$key]) ? $barChart[$year][$key] + $value : $value;
					$barChart[$year]['year'] = strval($year);
				}
			}
		}

		$barChart = array_values($barChart);
		
		return [
			'line_chart'=>$lineChart,
			'bar_chart'=>$barChart
		] ;
	}
	// protected function generateDashboardData(Project $project , bool $isSensitivity = false ):array 
	// {
	// 	$isMonthlyStudy = false;
	// 	$loanSchedulePaymentTableName =  $isSensitivity ? 'sensitivity_loan_schedule_payments' : 'loan_schedule_payments';
	// 	$percentageOfSalesColumnName = $isSensitivity ? 'sensitivity_expense_as_percentages' : 'expense_as_percentages';
	// 	$yearIndexWithYear = app('yearIndexWithYear');
	// 	$corporateTaxes = $project->getCorporateTaxesRate() / 100 ;
	// 	// $startDate = $study->getStudyStartDate();
	// 	// $endDate = $study->getStudyEndDate();
	// 	$formattedExpenses = [];
	// 	$formattedResult = [];
	// 	$salesRevenuePerTypes = [];
	// 	$yearWithItsIndexes = $project->getOperationDurationPerYearFromIndexes();
	// 	$monthsWithItsYear = $project->getMonthsWithItsYear($yearWithItsIndexes) ;
	// 	$monthsWithItsNumbers = $project->getMonthIndexWithMonthNumber($yearWithItsIndexes) ;

	// 	$titlesMapping = __('Title Here');
	// 	$loanSchedulePayments = DB::table($loanSchedulePaymentTableName)->selectRaw('portfolio_loan_type,revenue_stream_type,interestAmount')->where('study_id',$study->id)->get()->toArray();
		
	// 	$resultPerRevenueStreamType = [
	// 		'all'=>[]
	// 	];

	// 	$directFactoringBreakdown = DB::table('direct_factoring_breakdowns')
	// 	->where('project_id',$project->id)
	// 	->selectRaw('interest_revenue,bank_interest_expense')->get()->toArray();
	// 	$formattedDirectFactoring = [];
		
	// 	foreach($directFactoringBreakdown as $currentDirectFactoringBreakdown){
	// 		$interestRevenues= (array)json_decode($currentDirectFactoringBreakdown->interest_revenue);
	// 		$bankInterestExpenses= (array)json_decode($currentDirectFactoringBreakdown->bank_interest_expense);
	// 		foreach($monthsWithItsYear as $currentMonthIndex => $currentYearIndex)
	// 		{
	// 			$currentYearIndex = $monthsWithItsYear[$currentMonthIndex]??null;
	// 			$currentYearOrMonthIndex = $isMonthlyStudy ? $currentMonthIndex : $currentYearIndex ;
	// 			$currentYearAsString = $yearIndexWithYear[$currentYearIndex]??null;
				
	// 			$currentMonthNumber = $monthsWithItsNumbers[$currentMonthIndex]??null;
	// 			$currentYearOrMonthAsString = $isMonthlyStudy ? $currentMonthNumber : $currentYearAsString; 
	// 			$currentInterestRevenueAtMonthIndex  = $interestRevenues[$currentMonthIndex]??0;
	// 			$currentBankInterestExpenseAtMonthIndex = $bankInterestExpenses[$currentMonthIndex]??0;
	// 			if(!is_null($currentYearIndex)){
	// 				$formattedDirectFactoring['interest_revenue'][$currentYearOrMonthIndex] = isset($formattedDirectFactoring['interest_revenue'][$currentYearOrMonthIndex]) ? $formattedDirectFactoring['interest_revenue'][$currentYearOrMonthIndex] +  $currentInterestRevenueAtMonthIndex : $currentInterestRevenueAtMonthIndex;
	// 				$formattedDirectFactoring['bank_interest_expense'][$currentYearOrMonthIndex] = isset($formattedDirectFactoring['bank_interest_expense'][$currentYearOrMonthIndex]) ? $formattedDirectFactoring['bank_interest_expense'][$currentYearOrMonthIndex] +  $currentBankInterestExpenseAtMonthIndex : $currentBankInterestExpenseAtMonthIndex;
	// 				$resultPerRevenueStreamType['direct-factoring'][$currentYearOrMonthAsString] = $formattedDirectFactoring['interest_revenue'][$currentYearOrMonthIndex];
	// 		    	$salesRevenuePerTypes['direct-factoring'][$currentYearOrMonthIndex] = $resultPerRevenueStreamType['direct-factoring'][$currentYearOrMonthAsString];
	// 				$salesRevenuePerTypes['total_revenue'][$currentYearOrMonthIndex] =  isset($salesRevenuePerTypes['total_revenue'][$currentYearOrMonthIndex]) ? $salesRevenuePerTypes['total_revenue'][$currentYearOrMonthIndex] + $currentInterestRevenueAtMonthIndex : $currentInterestRevenueAtMonthIndex + $resultPerRevenueStreamType['direct-factoring'][$currentYearOrMonthAsString];
	// 			}
	// 		}
	// 	}
	
	// 	$testLoopIndex = 0 ;
	// 	foreach($loanSchedulePayments as $loanSchedulePaymentAsStdClass ){
	// 		$portfolioLoanType = $loanSchedulePaymentAsStdClass->portfolio_loan_type;
	// 		$isPortfolio = $portfolioLoanType == 'portfolio'; 
	// 		$revenueStreamType = $loanSchedulePaymentAsStdClass->revenue_stream_type;
	// 		$interestAmounts = json_decode($loanSchedulePaymentAsStdClass->interestAmount);
	// 		$testLoopIndex ++ ;
	// 		foreach($interestAmounts as $currentMonthIndex => $interestAmountAtMonthIndex){
				
	// 			$currentYearIndex = $monthsWithItsYear[$currentMonthIndex]??null;
	// 			$currentYearOrMonthIndex = $isMonthlyStudy ? $currentMonthIndex : $currentYearIndex ;
	// 			$currentDirectFactoringBankInterestExpenseAtYearIndex = $formattedDirectFactoring['bank_interest_expense'][$currentYearOrMonthIndex]??0;
	// 			$currentYearAsString = $yearIndexWithYear[$currentYearOrMonthIndex] ?? null ;
	// 			$currentMonthNumber = $monthsWithItsNumbers[$currentMonthIndex]??null;
	// 			$currentYearOrMonthAsString = $isMonthlyStudy ? $currentMonthNumber : $currentYearAsString; 
	// 			if(!is_null($currentYearOrMonthIndex)){
	// 				if($isPortfolio){
	// 					$salesRevenuePerTypes[$revenueStreamType][$currentYearOrMonthIndex] =  isset($salesRevenuePerTypes[$revenueStreamType][$currentYearOrMonthIndex]) ? $salesRevenuePerTypes[$revenueStreamType][$currentYearOrMonthIndex] + $interestAmountAtMonthIndex : $interestAmountAtMonthIndex;
	// 					$salesRevenuePerTypes['total_revenue'][$currentYearOrMonthIndex] =  isset($salesRevenuePerTypes['total_revenue'][$currentYearOrMonthIndex]) ? $salesRevenuePerTypes['total_revenue'][$currentYearOrMonthIndex] + $interestAmountAtMonthIndex : $interestAmountAtMonthIndex;
	// 					$formattedResult['sales_revenue'][$currentYearOrMonthIndex] = $salesRevenuePerTypes['total_revenue'][$currentYearOrMonthIndex] ;
	// 						 $resultPerRevenueStreamType[$revenueStreamType][$currentYearOrMonthAsString] = isset($resultPerRevenueStreamType[$revenueStreamType][$currentYearOrMonthAsString]) ? $resultPerRevenueStreamType[$revenueStreamType][$currentYearOrMonthAsString] + $interestAmountAtMonthIndex : $interestAmountAtMonthIndex;
	// 						$currentSalesRevenue = $formattedResult['sales_revenue'][$currentYearOrMonthIndex]??0 ;
	// 						$previousSalesRevenue = $formattedResult['sales_revenue'][$currentYearOrMonthIndex-1] ?? 0 ;
	// 						$formattedResult['growth_rate'][$currentYearOrMonthIndex] = $previousSalesRevenue ? (($currentSalesRevenue / $previousSalesRevenue)-1)*100 : 0 ;
	// 					}else{			
	// 						$formattedResult['interest_cogs'][$currentYearOrMonthIndex] = isset($formattedResult['interest_cogs'][$currentYearOrMonthIndex]) ? $formattedResult['interest_cogs'][$currentYearOrMonthIndex] + $interestAmountAtMonthIndex : $interestAmountAtMonthIndex + $currentDirectFactoringBankInterestExpenseAtYearIndex ;
	// 					}
	// 				}
	// 			}
			
	// 	}
	// 	$salaryExpenses = DB::table('departments')
	// 	->join('positions','positions.department_id','=','departments.id')
	// 	->selectRaw('expense_type,salary_expenses,expense_type')->where('type','manpower')->where('departments.company_id',$company->id)->get() ;
		
	// 	$expenses = DB::table('expenses')->join('expense_names','expense_names.id','=','expenses.expense_name_id')->selectRaw('expense_category,expense_names.name as name,name,relation_name,monthly_repeating_amounts,expense_as_percentages,sensitivity_expense_as_percentages,payload')->where('model_id',$study->id)->where('model_name','Study')->get()->toArray();
	// 	$columnPerTypes = [
	// 		'one_time_expense'=>'payload',
	// 		'percentage_of_sales'=>$percentageOfSalesColumnName,
	// 		'fixed_monthly_repeating_amount'=>'monthly_repeating_amounts',
	// 	];
	// 	$salaryExpensesForCategory = [];
	// 	foreach($salaryExpenses as $salaryExpense){
	// 		$expenseCategory = $salaryExpense->expense_type;
	// 		$salaryExpensePayload = json_decode($salaryExpense->salary_expenses);
	// 		foreach($monthsWithItsYear as $monthIndex => $yearIndex){
	// 			$currentYearOrMonthIndex = $isMonthlyStudy ? $monthIndex : $yearIndex ;
	// 			$currentSalaryExpense = $salaryExpensePayload[$monthIndex]??0;
	// 			$salaryExpensesForCategory[$expenseCategory][$currentYearOrMonthIndex] = isset($salaryExpensesForCategory[$expenseCategory][$currentYearOrMonthIndex]) ?  $salaryExpensesForCategory[$expenseCategory][$currentYearOrMonthIndex] + $currentSalaryExpense : $currentSalaryExpense;
	// 		}
	// 	}

		
	// 	foreach($expenses as $expense){
		
	// 		$name = $expense->name;
	// 		$relationName = $expense->relation_name;
	// 		$expenseCategory = $expense->expense_category;
	// 		$currentColumnName = $columnPerTypes[$relationName]??null;
	// 		if(is_null($currentColumnName)){
	// 			continue;
	// 		}
	// 		$monthlyExpenses = (array)json_decode($expense->{$currentColumnName});
			
	// 		$currentExpenseIndexes = $isMonthlyStudy ? $monthsWithItsNumbers :  $yearWithItsIndexes  ;
	// 		foreach($currentExpenseIndexes as $yearOrMonthIndex => $monthIndexWithActive){
	// 			$currentYearInterestCost = 0 ;
	// 			$currentYearManpowerTotal = 0 ;
	// 			$currentExpenseItemTotalPerYear = 0 ;
	// 			if($expenseCategory == 'cost-of-service' && !isset($formattedExpenses[$expenseCategory]['Interest Cost'][$yearOrMonthIndex])){
	// 				$formattedExpenses[$expenseCategory]['Interest Cost'][$yearOrMonthIndex]  = $formattedResult['interest_cogs'][$yearOrMonthIndex]??0 ;
	// 				$currentYearInterestCost = $formattedExpenses[$expenseCategory]['Interest Cost'][$yearOrMonthIndex];
	// 			}
	// 			if(!isset($formattedExpenses[$expenseCategory]['Manpower Salaries'][$yearOrMonthIndex])){
	// 				$formattedExpenses[$expenseCategory]['Manpower Salaries'][$yearOrMonthIndex] = $salaryExpensesForCategory[$expenseCategory][$yearOrMonthIndex] ?? 0;
	// 				$currentYearManpowerTotal = $formattedExpenses[$expenseCategory]['Manpower Salaries'][$yearOrMonthIndex];
	// 			}
	// 			if($isMonthlyStudy){
	// 				$currentExpenseItemTotalPerYear += $monthlyExpenses[$monthIndex]??0 ;
	// 			}else{
	// 				foreach($monthIndexWithActive as $monthIndex=> $isActiveIndex){
	// 						$currentExpenseItemTotalPerYear += $monthlyExpenses[$monthIndex]??0 ;
	// 				}
	// 			}
	// 			$formattedExpenses[$expenseCategory][$name][$yearOrMonthIndex] = $currentExpenseItemTotalPerYear;
	// 			$currentYearTotal = $currentExpenseItemTotalPerYear + $currentYearInterestCost +$currentYearManpowerTotal;
	// 			$formattedExpenses[$expenseCategory]['total'][$yearOrMonthIndex] = isset($formattedExpenses[$expenseCategory]['total'][$yearOrMonthIndex]) ? $formattedExpenses[$expenseCategory]['total'][$yearOrMonthIndex] + $currentYearTotal:$currentYearTotal    ; 
	// 		}
		
			
	// 	}
	// 	$currentExpenseIndexes = $isMonthlyStudy ? $monthsWithItsNumbers :  $yearWithItsIndexes  ;
	// 	foreach($currentExpenseIndexes as $yearOrMonthIndex => $monthWithItsIndexes){
	// 		$currentYearAsString = $yearIndexWithYear[$yearIndex] ?? null ;
	// 		$currentMonthNumber = $monthsWithItsNumbers[$yearOrMonthIndex]??null;
	// 		$currentSalesRevenue = $formattedResult['sales_revenue'][$yearOrMonthIndex]??0;
			
	// 		$currentYearAsOrMonthString = $isMonthlyStudy ? $currentMonthNumber : $currentYearAsString ;
	// 		$resultPerRevenueStreamType['all'][$currentYearAsOrMonthString] = $currentSalesRevenue;
	// 		// $currentInterestCogs = $formattedResult['interest_cogs'][$yearOrMonthIndex]??0;
	// 		$costOfServiceAtYearIndex = $formattedExpenses['cost-of-service']['total'][$yearOrMonthIndex]??0;
	// 		$formattedResult['gross_profit'][$yearOrMonthIndex] = $currentSalesRevenue - $costOfServiceAtYearIndex;
	// 		$formattedResult['gross_profit_percentage_of_sales'][$yearOrMonthIndex] = $currentSalesRevenue ? $formattedResult['gross_profit'][$yearOrMonthIndex] / $currentSalesRevenue *100 : 0 ;
	// 		$currentOPEXExpense =$formattedExpenses['other-operation-expense']['total'][$yearOrMonthIndex]??0; 
	// 		$currentMarketingExpense =$formattedExpenses['marketing-expense']['total'][$yearOrMonthIndex]??0; 
	// 		$currentSalesExpense =$formattedExpenses['sales-expense']['total'][$yearOrMonthIndex]??0; 
	// 		$currentGeneralExpense =$formattedExpenses['general-expense']['total'][$yearOrMonthIndex]??0; 
	// 		$currentDepreciationExpense =$formattedExpenses['depreciation-expense']['total'][$yearOrMonthIndex]??0; 
	// 		$currentEbitdaAtYearIndex = $currentSalesRevenue  - $costOfServiceAtYearIndex - $currentOPEXExpense - $currentMarketingExpense - $currentSalesExpense-$currentGeneralExpense+$currentDepreciationExpense;
	// 		$formattedResult['ebitda'][$yearOrMonthIndex] = $currentEbitdaAtYearIndex;
	// 		$formattedResult['ebitda_percentage_of_sales'][$yearOrMonthIndex] =$currentSalesRevenue ?  $currentEbitdaAtYearIndex / $currentSalesRevenue *100 :0;
	// 		$currentEbitAtYearIndex = $currentEbitdaAtYearIndex -  $currentDepreciationExpense;
	// 		$formattedResult['ebit'][$yearOrMonthIndex] = $currentEbitAtYearIndex;
	// 		$formattedResult['ebit_percentage_of_sales'][$yearOrMonthIndex] =$currentSalesRevenue ?  $currentEbitAtYearIndex / $currentSalesRevenue *100 :0;
	// 		$currentFinanceInterestExpense = $formattedExpenses['financial-interest-expense']['total'][$yearOrMonthIndex]??0;
	// 		$currentEbtAtYearIndex = $currentEbitAtYearIndex - $currentFinanceInterestExpense ;
	// 		$formattedResult['ebt'][$yearOrMonthIndex] = $currentEbtAtYearIndex;
	// 		$formattedResult['ebt_percentage_of_sales'][$yearOrMonthIndex] =$currentSalesRevenue ?  $currentEbtAtYearIndex / $currentSalesRevenue *100 :0;
	// 		$formattedResult['net_profit'][$yearOrMonthIndex] = $currentEbtAtYearIndex <0 ? $currentEbtAtYearIndex :$currentEbtAtYearIndex * (1-$corporateTaxes)  ;  
	// 		$formattedResult['net_profit_percentage_of_sales'][$yearOrMonthIndex] = $currentSalesRevenue ? $formattedResult['net_profit'][$yearOrMonthIndex] / $currentSalesRevenue  *100 :0 ;  
			
	// 	}
		
		
	// 	return [
	// 		'titlesMapping'=>$titlesMapping,
	// 		'lineChart'=>$lineChart ,
	// 		'barChart'=>$barChart ,
	// 		'formattedResult'=>$formattedResult ,
	// 		'formattedExpenses'=>$formattedExpenses,
	// 		'yearWithItsIndexes'=>$yearWithItsIndexes
	// 	];
		
	// }
	public function view(Request $request ,Project $project)
	{
		
		return view('dashboard.dashboard',$project->getDashboardViewVars());
	}
}
