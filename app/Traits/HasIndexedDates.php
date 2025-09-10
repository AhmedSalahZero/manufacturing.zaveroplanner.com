<?php
namespace App\Traits;

use App\ReadyFunctions\CalculateDurationService;
use Carbon\Carbon;

trait HasIndexedDates
{
    
    public function updateStudyAndOperationDates(array $datesAsStringAndIndex, array $datesIndexWithYearIndex, array $yearIndexWithYear, array $dateIndexWithDate, array $dateWithMonthNumber)
    {
        
        $studyDurationDates = $this->getStudyDurationPerMonth($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber, false);
        $extendedStudyDurationDates = $this->getExtendedStudyDurationPerMonth($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber, false);
        $operationDurationDates = $this->getOperationDurationPerMonth($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber, false);
        $operationDurationDates = $this->editOperationDatesStartingIndex($operationDurationDates, $studyDurationDates);
        $this->update([
            'study_dates'=>$studyDurationDates,
            'operation_dates'=>$operationDurationDates,
            'extended_study_dates'=>$extendedStudyDurationDates
        ]);
    }
    protected function editOperationDatesStartingIndex($operationDurationDates, $studyDurationDates)
    {
        $firstIndexInOperationDates = $operationDurationDates[0] ?? null;
        if (!$firstIndexInOperationDates) {
            return [];
        }
        $newDates = [];
        $firstIndex = array_search($firstIndexInOperationDates, $studyDurationDates);
        $loop = 0 ;
        foreach ($operationDurationDates as $oldIndex=>$value) {
            if ($loop == 0) {
                $newDates[$firstIndex] = $value;
            } else {
                $newDates[]=$value ;
            }
            $loop++;
        }
        return $newDates ;
    }
    
    public function getStudyDurationPerMonth(array $datesAsStringAndIndex, array $datesIndexWithYearIndex, array $yearIndexWithYear, array $dateIndexWithDate, array $dateWithMonthNumber, $maxYearIsStudyEndDate = true, $repeatIndexes = true)
    {
        $studyDurationPerMonth = [];
        $studyDurationPerYear = $this->getStudyDurationPerYear($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber, false, $maxYearIsStudyEndDate, $repeatIndexes);
        foreach ($studyDurationPerYear as $year => $values) {
            foreach ($values as $date => $value) {
                $studyDurationPerMonth[$date] = $value;
            }
        }

        return array_keys($studyDurationPerMonth);
    }
	public function getExtendedStudyDurationPerYears(){
		
		$datesAndIndexesHelpers = $this->datesAndIndexesHelpers();
			$datesIndexWithYearIndex=$datesAndIndexesHelpers['datesIndexWithYearIndex']; 
			$yearIndexWithYear=$datesAndIndexesHelpers['yearIndexWithYear']; 
			$dateIndexWithDate=$datesAndIndexesHelpers['dateIndexWithDate']; 
			$dateWithMonthNumber=$datesAndIndexesHelpers['dateWithMonthNumber']; 
				$datesAsStringAndIndex = $this->getDatesAsStringAndIndex();
				
		 $studyDurationPerMonth = [];
        $studyDurationPerYear = $this->getExtendedStudyDurationPerYear($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber, false, true, true);
        foreach ($studyDurationPerYear as $year => $values) {
            foreach ($values as $date => $value) {
                $studyDurationPerMonth[$date] = $value;
            }
        }
        return array_keys($studyDurationPerMonth);
		
	}
    public function getExtendedStudyDurationPerMonth(array $datesAsStringAndIndex, array $datesIndexWithYearIndex, array $yearIndexWithYear, array $dateIndexWithDate, array $dateWithMonthNumber, $maxYearIsStudyEndDate = true, $repeatIndexes = true)
    {
        $studyDurationPerMonth = [];
        $studyDurationPerYear = $this->getExtendedStudyDurationPerYear($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber, false, $maxYearIsStudyEndDate, $repeatIndexes);
        foreach ($studyDurationPerYear as $year => $values) {
            foreach ($values as $date => $value) {
                $studyDurationPerMonth[$date] = $value;
            }
        }
        return array_keys($studyDurationPerMonth);
    }
    public function getDurationInYears()
    {
        return $this->duration;
    }
    public function getOperationStartDate(): ?string
    {
        $startDate=$this->operation_start_date;

        return $startDate;
    }
    
