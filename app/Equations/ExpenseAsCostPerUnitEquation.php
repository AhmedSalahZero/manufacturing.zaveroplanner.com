<?php 
namespace App\Equations;

class ExpenseAsCostPerUnitEquation
{
	public function calculate($products,int $startDateAsIndex  , int $loopEndDateAsIndex , float $monthlyCostPerUnit , float $vatRate = 0 ,bool $isDeductible = true , float $withholdTaxRate = 0 ):array 
	{
			$resultAsDateIndexAndValue = [];
			$resultPerProducts = [];
			foreach($products as $product){
				$monthlySalesTargetQuantities = $product->monthly_sales_target_quantities;
				foreach($monthlySalesTargetQuantities as $monthIndex => $val){
					if($monthIndex <= $loopEndDateAsIndex && $monthIndex >= $startDateAsIndex){
						$valBeforeRate = $monthlyCostPerUnit  * $val ;
						$resultAsDateIndexAndValue[$monthIndex] = isset($resultAsDateIndexAndValue[$monthIndex]) ? $resultAsDateIndexAndValue[$monthIndex] + $valBeforeRate : $valBeforeRate ; 
						$resultPerProducts[$product->id][$monthIndex] = $valBeforeRate;
					}
				}
			}
			return [
				'expense_amounts'=>$resultAsDateIndexAndValue,
				'expense_allocations'=>$resultPerProducts
			];
		
	}
}
