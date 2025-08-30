<?php 
namespace App\Helpers;

use Illuminate\Support\Str;

class HHelpers 
{
	public static function getClassNameWithoutNameSpace($object){
		$class_parts = explode('\\', get_class($object));
 		 return end($class_parts);
	}
	public static function generateUniqueCodeForModel( string $modelName ,string $columnName,int $length){
			$modelFullName = 'App\Models\\'.$modelName;
			$randomCode = self::generateCodeOfLength($length); ;			
            $model = $modelFullName::where($columnName,$randomCode)->exists();
            if ($model) {
				return self::generateUniqueCodeForModel($modelName,$columnName,$length);
            }
			return $randomCode ; 
	}
	public static function generateCodeOfLength($length,$onlyNumbers = false )
	{
		
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		if($onlyNumbers){
			$characters = '0123456789';
		}
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
		return $randomString ;
	}
	public static function getModelFullNameFromTableName(?string $tableName = null):string 
	{
		$tableName = $tableName?: self::getTableNameFromRequest();
		return 'App\Models\\' . Str::studly(Str::singular($tableName));
	}
	public static function getTableNameFromRequest()
	{
		return Request()->segment(2);
	}
	public static function formatForSelect2(array $items):array
	{
		$formatted = [];
		foreach($items as $value => $title){
			$formatted[] = ['title'=>$title,'value'=>$value];
		}
		return $formatted ; 		
	}
	
}