    public function getOperationStartDateAsIndex(array $datesAsStringAndIndex, ?string $operationStartDateFormatted): ?int
    {
        return  $operationStartDateFormatted ? $datesAsStringAndIndex[$operationStartDateFormatted] : null;
    }
    
    
    public function getStudyDurationPerYear(array $datesAsStringAndIndex, array $datesIndexWithYearIndex, array $yearIndexWithYear, array $dateIndexWithDate, array $dateWithMonthNumber, $asIndexes = true, $maxYearIsStudyEndDate = true, $repeatIndexes = true)
    {
        $calculateDurationService = new CalculateDurationService();
        $studyStartDate  = $this->getStudyStartDate();
        $operationStartDate = $this->getOperationStartDate();
        if ($maxYearIsStudyEndDate) {
            $maxDate = $this->getStudyEndDate();
        } else {
            $maxDate = $this->getMaxDate($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber);
        }


        $studyDurationInYears = $this->getDurationInYears();

        $limitationDate = $operationStartDate;
        $studyDurationPerYear = $calculateDurationService->calculateMonthsDurationPerYear($studyStartDate, $maxDate, $studyDurationInYears, $limitationDate, true);
        $studyDurationPerYear = $this->removeDatesBeforeDate($studyDurationPerYear, $studyStartDate);
        
        $dates = [];
        if ($asIndexes) {
            $dates =  $this->convertMonthAndYearsToIndexes($studyDurationPerYear, $datesAsStringAndIndex, $datesIndexWithYearIndex);
        } else {
            $dates =  $studyDurationPerYear;
        }
        if ($repeatIndexes) {
            return $this->addMoreIndexes($dates, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber, $asIndexes);
        } else {
            return $dates;
        }
    }
    
    public function getExtendedStudyDurationPerYear(array $datesAsStringAndIndex, array $datesIndexWithYearIndex, array $yearIndexWithYear, array $dateIndexWithDate, array $dateWithMonthNumber, $asIndexes = true, $maxYearIsStudyEndDate = true, $repeatIndexes = true)
    {
        $calculateDurationService = new CalculateDurationService();
        $studyStartDate  = $this->getStudyStartDate();
        $operationStartDate = $this->getOperationStartDate();
        $maxDate = $this->getStudyEndDate();
        if ($maxYearIsStudyEndDate) {
        } else {
            // $maxDate = $this->getMaxDate($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber);
        }


        $studyDurationInYears = $this->getDurationInYears();

        $limitationDate = $operationStartDate;
        $studyDurationPerYear = $calculateDurationService->calculateMonthsDurationPerYear($studyStartDate, $maxDate, $studyDurationInYears, $limitationDate, true);
        $studyDurationPerYear = $this->removeDatesBeforeDate($studyDurationPerYear, $studyStartDate);
        
        $dates = [];
        if ($asIndexes) {
            $dates =  $this->convertMonthAndYearsToIndexes($studyDurationPerYear, $datesAsStringAndIndex, $datesIndexWithYearIndex);
        } else {
            $dates =  $studyDurationPerYear;
        }
        if ($repeatIndexes) {
            return $this->addMoreIndexes($dates, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber, $asIndexes);
        } else {
            return $dates;
        }
    }
    
    public function removeDatesBeforeDate(array $items, string $limitDate)
    {
        $newItems = [];
        $limitDate = Carbon::make($limitDate);
        foreach ($items as $year=>$dateAndValues) {
            foreach ($dateAndValues as $date=>$value) {
                $currentDate = Carbon::make($date);
                if ($limitDate->lessThanOrEqualTo($currentDate)) {
                    $newItems[$year][$date]=$value;
                }
            }
        }

        return $newItems;
    }
    
