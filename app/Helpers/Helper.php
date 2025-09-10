<?php

use App\Helpers\HArr;
use App\Services\IntervalSummationOperations;
use Carbon\Carbon;

const MAX_YEARS_COUNT = 7;
const FFE_COST = 'ffe_cost' ;

/**
 * * to convert product[0][name] to product.0.name
 */

function sumNumberOfOnes(array $items, int $year,array $datesIndexWithYearIndex)
{

	$counter = [];
	foreach ($items as $loopYear => $dateAndValues) {
		foreach ($dateAndValues as $dateIndex => $value) {
			$loopYear = $datesIndexWithYearIndex[$dateIndex];
			if ($value == 1) {
				$counter[$loopYear] = isset($counter[$loopYear]) ? $counter[$loopYear] + 1 : $value;
			}
		}
	}
	return $counter[$year] ?? 0;
}

function generateOldNameFromFieldName(string $str):string
{
    $field = preg_replace('/\[([^\]]+)\]/', '.$1', $str);

    return $field;
}
function getMonthsList(): array
{
    $months = [];
    for ($i = 1; $i <= 12; $i++) {
        $months[$i-1] = date('F', mktime(0, 0, 0, $i, 1));
    }
    return $months;
}
function getStudyYears():array
{
    $result = [];
    for ($i = 2020 ; $i<= 2050 ; $i++) {
        $result[] = $i ;
    }
    return $result;
}

function findByKey(array $items, $key, $searchId)
{
    foreach ($items as $item) {
        if (isset($item[$key]) && $item[$key] == $searchId) {
            return $item;
        }
    }
    return [];
}
function getTotalSteps()
{
    return 6;
}
function formatStringToTitle(string $str):string
{
    return ucwords(str_replace('_', ' ', $str));
}
function getManpowerTypes():array
{
    return [
         'direct_labor' => [
            'title'=>__('Direct Labor Salaries') ,
            'has_allocation'=>true ,
            'allocation_column_name'=>'product_manpower_allocation'
         ] ,
         'manufacturing_overheads'=>[
            'title'=>__('Manufacturing Overheads Salaries'),
            'has_allocation'=>true ,
            'allocation_column_name'=>'product_overheads_allocation'
         ],
          'operational_salaries'=>[
            'title'=>__('Other Operational Salaries'),
            'has_allocation'=>false ,
          ],
          'sales'=>[
            'title'=>__('Sales And Marketing Salaries'),
            'has_allocation'=>false
          ],
           'general'=>[
            'title'=>__('General Salaries'),
            'has_allocation'=>false
           ]
    ];
    

}

function generateDatesBetweenTwoDatesWithoutOverflow(Carbon $start_date, Carbon $end_date, $method = 'addMonthNoOverflow', $format = 'Y-m-d', $indexedArray = true, $indexFormat = 'Y-m-d')
{
    $dates = [];
    for ($date = $start_date->copy(); $date->lte($end_date); $date->{$method}()->setTime(0, 0)) {
        if ($indexedArray) {
            $dates[] = $date->format($format);
        } else {
            $dates[$date->format($indexFormat)] = $date->format($format);
        }
    }
    return $dates;
}

function getMeasurementUnits():array
{
    return [
        'ton'   => __('Ton'),
        "kg"  => __('Kilogram'),
        "gram"   => __('Gram'),
        "litter"   => __('Litter'),
        "milliliter"   => __('Milliliter'),
        "carton"   => __('Carton'),
        "box"   => __('Box'),
        'piece'=>__('Piece'),
        'each'=>__('Each'),
        'barrel' => __('Barrel'),
        'bottle'=> __('Bottle'),
        'others'=>__('Others')
    ];

}
function getRmInventoryCoverageDays()
{
    return [
        0 => __('0'),
        15 => __('15 Days'),
        30 => __('30 Days'),
        45 => __('45 Days'),
        60 => __('60 Days'),
        75 => __('75 Days'),
        90 => __('90 Days'),
        120 => __('120 Days'),
        150 => __('150 Days'),
        180 => __('180 Days')
    ];
}

