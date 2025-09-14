<?php
namespace App\ReadyFunctions;


class StraightMethodService
{
    
    public function calculateStraightAmount(float $amount,  int $startDateAsIndex, int $duration)
    {
        $steadyGrowthCount = [];
        if ($duration == 0) {
			
            return [
                $startDateAsIndex => $amount
            ];
        }
        for ($i = 1 ; $i <= $duration ; $i++) {
            $steadyGrowthCount[] = $duration;
        }
        // $steadyGrowthFactor = array_sum($steadyGrowthCount);
        // $steadyFactorAmount = $steadyGrowthFactor != 0 ? $amount / $steadyGrowthFactor : $amount  ;
        $straightAmount = [];
		 $straightDate = $startDateAsIndex; // 01-02-2023
        // $straightDate = Carbon::make($startDate)->format('Y-m-d'); // 01-02-2023
        foreach ($steadyGrowthCount as $steadyGrowthCountElement) {
            $straightAmount[$straightDate] = $amount / $duration;
         	 $straightDate = $straightDate+1;  // [$steadGrowthRate = 01-02-2023]

        }
        return $straightAmount;
    }

}
