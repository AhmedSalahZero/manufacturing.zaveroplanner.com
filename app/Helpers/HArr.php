<?php

namespace App\Helpers;

use Carbon\Carbon;
use Exception;

class HArr
{
	public static function encodeArr(array $items ):array 
	{
		$result = [];
		foreach($items as $key => $val){
			if(is_array($val)){
				$result[$key] = json_encode($val);
			}else{
				$result[$key] = $val ;
			}
		}
		return $result;
	}
	public static function sumAtDates(array $items, array $dates = [])
	{
		$itemsCount = count($items);
		if (!$itemsCount) {
			return [];
		}
		if (!isset($items[0])) {
			throw new Exception('Custom Exception .. First Parameter Must Be Indexes Array That Contains Arrays like [ [] , [] , [] ]');
		}

		$total = [];
		/**
		 * * في حاله لو ما حددتش انهي تواريخ يجمع عندها هناخد تاريخ اول اراي
		 * * ودا مناسب في حاله لو كل ال 
		 * * arries
		 * * ليها نفس التواريخ او الاندكسات
		 */
		$dates = count($dates) ? $dates : array_keys($items[0]);
		foreach ($dates as $date) {
			$currenTotal = 0;
			for ($i = 0; $i< $itemsCount; $i++) {
				$currenTotal+=$items[$i][$date]??0;
			}
			$total[$date] = $currenTotal;
		}

		return $total;
	}
	public static function sumTwoIntervalArrays(array $items1 , array $items2):array 
	{
		$result = [];
		foreach($items1 as $intervalName => $item1Details){
			foreach($item1Details as $keyName => $itemArr){
				foreach($itemArr as $dateAsIndex => $value){
					$item2Value = $items2[$intervalName][$keyName][$dateAsIndex]?? 0 ;
					$result[$intervalName][$keyName][$dateAsIndex] = $value + $item2Value ;
				}
			}
		}
		return $result;
	}
	public static function subtractAtDates(array $items, array $dates = [])
	{
		$itemsCount = count($items);
		if (!$itemsCount) {
			return [];
		}
		if (!isset($items[0])) {
			throw new Exception('Custom Exception .. First Parameter Must Be Indexes Array That Contains Arrays like [ [] , [] , [] ]');
		}
		$dates = count($dates) ? $dates : array_keys($items[0]);
		$total = [];
		foreach ($dates as $date) {
			$currenTotal = 0;
			for ($i = 0; $i< $itemsCount; $i++) {
				if ($i == 0) {
					$currenTotal += $items[$i][$date]??0;
				} else {
					$currenTotal -= $items[$i][$date]??0;
				}
			}
			$total[$date] = $currenTotal;
		}

		return $total;
	}
	public static function fillMissedKeysFromPreviousKeys(array $items, array $dates, $defaultValue = 0)
	{
		$previousValue = $defaultValue;
		$newItems = [];
		foreach ($dates as $date) {
			if (isset($items[$date])) {
				$previousValue = $items[$date];
				$newItems[$date] = $items[$date];
			} else {
				$newItems[$date] = $previousValue;
			}
		}

		return $newItems;
	}

	public static function accumulateArray(array $items)
	{
		$result =[];
		$finalResult =[];
		$index = 0;
		foreach ($items as $date=>$value) {
			$previousValue = $result[$index-1] ??0;
			$currentVal = $previousValue + $value;
			$result[$index] = $currentVal;
			$finalResult[$date] = $currentVal;
			$index++;
		}

		return $finalResult;
	}
	
	public static function MultiplyWithNumber(array $items , float $number):array
	{
		$newItems = [];
		foreach($items as $key=>$value){
			$newItems[$key]=$value * $number ;
		}
		return $newItems ;
	}
	public static function MultiplyWithNumberIfPositive(array $items , float $number)
	{
		$newItems = [];
		foreach($items as $key=>$value){
			if($value < 0){
				$newItems[$key]=0;
			}else{
				$newItems[$key]=$value * $number ;
			}
		}
		return $newItems ;
	}
	
