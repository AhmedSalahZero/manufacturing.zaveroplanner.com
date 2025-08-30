<?php 
namespace App\ReadyFunctions;

use App\Models\NonBankingService\Study;
use Illuminate\Support\Facades\DB;

class PortfolioPresentValue 
{
	public function calculate(array $dateIndexWithDate ,array $portfolioLoanFundingRatesPerMonths , array $operationDurationPerYearFromIndexes,int $tenorInYears,array $startFromPerYear , array $frequencyPerYear,array $portfolioMortgageTransactionAmountsPerYears,array $cbeLendingRatesPerMonths,float $marginRate,array $bankMarginRates , int $companyId , int $studyId , int $portfolioMortgageCategoryId):void 
	{
		
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('loan_schedule_payments')->where('study_id',$studyId)->where('revenue_stream_type',Study::PORTFOLIO_MORTGAGE)->where('revenue_stream_id',$portfolioMortgageCategoryId)->delete();
					
		$portfolioLoans=[];
		$currentUnearnedInterestStatement = [];
			$calculateFixedLoanAtEndService = new CalculateFixedLoanAtEndService; 
			$occurrenceDates = [];
			$monthlyAmounts=[];
			$loanType = 'normal' ;
			$tenorInMonths = $tenorInYears *12;
			$installmentPaymentIntervalName='monthly';
			$accumulatedMonthsAmountsDueDates = [];
			foreach($operationDurationPerYearFromIndexes  as $currentYearIndex => $months){
				$currentStartFrom = $startFromPerYear[$currentYearIndex];
				$currentFrequency = $frequencyPerYear[$currentYearIndex];
				$lastMonthIndexInCurrentYear =  array_key_last($months);
				if($currentFrequency == 0){
					$occurrenceDates[$currentYearIndex][] = $currentStartFrom;
				}else{
					for($i = $currentStartFrom  ; $i <=$lastMonthIndexInCurrentYear ; $i += $currentFrequency ){
						$occurrenceDates[$currentYearIndex][] = $i ; 
					}
				}
			}
			foreach($occurrenceDates as $currentYearIndex=>$occurrenceIndexesAndDates){
				$currentYearAmount = $portfolioMortgageTransactionAmountsPerYears[$currentYearIndex]??0;
				 
				foreach($occurrenceIndexesAndDates as $currentOccurrenceMonthIndex){
					$monthlyAmounts[$currentOccurrenceMonthIndex] = $currentYearAmount / ($tenorInMonths);
				}
			
			}

			foreach($monthlyAmounts as $currentOccurrenceMonthIndex => $currentOccurrenceAvgAmount){
				for($i = 1 ; $i<= $tenorInMonths ; $i++ ){
					$currentBaseRate = $cbeLendingRatesPerMonths[$currentOccurrenceMonthIndex];
					$currentPricingAtOccurrenceIndex = ($currentBaseRate + $marginRate) / 100;
					$currentMonthlyInterest =  $currentPricingAtOccurrenceIndex / 12 ;
					$currentMonthsCount = ($currentOccurrenceMonthIndex+$i -$currentOccurrenceMonthIndex  ) ;
					$currentNetPresetValue = $currentOccurrenceAvgAmount / pow(1+$currentMonthlyInterest,$currentMonthsCount);  
					$currentUnearnedInterest = $currentOccurrenceAvgAmount-$currentNetPresetValue;
					$currentMonthsAmountsDueDates[$currentOccurrenceMonthIndex][$currentOccurrenceMonthIndex+$i] = [
						'schedule_payment'=>$currentOccurrenceAvgAmount,
						'month_counts'=>$currentMonthsCount,
						'net_present_value'=>$currentNetPresetValue,
						'unearned_interest'=> $currentUnearnedInterest 
					]; 
					$portfolioLoanFundingRatesAtOccurrenceMonthIndex = $portfolioLoanFundingRatesPerMonths[$currentOccurrenceMonthIndex] / 100;
					$accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['net_present_value'] = isset($accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['net_present_value']) ? $accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['net_present_value'] + $currentNetPresetValue : $currentNetPresetValue;
					$accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['bank_loan_amount'] = $accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['net_present_value'] * $portfolioLoanFundingRatesAtOccurrenceMonthIndex;
					$accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['unearned_interest'] = isset($accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['unearned_interest']) ? $accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['unearned_interest'] + $currentUnearnedInterest : $currentUnearnedInterest;
					$accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['schedule_payment'] =  $currentOccurrenceAvgAmount;
					$accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['base_rate'] = $currentBaseRate ;
					$accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['margin_rate'] = $marginRate ;	
				}
			}
			 $this->calculateMonthlyAmounts($tenorInMonths,$installmentPaymentIntervalName,$loanType,$dateIndexWithDate,$currentUnearnedInterestStatement,$accumulatedMonthsAmountsDueDates,$portfolioLoans,$calculateFixedLoanAtEndService,$portfolioMortgageCategoryId,$studyId,$companyId);
			// $bankLoanAmounts
			DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('loan_schedule_payments')->insert(
				$portfolioLoans
			);
		
			
	
	}
	public function calculateForMonthlyStudy(array $monthlyAmounts , array $cbeLendingRatesPerMonths,array $portfolioLoanFundingRatesPerMonths,float $marginRate,int $tenorInYears ,array $dateIndexWithDate   , int $portfolioMortgageCategoryId,int $studyId, int $companyId)
	{
		
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('loan_schedule_payments')->where('study_id',$studyId)->where('revenue_stream_type',Study::PORTFOLIO_MORTGAGE)->where('revenue_stream_id',$portfolioMortgageCategoryId)->delete();
					
		$portfolioLoans=[];
		$currentUnearnedInterestStatement = [];
			$calculateFixedLoanAtEndService = new CalculateFixedLoanAtEndService; 
			$loanType = 'normal' ;
			$tenorInMonths = $tenorInYears *12;
			$installmentPaymentIntervalName='monthly';
			$accumulatedMonthsAmountsDueDates = [];
			
			
			foreach($monthlyAmounts as $currentOccurrenceMonthIndex => $currentOccurrenceAvgAmount){
				for($i = 1 ; $i<= $tenorInMonths ; $i++ ){
					$currentBaseRate = $cbeLendingRatesPerMonths[$currentOccurrenceMonthIndex];
					$currentPricingAtOccurrenceIndex = ($currentBaseRate + $marginRate) / 100;
					$currentMonthlyInterest =  $currentPricingAtOccurrenceIndex / 12 ;
					$currentMonthsCount = ($currentOccurrenceMonthIndex+$i -$currentOccurrenceMonthIndex  ) ;
					$currentNetPresetValue = $currentOccurrenceAvgAmount / pow(1+$currentMonthlyInterest,$currentMonthsCount);  
					$currentUnearnedInterest = $currentOccurrenceAvgAmount-$currentNetPresetValue;
					$currentMonthsAmountsDueDates[$currentOccurrenceMonthIndex][$currentOccurrenceMonthIndex+$i] = [
						'schedule_payment'=>$currentOccurrenceAvgAmount,
						'month_counts'=>$currentMonthsCount,
						'net_present_value'=>$currentNetPresetValue,
						'unearned_interest'=> $currentUnearnedInterest 
					]; 
					$portfolioLoanFundingRatesAtOccurrenceMonthIndex = $portfolioLoanFundingRatesPerMonths[$currentOccurrenceMonthIndex] / 100;
					$accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['net_present_value'] = isset($accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['net_present_value']) ? $accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['net_present_value'] + $currentNetPresetValue : $currentNetPresetValue;
					$accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['bank_loan_amount'] = $accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['net_present_value'] * $portfolioLoanFundingRatesAtOccurrenceMonthIndex;
					$accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['unearned_interest'] = isset($accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['unearned_interest']) ? $accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['unearned_interest'] + $currentUnearnedInterest : $currentUnearnedInterest;
					$accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['schedule_payment'] =  $currentOccurrenceAvgAmount;
					$accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['base_rate'] = $currentBaseRate ;
					$accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['margin_rate'] = $marginRate ;	
				}
			}
			
 		 $this->calculateMonthlyAmounts($tenorInMonths,$installmentPaymentIntervalName,$loanType,$dateIndexWithDate,$currentUnearnedInterestStatement,$accumulatedMonthsAmountsDueDates,$portfolioLoans,$calculateFixedLoanAtEndService,$portfolioMortgageCategoryId,$studyId,$companyId);

		
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('loan_schedule_payments')->insert(
				$portfolioLoans
			);
			
	}
	protected function calculateMonthlyAmounts($tenorInMonths,$installmentPaymentIntervalName,string $loanType,array $dateIndexWithDate,array &$currentUnearnedInterestStatement,array &$accumulatedMonthsAmountsDueDates ,array &$portfolioLoans , CalculateFixedLoanAtEndService $calculateFixedLoanAtEndService , int $portfolioMortgageCategoryId,int $studyId, int $companyId )
	{
		foreach($accumulatedMonthsAmountsDueDates as $currentOccurrenceMonthIndex => $portfolioMortgageLoanArray){
				$currentBankMarginRate = $bankMarginRates[$currentOccurrenceMonthIndex]??0;
				$currentLoanDateAsString = $dateIndexWithDate[$currentOccurrenceMonthIndex];
				$currentLoanAmount = $portfolioMortgageLoanArray['net_present_value'];
				$currentBankLoanAmount = $portfolioMortgageLoanArray['bank_loan_amount'];
				$currentBaseRate = $portfolioMortgageLoanArray['base_rate'];
				$currentMarginRate = $portfolioMortgageLoanArray['margin_rate'];
				$currentUnearnedInterest = $portfolioMortgageLoanArray['unearned_interest'];
				$currentDaysCount = 30 ;
				
				$portfolioLoanAmounts[$currentOccurrenceMonthIndex]=$calculateFixedLoanAtEndService->__calculate([],-1,$loanType,$currentLoanDateAsString,$currentLoanAmount,$currentBaseRate,$currentMarginRate,$tenorInMonths,$installmentPaymentIntervalName,0,null,0,null,0,$currentOccurrenceMonthIndex,$currentDaysCount)['final_result']??[];
				$portfolioLoanAmountsFormatted = $portfolioLoanAmounts[$currentOccurrenceMonthIndex];
				$bankLoanAmounts[$currentOccurrenceMonthIndex]=$calculateFixedLoanAtEndService->__calculate([],-1,$loanType,$currentLoanDateAsString,$currentBankLoanAmount,$currentBaseRate,$currentBankMarginRate,$tenorInMonths,$installmentPaymentIntervalName,0,null,0,null,0,$currentOccurrenceMonthIndex,$currentDaysCount)['final_result']??[];
				$bankLoanAmountsFormatted=$bankLoanAmounts[$currentOccurrenceMonthIndex];
				
				if(count($portfolioLoanAmountsFormatted)){
					$portfolioLoanAmountsFormatted['study_id'] = $studyId ;
					$portfolioLoanAmountsFormatted['company_id'] = $companyId ;
					$portfolioLoanAmountsFormatted['month_as_index'] = $currentOccurrenceMonthIndex ;
					$portfolioLoanAmountsFormatted['revenue_stream_id'] =$portfolioMortgageCategoryId ;
					$portfolioLoanAmountsFormatted['revenue_stream_category_id'] =null ;
					$portfolioLoanAmountsFormatted['portfolio_loan_type'] ='portfolio';
					$portfolioLoanAmountsFormatted['revenue_stream_type'] =Study::PORTFOLIO_MORTGAGE;
					
					$portfolioLoans[]=collect($portfolioLoanAmountsFormatted)->map(function($item,$keyName){
						
						if(is_array($item)){
							return json_encode($item);
						}
						return $item;
					})->toArray();
				}
				if(count($bankLoanAmountsFormatted)){
					$bankLoanAmountsFormatted['study_id'] = $studyId ;
					$bankLoanAmountsFormatted['company_id'] = $companyId ;
					$bankLoanAmountsFormatted['month_as_index'] = $currentOccurrenceMonthIndex ;
					$bankLoanAmountsFormatted['revenue_stream_id'] =$portfolioMortgageCategoryId ;
					$bankLoanAmountsFormatted['revenue_stream_category_id'] =null ;
					$bankLoanAmountsFormatted['portfolio_loan_type'] ='bank_portfolio';
					$bankLoanAmountsFormatted['revenue_stream_type'] =Study::PORTFOLIO_MORTGAGE;
					
					$portfolioLoans[]=collect($bankLoanAmountsFormatted)->map(function($item,$keyName){
						
						if(is_array($item)){
							return json_encode($item);
						}
						return $item;
					})->toArray();
				}
				
				
				
				
				$interestAmountsAtOccurrenceMonthIndex[$currentOccurrenceMonthIndex]=$portfolioLoanAmounts[$currentOccurrenceMonthIndex]['interestAmount']??[];
				
				$currentEndUnearnedBeginningBalance = 0 ;
				foreach($interestAmountsAtOccurrenceMonthIndex[$currentOccurrenceMonthIndex] as $currentMonth => $currentInterestAmount){
					$currentEndUnearnedEndBalance = $currentEndUnearnedBeginningBalance + $currentInterestAmount - $currentUnearnedInterest;
					$currentUnearnedInterestStatement[$currentOccurrenceMonthIndex][$currentMonth]['beginning_balance'] = $currentEndUnearnedBeginningBalance;
					$currentUnearnedInterestStatement[$currentOccurrenceMonthIndex][$currentMonth]['interest_amount'] = $currentInterestAmount;
					$currentUnearnedInterestStatement[$currentOccurrenceMonthIndex][$currentMonth]['unearned_interest'] = $currentUnearnedInterest;
					$currentUnearnedInterestStatement[$currentOccurrenceMonthIndex][$currentMonth]['end_balance'] = $currentEndUnearnedEndBalance < 1 && $currentEndUnearnedEndBalance > -1 ? 0 : $currentEndUnearnedEndBalance ;
					$currentEndUnearnedBeginningBalance = $currentEndUnearnedEndBalance;
					$currentUnearnedInterest=0;
				}
				// $interestRevenue 
				
			}
	}
}
