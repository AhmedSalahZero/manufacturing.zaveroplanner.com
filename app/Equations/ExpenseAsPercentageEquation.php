<?php 
namespace App\Equations;

class ExpenseAsPercentageEquation
{
	public function calculate($products,int $startDateAsIndex  , int $loopEndDateAsIndex , float $monthlyPercentage , float $vatRate = 0 ,bool $isDeductible = true , float $withholdTaxRate = 0 ):array 
	{
			$resultAsDateIndexAndValue = [];
			$resultPerProducts = [];
			foreach($products as $product){
				$monthlySalesTargetValues = $product->monthly_sales_target_values;
				foreach($monthlySalesTargetValues as $monthIndex => $monthlySalesTargetValue){
					if($monthIndex <= $loopEndDateAsIndex && $monthIndex >= $startDateAsIndex){
						$valBeforeRate = $monthlyPercentage / 100 * $monthlySalesTargetValue ;
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
