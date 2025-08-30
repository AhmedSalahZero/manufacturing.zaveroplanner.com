<?php
namespace App\Traits;

use App\Helpers\HArr;
use App\Product;



trait HasCollectionPolicy
{
	public function getCollectionDownPaymentAtIndex($index)
	{
		return $this->collection_policy_value[$index]['cash_payment'] ?? 0;
	}
	public function getCollectionRateAtIndex( int $index,int $numberOfCollection)
	{
		return $this->collection_policy_value[$index]['rate'][$numberOfCollection] ?? 0;
	}
	/**
	 * * $index is 0 for first collection and 1 for second collection
	 * * $numberOfCollection 0  for first one and 1 for second one
	 */
	public function getCollectionDueDaysAtIndex(int $index,int $numberOfCollection)
	{
		return $this->collection_policy_value[$index]['due_in_days'][$numberOfCollection] ?? 0;
	}
	public function getDueDayWithRates(int $index):array 
	{
		$dueDayAndRatesAtIndex = $this->collection_policy_value[$index] ;
		return  [
			0 => $dueDayAndRatesAtIndex['cash_payment']??0 , 
			$dueDayAndRatesAtIndex['due_in_days'][0] => $dueDayAndRatesAtIndex['rate'][0],
			$dueDayAndRatesAtIndex['due_in_days'][1] => $dueDayAndRatesAtIndex['rate'][1],
		];
	}
	
	public function calculateMultiYearsCollectionPolicy($monthlySalesTargetValueBeforeVat)
	{
		  $withholdRate = $this->getWithholdTaxRate() / 100;
        $vatRate  = $this->getVatRate() / 100;
        $dateIndexWithDate = $this->project->getDateIndexWithDate();
        
        $withholdAmounts = HArr::MultiplyWithNumber($monthlySalesTargetValueBeforeVat, $withholdRate);
        $vatAmounts = HArr::MultiplyWithNumber($monthlySalesTargetValueBeforeVat, $vatRate);
        
        
        
        
        
        // for amount after vat
        $monthlySalesTargetValueAfterVat = HArr::sumAtDates([$monthlySalesTargetValueBeforeVat,$vatAmounts], array_keys($monthlySalesTargetValueBeforeVat));
        $salesActiveYearsIndexWithItsMonths=  $this->getSalesActiveYearsIndexWithItsMonths();
        $hasMultiYear = array_key_exists(1, $salesActiveYearsIndexWithItsMonths) ;
        $monthlySalesTargetValueAfterVatForFirstYearMonths = array_intersect_key($monthlySalesTargetValueAfterVat, array_flip(array_keys($salesActiveYearsIndexWithItsMonths[0])));
        $dueDayWithRates = $this->getDueDayWithRates(0);
        $amountAfterVat = [];
        $amountAfterVatForFirstYear = $this->calculateCollectionOrPaymentForMultiCustomizedAmounts($dueDayWithRates, $monthlySalesTargetValueAfterVatForFirstYearMonths);
        $amountAfterVat = $amountAfterVatForFirstYear;
        if ($hasMultiYear) {
            $secondYearStartMonthIndex = array_key_last(($salesActiveYearsIndexWithItsMonths[0])) + 1 ;
            $monthlySalesTargetValueAfterVatForMultiYearMonths = array_slice($monthlySalesTargetValueAfterVat, $secondYearStartMonthIndex, null, true);
            $dueDayWithRates = $this->getDueDayWithRates(1);
            $amountAfterVatForMultiYear = $this->calculateCollectionOrPaymentForMultiCustomizedAmounts($dueDayWithRates, $monthlySalesTargetValueAfterVatForMultiYearMonths);
            $amountAfterVat = HArr::sumAtDates([$amountAfterVatForFirstYear,$amountAfterVatForMultiYear], array_keys($monthlySalesTargetValueAfterVat));
        }
        
        $salesActiveYearsIndexWithItsMonths=  $this->getSalesActiveYearsIndexWithItsMonths();
        $withholdAmountsForFirstYearMonths = array_intersect_key($withholdAmounts, array_flip(array_keys($salesActiveYearsIndexWithItsMonths[0])));
        $dueDayWithRates = $this->getDueDayWithRates(0);
    
        $withholdAmountsForFirstYear = $this->calculateCollectionOrPaymentForMultiCustomizedAmounts($dueDayWithRates, $withholdAmountsForFirstYearMonths);
        $withholdPayments = $withholdAmountsForFirstYear;
        if ($hasMultiYear) {
            $secondYearStartMonthIndex = array_key_last(($salesActiveYearsIndexWithItsMonths[0])) + 1 ;
            $withholdAmountsForMultiYearMonths = array_slice($withholdAmounts, $secondYearStartMonthIndex, null, true);
            $dueDayWithRates = $this->getDueDayWithRates(1);
            $amountAfterVatForMultiYear = $this->calculateCollectionOrPaymentForMultiCustomizedAmounts($dueDayWithRates, $withholdAmountsForMultiYearMonths);
            $withholdPayments = HArr::sumAtDates([$withholdAmountsForFirstYear,$amountAfterVatForMultiYear], array_keys($withholdAmounts));
        }
        $netPaymentsAfterWithhold = HArr::subtractAtDates([$amountAfterVat,$withholdPayments], array_keys($monthlySalesTargetValueBeforeVat));
        $collectionStatement = self::calculateStatement($monthlySalesTargetValueBeforeVat, $vatAmounts, $netPaymentsAfterWithhold, $withholdPayments, $dateIndexWithDate);
		return $collectionStatement;

		
	}
}