	public static function sumWithNumber(array $items , float $number)
	{
		$newItems = [];
		foreach($items as $key=>$value){
			$newItems[$key]=$value + $number ;
		}
		return $newItems ;
	}
	public static function getIndexesBeforeDateOrNumericIndex(array $items, string $index, $indexIsDate = true)
	{
		$result = [];
		foreach ($items as $date => $value) {
			if ($indexIsDate ? Carbon::make($date)->lessThan(Carbon::make($index)) : $date < $index) {
				$result[$date]=$value;
			}
		}

		return $result;
	}
	public static  function sortBasedOnKey(array $arr, string $key):array 
	{
		usort($arr, function ($a, $b) use($key) {
			return strtotime($a[$key]) - strtotime($b[$key]); 
		});
		return $arr ;
	}
	public static  function sortBasedOnSumOfKey(array $arr, string $key):array 
	{
		usort($arr, function ($a, $b) use($key) {
			return strtotime($a[$key]) - strtotime($b[$key]); 
		});
		return $arr ;
	}
	public static function sortBySumOfKeyWithoutPreservingOriginalArray(array $items , string $sortBySumOfKeyName ):array // by reference
	{
		uasort($items, function ($a, $b) use ($sortBySumOfKeyName) {
			$sumA = isset($a[$sortBySumOfKeyName]) ? array_sum($a[$sortBySumOfKeyName]) : 0;
			$sumB = isset($b[$sortBySumOfKeyName]) ? array_sum($b[$sortBySumOfKeyName]) : 0;
			return $sumB <=> $sumA; // Descending order
		});
		return $items;
	}
	public static function sortTwoDimArrayAndPreserveKeyNameBasedOnKeyDesc(array $items , string $key)
	{
		
		uasort($items, function ($a, $b) use($key) {
			return $b[$key] <=> $a[$key]; // Descending order
		});
		return $items;		
	}
	public static function sortBySumOfKeyAndPreserveOriginalArray(array $items , string $sortBySumOfKeyName )
	{
		// $sortBySumOfKeyName = 'Avg. Prices'  for example  
		return collect($items)
    ->map(function ($item) use($sortBySumOfKeyName) {
        $sum = isset($item[$sortBySumOfKeyName]) ? array_sum($item[$sortBySumOfKeyName]) : 0;
        return ['sum' => $sum, 'data' => $item];
    })
    ->sortByDesc('sum')
    ->map(function ($item) {
        return $item['data'];
    })
    ->toArray();
	}
	public static function removeKeyFromArrayByValue(array $items , array $valuesToRemove){
		foreach($valuesToRemove as $valueToRemove){
			$found = array_search($valueToRemove , $items);
			if($found !== false){
				unset($items[$found]);
			}
		}
		return array_values($items) ; 
	}
	public static function removeNullValues(array $items){
		$result = [];
		foreach($items as $key => $val){
			if(!trim($val)){
				continue ;
			}
			$result[$key] = $val ;
		}	
		return $result ;
	}
	/**
	 * get only items that has keys 
	 */
	public static function filterByKeys(array $items , array $keys){
		$newItems = [];
		foreach($items as $key => $value){
			if(in_array($key , $keys)){
				$newItems[$key] = $value ;
			}
		}
		return $newItems ;
	}
	public static function removeKeysFromArray(array $items , array $keysToBeRemoved){
		$result = [];
		foreach($items as $currentKey => $value){
			if(!in_array($currentKey,$keysToBeRemoved)){
				$result[$currentKey] = $value ;
			}
		}
		return $result; 
	}
	public static function filterTrulyValue(array $arr):array 
	{
		return array_filter($arr,function($value){
			return $value ;
		});
	}
	public static function atLeastOneValueExistInArray(array $items , array $itemsToSearchIn){
		foreach($items as $item){
			if(in_array($item,$itemsToSearchIn)){
				return true  ;
			}
		}
		return false ;
	}
	public static function unformatValues(array $items )
	{
		$result = [];
		foreach($items as $key=>$value){
			$result[$key] = unformat_number($value); 
		}
		return $result;
	}
	public static function mergeTwoAssocArr(array $items1 , array $items2):array
	{
		$result = [];
		
		foreach($items1 as $key => $val){
			$result[$key] = $val ;
		}
		foreach($items2 as $key => $val){
			$result[$key] = $val ;
		}
		
		return $result ;
	}
	public static function twoArrayHasAtLeastNonZeroValue(array $firstItems , array $secondItems):bool{
		$hasAtLeastNonZeroValue = false ;
		if(count($firstItems) == 0 && count($secondItems) == 0){
			$hasAtLeastNonZeroValue = false ;
		}
		foreach($firstItems as $value){
			if($value != 0){
				$hasAtLeastNonZeroValue = true ;
			}
		}
	
		foreach($secondItems as $value){
			if($value != 0){
				$hasAtLeastNonZeroValue = true ;
			}
		}
		return $hasAtLeastNonZeroValue ; 
	}
	public static function orderByDayNameForTwoDimension(array $items){

		$days = [
			'Friday',
			'Saturday',
			'Sunday',
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday'
		];
		usort($items, function ($a, $b) use ($days) {
			$posA = array_search($a['item'], $days);
			$posB = array_search($b['item'], $days);
			return $posA <=> $posB;
		});
		return $items ;
	}
	public static function orderByDayNameForOneDimension(array $items){

		$days = [
			'Friday',
			'Saturday',
			'Sunday',
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday'
		];
		uksort($items, function ($a, $b) use ($days) {
			$posA = array_search($a, $days);
			$posB = array_search($b, $days);
			return $posA <=> $posB;
		});
		return $items ;
	}
	public static function getKeysSortedDescByKey(array $items,$keyName = 'Sales Values'):array
	{
		$values = [];
		$result= [];
		foreach($items as $categoryName => $itemArr ){
			$sumSalesValue = array_sum($itemArr[$keyName]);
			$values[$categoryName] = $sumSalesValue;
		}
		
		arsort($values);
		$sortedKeys = array_keys($values);
		foreach($sortedKeys as $key){
			$result[$key] = $items[$key];
		}
		return $result;
	}
	public static function fillMissingKeyInTwoDimArrWith(array $items , array $dates)
	{

		$allItems = [];

		foreach($items as $cate=>$keyAndVal){
			foreach($dates as $date){
				if(isset($keyAndVal[$date])){
					$allItems[$cate][$date] = $keyAndVal[$date];
				}else{
					$allItems[$cate][$date] = 0;
				}
			}
				
			}
		return $allItems;
	}
	public static function fillMissingKeyInOneDimArrWith(array $items , array $dates)
	{

		$allItems = [];
				foreach($dates as $date){
				if(isset($items[$date])){
					$allItems[$date] = $items[$date];
				}else{
					$allItems[$date] = 0;
				}
				
			}
		return $allItems;
	}
	public static function filterByUnique(array $items , array $uniqueKeys):array 
	{
		return  collect($items)->unique(function ($item) use ($uniqueKeys)  {
			$uniqueKey = '';
			foreach($uniqueKeys as $key){
				$uniqueKey.= $item->{$key};
			}
			return $uniqueKey;
		})->values()->toArray();
	}
	public static function getValueFromMonth(array $items, string $month)
	{
		foreach($items as $date => $value){
			if(Carbon::make($date)->format('m')== $month){
				return $value ;
			}
		}
		return 0 ;
	}
	public static function getValueFromMonthAndYear(array $items, string $month,string $year)
	{
		foreach($items as $date => $value){
			if(Carbon::make($date)->format('m')== $month && $year == Carbon::make($date)->format('Y')){
				return $value ;
			}
		}
		return 0 ;
	}
	public static function sliceWithDates($items , $endDate,$offsite = 11 ):array{
		$result = [];
		$startDate = Carbon::make($endDate)->subMonths($offsite)->format('Y-m-d');
		foreach($items as $date => $value){
			if(Carbon::make($date)->between(Carbon::make($startDate) , Carbon::make($endDate))){
				$result[$date] = $value ;
			}
		}
		return $result;
	}
	public static function searchForCorrespondingItem($items , $searchFor){
		$result = 0 ;
		foreach($items as $item){
			if($item['item'] == $searchFor){
				$result = $item['Sales Value'] ?? 0;		
			}
		}
		return $result;
		
	}
	public static function getMaxValuesWithItsDate(array $items)
	{
		$dates = [];
		$max = max($items);
		foreach($items as $date=>$value){
			if($value == $max){
				$dates[] = $date ;
			}
		}
		return [
			'value'=>$max ,
			'dates'=>$dates
		];
	}
	public static function getMinValuesWithItsDate(array $items)
	{
		$dates = [];
		$min = min($items);
		foreach($items as $date=>$value){
			if($value == $min){
				$dates[] = $date ;
			}
		}
		return [
			'value'=>$min ,
			'dates'=>$dates
		];
	}
	public static function numberFormatTwoDimArrBasedOnKey(array $items,string $key ):array
	{
		$result = [];
		foreach($items as $index=>$item){
			foreach($item as $currentKey => $currentValue){
				if($currentKey == $key){
					$result[$index][$currentKey] = number_format($currentValue);
				}else{
					$result[$index][$currentKey] = $currentValue;
					
				}
			}
		}
	return $result;
	}
	public static function getFirstOfYear(array $items):array{
		$result = [];
		$years = [];
		foreach($items as $date => $value){
			$year = explode('-',$date)[0];
			if(!isset($years[$year])){
				$years[$year] = $year ;
				$result[$date] = $value ;
			}
		}
		return $result;
	}
	public static function getPreviousKey(array $array, $currentKey)
{
    $keys = array_keys($array); // Get all keys
    $index = array_search($currentKey, $keys); // Find the index of the given key
    
    if ($index === false || $index === 0) {
        return null; // Return null if the key is not found or it's the first key
    }

    return $keys[$index - 1]; // Return the previous key
}
public static function filterByYearIndex(array $baseRatesMapping,array $yearIndexWithYear,int $yearIndex,string $currentLoopDateString, bool $isMonthlyStudy):array 