function getDayFromDate(string $date)
{
    return explode('-', $date)[2];
}
function getMonthFromDate(string $date)
{
    return explode('-', $date)[1];
}
if (!function_exists('dateFormatting')) {
    function dateFormatting($date, $formate = 'Y-m-d')
    {
        return date($formate, strtotime($date));
    }
}
function repeatJson($jsonItems)
{
    $itemsArray = convertJsonToArray($jsonItems);
    if (!count($itemsArray)) {
        return null ;
    }
    $lastKey = array_key_last($itemsArray);
    $loopingKey = $lastKey+1;
    for ($loopingKey ; $loopingKey < MAX_YEARS_COUNT ; $loopingKey++) {
        $itemsArray[$loopingKey] =$itemsArray[$lastKey];
    }
    return json_encode($itemsArray);
}
function convertJsonToArray(?string $json):array
{
    return $json ? (array)json_decode($json) : [];
}
function removeSquareBrackets($input)
{
    // Use preg_replace to remove [ ] and text between them
    $result = preg_replace('/\[[^\]]*\]/', '', $input);
    return $result;
}

function number_unformat($number, $force_number = true, $dec_point = '.', $thousands_sep = ',')
{
    $isNegativeNumber = str_starts_with($number, '-');
    if ($force_number) {
        $number = preg_replace('/^[^\d]+/', '', $number);
    } elseif (preg_match('/^[^\d]+/', $number)) {
        return false;
    }
    $type = (strpos($number, $dec_point) === false) ? 'int' : 'float';
    $number = str_replace([$dec_point, $thousands_sep], ['.', ''], $number);
    settype($number, $type);
    if ($isNegativeNumber) {
        $number  = $number * -1 ;
    }
    return $number;
}
function getIntervalFormatted():array
{
    return ['monthly'=>__('Monthly'),'quarterly'=>__('Quarterly'),'semi-annually'=>__('Semi-annually'),'annually'=>__('Annually')];
}
function sumIntervalsIndexes(array $dateValues, string $intervalName, string $financialYearStartMonth, array $dateIndexWithDate)
{
    return (new IntervalSummationOperations())->sumForInterval($dateValues, $intervalName, $financialYearStartMonth, $dateIndexWithDate, true);
}
function removeDateFrom(array $dateIndexWithDate)
{
    $result = [];
    foreach ($dateIndexWithDate as $dateAsIndex => $dateAsString) {
        $dateExploded = explode('-', $dateAsString);
        $month = $dateExploded[1];
        $year = $dateExploded[0];
        $dateMonthAndYear =$month.'-'.$year;
        $result[$dateMonthAndYear] = $dateAsIndex;
    }
    return $result;
}
function extendArr(array $arr, int $pos, int $repeat): array
{

    $arr = array_values($arr);

    if (!isset($arr[$pos])) {
        throw new Exception("Position $pos not found in array");
    }

    $value = $arr[$pos];
    $arr = array_slice($arr, 0, $pos + 1);
    for ($i = 1; $i <= $repeat; $i++) {
        $arr[] = $value;
    }
    return $arr;
}
function convertIndexKeysToString(array $items, array $datesAsIndexAndString)
{
    $result = [];
    foreach ($items as $dateAsIndex => $value) {
        $dateAsString = $datesAsIndexAndString[$dateAsIndex];
        $result[$dateAsString] = $value;
    }
    return $result ;
}
function convertStringKeysToIndexes(array $items, array $datesAsIndexAndString)
{
    $result = [];
    foreach ($items as $dateAsString => $value) {
        $dateAsIndex = array_search($dateAsString, $datesAsIndexAndString);
        if ($dateAsIndex !== false) {
            $result[$dateAsIndex] = $value ;
        }
    }
    return $result ;
}
function sumDueDayWithPayment($paymentRate, $dueDays)
{
    $items = [];
    foreach ($dueDays as $index=>$dueDay) {
        $currentPaymentRate = $paymentRate[$index]??0 ;
        $items[$dueDay] = isset($items[$dueDay]) ? $items[$dueDay] + $currentPaymentRate : $currentPaymentRate;
    }
    return $items;
}
function zeroValuesBefore(array $twoDimArr, $dateIndex):array
{
    $result = [];
    foreach ($twoDimArr as $index => $itemArr) {
        foreach ($itemArr as $dateAsIndex => $zeroOrOne) {
            if ($dateAsIndex >= $dateIndex) {
                $result[$index][$dateAsIndex] = 1;
            } else {
                $result[$index][$dateAsIndex] = 0;
            }
        }
    }
    return  $result;
}
function collectionDueDays():array
{
    $result = [];
    foreach ([
        15 , 30,45,60,75,90,120,150,180
    ] as $dueDay) {
        $result[$dueDay] = $dueDay;
    }
    return $result;
}
function getFgInventoryBreakdownTypes():array
{
    return [
        'raw_material_value'=>__('Raw Material Value'),
        'direct_labor_value'=>__('Direct Labor Value'),
        'manufacturing_overheads_value'=>__('Manufacturing Overheads Value'),
    ];
}
function getPaymentTerms(): array
{

    return [
        [
            'value' => 'customize',
            'title' => __('Customize')
        ],
        [
            'value' => 'cash',
            'title' => __('Cash')
        ],
        [
            'value' => 'quarterly',
            'title' => __('Quarterly')
        ],
        [
            'value' => 'semi-annually',
            'title' => __('Semi Annually')
        ],
        [
            'value' => 'annually',
            'title' => __('Annually')
        ],
    ];
}
function dueInDays()
{
    return [
        [
            'value'=>0 ,
            'title'=>0
        ],
        [
            'value'=>15 ,
            'title'=>15 . ' ' . __('Days')
        ],
        [
            'value'=>30,
            'title'=>30 . ' ' . __('Days')
        ],
        [
            'value'=>60,
            'title'=>60 . ' ' . __('Days')
        ],
        [
            'value'=>90 ,
            'title'=>90 .  ' ' . __('Days')
        ],
        [
            'value'=> 120 ,
            'title'=>120 . ' ' . __('Days')
        ],
        [
            'value'=>150,
            'title'=>150 . ' ' . __('Days')
        ],
        [
            'value'=> 180 ,
            'title'=>180 . ' ' . __('Days')
        ],
        [
            'value'=> 210 ,
            'title'=>210 . ' ' . __('Days')
        ],
        [
            'value'=>240 ,
            'title'=>240 . ' ' . __('Days')
        ],
        [
            'value'=>270 ,
            'title'=>270 . ' ' . __('Days')
        ],

        [
            'value'=>300 ,
            'title'=>300 . ' ' . __('Days')
        ],
        [
            'value'=>330 ,
            'title'=>330 . ' ' . __('Days')
        ],
        [
            'value'=> 360 ,
            'title'=>360 . ' ' . __('Days')
        ],
    ];
}
function getExpensesTypes():array
{
    return [
            'expense_as_percentage',
            'fixed_monthly_repeating_amount',
            'one_time_expense'
    ];
}
function sumTwoArray(array $first, array $second)
{
    $result  =[];
    $dates = array_values(array_unique(array_merge(array_keys($first), array_keys($second))));
    foreach ($dates as $date) {
        $secondVal = $second[$date] ?? 0;
        $value = $first[$date] ?? 0;
        $result[$date] = $value  + $secondVal ;
    }
    return $result ;
}
function getNextDate(?array $array, ?string $date, $datesExistsAsKeys = true)
{

    $searched = array_search($date, $datesExistsAsKeys ? array_keys($array) : $array);
    $arrayPlusOne = $datesExistsAsKeys ? @array_keys($array)[$searched +1] : @($array)[$searched +1];
    if ($searched !== null &&  isset($arrayPlusOne)) {
        return $datesExistsAsKeys ? array_keys($array)[$searched +1] : ($array)[$searched +1];
    }
    return null;
}
function getPreviousDate(?array $array, ?string $date, $datesExistsAsKeys = true)
{

    $searched = array_search($date, $datesExistsAsKeys ? array_keys($array) : $array);
    $arrayPlusOne = $datesExistsAsKeys ? @array_keys($array)[$searched - 1] : @($array)[$searched - 1];
    if ($searched !== null &&  isset($arrayPlusOne)) {
        return $datesExistsAsKeys ? array_keys($array)[$searched - 1] : ($array)[$searched - 1];
    }
    return null;
}
    
