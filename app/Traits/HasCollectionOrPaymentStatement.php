<?php 
namespace App\Traits;

use App\ReadyFunctions\CollectionPolicyService;

trait HasCollectionOrPaymentStatement {
	
	 public function calculateCollectionOrPaymentAmounts(string $paymentTerm, array $totalAfterVat, array $datesAsIndexAndString, array $customCollectionPolicy, $debug=false)
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
	 public function calculateCollectionOrPaymentForMultiCustomizedAmounts(array $dueDayWithRates, array $dateAsIndexAndValue)
    {
//        $dateAsStringAndValue = convertIndexKeysToString($dateAsIndexAndValue, $datesAsIndexAndString);
        return  (new CollectionPolicyService())->applyMultiCustomizedCollectionPolicy($dueDayWithRates, $dateAsIndexAndValue) ;
       // return convertStringKeysToIndexes($result, $datesAsIndexAndString);
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
	
	 public static function calculateStatement(array $expenses, array $vatAmounts, array $netPaymentsAfterWithhold, array $withholdPayments, array $dateIndexWithDate, float $beginningBalance = 0)
    {
		$financialYearStartMonth = 'january';
        $expensesForIntervals = [
            'monthly'=>$expenses,
            'quarterly'=>sumIntervalsIndexes($expenses, 'quarterly', $financialYearStartMonth, $dateIndexWithDate),
            'semi-annually'=>sumIntervalsIndexes($expenses, 'semi-annually', $financialYearStartMonth, $dateIndexWithDate),
            'annually'=>sumIntervalsIndexes($expenses, 'annually', $financialYearStartMonth, $dateIndexWithDate),
        ];
        $netPaymentAfterWithholdForInterval = [
            'monthly'=>$netPaymentsAfterWithhold,
            'quarterly'=>sumIntervalsIndexes($netPaymentsAfterWithhold, 'quarterly', $financialYearStartMonth, $dateIndexWithDate),
            'semi-annually'=>sumIntervalsIndexes($netPaymentsAfterWithhold, 'semi-annually', $financialYearStartMonth, $dateIndexWithDate),
            'annually'=>sumIntervalsIndexes($netPaymentsAfterWithhold, 'annually', $financialYearStartMonth, $dateIndexWithDate),
        ];
        
        $result = [];
        foreach (getIntervalFormatted() as $intervalName=>$intervalNameFormatted) {
            $beginningBalance = 0;
            foreach ($expensesForIntervals[$intervalName] as $dateIndex=>$currentExpenseValue) {
                $date = $dateIndex;
                $result[$intervalName]['beginning_balance'][$date] = $beginningBalance;
                $currentVat = $vatAmounts[$date]??0 ;
                $totalDue[$date] =  $currentExpenseValue+$currentVat+$beginningBalance;
                $paymentAtDate = $netPaymentAfterWithholdForInterval[$intervalName][$date]??0 ;
                $withholdPaymentAtDate = $withholdPayments[$date]?? 0 ;
                $endBalance[$date] = $totalDue[$date] - $paymentAtDate  - $withholdPaymentAtDate ;
                $beginningBalance = $endBalance[$date] ;
                $result[$intervalName]['expense'][$date] =  $currentExpenseValue ;
                $result[$intervalName]['vat'][$date] =  $currentVat ;
                $result[$intervalName]['total_due'][$date] = $totalDue[$date];
                $result[$intervalName]['payment'][$date] = $paymentAtDate;
                $result[$intervalName]['withhold_amount'][$date] = $withholdPaymentAtDate;
                $result[$intervalName]['end_balance'][$date] =$endBalance[$date];
            }
        }
        return $result;
    
        
    }
	
	
	
	 public static function calculateSettlementStatement(array $settlements ,array $additions = [] , float $initialBeginningBalance = 0 , array $dateIndexWithDate)
    {
		$financialYearStartMonth = 'january';
        $additionForIntervals = [
            'monthly'=>$additions,
            'quarterly'=>sumIntervalsIndexes($additions, 'quarterly', $financialYearStartMonth, $dateIndexWithDate),
            'semi-annually'=>sumIntervalsIndexes($additions, 'semi-annually', $financialYearStartMonth, $dateIndexWithDate),
            'annually'=>sumIntervalsIndexes($additions, 'annually', $financialYearStartMonth, $dateIndexWithDate),
        ];
        $settlementsForInterval = [
            'monthly'=>$settlements,
            'quarterly'=>sumIntervalsIndexes($settlements, 'quarterly', $financialYearStartMonth, $dateIndexWithDate),
            'semi-annually'=>sumIntervalsIndexes($settlements, 'semi-annually', $financialYearStartMonth, $dateIndexWithDate),
            'annually'=>sumIntervalsIndexes($settlements, 'annually', $financialYearStartMonth, $dateIndexWithDate),
        ];
     
        $result = [];
        foreach (getIntervalFormatted() as $intervalName=>$intervalNameFormatted) {
            $beginningBalance = $initialBeginningBalance;
            foreach ($settlementsForInterval[$intervalName] as $dateIndex=>$settlementAtDate) {
                $dateIndex;
                $result[$intervalName]['beginning_balance'][$dateIndex] = $beginningBalance;
				$addition = $additionForIntervals[$intervalName][$dateIndex]??0;
                $totalDue[$dateIndex] =  $addition+$beginningBalance;
       //         $settlementAtDate = $settlementsForInterval[$intervalName][$dateIndex]??0 ;
                $endBalance[$dateIndex] = $totalDue[$dateIndex] - $settlementAtDate   ;
                $beginningBalance = $endBalance[$dateIndex] ;
                $result[$intervalName]['addition'][$dateIndex] =  $addition ;
                $result[$intervalName]['total_due'][$dateIndex] = $totalDue[$dateIndex];
                $result[$intervalName]['payment'][$dateIndex] = $settlementAtDate;
                $result[$intervalName]['end_balance'][$dateIndex] =$endBalance[$dateIndex];
            }
        }
	
        return $result;
    
        
    }
	
	
}
