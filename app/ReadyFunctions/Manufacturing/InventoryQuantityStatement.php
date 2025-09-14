<?php
namespace App\ReadyFunctions\Manufacturing;

class InventoryQuantityStatement
{

    /**
     * * $salesData monthly_sales_target_quantities
     * * $beginningBalance Finished Goods Beginning Inventory Quantity
     * *$monthsToCover  Finished Goods Inventory Coverage Days * / 30
     */
    public function createInventoryQuantityStatement($salesData, $beginningBalance  = 0 , $monthsToCover = 1  )
{
    // Initialize result array
    $inventoryStatement =   [
        'dates' => [],
        'beginning_balance' => [],
        'manufacturing_quantity' => [],
        'total_quantity_available' => [],
        'sales_quantity' => [],
        'end_balance' => []
    ];

    // Get dates array and sort them
    $dates = array_keys($salesData);
    sort($dates);

    $currentBalance = $beginningBalance;

    // Process each month
    for ($i = 0; $i < count($dates); $i++) {
        $currentDate = $dates[$i];
        $currentSales = $salesData[$currentDate];

        // Store beginning balance
        $inventoryStatement['beginning_balance'][$currentDate] = $currentBalance;
        $inventoryStatement['dates'][$currentDate] = $currentDate;
        $inventoryStatement['sales_quantity'][$currentDate] = $currentSales;

        // Calculate required quantity for future months
        $requiredForFuture = 0;
        $remainingMonths = $monthsToCover;

        // Handle whole months
        for ($j = 1; $j <= floor($monthsToCover) && ($i + $j) < count($dates); $j++) {
            $requiredForFuture += $salesData[$dates[$i + $j]];
            $remainingMonths -= 1;
        }

        // Handle fractional month
        if ($remainingMonths > 0 && ($i + floor($monthsToCover) + 1) < count($dates)) {
            $nextMonthSales = $salesData[$dates[$i + floor($monthsToCover) + 1]];
            $requiredForFuture += $nextMonthSales * $remainingMonths;
        }

        // Calculate needed manufacturing_quantity
        $totalNeeded = $currentSales + $requiredForFuture;
        $manufacturing_quantity = max(0, $totalNeeded - $currentBalance);

        $inventoryStatement['manufacturing_quantity'][$currentDate] = $manufacturing_quantity;
        $totalAvailable = $currentBalance + $manufacturing_quantity;
        $inventoryStatement['total_quantity_available'][$currentDate] = $totalAvailable;

        // Calculate ending balance
        $endBalance = $totalAvailable - $currentSales;
        $inventoryStatement['end_balance'][$currentDate] = $endBalance;

        // Set beginning balance for next iteration
        $currentBalance = $endBalance;
    }
    return $inventoryStatement;
}








}
