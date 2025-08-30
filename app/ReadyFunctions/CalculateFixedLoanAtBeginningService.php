<?php

namespace App\ReadyFunctions;

use App\Helpers\HArr;
use App\Helpers\HDate;
use App\Models\Loan;
use App\ReadyFunctions\Date;
use Carbon\Carbon;

class CalculateFixedLoanAtBeginningService
{
	public function __calculateBasedOnDiffBaseRates( array $baseRatesMapping ,string $loanType, string $loanStartDate, float $loanAmount, float $marginRate, float $tenor, string $installmentPaymentIntervalName,int $installmentPaymentIntervalValue, float $stepUpRate = 0, string $stepUpIntervalName = null, float $stepDownRate = 0, string $stepDownIntervalName = null, float $gracePeriod  = 0,int $monthIndex = 0,array $datesAsStringAndIndex = [],array $dateWithDateIndex = [] ):array 
	{
		
		$currentStartDateAsIndex=$monthIndex ;		
		if($loanAmount <= 0){
			return [] ;
		}
		$fixedAtEndResult = [];
		$i = 0 ;
		$previousResult = [];
		$diffInMonths = 0;
		foreach($baseRatesMapping as $currentBaseRateDate => $currentBaseRate){
		
		
			if($i != 0){
				$diffInMonths=Carbon::make($currentBaseRateDate)->diffInMonths($loanStartDate);
				$currentBaseRateDateAsIndex = $datesAsStringAndIndex[$currentBaseRateDate];
				$previousLoopDateAsIndex = $currentBaseRateDateAsIndex-1;
				$currentStartDateAsIndex  = $previousLoopDateAsIndex ;
				$loanStartDate = $dateWithDateIndex[$currentStartDateAsIndex];
				$tenor = $tenor -$diffInMonths+ $installmentPaymentIntervalValue ; 
				
				
				if($tenor >= 1 ){
					$loanAmount = HArr::getValueOrPrevious($fixedAtEndResult['current_result'][$i-1]['endBalance'], $currentStartDateAsIndex);
				}
			}
				$currentResultArr = [];
				if($tenor >= 1){
					$currentResultArr =$this->__calculate($previousResult,$i,$loanType,$loanStartDate,$loanAmount,$currentBaseRate,$marginRate,$tenor,$installmentPaymentIntervalName,$stepUpRate,$stepUpIntervalName,$stepDownRate,$stepDownIntervalName,$gracePeriod,$currentStartDateAsIndex);
					$previousResult =$currentResultArr['final_result']??[]; 
					$fixedAtEndResult['current_result'][]= $currentResultArr['result']??[]  ;
					$fixedAtEndResult['final_result']= $currentResultArr['final_result']??[]  ;
					$i++ ;
				}
			
		}
	
		// $time = $fixedAtEndResult['time'];
		$finalResult = $fixedAtEndResult['final_result']??[] ;
		unset($finalResult['totals']);
		return $finalResult;
	}
	
public function __calculate($previousResult ,int $indexOfLoop,string $loanType, string $startDate, float $loanAmount,  $baseRate, float $marginRate, float $tenor, string $installmentPaymentIntervalName, float $stepUpRate = 0, string $stepUpIntervalName = null, float $stepDownRate = 0, string $stepDownIntervalName = null, float $gracePeriod  = 0,$currentStartDateAsIndex=0  )
	{
		if($loanAmount <= 0){
			return [] ;
		}
		$loanFactors = [];
		$installmentFactors = [];
		$datesAsIndexString=HDate::generateDatesBetweenStartDateAndDuration($currentStartDateAsIndex,$startDate,$tenor,$installmentPaymentIntervalName,false);
		$datesIndexAndDaysCount =HDate::calculateDaysCountAtBeginning($datesAsIndexString); 
		unset($datesAsIndexString[array_key_last($datesAsIndexString)]);
		$datesAsStringIndex = array_flip($datesAsIndexString);
		$installmentPaymentIntervalValue = $this->getInstallmentPaymentIntervalValue($installmentPaymentIntervalName);
		$currentPricing =  ($baseRate + $marginRate) /100  ;
		$stepRate = Loan::getStepRate($loanType, $stepUpRate, $stepDownRate);
		$stepRate = $stepRate / 100;
		$isWithCapitalization = Loan::isWithCapitalization($loanType);
		$appliedStepName = Loan::getAppliedStepIntervalName($loanType, $stepUpIntervalName, $stepDownIntervalName);
		$appliedStepValue = $this->getAppliedStepIntervalValue($appliedStepName);
		$installmentStartDateAsIndex = $datesAsStringIndex[HDate::getDateAfterIndex($datesAsIndexString,$datesAsStringIndex,$startDate,($gracePeriod)/$installmentPaymentIntervalValue)];
		$endDateAsIndex = array_key_last($datesAsIndexString);
		$stepFactors = [];
		$currentStepFactorCounterValue = 0;
		$currentAppliedStepCounter = 0 ;
		$currentLoanFactor = $loanAmount;
		$currentInstallmentFactor = 0 ;
		foreach($datesIndexAndDaysCount as $currentDateAsIndex => $currentDaysCount){
			
				/**
				 * * calculate Interest Loan Factor 
				 */
				$interestFactors[$currentDateAsIndex]=($currentPricing / 360) * $currentDaysCount;
			
				$currentInterestFactor = $interestFactors[$currentDateAsIndex] ;
				/**
				 * * Calculate Loan Factors 
				 */
				if(!$isWithCapitalization && $currentDateAsIndex < $installmentStartDateAsIndex  ){
					$currentLoanFactor = $loanAmount;
				}else{
					$currentLoanFactor = $currentLoanFactor + ($currentLoanFactor * $currentInterestFactor);
				}
				$loanFactors[$currentDateAsIndex] = $currentLoanFactor;
				 
				 /**
				  * * Calculate Step Factor
				  */
				  if($currentDateAsIndex < $installmentStartDateAsIndex ){
					$stepFactors[$currentDateAsIndex]= 0 ;
				}else{
					$currentAppliedStepCounter++ ;
					$stepFactors[$currentDateAsIndex] = $currentStepFactorCounterValue;
					if($currentAppliedStepCounter == $appliedStepValue/$installmentPaymentIntervalValue){
						$currentAppliedStepCounter = 0 ;
						$currentStepFactorCounterValue++;
					}
				  }
				  
				  if($currentDateAsIndex < $installmentStartDateAsIndex ){
					$currentInstallmentFactor =  0 ;
				  }
				  else{
					$v = $stepFactors[$currentDateAsIndex] ;
					$currentInstallmentFactor = ($currentInstallmentFactor + (($currentInstallmentFactor-((pow((1 + $stepRate), ($v))))) * $currentInterestFactor) - (1 * pow((1 + $stepRate), ($v))));
				  }
				  $installmentFactors[$currentDateAsIndex] = $currentInstallmentFactor; 
				  /**
				   * * Calculate Installment Factor
				   */
		
		}
		$installmentAmounts = $this->calculateInstallmentAmount($loanFactors,$installmentFactors, $stepRate, $installmentStartDateAsIndex, $endDateAsIndex, $tenor, $installmentPaymentIntervalValue, $appliedStepValue);

		$loanScheduleResult = $this->calculateLoanScheduleResult($datesIndexAndDaysCount,$loanType, $loanAmount, $interestFactors, $installmentAmounts,$currentStartDateAsIndex);
	
		
		if($indexOfLoop == -1){
		
			return [
				'final_result'=>$loanScheduleResult,
			];
		}
		$mergedResult = $indexOfLoop == 0 ? $loanScheduleResult :$previousResult ;
	
		$mergedResult = $loanScheduleResult;

		
			foreach($previousResult as $key => $currentArr){
				if($key == 'totals'){
					continue;
				}

				$firstKey = array_key_first($loanScheduleResult[$key]);
				unset($loanScheduleResult[$key][$firstKey]);
				$mergedResult[$key]=HArr::mergeTwoAssocArr($previousResult[$key],$loanScheduleResult[$key],$key);
			
		}

		
		return [
			'result'=>$loanScheduleResult ,
			'final_result'=>$mergedResult ,
			// 'time'=>$time
		];
		
	}

	
	public function getInstallmentPaymentIntervalValue($installmentPayment):int
	{
		switch($installmentPayment) {
			case 'monthly':
				return 1;
			case 'quartly':
				return 3;
			case 'semi annually':
				return 6;
		}
	}

