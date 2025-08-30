<?php

namespace App\ReadyFunctions;

use Carbon\Carbon;

class CollectionPolicyService
{
	public function applyCollectionPolicy($hasCollectionPolicy, ?string $collectionPolicyType, $collectionPolicyValue, array $dateValue)
	{
		$collections = [];
		if (!$hasCollectionPolicy) {
			// reset Collection Policy
			foreach ($dateValue as $date => $value) {
				$collections[$date] = 0;
			}
			
		} elseif ($collectionPolicyType == 'customize' && is_array($collectionPolicyValue)) {
			
			$ratesWithDueDays = $this->formatRatesWithDueDays($collectionPolicyValue);
			foreach ($dateValue as $currentDate => $target) {
				foreach ($ratesWithDueDays as $dueDay => $rate) {
					$rate =  $rate / 100;
					$actualMonthsNumbers = $dueDay < 30 ? 0 : round((($dueDay) / 30));
					$date = (Carbon::make($currentDate))->addMonths($actualMonthsNumbers);
					$month = $date->format('m');
					$year = $date->format('Y');
					$day = $date->format('d');
					$fullDate = $year  . '-' . $month . '-' . $day;
					$collections[$fullDate] = ($target * $rate) + ($collections[$fullDate] ?? 0);
				}
			}
		} elseif ($collectionPolicyType == 'system_default' && $collectionPolicyValue && is_string($collectionPolicyValue)) {
			$collections = $this->sumForInterval($dateValue, $collectionPolicyValue);
		}
		return $collections;
	}
	/**
	 * * $dueDayWithRates [0=>10 , 15=>30 ] and so on
	 */
	public function applyMultiCustomizedCollectionPolicy($dueDayWithRates,  array $dateValue)
	{
			$collections = [];
		
			foreach ($dateValue as $currentDateAsIndex => $target) {
				foreach ($dueDayWithRates as $dueDay => $rate) {
					$rate =  $rate / 100;
					$actualMonthsNumbers = $dueDay < 30 ? 0 : round((($dueDay) / 30));
					$newDateAsIndex = $currentDateAsIndex+$actualMonthsNumbers;
					// $month = $date->format('m');
					// $year = $date->format('Y');
					// $day = $date->format('d');
					// $fullDate = $year  . '-' . $month . '-' . $day;
					$collections[$newDateAsIndex] = ($target * $rate) + ($collections[$newDateAsIndex] ?? 0);
				}
			}
		return $collections;
	}
	
	protected function sumForInterval(array $dateValues, string $intervalName)
	{
		$result = [];
		$periodInterval = $this->getPeriodsForStartMonths($intervalName);
		

		foreach ($dateValues as $currentDate => $value) {
			$dateObject = Carbon::make($currentDate);
			$year = $dateObject->format('Y');
			$month = $dateObject->format('m');
			$day = $dateObject->format('d');
			$sumMonth = sprintf("%02d", $this->getSumMonth($month, $periodInterval));
			$resultDate = $year  . '-' . $sumMonth . '-' . $day;
			$result[$resultDate] = isset($result[$resultDate]) ? $result[$resultDate] + $value  : $value;
		}
		return $result;
	}

	protected function getPeriodsForStartMonths($interval)
	{

		if ($interval == 'monthly') {
			return  [
				1 => [1],
				2 => [2],
				3 => [3],
				4 => [4],
				5 => [5],
				6 => [6],
				7 => [7],
				8 => [8],
				9 => [9],
				10 => [10],
				11 => [11],
				12 => [12],
			];
		}

		if ($interval == 'quarterly') {

			return [
				1 => [1, 2, 3], 4 => [4, 5, 6], 7 => [7, 8, 9], 10 => [10, 11, 12]
			];
		}
		if ($interval == 'semi-annually') {
			return [
				1 => [1, 2, 3, 4, 5, 6], 7 => [7, 8, 9, 10, 11, 12]
			];
		}

		if ($interval == 'annually') {
			return [
				1 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
			];
		}
		return [];
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


	protected function getSumMonth($month, $mapMonths)
	{

		foreach ($mapMonths as $sumMonth => $sumMonths) {
			if (in_array($month, $sumMonths)) {
				return $sumMonth;
			}
		}
	}
}
