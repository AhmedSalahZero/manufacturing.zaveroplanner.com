<?php 
namespace App\Equations;

use App\Helpers\HArr;
use App\Helpers\HStr;
use App\Models\NonBankingService\Study;
use App\Product;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ExpenseAsPercentageEquation
{
	public function calculate($products,array $productAllocations,int $startDateAsIndex  , int $loopEndDateAsIndex , float $monthlyPercentage , float $vatRate ,bool $isDeductible , float $withholdTaxRate ):array 
	{
			$resultAsDateIndexAndValue = [];
			$expenseAllocations = [];
			foreach($products as $product){
			
				$monthlySalesTargetValues = $product->monthly_sales_target_values;
				foreach($monthlySalesTargetValues as $monthIndex => $val){
					if($monthIndex <= $loopEndDateAsIndex && $monthIndex >= $startDateAsIndex){
						$valBeforeRate = $monthlyPercentage / 100 * $val ;
						$resultAsDateIndexAndValue[$monthIndex] = isset($resultAsDateIndexAndValue[$monthIndex]) ? $resultAsDateIndexAndValue[$monthIndex] + $valBeforeRate : $valBeforeRate ; 
					}

				}
			}
			$expenseAllocations = Product::multiplyWithAllocation($productAllocations,$products,$resultAsDateIndexAndValue);
			
			
		
			
			return [
				'expense_amounts'=>$resultAsDateIndexAndValue,
				'expense_allocations'=>$expenseAllocations
			];
			// return [
			// 	'expense_amounts'=> ,
			// 	''
			// ];
		// return [
			// 'total_withhold'=>$totalWithhold , 
			// 'total_before_vat'=>$totalWithoutVat ,
			// 'total_vat'=>$totalVat,
			// 'total_after_vat'=>HArr::sumAtDates([$totalWithoutVat,$totalVat],$dates)
		// ];
	}
}
