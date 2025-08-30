<?php
namespace App\Traits;


use App\FixedAsset;
use App\Helpers\HArr;
use App\Project;
use App\ReadyFunctions\CalculateFixedLoanAtBeginningService;
use App\ReadyFunctions\CalculateFixedLoanAtEndService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait HasFixedAsset
{
	 public function storeFixedLoansForFixedAssets(string $fixedAssetType, $isSensitivity = false):void
    {
		/**
		 * @var Project $this
		 */
        $loanSchedulePaymentTableName = $isSensitivity ? 'sensitivity_fixed_assets_loan_schedule_payments' : 'fixed_assets_loan_schedule_payments';
        $loanInfoArr = $this->getFixedAssetStructureForFixAssetType($fixedAssetType);
        $loanAmounts = $loanInfoArr['loan_amount'];
        $calculateFixedLoanAtEndService = new CalculateFixedLoanAtEndService ;
        $calculateFixedLoanAtBeginningService = new CalculateFixedLoanAtBeginningService ;
        $portfolioLoans = [];
        $studyId  = $this->id ;
        $companyId = $this->company->id ;
        $study = $this ;
        $operationDurationPerYear=$study->getOperationDurationPerYearFromIndexes();

        // $leasingRevenueStreams =$study->{$relationName};
      //  $generalAndReserveAssumption = $study->generalAndReserveAssumption;
        // $leasingEclAndNewPortfolioFundingRate = $study->{$eclRelationName};
        
        /**
         * @var GeneralAndReserveAssumption $generalAndReserveAssumption
         */
        $dateIndexWithDate = app('dateIndexWithDate');
        $dateWithDateIndex = app('dateWithDateIndex');
        $yearIndexWithYear = app('yearIndexWithYear');

        $baseRates = 0 ;
        // $baseRates = $generalAndReserveAssumption->getCbeLendingCorridorRates() ;
		$pricingPerMonths = $loanInfoArr['interest_rate'] ;
        $baseRatesPerMonths= [];
        foreach ($operationDurationPerYear as $yearIndex => $yearMonthIndexes) {
            foreach ($yearMonthIndexes as $monthIndex => $monthlyZeroOrOne) {
                $yearOrMonthIndex = $this->isMonthlyStudy() ? $monthIndex : $yearIndex;
                $baseRatesPerMonths[Carbon::make($dateIndexWithDate[$monthIndex])->format('Y-m-d')] = $baseRates[$yearOrMonthIndex];
            }
        }
        DB::table($loanSchedulePaymentTableName)
        // ->where('revenue_stream_type',$revenueStreamType)
        ->where('study_id', $studyId)->where('fixed_asset_type', $fixedAssetType)->delete();
        
        $baseRatesMapping = HArr::getFirstOfYear($baseRatesPerMonths);
        $bankLendingMarginRates=$generalAndReserveAssumption->getBankLendingMarginRates();

        
        
        $baseRatesMapping = HArr::isAllValuesEqual($baseRatesMapping, $bankLendingMarginRates);
        $totalMonthlyLoanAmounts = [];
        // $time = 0 ;
    
        
        foreach ($operationDurationPerYear as $yearIndex => $yearMonthIndexes) {
            $baseRatesMapping = is_array($baseRatesMapping) ? HArr::filterByYearIndex($baseRatesMapping, $yearIndexWithYear, $yearIndex, $dateIndexWithDate[$monthIndex], $this->isMonthlyStudy()) : $baseRatesMapping;
            
            foreach ($yearMonthIndexes as $monthIndex => $monthlyZeroOrOne) {
                $yearOrMonthIndex = $this->isMonthlyStudy() ? $monthIndex : $yearIndex;
                $currentMonthlyLoanAmount = $loanAmounts[$monthIndex]??0;
                // foreach($loanAmounts as  $monthIndexWithAmount ){
                // $currentMonthlyLoanAmount = $monthIndexWithAmount[$monthIndex]??0 ;
        
                        
                if ($currentMonthlyLoanAmount <= 0) {
                    continue ;
                }
                $totalMonthlyLoanAmounts[$monthIndex]  = isset($totalMonthlyLoanAmount[$monthIndex]) ? $totalMonthlyLoanAmount[$monthIndex] +  $currentMonthlyLoanAmount : $currentMonthlyLoanAmount ;
                        
                $currentMonth = $dateIndexWithDate[$monthIndex];
                        
                    
                $gracePeriod = $loanInfoArr['grace_period'];
                // $gracePeriod = $fixedAssetsFundingStructure? $fixedAssetsFundingStructure->getGracePeriodAtMonthIndex($monthIndex):0;
                $tenor =  $loanInfoArr['tenor'];
                // $tenor = $fixedAssetsFundingStructure ? $fixedAssetsFundingStructure->getTenorsAtMonthIndex($monthIndex) : 0;
                if ($tenor <=0) {
                    continue ;
                }
                $installmentInterval = $loanInfoArr['installment_interval'];
                // $installmentInterval = $fixedAssetsFundingStructure ? $fixedAssetsFundingStructure->getInstallmentIntervalAtMonthIndex($monthIndex):null;
                // $installmentPaymentIntervalValue = $calculateFixedLoanAtEndService->getInstallmentPaymentIntervalValue($installmentInterval);
                $stepUp = 0;
                $stepDown = 0;
                $stepInterval =null;
                $loanType = 'normal';
                // $loanNature = $leasingRevenueStreamBreakdown->getLoanNature();
                // $loanService = $loanNature == 'fixed-at-end' ? $calculateFixedLoanAtEndService : $calculateFixedLoanAtBeginningService ;
                $loanService = $calculateFixedLoanAtEndService ;
                        
                $newLoanFundingRate = $fixedAssetsFundingStructure->getNewLoansFundingRatesAtMonthIndex($monthIndex);
                            
                $currentMarginRate = $generalAndReserveAssumption->getBankLendingMarginRatesAtYearOrMonthIndex($yearOrMonthIndex);
                $currentMonthlyLoanAmount = $totalMonthlyLoanAmounts[$monthIndex];
                $currentMonthlyLoanAmount = $currentMonthlyLoanAmount * $newLoanFundingRate / 100 ;
                        
                // if(is_array($baseRatesMapping)){
                // 	$currentPortfolioLoans=$loanService->__calculateBasedOnDiffBaseRates($baseRatesMapping ,$loanType, $currentMonth, $currentMonthlyLoanAmount,  $currentMarginRate,  $tenor, $installmentInterval,$installmentPaymentIntervalValue, $stepUp, $stepInterval ,$stepDown ,  $stepInterval ,$gracePeriod,$monthIndex,$dateWithDateIndex,$dateIndexWithDate );
                // }else{
                                
                $currentLoanArr=$loanService->__calculate([], -1, $loanType, $currentMonth, $currentMonthlyLoanAmount, $baseRatesMapping, $currentMarginRate, $tenor, $installmentInterval, $stepUp, $stepInterval, $stepDown, $stepInterval, $gracePeriod, $monthIndex, null, $pricingPerMonths);
                $finalResult = $currentLoanArr['final_result']??[];
                unset($finalResult['totals']);
                $currentLoanArr = $finalResult;
                                
                            
                
                // }
                if (isset($currentLoanArr) && count($currentLoanArr)) {
                    $currentLoanArr['study_id'] = $studyId ;
                //    $currentLoanArr['company_id'] = $companyId ;
                    $currentLoanArr['month_as_index'] = $monthIndex ;
                    $currentLoanArr['fixed_asset_type'] = $fixedAssetType ;
                    // $currentLoanArr['portfolio_loan_type'] ='bank_portfolio';
                    $portfolioLoans[]=collect($currentLoanArr)->map(function ($item, $keyName) {
                        if (is_array($item)) {
                            return json_encode($item);
                        }
                        return $item;
                    })->toArray();
                }
                            
                            
                        
                    
                        
                        
                        
                        
                        
                    
                // }
            }
        }
        DB::table($loanSchedulePaymentTableName)->insert($portfolioLoans);
    }
	public function getFixedAssetStructureForFixAssetType(string $fixedAssetType)
    {
		/**
		 * @var Project $this
		 */
        if ($fixedAssetType == FixedAsset::FFE) {
            return [
				'loan_amount'=>$this->getLoanAmount(),
				'interest_rate'=>$this->getInterestRate(),
				'grace_period'=>$this->getGracePeriod(),
				'installment_interval'=>$this->getInstallmentInterval(),
				'tenor'=>$this->getTenor()
			];
        } 
		// elseif ($fixedAssetType == FixedAsset::NEW_BRANCH) {
        //     return $this->newBranchFixedAssetsFundingStructure;
        // } elseif ($fixedAssetType == FixedAsset::PER_EMPLOYEE) {
        //     return $this->perEmployeeFixedAssetsFundingStructure;
        // }
        dd('not supported fixed asset type');
        // return $this->fixedAssetsFundingStructure->where('fixed_asset_type',$fixedAssetType)->first();
    }
	
} 