    protected function addMoreIndexes(array $yearAndDatesValues, array $yearIndexWithYear, array $dateIndexWithDate, array $dateWithMonthNumber, bool $asIndexes):array
    {
        $maxYearsCount = MAX_YEARS_COUNT;
        $lastYear = array_key_last($yearAndDatesValues);
        $firstYear = array_key_first($yearAndDatesValues);
        $maxYear = $firstYear  + $maxYearsCount;
        $firstYearAfterLast = $lastYear+1;
        for ($firstYearAfterLast; $firstYearAfterLast < $maxYear; $firstYearAfterLast++) {
            $dates = $this->replaceIndexWithItsStringDate($yearAndDatesValues[$lastYear], $dateIndexWithDate);
            if ($asIndexes) {
                $yearAndDatesValues[$firstYearAfterLast] = $this->replaceYearWithAnotherYear($dates, $yearIndexWithYear[$firstYearAfterLast], $asIndexes, $dateIndexWithDate, $dateWithMonthNumber);
            } else {
                $yearAndDatesValues[$firstYearAfterLast] = $this->replaceYearWithAnotherYear($dates, $firstYearAfterLast, $asIndexes, $dateIndexWithDate, $dateWithMonthNumber);
            }
        }
        return $yearAndDatesValues;
    }
    public function getOperationDurationPerMonth(array $datesAsStringAndIndex, array $datesIndexWithYearIndex, array $yearIndexWithYear, array $dateIndexWithDate, array $dateWithMonthNumber, $maxYearIsStudyEndDate  = true)
    {
        $operationDurationPerMonth = [];
        $operationDurationPerYear = $this->getOperationDurationPerYear($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber, false, $maxYearIsStudyEndDate);
        foreach ($operationDurationPerYear as $key => $values) {
            foreach ($values as $k => $v) {
                if ($v) {
                    $operationDurationPerMonth[$k] = $v;
                }
            }
        }

        return array_keys($operationDurationPerMonth);
    }
    public function getOperationDurationPerYearFromIndexes()
    {
        $datesAsStringAndIndex = $this->getDatesAsStringAndIndex();
        $datesIndexWithYearIndex = App('datesIndexWithYearIndex');
        $yearIndexWithYear = App('yearIndexWithYear');
        $dateIndexWithDate = App('dateIndexWithDate');
        $dateWithMonthNumber = App('dateWithMonthNumber');
        return $this->getOperationDurationPerYear($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber);
    }
    public function getOperationDurationPerYear(array $datesAsStringAndIndex, array $datesIndexWithYearIndex, array $yearIndexWithYear, array $dateIndexWithDate, array $dateWithMonthNumber, $asIndexes = true, $maxYearIsStudyEndDate = true)
    {
        $calculateDurationService = new CalculateDurationService();
        $operationStartDate  = $this->getOperationStartDateFormatted();
        if ($maxYearIsStudyEndDate) {
            $maxDate = $this->getStudyEndDate();
        } else {
            $maxDate = $this->getMaxDate($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber);
        }
        $studyDurationInYears = $this->getDurationInYears();
        $operationDurationPerYear = $calculateDurationService->calculateMonthsDurationPerYear($operationStartDate, $maxDate, $studyDurationInYears, true);

        $operationDurationPerYear = $this->removeZeroValuesFromTwoDimArr($operationDurationPerYear);
        if ($asIndexes) {
            return $this->convertMonthAndYearsToIndexes($operationDurationPerYear, $datesAsStringAndIndex, $datesIndexWithYearIndex);
        }

        return $operationDurationPerYear;
    }
    protected function removeZeroValuesFromTwoDimArr(array $dates)
    {
        $result = [];
        foreach ($dates as $year => $dateAndValues) {
            foreach ($dateAndValues as $date=>$value) {
                if ($value) {
                    $result[$year][$date] = $value;
                }
            }
        }

        return $result;
    }
    protected function convertMonthAndYearsToIndexes(array $yearsAndItsDates, array $datesAsStringAndIndex, array $datesIndexWithYearIndex)
    {
        $result = [];
        foreach ($yearsAndItsDates as $yearNumber => $datesAndZeros) {
            foreach ($datesAndZeros as $date => $zeroOrOne) {
                if (isset($datesAsStringAndIndex[$date])) {
                    $dateIndex = $datesAsStringAndIndex[$date];
                    $yearIndex = $datesIndexWithYearIndex[$dateIndex]??null;
					if(!is_null($yearIndex)){
						$result[$yearIndex][$dateIndex] = $zeroOrOne;
					}
                }
            }
        }

        return $result;
    }
    