function calculateReplacementDates(array $studyDates, int $operationStartDateAsIndex, int $studyEndDateAsIndex, int $propertyReplacementIntervalInMonths)
{
    $replacementDates = [];
    foreach ($studyDates as $studyDateAsString=>$studyDateAsIndex) {
        if ($operationStartDateAsIndex > $studyEndDateAsIndex) {
            break ;
        }
        $replacementDates[$studyDateAsIndex] = $operationStartDateAsIndex+ $propertyReplacementIntervalInMonths;
        $operationStartDateAsIndex = $replacementDates[$studyDateAsIndex] ;
    }
    return $replacementDates ;
}
function calculateAccumulatedDepreciation(array $totalMonthlyDepreciation, array $studyDates)
{
    $result = [];
    foreach ($studyDates as $dateIndex) {
        $value = $totalMonthlyDepreciation[$dateIndex] ?? 0;
        $previousDateAsIndex = $dateIndex-1;
        $result[$dateIndex] = $previousDateAsIndex >=0 ?  $result[$previousDateAsIndex] + $value : $value;
    }
    return $result;
}
function generateDatesBetweenTwoIndexedDates(int $startDateAsIndex, int $endDateAsIndex):array
{
    $result = [];
    for ($i =$startDateAsIndex ; $i <=$endDateAsIndex ; $i++) {
        $result[] = $i;
    }
    return $result;
}
function getDifferenceBetweenTwoDatesInDays(Carbon $firstDate, Carbon $secondDate)
{
    return $secondDate->diffInDays($firstDate);
}


