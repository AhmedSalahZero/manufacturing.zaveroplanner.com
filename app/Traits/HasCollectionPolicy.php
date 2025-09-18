<?php
namespace App\Traits;

use App\Helpers\HArr;
use App\Product;



trait HasCollectionPolicy
{
	public function getCollectionDownPaymentAtIndex($index,?string $localOrExport)
	{
		if(!is_null($localOrExport)){
			return $this->collection_policy_value[$localOrExport][$index]['cash_payment'] ?? 0;
		}
		return $this->collection_policy_value[$index]['cash_payment'] ?? 0;
	}
	public function getCollectionRateAtIndex( int $index,int $numberOfCollection,?string $localOrExport)
	{
		if(!is_null($localOrExport)){
			
			return $this->collection_policy_value[$localOrExport][$index]['rate'][$numberOfCollection] ?? 0;
		}
		return $this->collection_policy_value[$index]['rate'][$numberOfCollection] ?? 0;
	}
	/**
	 * * $index is 0 for first collection and 1 for second collection
	 * * $numberOfCollection 0  for first one and 1 for second one
	 */
	public function getCollectionDueDaysAtIndex(int $index,int $numberOfCollection,?string $localOrExport)
	{
		$defaultValue  = $index ==0 ? 30 : 60 ;
		if(!is_null($localOrExport)){
			return $this->collection_policy_value[$localOrExport][$index]['due_in_days'][$numberOfCollection] ?? $defaultValue;
		}
		return $this->collection_policy_value[$index]['due_in_days'][$numberOfCollection] ?? $defaultValue;
	}
	public function getDueDayWithRates(int $index,$localOrExport):array 
	{
		
		$dueDayAndRatesAtIndex = [];
		if(!is_null($localOrExport)){
			$dueDayAndRatesAtIndex = $this->collection_policy_value[$localOrExport][$index] ;
			
		}else{
			$dueDayAndRatesAtIndex = $this->collection_policy_value[$index]??[] ;
		}
		
		if(is_null($localOrExport) && !count($dueDayAndRatesAtIndex)){
			return [];
		}
		
		   $firstDueDate = $dueDayAndRatesAtIndex['due_in_days'][0] ?? null ;
			$firstDueRate = $dueDayAndRatesAtIndex['rate'][0]??null ;
	
			$secondDueDate = $dueDayAndRatesAtIndex['due_in_days'][1] ?? null ;
			$secondDueRate = $dueDayAndRatesAtIndex['rate'][1] ?? null ;
			$dueWithRates = [
				0 => $dueDayAndRatesAtIndex['cash_payment']??0 , 
				
			];
	
			if(!is_null($firstDueDate)){
				$dueWithRates[$firstDueDate] = $firstDueRate;
			}if(!is_null($secondDueDate)){
				$dueWithRates[$secondDueDate] = $secondDueRate;
			}
				return  $dueWithRates;
				
		// return  [
		// 	0 => $dueDayAndRatesAtIndex['cash_payment']??0 , 
		// 	$dueDayAndRatesAtIndex['due_in_days'][0] => $dueDayAndRatesAtIndex['rate'][0],
		// 	$dueDayAndRatesAtIndex['due_in_days'][1] => $dueDayAndRatesAtIndex['rate'][1],
		// ];
	}
	/**
	 * * $localOrExport خاص بالسيستم دا بس 
	 * * في الحالات ما عدا ذالك ممكن تشيله او تمررة بنال
	 */
	public function calculateMultiYearsCollectionPolicy($monthlySalesTargetValueBeforeVat , $localOrExport,$debug = false)
	{
		
		$withholdRate = $localOrExport =='export' ? 0: $this->getWithholdTaxRate() / 100;
        $vatRate  = $localOrExport =='export' ? 0 : $this->getVatRate() / 100;
        $dateIndexWithDate = $this->project->getDateIndexWithDate();
        
        $withholdAmounts = HArr::MultiplyWithNumber($monthlySalesTargetValueBeforeVat, $withholdRate);
        $vatAmounts = HArr::MultiplyWithNumber($monthlySalesTargetValueBeforeVat, $vatRate);
        
        
        
        
        
        // for amount after vat
        $monthlySalesTargetValueAfterVat = HArr::sumAtDates([$monthlySalesTargetValueBeforeVat,$vatAmounts], array_keys($monthlySalesTargetValueBeforeVat));
	
        $salesActiveYearsIndexWithItsMonths=  $this->getSalesActiveYearsIndexWithItsMonths();
        $hasMultiYear = array_key_exists(1, $salesActiveYearsIndexWithItsMonths) ;
		$firstYearIndex = array_key_first($salesActiveYearsIndexWithItsMonths);
        $monthlySalesTargetValueAfterVatForFirstYearMonths = array_intersect_key($monthlySalesTargetValueAfterVat, array_flip(array_keys($salesActiveYearsIndexWithItsMonths[$firstYearIndex])));
        $dueDayWithRates = $this->getDueDayWithRates(0,$localOrExport);
	
		// if($localOrExport == 'export'){
			
		// }
        $amountAfterVat = [];
        $amountAfterVatForFirstYear = $this->calculateCollectionOrPaymentForMultiCustomizedAmounts($dueDayWithRates, $monthlySalesTargetValueAfterVatForFirstYearMonths);
        $amountAfterVat = $amountAfterVatForFirstYear;
        if ($hasMultiYear) {
            $secondYearStartMonthIndex = array_key_last(($salesActiveYearsIndexWithItsMonths[$firstYearIndex])) + 1 ;
            $monthlySalesTargetValueAfterVatForMultiYearMonths = array_slice($monthlySalesTargetValueAfterVat, $secondYearStartMonthIndex, null, true);
            $dueDayWithRates = $this->getDueDayWithRates(1,$localOrExport);
            $amountAfterVatForMultiYear = $this->calculateCollectionOrPaymentForMultiCustomizedAmounts($dueDayWithRates, $monthlySalesTargetValueAfterVatForMultiYearMonths);
            $amountAfterVat = HArr::sumAtDates([$amountAfterVatForFirstYear,$amountAfterVatForMultiYear], array_keys($monthlySalesTargetValueAfterVat));
        }
        
        $salesActiveYearsIndexWithItsMonths=  $this->getSalesActiveYearsIndexWithItsMonths();
        $withholdAmountsForFirstYearMonths = array_intersect_key($withholdAmounts, array_flip(array_keys($salesActiveYearsIndexWithItsMonths[$firstYearIndex])));
        $dueDayWithRates = $this->getDueDayWithRates(0,$localOrExport);

	
        $withholdAmountsForFirstYear = $this->calculateCollectionOrPaymentForMultiCustomizedAmounts($dueDayWithRates, $withholdAmountsForFirstYearMonths);
        $withholdPayments = $withholdAmountsForFirstYear;
        if ($hasMultiYear) {
            $secondYearStartMonthIndex = array_key_last(($salesActiveYearsIndexWithItsMonths[$firstYearIndex])) + 1 ;
            $withholdAmountsForMultiYearMonths = array_slice($withholdAmounts, $secondYearStartMonthIndex, null, true);
            $dueDayWithRates = $this->getDueDayWithRates(1,$localOrExport);
            $amountAfterVatForMultiYear = $this->calculateCollectionOrPaymentForMultiCustomizedAmounts($dueDayWithRates, $withholdAmountsForMultiYearMonths);
            $withholdPayments = HArr::sumAtDates([$withholdAmountsForFirstYear,$amountAfterVatForMultiYear], array_keys($withholdAmounts));
        }
		
        $netPaymentsAfterWithhold = HArr::subtractAtDates([$amountAfterVat,$withholdPayments], array_keys($monthlySalesTargetValueBeforeVat));
        $collectionStatement = self::calculateStatement($monthlySalesTargetValueBeforeVat, $vatAmounts, $netPaymentsAfterWithhold, $withholdPayments, $dateIndexWithDate);
		return $collectionStatement;

		
	}
}