    public function datesAndIndexesHelpers()
    {
        $studyDates = $this->getExtendedStudyDates() ;
        $firstLoop = true ;
        $baseYear = null ;
        $datesIndexWithYearIndex = [];
        $yearIndexWithYear = [];
        $dateIndexWithDate = [];
        $dateIndexWithMonthNumber = [];
        $dateWithMonthNumber = [];
        $dateWithDateIndex = [];
        
        foreach ($studyDates as $dateIndex => $dateAsString) {
            $year = explode('-', $dateAsString)[0];
            $montNumber = explode('-', $dateAsString)[1];
            if ($firstLoop) {
                $baseYear = $year ;
                $firstLoop = false ;
            }
            $yearIndex = $year - $baseYear ;
            $datesIndexWithYearIndex[$dateIndex] =$yearIndex ;
            $yearIndexWithYear[$yearIndex] = $year ;
            $dateIndexWithDate[$dateIndex] = $dateAsString ;
            $dateIndexWithMonthNumber[$dateIndex] = $montNumber ;
            $dateWithMonthNumber[$dateAsString] = $montNumber ;
            $dateWithDateIndex[$dateAsString] =$dateIndex ;
            
        }
    
        return [
            'datesIndexWithYearIndex'=>$datesIndexWithYearIndex,
            'yearIndexWithYear'=>$yearIndexWithYear,
            'dateIndexWithDate'=>$dateIndexWithDate,
            'dateIndexWithMonthNumber'=>$dateIndexWithMonthNumber,
            'dateWithMonthNumber'=>$dateWithMonthNumber,
            'dateWithDateIndex'=>$dateWithDateIndex,
        ];
        return $datesIndexWithYearIndex ;
    }
    
    public function getViewStudyDates(): array
    {
        return  $this->study_dates ?: [];
    }
    public function getStudyDates(): array
    {
        return  $this->extended_study_dates ?: [];
    }
    public function getDatesAsStringAndIndex()
    {
        return array_flip($this->getStudyDates());
    }
    // public function getOperationStartMonth(): ?int
    // {
    // 	return $this->operation_start_month ?: 0;
    // }
    public function financialYearStartMonth(): ?string
    {
		return 'january';
		
        return $this->financial_year_start_month;
    }
    public function getOperationStartDateFormatted()
    {
        $operationStartDate = $this->getOperationStartDate();

        return  $operationStartDate ? Carbon::make($operationStartDate)->format('Y-m-d') : null;
    }
    public function getOperationStartDateFormattedForView()
    {
        $operationStartDate = $this->getOperationStartDate();

        return  $operationStartDate ? dateFormatting($operationStartDate, 'M\' Y') : null;
    }
    protected function getMaxDate(array $datesAsStringAndIndex, array $datesIndexWithYearIndex, array $yearIndexWithYear, array $dateIndexWithDate, array $dateWithMonthNumber)
    {
        $studyDurationPerMonth = $this->getStudyDurationPerMonth($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber);

        return $studyDurationPerMonth[array_key_last($studyDurationPerMonth)];
    }
    public function getStudyStartDate(): ?string
    {
        return $this->study_start_date;
    }