	{
		$result = [];
		
		$currentYear = $yearIndexWithYear[$yearIndex];
		// $loopIndex = 0 ;
		foreach($baseRatesMapping as $currentDateString => $no){
			$currentYearNumber =explode('-',$currentDateString)[0];
			$currentMonthNumber =explode('-',$currentDateString)[1];
			$currentMonth = explode('-',$currentLoopDateString)[1] ;
			// $loopIndex++;
			$condition = $isMonthlyStudy ? $currentYearNumber >= $currentYear && $currentMonthNumber>=$currentMonth // new added to be reviewed 
			: $currentYearNumber >= $currentYear   ; 
			if($condition){
				$result[$currentDateString] = $no;
			}
		}
	
		return $result;
	}

	public static function getMonthsAsArray($category):array 
	{
		return [
			30 => [30],
			45 => [30,15],
			60 => [30,30],
			75 => [30,30,15],
			90 => [30,30,30],
			105 => [30,30,30,15],
			120 => [30,30,30,30],
			150 => [30,30,30,30,30],
			180 => [30,30,30,30,30,30],
			210 => [30,30,30,30,30,30,30],
			240 => [30,30,30,30,30,30,30,30],
			270 => [30,30,30,30,30,30,30,30,30],
			300 => [30,30,30,30,30,30,30,30,30,30],
			330 => [30,30,30,30,30,30,30,30,30,30,30],
			360 => [30,30,30,30,30,30,30,30,30,30,30,30],
		][$category];
	}
	public static function getValueOrPrevious(array $data, string $date)
{
	return $data[$date];
	 // Convert keys to timestamps for proper sorting
	//  $timestamps = array_map('strtotime', array_keys($data));
	//  $dataWithTimestamps = array_combine($timestamps, array_keys($data));
	//  $valuesWithTimestamps = array_combine($timestamps, array_values($data));
	 
	//  // Sort by timestamp
	//  ksort($dataWithTimestamps);
	//  ksort($valuesWithTimestamps);
	 
	//  $previousDate = null;
	//  $previousValue = null;
	//  $dateTimestamp = strtotime($date);
	//  foreach ($dataWithTimestamps as $key => $originalDate) {
	// 	 if ($key == $dateTimestamp) {
	// 		 return ['date' => $originalDate, 'value' => $valuesWithTimestamps[$key]]; // Exact match found
	// 	 }
	
	// 	 if ($key > $dateTimestamp) {
	// 		 break; // Stop when a future date is encountered
	// 	 }
	// 	 $previousDate = $originalDate;
	// 	 $previousValue = $valuesWithTimestamps[$key];
	//  }
	
	//  return ['date' => $previousDate, 'value' => $previousValue]; // Return closest past date and value
}
	public static function isAllValuesEqual(array $items,array $items2){
		

		$firstIsEqual = true ;
		$previousVal = null ;
		foreach($items as  $val1){
				if(!is_null($previousVal)){
					if($val1 != $previousVal){
						$firstIsEqual = false ;
						
					}
					$previousVal = $val1 ;
				}else{
					$previousVal = $val1; 
				}
		}
		$secondIsEqual = true ;
		$previousVal = null;
		foreach($items2 as  $val){
			if(!is_null($previousVal)){
				if($val != $previousVal){
					$secondIsEqual = false ;
					
				}
				$previousVal = $val ;
			}else{
				$previousVal = $val; 
			}
		}
		if($firstIsEqual === true && $secondIsEqual === true ){
				return $val1;
		}
		
		return $items;
	}
	public static function getActualDatesAsIndexAndBoolean(array $datesAsIndexAndString ){
		$result = [];
		
		foreach($datesAsIndexAndString as $dateIndex => $dateString ){
			$result[$dateIndex] = (int)isActualDate($dateString);
			
		}
		return $result;
	}
	protected static function sumPerIndexes(array $items , array $dateIndexWithDate , array $financialYearsEndMonths){
		
		$yearlySums = [];

foreach ($dateIndexWithDate as $index => $date) {
    // Parse the date to get the year
    $year = date('Y', strtotime($date));
    
    // Initialize the year in the result array if not set
    if (!isset($yearlySums[$year])) {
        $yearlySums[$year] = ['sum' => 0, 'lastIndex' => $index];
    }
    
    // Add the item value to the year's sum (check if index exists in $items)
    if (isset($items[$index])) {
        $yearlySums[$year]['sum'] += $items[$index];
    }
    
    // Update the last index for the year
    $yearlySums[$year]['lastIndex'] = $index;
}

// Transform the result to use the last month's index as the key
$result = [];
foreach ($yearlySums as $year => $data) {
    $result[$data['lastIndex']] = $data['sum'];
}


// Sort by index to maintain order
ksort($result);
return $result;

	}
	public static function calculateGrowthRate(array $items):array {
		$previousValue = 0 ;
		$result = [];	
		foreach($items as $dateIndex => $currentValue){
			$result[$dateIndex] = $previousValue ? ($currentValue - $previousValue) / $previousValue * 100 : 0 ;
			$previousValue = $currentValue;
		}
		return $result;
	}
	public static function calculatePercentageOf(array $salesRevenues , array $items):array {
		$result = [];
		foreach($salesRevenues as $dateIndex => $salesValue){
			$currenItemVal = $items[$dateIndex]??0 ;
			$result[$dateIndex] =$salesValue ? $currenItemVal  / $salesValue * 100 : 0;
		}
		return $result;
	}
	
