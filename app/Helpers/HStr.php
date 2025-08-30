<?php 
namespace App\Helpers;
class HStr {
	public static function camelizeWithSpace($input, $separator = '-')
	{
		return str_replace($separator, ' ', ucwords($input, $separator));
	}
	public static function replaceSpecialCharacters($string){
		return str_replace(array( '\'', '"',',' , ';', '<', '>','\\' ), ' ', $string);
	} 
	public static function generateWhereFromMultipleArrs(array $wheres , string $orOrAnd):string 
	{
		$result = '';		
		foreach($wheres as $index => $whereArr){
			$column = $whereArr[0]; // company_id for example
			$operator = $whereArr[1]; // > for example
			$value = $whereArr[2]; // 21 for example
			$result.= ('`'.$column.'`' . ' ' . $operator . ' ' . $value  . ' ' . $orOrAnd . ' ');
		}
		return trim($result , $orOrAnd.' ');

	}
}
