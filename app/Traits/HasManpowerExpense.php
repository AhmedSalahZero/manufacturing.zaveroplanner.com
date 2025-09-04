<?php
namespace App\Traits;

use App\Helpers\HArr;
use App\ManPower;
use App\Product;
use App\Project;
use App\ReadyFunctions\CollectionPolicyService;

trait HasManpowerExpense
{
    
    
    public function calculateManpowerResult(array $dateAsIndexes, int $existingCount, array $hiringCounts, float $monthlyNetSalary, float $salaryTaxesRate, float $socialInsuranceRate)
    {
        /**
     * @var Project $this
     */
        $yearWithItsIndexes = $this->getOperationDurationPerYearFromIndexesForAllStudyInfo();
        $monthsWithItsYear = $this->getMonthsWithItsYear($yearWithItsIndexes) ;
       
        $isYearsStudy = true;
        $currentIndex = 0 ;
        $currentSalaryAtMonthIndex = $monthlyNetSalary ;
        $accumulatedManpowerCounts = [];
        $monthlySalariesPayments = [];
        $annualIncreaseRate = $this->getSalaryIncreaseRate();
        $salaryExpenses =[];
        $yearIndexWithItsMonthsAsIndexAndString = $this->getYearIndexWithItsMonthsAsIndexAndString();
        foreach ($yearIndexWithItsMonthsAsIndexAndString as $yearAsIndex => $itsMonths) {
            foreach ($itsMonths as $dateAsIndex => $dateAsString) {
                
                $currentYearOrMonthIndex = $isYearsStudy ? $monthsWithItsYear[$dateAsIndex] : $dateAsIndex  ;
                $increaseRateCondition = $isYearsStudy ? $currentIndex%12 == 0 : true ; // true to be increase every month so this condition will not have any effect if monthly study
                $previousHiringCount = $accumulatedManpowerCounts[$dateAsIndex-1] ?? $existingCount;
        
                $accumulatedManpowerCounts[$dateAsIndex] = $hiringCounts[$dateAsIndex] + $previousHiringCount   ;
                if ($increaseRateCondition && $currentIndex != 0) {
                    $currentSalaryAtMonthIndex = $currentSalaryAtMonthIndex * (1+($annualIncreaseRate/100)) ;
                }
                $monthlySalariesPayments[$dateAsIndex] = $currentSalaryAtMonthIndex * $accumulatedManpowerCounts[$dateAsIndex];
                $salaryExpenses[$dateAsIndex] = $currentSalaryAtMonthIndex * $accumulatedManpowerCounts[$dateAsIndex] / (1 - ($salaryTaxesRate + $socialInsuranceRate));
                $currentIndex++;
            }
        }
            
        /**
         * * To Calculate Payment Statement
         */
        $salaryTaxAndSocialInsuranceAmounts = HArr::MultiplyWithNumber($salaryExpenses, ($salaryTaxesRate+$socialInsuranceRate));
        $dateIndexWithDate = array_flip($dateAsIndexes);
        $salaryTaxAndSocialInsuranceAmountsPayment= (new CollectionPolicyService())->applyMultiCustomizedCollectionPolicy([30=>100], $salaryTaxAndSocialInsuranceAmounts);
        $salaryTaxAndSocialInsuranceAmountsStatement = ManPower::calculateStatement($salaryTaxAndSocialInsuranceAmounts, [], $salaryTaxAndSocialInsuranceAmountsPayment, [], $dateIndexWithDate);
    
        /**
         * * End Calculate
         */
    
        return [
            'accumulated_manpower_counts'=>$accumulatedManpowerCounts,
            'salary_payments'=>$monthlySalariesPayments,
            'salary_expenses'=>$salaryExpenses,
            'tax_and_social_insurance_statement'=>$salaryTaxAndSocialInsuranceAmountsStatement
        ];
    }
    
    //    public function recalculateManpower()
    // {
    //     $positions = $this->positions ;
    //     /**
    //      * @var Position $position
    //      */
    //     $operationStartDateAsIndex = $this->operation_start_month;
    //     $salaryTaxesRate = $this->getSalaryTaxesRate() / 100;
    //     $socialInsuranceRate = $this->getSocialInsuranceRate() /100 ;
    //     foreach ($positions as $position) {
    //         $positionArr = [];
    //         $hiringCounts = $position->getHiringCounts();
    //         $currentExistingCount = $position->getExistingCount();
    //         $dateAsIndexes = array_keys($hiringCounts);
    //         $monthlyNetSalary = $position->getMonthlyNetSalary();
    //         $additionalDatabaseResult =  $this->calculateManpowerResult($dateAsIndexes, $currentExistingCount, $hiringCounts, $operationStartDateAsIndex, $monthlyNetSalary, $salaryTaxesRate, $socialInsuranceRate);
    //         foreach ($additionalDatabaseResult as $columnName => $payload) {
    //             $positionArr[$columnName] = $payload;
    //         }
    //         $position->update($positionArr);
    //     }
        
        
    // }
    public function getDueDayWithRates(int $index):array
    {
        return  [
            30 => 100 ,
        ];
    }
}