// function eachIndexMinusPreviousIfNegative(array $items,$debug = false  )
// {
// 	$result = [];
// 	foreach($items as $dateAsIndex=>$value)
// 	{
// 		$previousIndex = $dateAsIndex-1;
// 		$previousValue  = $items[$previousIndex] ?? 0;
// 		$newValue = 0 ;
// 		if($value < 0){
// 			$newValue = ($value - $previousValue) * -1;
// 			if($newValue <0){
// 				$newValue = 0;
// 			}
// 		}
// 		$result[$dateAsIndex] = $newValue ;
// 	}
// 	return $result ;
// }

// function getMinAtEveryIndex(array $keyAndValues)
// {
// 	$result = [];
// 	foreach($keyAndValues as $index=>$values)
// 	{
// 		$result[$index] = min($values);
// 	}
// 	return $result;
// }
function getValueFromArrayStringAndIndex(array $items, $dateAsString, $dateAsIndex, $defaultValue = 0)
{
    if (isset($items[$dateAsString])) {
        return $items[$dateAsString];
    }
    if (isset($items[$dateAsIndex])) {
        return $items[$dateAsIndex];
    }
    return $defaultValue ;
}
function sumIntervals(array $dateValues, string $intervalName, string $financialYearStartMonth, array $dateIndexWithDate)
{
    return (new IntervalSummationOperations())->sumForInterval($dateValues, $intervalName, $financialYearStartMonth, $dateIndexWithDate);
}
function getBusinessSectors()
{
	return  [
        'Textiles & Garments',
		'Automotive',
		'Food & Beverage',
		'Pharmaceuticals',
		'Fertilizers',
		'Plastics',
		'Chemicals',
		'Cement',
		'Steel',
		'Glass',
		'Sanitary Products',
		'Ceramics',
		'Other Construction Materials',
		'Wood & Furniture',
		'Home Appliance',
		'Printing & Packaging',
		'Heavy Machinery',
		'Metal Industries & Fabrication',
		'Ready Made Concrete',
		'Medical Disposables & Devices',
		'Electronics',
		'Others',
		'Leather Products',
		'Agro-Processing'
    ] ;}
	
	function getDivisionNumber(){
	return 1000;
}	
 function calculateNetPresentValue(array $freeCashFlow , float $costOfFundRate):float 
	{
		$netPresentValues =   [];
		$costOfFundRate = $costOfFundRate / 100 ; 
		$index = 1 ;
		foreach ($freeCashFlow  as $date => $value){
			$netPresentValues[$date] = $value / (pow((1+$costOfFundRate) , $index));
			$index++;
		}
		return array_sum($netPresentValues) ; 
	}
function convertStringToClass(string $str): string
{
    $reg = " /^[\d]+|[!\"#$%&'\(\)\*\+,\.\/:;<=>\?\@\~\{\|\}\^ ]/ ";

    return preg_replace($reg, '-', $str);
}
