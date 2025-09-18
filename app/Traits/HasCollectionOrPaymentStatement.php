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
        }elseif($collectionPolicyType == 'system_default'){
			$collectionPolicyValue = $paymentTerm;
		}
        $dateValue = convertIndexKeysToString($dateValue, $datesAsIndexAndString);
        $collectionPolicyValue = is_array($collectionPolicyValue) ?  $this->formatDues($collectionPolicyValue) : $collectionPolicyValue;
        $result = (new CollectionPolicyService())->applyCollectionPolicy(true, $collectionPolicyType, $collectionPolicyValue, $dateValue) ;
        
        return convertStringKeysToIndexes($result, $datesAsIndexAndString);
    }
	//  private function calculateCollectionOrPaymentAmounts(string $paymentTerm, array $totalAfterVat, array $datesAsIndexAndString, array $customCollectionPolicy, $debug=false)
    // {
    //     $collectionPolicyType  = $paymentTerm == 'customize' ? 'customize':'system_default';
    //     $collectionPolicyValue = $collectionPolicyType ;
    //     $dateValue = $totalAfterVat;
    //     if ($collectionPolicyType == 'customize') {
	// 		$collectionPolicyValue = $customCollectionPolicy ;
    //     } elseif ($collectionPolicyType == 'system_default' && $paymentTerm=='cash') {
	// 		$collectionPolicyValue = 'monthly';
    //     }elseif($collectionPolicyType == 'system_default'){
	// 		$collectionPolicyValue = $paymentTerm;
	// 	}
    //     $dateValue = convertIndexKeysToString($dateValue, $datesAsIndexAndString);
    //     $collectionPolicyValue = is_array($collectionPolicyValue) ?  $this->formatDues($collectionPolicyValue) : $collectionPolicyValue;
    //     $result = (new CollectionPolicyService())->applyCollectionPolicy(true, $collectionPolicyType, $collectionPolicyValue, $dateValue) ;
    //     return convertStringKeysToIndexes($result, $datesAsIndexAndString);
    // }
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
	
	
	
	 public static function calculateSettlementStatement(array $dates,array $settlements ,array $additions = [] , float $initialBeginningBalance = 0 , array $dateIndexWithDate , bool $notUpdateBeginning =false , $debug = false )
    {
		$financialYearStartMonth = 'january';
        $withholdForIntervals = [
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
            foreach ($dates as $dateIndex) {
		
				$settlementAtDate = $settlementsForInterval[$intervalName][$dateIndex]??0;
                $result[$intervalName]['beginning_balance'][$dateIndex] = $beginningBalance;
				$addition = $withholdForIntervals[$intervalName][$dateIndex]??0;
                $totalDue[$dateIndex] =  $addition+$beginningBalance;
                $endBalance[$dateIndex] = $totalDue[$dateIndex] - $settlementAtDate   ;
                $beginningBalance = $notUpdateBeginning ? $beginningBalance :  $endBalance[$dateIndex] ;
                $result[$intervalName]['addition'][$dateIndex] =  $addition ;
                $result[$intervalName]['total_due'][$dateIndex] = $totalDue[$dateIndex];
                $result[$intervalName]['payment'][$dateIndex] = $settlementAtDate;
                $result[$intervalName]['end_balance'][$dateIndex] =$endBalance[$dateIndex];
            }
        }
	
        return $result;
    
        
    }

	
	
	public static function calculateWithholdStatement(array $withholds = [] , float $initialBeginningBalance = 0 , array $dateIndexWithDate)
    {
		$financialYearStartMonth = 'january';
        $withholdForIntervals = [
            'monthly'=>$withholds,
            'quarterly'=>sumIntervalsIndexes($withholds, 'quarterly', $financialYearStartMonth, $dateIndexWithDate),
            'semi-annually'=>sumIntervalsIndexes($withholds, 'semi-annually', $financialYearStartMonth, $dateIndexWithDate),
            'annually'=>sumIntervalsIndexes($withholds, 'annually', $financialYearStartMonth, $dateIndexWithDate),
        ];
       
     
        $result = [];
        foreach (getIntervalFormatted() as $intervalName=>$intervalNameFormatted) {
            $beginningBalance = $initialBeginningBalance;
            foreach ($withholdForIntervals[$intervalName] as $dateIndex=>$withhold) {
				$monthNumber = explode('-',$dateIndexWithDate[$dateIndex])[01];
                $dateIndex;
                $result[$intervalName]['beginning_balance'][$dateIndex] = $beginningBalance;
                $totalDue[$dateIndex] =  $withhold+$beginningBalance;
				$settlementAtDate = 0 ;
				if($monthNumber == 01 || $monthNumber == 04 || $monthNumber == 07 || $monthNumber == 10){
						$settlementAtDate = $result[$intervalName]['total_due'][$dateIndex-1]??0 ;
				}
                $endBalance[$dateIndex] = $totalDue[$dateIndex] - $settlementAtDate   ;
                $beginningBalance = $endBalance[$dateIndex] ;
                $result[$intervalName]['withhold'][$dateIndex] =  $withhold ;
                $result[$intervalName]['total_due'][$dateIndex] = $totalDue[$dateIndex];
                $result[$intervalName]['payment'][$dateIndex] = $settlementAtDate;
                $result[$intervalName]['end_balance'][$dateIndex] =$endBalance[$dateIndex];
            }
        }
        return $result;
    
        
    }
	
	 public static function calculateVatStatement(array $additions  , float $initialBeginningBalance = 0 , array $dateIndexWithDate)
    {
		$financialYearStartMonth = 'january';
        $additionsForIntervals = [
            'monthly'=>$additions,
            'quarterly'=>sumIntervalsIndexes($additions, 'quarterly', $financialYearStartMonth, $dateIndexWithDate),
            'semi-annually'=>sumIntervalsIndexes($additions, 'semi-annually', $financialYearStartMonth, $dateIndexWithDate),
            'annually'=>sumIntervalsIndexes($additions, 'annually', $financialYearStartMonth, $dateIndexWithDate),
        ];
        $result = [];
        foreach (getIntervalFormatted() as $intervalName=>$intervalNameFormatted) {
            $beginningBalance = $initialBeginningBalance;
			$settlements = [];
			$isFirstMonth = true ;
			$previousAddition = 0 ;
			$endBalance = [];
            foreach ($additionsForIntervals[$intervalName] as $dateIndex=>$additionAtDate) {
                $dateIndex;
                $result[$intervalName]['beginning_balance'][$dateIndex] = $beginningBalance;
			//	$addition = $withholdForIntervals[$intervalName][$dateIndex]??0;
			$totalDue[$dateIndex] =  $additionAtDate+$beginningBalance;
				$settlementAtDate = $settlements[$dateIndex]??0;
				 $endBalance[$dateIndex] = $totalDue[$dateIndex] - $settlementAtDate   ;
				$previousEndBalance = $endBalance[$dateIndex] ?? 0 ;
				$settlements[$dateIndex+1] = $totalDue[$dateIndex] <= 0 ? 0 : $previousEndBalance;
				if($initialBeginningBalance > 0 && $isFirstMonth){
					$settlements[$dateIndex] = $initialBeginningBalance;
				}
       //         $settlementAtDate = $settlementsForInterval[$intervalName][$dateIndex]??0 ;
               
                $beginningBalance = $endBalance[$dateIndex] ;
                $result[$intervalName]['addition'][$dateIndex] =  $additionAtDate ;
                $result[$intervalName]['total_due'][$dateIndex] = $totalDue[$dateIndex];
                $result[$intervalName]['payment'][$dateIndex] = $settlementAtDate;
                $result[$intervalName]['end_balance'][$dateIndex] =$endBalance[$dateIndex];
				$isFirstMonth=false;
            }
        }
        return $result;
    
        
    }
	
	 public static function calculateCorporateTaxesStatement(array $additions  ,array $calculatedCorporateTaxesPerYear , float $initialBeginningBalance  , array $dateIndexWithDate , string $studyStartDateAsMonthNumber)
    {
	
		$financialYearStartMonth = 'january';
        $additionsForIntervals = [
            'monthly'=>$additions,
            'quarterly'=>sumIntervalsIndexes($additions, 'quarterly', $financialYearStartMonth, $dateIndexWithDate),
            'semi-annually'=>sumIntervalsIndexes($additions, 'semi-annually', $financialYearStartMonth, $dateIndexWithDate),
            'annually'=>sumIntervalsIndexes($additions, 'annually', $financialYearStartMonth, $dateIndexWithDate),
        ];
		$corporateTaxesForIntervals = [
            'monthly'=>$calculatedCorporateTaxesPerYear,
            'quarterly'=>sumIntervalsIndexes($calculatedCorporateTaxesPerYear, 'quarterly', $financialYearStartMonth, $dateIndexWithDate),
            'semi-annually'=>sumIntervalsIndexes($calculatedCorporateTaxesPerYear, 'semi-annually', $financialYearStartMonth, $dateIndexWithDate),
            'annually'=>sumIntervalsIndexes($calculatedCorporateTaxesPerYear, 'annually', $financialYearStartMonth, $dateIndexWithDate),
        ];
        $result = [];
		$lastMonthsInYearKeys = array_keys($calculatedCorporateTaxesPerYear);
        foreach (getIntervalFormatted() as $intervalName=>$intervalNameFormatted) {
            $beginningBalance = $initialBeginningBalance;
			$settlements = [];
			$isFirstLoop = true ; 
			$isStudyDateIsJan = $studyStartDateAsMonthNumber == '01';
            foreach ($additionsForIntervals[$intervalName] as $dateIndex=>$additionAtDate) {
                $dateIndex;
                $result[$intervalName]['beginning_balance'][$dateIndex] = $beginningBalance;
				$corporateTaxesAtDate = $corporateTaxesForIntervals[$intervalName][$dateIndex]??0;
				$isLastMonthInYear = in_array($dateIndex,$lastMonthsInYearKeys);
				
                $totalDue[$dateIndex] =  $beginningBalance-$additionAtDate + $corporateTaxesAtDate;
			//	$settlements[$dateIndex] = 0 ;
				if($isStudyDateIsJan && $isFirstLoop){
					$settlements[$dateIndex+4] = $initialBeginningBalance;
				}
				if($isLastMonthInYear){
					if($totalDue[$dateIndex] <0 ){
						$settlements[$dateIndex+4]=0;
					}else{
						$settlements[$dateIndex+4]= $totalDue[$dateIndex];
					}
				}
				$settlementAtDate = $settlements[$dateIndex]??0;
                $endBalance[$dateIndex] = $totalDue[$dateIndex] - $settlementAtDate   ;
                $beginningBalance = $endBalance[$dateIndex] ;
                $result[$intervalName]['addition'][$dateIndex] =  $additionAtDate ;
                $result[$intervalName]['total_due'][$dateIndex] = $totalDue[$dateIndex];
                $result[$intervalName]['payment'][$dateIndex] = $settlementAtDate;
                $result[$intervalName]['end_balance'][$dateIndex] =$endBalance[$dateIndex];
				$isFirstLoop=false ;
            }
        }
		
        return $result;
    
        
    }
	
	
	
}
