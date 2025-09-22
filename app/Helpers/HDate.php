<?php 
namespace App\Helpers;

use App\ReadyFunctions\Date;
use Carbon\Carbon;

class HDate 
{
	public static function formatDateFromDatePicker(?string $date):?string
	{
		$originDate = $date;
		if(!$date){
			return null ;
		}
		$date = explode('/',$date);
		if(isset($date[1])){
			return $date[2] .'-'.$date[1] . '-'.$date[0];
		}
		return $originDate ;
	}	
	public static function generateUniqueDateTimeForModel(string $fullClassName , string $columnName , string $dateTime , array $additionalConditions)
	{
		return self::searchForUnique($fullClassName  , $columnName , $dateTime , $additionalConditions);
	}
	public static function searchForUnique(string $fullClassName , string $columnName , string $dateTime , array $additionalConditions)
	{
		$query = $fullClassName::where($columnName,$dateTime);
		foreach($additionalConditions as $condition){
			$additionalColumnName = $condition[0];
			$operation = $condition[1];
			$value = $condition[2];
			$query->where($additionalColumnName,$operation,$value);
		}
		$isExist = $query->exists() ;
		if($isExist){
			$dateTime = Carbon::make($dateTime)->addSecond()->format('Y-m-d H:i:s');
			return self::searchForUnique($fullClassName  , $columnName , $dateTime , $additionalConditions);
		}
		return $dateTime;
	}
	public static function allDatesGreaterThanOrEqual(array $dates , ?string $checkDate = null)
	{
		if(is_null($checkDate)){
			return false ;
		}
		foreach($dates as $date){
			$currentDate = Carbon::make($date) ;
			if(is_null($currentDate)){
				return false ;
			}
			$lessThan = $currentDate->lessThan(Carbon::make($checkDate));
			if($lessThan){
				return false ;
			}
		}
		return true ;
	}
	public static function generateStartDateAndEndDateBetween(string $startDate , string $endDate):array{
		
		$result  = [];
		$dates = generateDatesBetweenTwoDates(Carbon::make($startDate),Carbon::make($endDate));
		$currentStartDate = null;
		foreach($dates as $startDate){
			$startDate = $currentStartDate ?: $startDate;
			$endDateOfCurrentMonth = Carbon::make($startDate)->endOfMonth()->format('Y-m-d');
			if(Carbon::make($endDateOfCurrentMonth)->greaterThan(Carbon::make($endDate))){
				$endDateOfCurrentMonth = $endDate ;
			}
			$result[] = ['start_date'=>$startDate,'end_date'=>$endDateOfCurrentMonth];
			$currentStartDate = Carbon::make($endDateOfCurrentMonth)->addDay()->format('Y-m-d');
		}
		return $result;
		
	}
	public static function generateEndOfMonthsDatesBetweenTwoDates(Carbon $startDate , Carbon $endDate)
	{
		$startDate = $startDate->endOfMonth();
		$endDate = $endDate->endOfMonth();
		$currentDate = $startDate;
		$intervalDates = [
			$currentDate->format('Y-m-d')
		];
		
		while ($endDate->greaterThan($currentDate)){
			$intervalDates[] = $currentDate->addMonthWithNoOverflow()->endOfMonth()->format('Y-m-d');
		}
		return $intervalDates;

	}
	public static function generateDatesBetweenStartDateAndDuration(int $currentStartDateAsIndex,string $startDate , int $duration,string $intervalName , bool $isAtEnd = true)
	{
		$dateService = new Date;
		
		$intervalValue = 12 ;
		switch($intervalName) {
			case 'monthly':
				$intervalValue = 1 ;
				break;
				case 'quartly':
					$intervalValue = 3;
					break;
					case 'semi annually':
						$intervalValue = 6 ;
						break;
		}
		$startDateDay =explode('-',$startDate)[2];
		
		$result=[];
		if($isAtEnd){
			for($i =0 ; $i <= ($duration/$intervalValue); $i++  ){
				$result[$currentStartDateAsIndex]=$dateService->addMonths($startDateDay,$startDate,$i*$intervalValue);
				$currentStartDateAsIndex+=$intervalValue;
			}
		}else{
			for($i =0 ; $i <= ($duration/$intervalValue); $i++  ){
				$result[$currentStartDateAsIndex]=$dateService->addMonths($startDateDay,$startDate,$i*$intervalValue);
				$currentStartDateAsIndex+=$intervalValue;
			}
		}
		return $result;
		
		
	}
	
	public static function getDateAfterIndex(array $datesAsIndexString , array $datesAsStringIndex  , string $date , int $numberOfShifts)
	{
		$index =$datesAsStringIndex[$date];
		$nextIndex = getNthKeyAfter($datesAsIndexString, $index, $numberOfShifts) ;
		return $datesAsIndexString[$nextIndex]??null;
	}
	public static function calculateDaysCountAtEnd(array $items,int $intervalValue,int $currentDaysCount = null):array{
		$currentDayCount = 0 ; 
		$dayCounts = [];
		$secondDate = null ;
		$loopIndex = 0 ;
		foreach($items as $currentDateIndex => $dateAsString){
			
			if($loopIndex == 0){
				$dayCounts[$currentDateIndex] = $currentDayCount ;
				$loopIndex++;
			}
			else{
				if(!is_null($currentDaysCount)){
					$dayCounts[$currentDateIndex] =$currentDaysCount;
					continue;   
				}
				$secondDate = $dateAsString  ;
				$firstDate = $items[$currentDateIndex-$intervalValue];
				$secondDateTime  = strtotime($secondDate.' 00:00:00');
				$firstDateTime  = strtotime($firstDate.' 00:00:00');
				$result = $secondDateTime-$firstDateTime;
				$result = round(($result) / (60 * 60 * 24));
				$dayCounts[$currentDateIndex] =   (int)$result;
			}
		}
		return $dayCounts;
		
	}
	public static function calculateDaysCountAtBeginning(array $items,int $currentDaysCount = null):array{
		$currentDayCount = 0 ; 
		$dayCounts = [];
		$secondDate = null ;
		$loopIndex = 0 ;
		foreach($items as $currentDateIndex => $dateAsString){
			
			
				if(!is_null($currentDaysCount)){
					$dayCounts[$currentDateIndex] =$currentDaysCount;
					continue;   
				}
				$secondDate = $items[$currentDateIndex+1]??null  ;
				
				$firstDate = $dateAsString ;
				if(!is_null($secondDate)){
					$secondDateTime  = strtotime($secondDate.' 00:00:00');
				$firstDateTime  = strtotime($firstDate.' 00:00:00');
				$result = $secondDateTime-$firstDateTime;
				$result = round(($result) / (60 * 60 * 24));
				$dayCounts[$currentDateIndex] =   (int)$result;
				}
				
		}
		return $dayCounts;
		
	}
	
	public static function convertDateIndexArrayToDateString(array $payload, array $dateIndexWithDate){
		$result = [];
		foreach($payload as $dateIndex => $value){
			$dateAsString = $dateIndexWithDate[$dateIndex];
			$result[$dateAsString] = $value;
		}
		return $result;
	}
	// public static function getWeekNumberAndYearFromDateString(string $date)
	// {
	// 	$date = Carbon::parse($date);
	// 	return $date->weekOfYear.'-'.$date->year;
	// }
}