    public function getStudyStartDateFormattedForView(): string
    {
        $studyStartDate = $this->getStudyStartDate();

        return dateFormatting($studyStartDate, 'M\' Y');
    }
    public function getStudyEndDate(): ?string
    {
        return $this->extended_end_date;
    }
    public function getViewStudyEndDate(): ?string
    {
        return $this->study_end_date;
    }
    public function getEndDate(): ?string
    {
        return $this->getStudyEndDate();
    }
    public function getStudyEndDateAsIndex():int
    {
        return $this->getIndexDateFromString($this->getStudyEndDate());
    }
    public function getViewStudyEndDateAsIndex():int
    {
        return $this->getIndexDateFromString($this->getViewStudyEndDate());
    }
    public function getStudyEndYearAsIndex():int
    {
        return $this->getYearIndexFromDateIndex($this->getStudyEndDateAsIndex());
    }
    public function getViewStudyEndYearAsIndex():int
    {
        return $this->getYearIndexFromDateIndex($this->getStudyEndDateAsIndex());
    }
    public function getStudyEndDateFormatted()
    {
    
    }
    // public function getStudyEndDateFormattedForView(): string
    // {
    //     $studyEndDate = $this->getStudyEndDate();
    //     return dateFormatting($studyEndDate, 'M\' Y');
    // }
    
    protected function replaceYearWithAnotherYear(array $dateAndValues, $newYear, bool $asIndexes, array $dateIndexWithDate, array $dateWithMonthNumber)
    {
        $newDatesAndValues   = [];
        foreach ($dateAndValues as $date=>$value) {
            $dateAsIndex = null;
            if ($asIndexes) {
                $dateAsIndex = $date;
                $date = $dateIndexWithDate[$date];
            }
            $day = getDayFromDate($date);
            
            $monthNumber = $dateWithMonthNumber[$date] ?? getMonthFromDate($date);
            $fullDate =$newYear.'-' .$monthNumber . '-'  .$day  ;

            if ($asIndexes) {
                $newDatesAndValues[$dateAsIndex] = $value;
            } else {
                $newDatesAndValues[$fullDate] = $value;
            }
        }

        return $newDatesAndValues;
    }
    public function replaceIndexWithItsStringDate(array $dates, array $dateIndexWithDate):array
    {
        $stringFormattedDates = [];
        foreach ($dates as $dateIndex => $value) {
            if (is_numeric($dateIndex)) {
                // is index date like 25
                $stringFormattedDates[$dateIndexWithDate[$dateIndex]] =$value;
            } else {
                // is already date string like 10-10-2025
                $stringFormattedDates[$dateIndex] = $value;
            }
        }

        return $stringFormattedDates;
    }
    public function getStudyDurationPerYearFromIndexes()
    {
        $datesAsStringAndIndex = $this->getDatesAsStringAndIndex();
        $datesIndexWithYearIndex = App('datesIndexWithYearIndex');
        $yearIndexWithYear = App('yearIndexWithYear');
        $dateIndexWithDate = App('dateIndexWithDate');
        $dateWithMonthNumber = App('dateWithMonthNumber');
        return $this->getStudyDurationPerMonth($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber, false);
        
    }
    /**
     * * هنا مفرودة لغايه السنوات الاضافيه
     */
    public function getStudyDurationPerYearFromIndexesForView()
    {
        $datesAsStringAndIndex = $this->getDatesAsStringAndIndex();
        $datesIndexWithYearIndex = App('datesIndexWithYearIndex');
        $yearIndexWithYear = App('yearIndexWithYear');
        $dateIndexWithDate = App('dateIndexWithDate');
        $dateWithMonthNumber = App('dateWithMonthNumber');
        return $this->getStudyDurationPerMonth($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber, true, false);
        
    }
    public function getDatesIndexesHelper()
    {
        return $this->datesAndIndexesHelpers();
    }
    
    
    public function getDateIndexWithDate():array
    {
        $datesAndIndexesHelpers = $this->getDatesIndexesHelper();
        return $datesAndIndexesHelpers['dateIndexWithDate'];
        ;
    }
    public function getDateWithDateIndex():array
    {
        $datesAndIndexesHelpers = $this->getDatesIndexesHelper();
        return $datesAndIndexesHelpers['dateWithDateIndex'];
    }
    public function getIndexDateFromString(string $dateAsString):int
    {
        $dateWithDateIndex = $this->getDateWithDateIndex();
        return $dateWithDateIndex[$dateAsString];
    }
    public function getDateFromDateIndex(int $dateAsIndex):string
    {
        $dateIndexWithDate = $this->getDateIndexWithDate();
        return $dateIndexWithDate[$dateAsIndex];
    }
    public function getYearIndexFromDateIndex(int $dateAsIndex)
    {
        $datesAndIndexesHelpers = $this->getDatesIndexesHelper();
        $datesIndexWithYearIndex = $datesAndIndexesHelpers['datesIndexWithYearIndex'];
        return $datesIndexWithYearIndex[$dateAsIndex];
    }
    public function getYearFromYearIndex(int $yearAsIndex):?int
    {
        $datesAndIndexesHelpers = $this->getDatesIndexesHelper();
        $yearIndexWithYear = $datesAndIndexesHelpers['yearIndexWithYear'];
        return $yearIndexWithYear[$yearAsIndex]??null;
    }
    public function getYearFromDateIndex(int $dateAsIndex):int
    {
        $yearIndex = $this->getYearIndexFromDateIndex($dateAsIndex);
        $datesAndIndexesHelpers = $this->getDatesIndexesHelper();
        $yearIndexWithYear = $datesAndIndexesHelpers['yearIndexWithYear'];
        return $yearIndexWithYear[$yearIndex];
    }
    public function getYearIndexWithYear():array
    {
        $datesAndIndexesHelpers = $this->getDatesIndexesHelper();
        return $datesAndIndexesHelpers['yearIndexWithYear'];
    }
    