	protected function getAppliedStepIntervalValue($appliedStepIntervalName):int
	{
	
		switch($appliedStepIntervalName) {
			case 'quartly':
				return 3;
			case 'semi annually':
				return 6;
			case 'annually':
				return 12;
			default:
				return 12;
		}
	}

	protected function defaultDateFormat():string
	{
		return 'Y-m-d';
	}

	protected function addMonths(string $loanStartDateDay,string $date, int $duration):Carbon
	{
		return Carbon::make((new Date())->addMonths($loanStartDateDay,$date, $duration,0,1,2));
	}

	protected function getDateFormatted(Carbon $date):string
	{
		return $date->format($this->defaultDateFormat());
	}
	

	protected function calculateInstallmentAmount(array $loanFactors,array $installmentFactory, float $stepRate, int $installmentStartDateAsIndex, int $endDateAsIndex, float $tenor, int $installmentPaymentIntervalValue, int $appliedStepValue )
	{
	
		$installmentsAmounts = [];

		$loanFactoryAtEndDate = $loanFactors[$endDateAsIndex];
		
		$installmentFactorAtEndDate = $installmentFactory[$endDateAsIndex];

		$installmentAmount = $loanFactoryAtEndDate / ($installmentFactorAtEndDate * -1);

		$installmentsAmounts[$installmentStartDateAsIndex] = $installmentAmount;

		
		for ($i=1 ; $i <= ($tenor / $installmentPaymentIntervalValue) ; $i++) {
			$loopDateAsIndex = $installmentStartDateAsIndex ;
				$stepVal = ($appliedStepValue / $installmentPaymentIntervalValue ) ;
				if ($i != 1 && ($i %$stepVal ) == 1 ) {
					$installmentAmount = $installmentAmount * ((pow((1 + $stepRate), 1)));
				} else {
					$installmentAmount = $installmentAmount;
				}
				$installmentsAmounts[$loopDateAsIndex]=$installmentAmount;
				$installmentStartDateAsIndex = $loopDateAsIndex+1;
		}
		return $installmentsAmounts;
	}

