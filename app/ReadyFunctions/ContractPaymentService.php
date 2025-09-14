<?php

namespace App\ReadyFunctions;

use Carbon\Carbon;

class ContractPaymentService
{
	public function __calculate(array $executionAmounts ,array $ratesWithDueDays, array $ratesWithIsFromTotal , array $ratesWithIsFromExecution  ,array $dateIndexWithDate, array $dateWithDateIndex)
	{
	
		$collections = [];
		$dateValue = $executionAmounts ;
		$executionStartDateAsIndex = array_key_first($executionAmounts);
		$totalAmount = array_sum($executionAmounts);
		
		/**
		 * 
		 * * From Total
		 */
			foreach ($ratesWithIsFromTotal as $dueDay => $false) {
				$rate = $ratesWithDueDays[$dueDay] ?? 0 ;
				$rate =  $rate / 100;
				$actualMonthsNumbers = $dueDay < 30 ? 0 : round((($dueDay) / 30));
				$executionStartDateAsString =     $dateIndexWithDate[$executionStartDateAsIndex]  ;
				$currentDateObject =   Carbon::make($executionStartDateAsString) ;
				$date =$currentDateObject->addMonths($actualMonthsNumbers);
				$month = $date->format('m');
				$year = $date->format('Y');
				$day = $date->format('d');
				$fullDate =$year . '-' . $month . '-' . $day   ;
				$dateIndex =  $dateWithDateIndex[$fullDate];
				$collections[$dateIndex] = ($totalAmount * $rate) + ($collections[$dateIndex] ?? 0);
			}
			
		/**
		 * 
		 * * From Execution
		 */
		foreach ($dateValue as $currentDate => $target) {
			foreach ($ratesWithIsFromExecution as $dueDay => $false) {
				$isFromTotal = false ;
				
				$rate = $ratesWithDueDays[$dueDay] ?? 0 ;
				$rate =  $rate / 100;
				$actualMonthsNumbers = $dueDay < 30 ? 0 : round((($dueDay) / 30));
				$dateAsString =  is_numeric($currentDate) ?   $dateIndexWithDate[$currentDate] :$currentDate ;
				$currentDateObject =    Carbon::make($dateAsString);
				$date =$currentDateObject->addMonths($actualMonthsNumbers);
				$month = $date->format('m');
				$year = $date->format('Y');
				$day = $date->format('d');
				$fullDate =$year . '-' . $month . '-' . $day   ;
				$dateIndex =  $dateWithDateIndex[$fullDate];
				$collections[$dateIndex] = ($target * $rate) + ($collections[$dateIndex] ?? 0);
			}
		}
		ksort($collections);
		return $collections;
		}
	
	protected function formatRatesWithDueDays(array $ratesAndDueDays): array
	{
		$result = [];
		foreach ($ratesAndDueDays['due_in_days'] ?? [] as $index => $dueDay) {
			$rate = $ratesAndDueDays['rate'][$index] ?? 0;
			if ($rate) {
				if (isset($result[$dueDay])) {
					$result[$dueDay] += $rate;
				} else {
					$result[$dueDay] = $rate;
				}
			}
		}

		return $result;
	}
	
}