    public function getDatesIndexWithYearIndex()
    {
        $datesAndIndexesHelpers = $this->getDatesIndexesHelper();
        return $datesAndIndexesHelpers['datesIndexWithYearIndex'];
    }
	 public function getDateWithMonthNumber()
    {
        $datesAndIndexesHelpers = $this->getDatesIndexesHelper();
        return $datesAndIndexesHelpers['dateWithMonthNumber'];
    }
    public function getYearIndexWithItsMonths():array
    {
        $dateIndexWithYearIndex = $this->getDatesIndexWithYearIndex();
        $result = [];
        foreach ($dateIndexWithYearIndex as $dateAsIndex => $yearAsIndex) {
            $result[$yearAsIndex][] = $this->getDateFromDateIndex($dateAsIndex);
        }
        return $result;
    }
    
    public function convertIndexYearsAndMonthsToString(array $items, array $yearIndexWithYear, array $dateIndexWithDate)
    {
        $result = [];
        foreach ($items as $yearAsIndex=>$dateAsIndexAndValue) {
    
            foreach ($dateAsIndexAndValue as $dateAsIndex=>$value) {
                if (is_numeric($yearAsIndex) && is_numeric($dateAsIndex)) {
                    $yearAsString = $yearIndexWithYear[$yearAsIndex];
                    $dateAsString = $dateIndexWithDate[$dateAsIndex];
                    $result[$yearAsString][$dateAsString] = $value;
                } else {
                    throw new \Exception('Custom Exception .. Year Passed [ ' . $yearAsIndex . ' ] And Full Date [ ' . $dateAsIndex . ' ] And Both Must Be Numeric Values');
                }
            }
        }

        return $result;
    }
    