	protected function calculateLoanScheduleResult(array $datesIndexAndDaysCount,string $loanType, float $loanAmount, array $interestFactor, array $installmentAmount)
	{
		$loanScheduleResult = [];
		$loanScheduleResult['totals']['totalSchedulePayment'] = 0;
		$loanScheduleResult['totals']['totalPrincipleAmount'] = 0;
		$loanScheduleResult['totals']['totalInterestAmount'] = 0;
		$isWithoutCapitalization =  Loan::isWithoutCapitalization($loanType);
		$firstLoop = true ;
		foreach($datesIndexAndDaysCount as $dateAsIndex => $currentDaysCount) {
			$previousDate = $dateAsIndex-1;
			$i = $dateAsIndex ; 
			$loanScheduleResult['beginning'][$i] =  $firstLoop ? $loanAmount : $loanScheduleResult['endBalance'][$previousDate]??0;
			$currentInstallmentAmount = $installmentAmount[$i] ?? 0;
			$loanScheduleResult['interestAmount'][$i] = ($loanScheduleResult['beginning'][$i] -$currentInstallmentAmount ) *   $interestFactor[$i];
			$loanScheduleResult['totals']['totalInterestAmount'] += $loanScheduleResult['interestAmount'][$i];
			$installmentAmountAtIndex =$installmentAmount[$i] ?? 0;
			$loanScheduleResult['schedulePayment'][$i] = $isWithoutCapitalization && $installmentAmountAtIndex == 0 ? $loanScheduleResult['interestAmount'][$i] : $installmentAmountAtIndex;
			$loanScheduleResult['totals']['totalSchedulePayment'] = $loanScheduleResult['totals']['totalSchedulePayment'] + $loanScheduleResult['schedulePayment'][$i];
			$loanScheduleResult['principleAmount'][$i] = $loanScheduleResult['schedulePayment'][$i] - $loanScheduleResult['interestAmount'][$i];
			$loanScheduleResult['totals']['totalPrincipleAmount'] += $loanScheduleResult['principleAmount'][$i];
			$loanScheduleResult['endBalance'][$i] = $loanScheduleResult['beginning'][$i]  + $loanScheduleResult['interestAmount'][$i] -$loanScheduleResult['schedulePayment'][$i];
			$loanScheduleResult['endBalance'][$i] = $loanScheduleResult['endBalance'][$i] < 1 && $loanScheduleResult['endBalance'][$i] > -1 ? 0 : $loanScheduleResult['endBalance'][$i];
			$firstLoop = false ;
		}
		return $loanScheduleResult;
	}
	

	
}
