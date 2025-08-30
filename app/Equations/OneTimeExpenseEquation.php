<?php 
namespace App\Equations;
class OneTimeExpenseEquation
{
	public function calculate(float $amount,int $startDateAsIndex,bool $isDeductible,float $vatRate):array 
	{
		$payload = [];
		$oneTimeAmount = ($isDeductible ? $amount : $amount  * (1+($vatRate / 100))) ;
		$amount = $oneTimeAmount / 12;
		for($i =  0 ; $i<12 ; $i++){
			
			$payload[$startDateAsIndex+$i] = $amount;
 		}

		$currentBeginningBalance=   $oneTimeAmount  ;
		 foreach($payload as $dateAsIndex => $value){
			$statements['beginning_balance'][$dateAsIndex] = $currentBeginningBalance ;
			$statements['end_balance'][$dateAsIndex] = $statements['beginning_balance'][$dateAsIndex] - $value ;
			$currentBeginningBalance = $statements['end_balance'][$dateAsIndex];
			 
		}
		return [
			'one_time'=>$statements['beginning_balance']??[] , 
			'monthly_one_time'=>$payload , 
			'end_balance'=>$statements['end_balance']??[]
		];
	}
}