    public function getOperationDurationPerYearFromIndexesForAllStudyInfo()
    {
        $datesAsStringAndIndex = $this->getDatesAsStringAndIndex();
        $datesIndexWithYearIndex = App('datesIndexWithYearIndex');
        $yearIndexWithYear = App('yearIndexWithYear');
        $dateIndexWithDate = App('dateIndexWithDate');
        $dateWithMonthNumber = App('dateWithMonthNumber');
        return $this->getOperationDurationPerYear($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber, true, false);
        
    }
	/**
	 * * extended
	 */
	 public function getOperationDatesAsDateAndDateAsIndex()
    {
        
		$operationsYearAndItsMonths = $this->getOperationDurationPerYearFromIndexesForAllStudyInfo();
		$result =[];
		foreach($operationsYearAndItsMonths as $yearAsIndex => $itsMonths){
			foreach($itsMonths as $dateAsIndex => $val){
				$result[$this->getDateFromDateIndex($dateAsIndex)] =$dateAsIndex ;
			}
		}
        return $result;
        
    }
	/**
	 *  to study end date
	 */
	public function getOperationDatesAsDateAndDateAsIndexToStudyEndDate()
    {
        
		$operationsYearAndItsMonths = $this->getOperationDurationPerYearFromIndexesForAllStudyInfo();
		array_pop($operationsYearAndItsMonths);
		$result =[];
		foreach($operationsYearAndItsMonths as $yearAsIndex => $itsMonths){
			foreach($itsMonths as $dateAsIndex => $val){
				$result[$this->getDateFromDateIndex($dateAsIndex)] =$dateAsIndex ;
			}
		}
        return $result;
        
    }
    public function getMonthsWithItsYear(array $yearWithItsIndexes):array
    {
        $result = [];
        
        foreach ($yearWithItsIndexes as $yearIndex => $months) {
            foreach ($months as $monthIndex=>$isActive) {
                if ($isActive) {
                    $result[$monthIndex] = $yearIndex;
                }
                
            }
        }
        return $result;
    }
    public function convertStringIndexesToDateIndex(array $itemsAsDateStringAndValue):array
    {
        $result = [];
        foreach ($itemsAsDateStringAndValue as $dateAsString => $value) {
            $dateAsIndex = $this->getIndexDateFromString($dateAsString);
            if (!is_null($dateAsIndex)) {
                $result[$dateAsIndex] = $value ;
            }
        }
        return $result;
    }
    public function getYearIndexWithItsMonthsAsIndexAndString()
    {
        $result =[];
        foreach ($this->getOperationDurationPerYearFromIndexesForAllStudyInfo() as $yearIndex => $dateAsIndexAndIsActive) {
            foreach ($dateAsIndexAndIsActive as $dateAsIndex => $isActive) {
                if ($isActive) {
                    $dateAsString = $this->getDateFromDateIndex($dateAsIndex);
                    $result[$yearIndex][$dateAsIndex]=$dateAsString;
                }
            }
            
        }
		return $result;
    }
	public function getOnlyDatesOfActiveStudy(array $studyDurationPerYear,array $dateIndexWithDate)
	{
		$result = [];
		foreach ($studyDurationPerYear as $currentYear => $datesAndZerosOrOnes) {
			foreach ($datesAndZerosOrOnes as $dateIndex => $zeroOrOneAtDate) {
				if (is_numeric($dateIndex)) {
					$dateFormatted =$dateIndexWithDate[$dateIndex];
				} else {
					$dateFormatted = $dateIndex;
				}
				$result[$dateFormatted] = $dateIndex;
			}
		}

		return $result;
	}
	
	public function sumTwoArrayUntilIndex(array $first, array $second, int $limitDateAsIndex):array
	{
		$dates = array_values(array_unique(array_merge(array_keys($first), array_keys($second))));
		$result = [];
		foreach ($dates as $dateAsIndex) {
			if ($dateAsIndex<=$limitDateAsIndex) {
				$secondVal = $second[$dateAsIndex] ?? 0;
				$value = $first[$dateAsIndex] ?? 0;
				$result[$dateAsIndex] = $value  + $secondVal;
			} else {
				$result[$dateAsIndex] = 0;
			}
		}
		return $result;
	}
	
