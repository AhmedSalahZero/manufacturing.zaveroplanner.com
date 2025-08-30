<?php
namespace App\ReadyFunctions;

use Carbon\Carbon;

class SteadyGrowthMethod {
	
	
	public function calculateSteadyGrowthAmount(float $amount, string $startDate, int $duration){
		$steadyGrowthCount = [];
		for($i = 1 ; $i <= $duration ; $i++){
			$steadyGrowthCount[] = $i;			
		}
		$steadyGrowthFactor = array_sum($steadyGrowthCount);
		$steadyFactorAmount = $steadyGrowthFactor != 0 ? $amount / $steadyGrowthFactor : $amount  ; 
		$steadyGrowthAmount = [];
		$steadyGrowthDate = Carbon::make($startDate)->format('Y-m-d'); // 01-02-2023 
		foreach($steadyGrowthCount as $steadyGrowthCountElement){
			$steadyGrowthAmount[$steadyGrowthDate] = $steadyFactorAmount * $steadyGrowthCountElement;
			$steadyGrowthDate = Carbon::make($steadyGrowthDate)->addMonth()->format('Y-m-d'); // [$steadGrowthRate = 01-02-2023]

		}
		return $steadyGrowthAmount;
		}
	
}