	public static function addTotalMonthsPerYear(array $items ,array $dateIndexWithDate, array $financialYearsEndMonths):array{
		$result = [];
		foreach($items as $index => $itemArr){
			foreach($itemArr as $mainItemId => $mainItemsArr){
				foreach($mainItemsArr as $subItemId => $subItemData){
				
					if($subItemId == 'growth-rate'){
						$totalOfSalesRevenue = $result[0]['main_items']['sales-revenue']['total']??[];
						$subItemData['total'] = self::calculateGrowthRate($totalOfSalesRevenue);						
					}
					elseif($subItemId == '% Of Revenue'){
						$totalOfSalesRevenue = $result[0]['main_items']['sales-revenue']['total']??[];
						$currentItemTotal = array_values($result[$index][$mainItemId])[0]['total']??[];
						$subItemData['total'] = self::calculatePercentageOf($totalOfSalesRevenue,$currentItemTotal);
					}
					else{
						$subItemData['total'] = self::sumPerIndexes($subItemData['data']??[],$dateIndexWithDate,$financialYearsEndMonths);
					}
					$result[$index][$mainItemId][$subItemId]=$subItemData;
				}
			}
		}
		return $result;
	}
	public static function sumForInternalIndexes(array $items)
	{
		$result = [];
		foreach ($items as $item) {
			foreach ($item as $index => $value) {
				$result[$index] =  isset($result[$index] )  ? $result[$index] + $value : $value;
			}
		}
		return $result ; 
	}
	public static function getTitleFromValueArray(array $items , string $value):string
	{
		foreach($items as $itemArr){
			if($itemArr['value'] == $value){
				return $itemArr['title'];
			}
		}
		dd('title not found');
	}
	public static function getIndexUsingName(array $array , $searchName)
	{
		foreach ($array as $key => $item) {
			if ($item['name'] === $searchName) {
				return  $key;
			}
		}
		dd('name not found');
	}
	public static function getLatestNonZeroExecutionKeys(array $data): array
{
    $maxEndDate = null;
    $selectedIndex = null;

    // Iterate through possible indices (1 to 5 in your example)
    for ($i = 1; $i <= 5; $i++) {
        $executionPercentageKey = "execution_percentage_$i";
        $endDateKey = "end_date_$i";

        // Check if the keys exist and execution_percentage is greater than 0
        if (
            isset($data[$executionPercentageKey], $data[$endDateKey]) &&
            floatval($data[$executionPercentageKey]) > 0
        ) {
            $currentEndDate = \Carbon\Carbon::parse($data[$endDateKey]);

            // Update if this end_date is greater or if maxEndDate is not set
            if ($maxEndDate === null || $currentEndDate->greaterThan($maxEndDate)) {
                $maxEndDate = $currentEndDate;
                $selectedIndex = $i;
            }
        }
    }

    // If no valid set is found, return an empty array
    if ($selectedIndex === null) {
        return [];
    }

    // Collect all keys related to the selected index
    $result = [];
    $keys = [
        "start_date_$selectedIndex",
        "end_date_$selectedIndex",
        "execution_percentage_$selectedIndex",
        "execution_days_$selectedIndex",
        "collection_days_$selectedIndex",
		'so_number',
		'po_number',
		'amount'
    ];

    foreach ($keys as $key) {
        if (isset($data[$key])) {
			$r= '_'.$selectedIndex;
			$newKey = str_replace($r,'',$key);
            $result[$newKey] = $data[$key];
        }
    }

    return $result;
}
public static function divideArrBy(array $items , int $num):array 
{
	$result = [];
	foreach($items as $index=> $val){
		$result[$index] = $val / $num;
	}
	return $result ; 
}
public static function multipleTwoArrAtSameIndex(array $firstArr , array $secondArr){
	$result = [];
	foreach($firstArr as $index => $value){
		$secondAtValue = $secondArr[$index]??0;
		$result[$index] = $value * $secondAtValue ;
	}
	return $result ; 
}
public static function divideTwoArrAtSameIndex(array $firstArr , array $secondArr){
	$result = [];
	foreach($firstArr as $index => $value){
		$secondAtValue = $secondArr[$index]??0;
		$result[$index] = $secondAtValue ?  $value / $secondAtValue  : 0;
	}
	return $result ; 
}
public static function sumPerYearIndex(array $itemsAsDateIndexAndValue ,  array $yearWithItsMonths):array{
	$result = [];
	foreach($yearWithItsMonths as $yearIndex => $itsMonths){
		$currentYearTotal = 0;
		foreach($itsMonths as $dateAsIndex => $dateAsString){
			$currentValue = $itemsAsDateIndexAndValue[$dateAsIndex]??0 ;
			$currentYearTotal +=  $currentValue;
		}
		/**
		 * * هنحط النتيجه بتاعتك كل سنه عند اخر شهر في السنه دي
		 */
		$result[$dateAsIndex] = $currentYearTotal;
	}
	return $result ;
}
public static function getPerYearIndexForEndBalance(array $itemsAsDateIndexAndValue ,  array $yearWithItsMonths):array{
	$result = [];
	foreach($yearWithItsMonths as $yearIndex => $itsMonths){
		$currentYearTotal = 0;
		foreach($itsMonths as $dateAsIndex => $dateAsString){
			$currentValue = $itemsAsDateIndexAndValue[$dateAsIndex]??0 ;
			$currentYearTotal =  $currentValue;
		}
		/**
		 * * هنحط النتيجه بتاعتك كل سنه عند اخر شهر في السنه دي
		 */
		$result[$dateAsIndex] = $currentYearTotal;
	}
	return $result ;
}
public static function getPerYearIndexForFirstMonthInYear(array $itemsAsDateIndexAndValue ,  array $yearWithItsMonths):array{
	$result = [];
	foreach($yearWithItsMonths as $yearIndex => $itsMonths){
		$currentYearTotal = 0;
		$isFirstMonth = true ;
		foreach($itsMonths as $dateAsIndex => $dateAsString){
			if($isFirstMonth){
				$currentValue = $itemsAsDateIndexAndValue[$dateAsIndex]??0 ;
				$currentYearTotal =  $currentValue;
				$isFirstMonth = false ;
			}
		}
		/**
		 * * هنحط النتيجه بتاعتك كل سنه عند اخر شهر في السنه دي
		 */
		$result[$dateAsIndex] = $currentYearTotal;
	}
	return $result ;
}
public static function getPerYearIndexForCashAndBank(array $itemsAsDateIndexAndValue ,  array $yearWithItsMonths):array{
	$result = [];
	foreach($yearWithItsMonths as $yearIndex => $itsMonths){
		$currentYearTotal = 0;
		$isFirstLoop = true ;
		foreach($itsMonths as $dateAsIndex => $dateAsString){
			$currentValue = $itemsAsDateIndexAndValue[$dateAsIndex]??0 ;
			if($isFirstLoop){
				$currentYearTotal =  $currentValue;
				$isFirstLoop=false;
			}
		}
		/**
		 * * هنحط النتيجه بتاعتك كل سنه عند اخر شهر في السنه دي
		 */
		$result[$dateAsIndex] = $currentYearTotal;
	}
	return $result ;
}
public static function calculateWorkingCapital($cashAndBankAmount,$totalCashInAsDateIndexAndValue , $totalCashOutAsDateIndexAndValue , $sumKeys)
{
	$openingBalance = $cashAndBankAmount ; 
	$statements = [];
	foreach($sumKeys as $dateAsIndex){
		$statements['beginning_balance'][$dateAsIndex] = $openingBalance;
		$currentTotalCashIn = $totalCashInAsDateIndexAndValue[$dateAsIndex]??0;
		$statements['total_cash_in'][$dateAsIndex] = $currentTotalCashIn;
		$currentTotalCashOut = $totalCashOutAsDateIndexAndValue[$dateAsIndex]??0;
		$statements['total_cashout'][$dateAsIndex] = $currentTotalCashOut;
		$netCashBeforeWorkingCapital = $openingBalance + $currentTotalCashIn - $currentTotalCashOut ;
		$statements['net_cash_before_working_capital'][$dateAsIndex] =$netCashBeforeWorkingCapital; 
		$workingCapitalInjection = 0 ;
		if($netCashBeforeWorkingCapital < 0){
			$workingCapitalInjection = $netCashBeforeWorkingCapital * -1 ;
		}
		$statements['working_capital_injection'][$dateAsIndex] =$workingCapitalInjection; 
		$endCashBalance = $netCashBeforeWorkingCapital + $workingCapitalInjection;
		$statements['cash_end_balance'][$dateAsIndex] = $endCashBalance;
		$openingBalance = $endCashBalance ;
	}
	return $statements;
	
	
	}
	public static function repeatThrough(float $value , array $keys):array{
		$result = [];
		foreach($keys as $index){
			$result[$index] = $value ; 
		}
		return $result;
	}
	public static function calculateRetainEarning(float $retainedEarningOpening,array $netProfit):array{
		$retainedEarnings  = [0 => $retainedEarningOpening];
		foreach($netProfit as $dateAsIndex => $value){
			if($dateAsIndex == 0){
				continue ;
			}
			$previousNetProfit = $netProfit[$dateAsIndex-1] ?? 0 ; 
			$previousRetainedEarning = $retainedEarnings[$dateAsIndex-1]??0;
			$retainedEarnings[$dateAsIndex] = $previousNetProfit + $previousRetainedEarning;
			
		}
		return $retainedEarnings;
	}
	public static function getLastMonthOfYear(array $yearWithItsMonths){
		$result = [];
		foreach($yearWithItsMonths as $yearAsIndex => $itsMonths){
			$result[$yearAsIndex]  = array_key_last($itsMonths);
		}	
		return $result;
	}
	public static function calculateChangeInAfter(array $customerReceivables , float $openingBalance ,array $yearIndexWithLastMonth,$debug=false){
		
		$isFirst = true ; 
		$result = [];
		foreach($yearIndexWithLastMonth as $yearIndex => $lastMonthAsDateIndex){
			$currentCustomerReceivables = $customerReceivables[$lastMonthAsDateIndex]??0;
			if($isFirst){
				$currentCustomerReceivables= $openingBalance - $currentCustomerReceivables ;
				$isFirst = false ; 
			}else{
				$nextIndex = $lastMonthAsDateIndex - 12 ;
				$nextYearValue = $customerReceivables[$nextIndex]??0;
				$currentCustomerReceivables = $nextYearValue - $currentCustomerReceivables ;
			}
				
			$result[$lastMonthAsDateIndex] = $currentCustomerReceivables ;
			
		}
		
		return $result;
	}
	public static function calculateChangeInBefore(array $customerReceivables , float $openingBalance ,array $yearIndexWithLastMonth){
		
		$isFirst = true ; 
		$result = [];
		foreach($yearIndexWithLastMonth as $yearIndex => $lastMonthAsDateIndex){
			$currentCustomerReceivables = $customerReceivables[$lastMonthAsDateIndex]??0;
			if($isFirst){
				$currentCustomerReceivables= $currentCustomerReceivables- $openingBalance  ;
				$isFirst = false ; 
			}else{
				$nextIndex = $lastMonthAsDateIndex - 12 ;
				$nextYearValue = $customerReceivables[$nextIndex]??0;
				$currentCustomerReceivables = $currentCustomerReceivables- $nextYearValue  ;
			}
				
			$result[$lastMonthAsDateIndex] = $currentCustomerReceivables ;
			
		}
		
		return $result;
		
	}
	public static function fillArr($collection):array{
		$firstKey = array_key_first($collection);
		$lastKey = array_key_last($collection);
		$dates = range($firstKey,$lastKey);
		$result = [];
		foreach($dates as $dateAsIndex){
			$result[$dateAsIndex] = $collection[$dateAsIndex]??0;
		}
		return $result;
	}
	public static function getLastNonZeroKey(array $items){
		$result = null ;
		foreach($items as $key => $value){
			if($value != 0 ){
				$result = $key;
			}
		}
		return $result;
	}
	public static function slice_from_index(array $arr , int $index)
	{
		$result = [];
		foreach($arr as $currentIndex => $value){
			if($currentIndex >= $index){
				$result[$currentIndex] = $value;
			}
		}
		return $result;
	}
	public static function extendFreeCashflowTenYears(array $freeCashFlowForEquity,float $perptual)
	{
		
		$length = count($freeCashFlowForEquity);
		if($length == 0){
			return [];
		}
		$lengthMinus12 = $length-12;
		$last12CashFlow = array_slice($freeCashFlowForEquity , $lengthMinus12,null,true);
		$lastIndex = $length -1 ;
		foreach($last12CashFlow as $dateAsIndex => $value){
			$lastIndex+=1;
			$freeCashFlowForEquity[$lastIndex] = $value * (1+$perptual) ;
		}
		if(count($freeCashFlowForEquity) >=240){
			return $freeCashFlowForEquity;
		}
		return self::extendFreeCashflowTenYears($freeCashFlowForEquity,$perptual);
		// foreach()
	}
	public static function avgWithOpening(array $items)
	{
		
		$result = [];
		foreach($items as $dateAsIndex => $value){
			$nextIndex = getNextKey($items,$dateAsIndex);
			if(!is_null($nextIndex)){
				$currentValue = $value ;
				$nextValue = $items[$nextIndex];
				$result[$nextIndex] = ($nextValue + $currentValue) / 2 ;
			}
		}
		return $result;
	}
}