	public function getStudyDateFormatted(array $datesAsStringAndIndex,array $datesIndexWithYearIndex,array $yearIndexWithYear,array $dateIndexWithDate)
	{
		$dateWithMonthNumber=App('dateWithMonthNumber');
		$studyDurationPerYear = $this->getStudyDurationPerYear($datesAsStringAndIndex,$datesIndexWithYearIndex,$yearIndexWithYear,$dateIndexWithDate,$dateWithMonthNumber, true, true, false);
	
		return  $this->getOnlyDatesOfActiveStudy($studyDurationPerYear,$dateIndexWithDate);
	}
	public function convertDateStringToDateIndex(string $dateAsString):int
	{
		return app('dateWithDateIndex')[$dateAsString];		
	}
		
	public function getFinancialYearsEndMonths():array
    {
        $studyStartDateMonth = $this->getStudyStartDate();
        $studyStartDateMonth = explode('-', $studyStartDateMonth)[1];
        $financialEndMonth = $this->getFinancialYearEndMonthNumber();
        $firstYearEndMonth  = $financialEndMonth - $studyStartDateMonth ;
        if ($firstYearEndMonth<0) {
            $firstYearEndMonth  = $firstYearEndMonth+12 ;
        }
        $result = [];
        for ($i = 0 ; $i<11 ; $i++) {
            $result[] = $firstYearEndMonth   ;
            $firstYearEndMonth  = $firstYearEndMonth  + 12 ;
        }
        return $result;
    }
	   public function getYearOrMonthIndexes():array 
    {
        // if ($this->isMonthlyStudy()) {
        //     return $this->getMonthlyIndexes();
        // }
        return $this->getYearlyIndexes();
    }
	public function getMonthlyIndexes()
	{
		$yearIndexWithItsActiveMonths = $this->getOperationDurationPerYearFromIndexes();
        $datesAndIndexesHelpers = $this->getDatesIndexesHelper();
		$dateIndexWithDate=$datesAndIndexesHelpers['dateIndexWithDate'];
		
		return $this->getActiveMonthlyDates($yearIndexWithItsActiveMonths,$dateIndexWithDate);
	}
		public function getYearlyIndexes():array 
	{
		        $yearIndexWithItsActiveMonths = $this->getOperationDurationPerYearFromIndexes();
		 $datesAndIndexesHelpers = $this->getDatesIndexesHelper();
		 $yearIndexWithYear=$datesAndIndexesHelpers['yearIndexWithYear'];
		  $results = [];
		  unset($yearIndexWithItsActiveMonths[array_key_last($yearIndexWithItsActiveMonths)]);
        foreach ($yearIndexWithItsActiveMonths as $yearIndex => $monthsForThisYearArray) {
            $results[$yearIndex] = 'Yr-'.$yearIndexWithYear[$yearIndex] ;
        }
        return $results;
		
	}
	public function getActiveMonthlyDates($yearIndexWithItsActiveMonths,$dateIndexWithDate)
	{
		$results = [];
            foreach ($yearIndexWithItsActiveMonths as $yearAsIndex => $monthsForThisYearArray) {
                foreach ($monthsForThisYearArray as $dateAsIndex => $isActive) {
                    $dateAsString = $dateIndexWithDate[$dateAsIndex] ;
                    $results[$dateAsIndex] = Carbon::parse($dateAsString)->format('M`Y');
                }
            }
            return $results;
			
	}
	public function replaceYearIndexWithYear($items,$years):array{
		$result = [];
		foreach($years as $yearAsIndex){
			$value = $items[$yearAsIndex]??0;
			$yearAsString = $this->getYearFromYearIndex($yearAsIndex);
			$result[$yearAsString] = $value ;
		}
		return $result;
	}
}
