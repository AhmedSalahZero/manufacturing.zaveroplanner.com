<?php

namespace App;

use App\Traits\HasCollectionOrPaymentStatement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    use HasCollectionOrPaymentStatement;
    protected $guarded = ['id'];
	public const FFE = 'ffe';
	public const NEW_BRANCH = 'new-branch';
	public const PER_EMPLOYEE = 'per-employee';
	protected $casts = [
		'ffe_counts'=>'array',
		'monthly_amounts'=>'array',
		'position_ids'=>'array',
		'department_ids'=>'array',
		'product_allocations'=>'array',
		'custom_collection_policy'=>'array',
		'depreciation_statement'=>'array',
		'capitalization_statement'=>'array',
		'admin_depreciations'=>'array',
		'ffe_equity_payment'=>'array',
		'ffe_loan_withdrawal'=>'array',
		'ffe_payment'=>'array',
		
	];
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
	
    public function getName()
    {
        return $this->name ;
    }
	public function getCounts()
	{
		return $this->counts;
	}
	public function getTotalItemsCost()
	{
		return $this->getCounts() * $this->getAmount();
	}
	public function getTotalCost()
	{
		return $this->getTotalItemsCost();
	}
    public function getStartDateAsIndex()
    {
        return $this->start_date;
    }
	public function getStartDateAsString()
	{
		return $this->project->getDateFromDateIndex($this->getStartDateAsIndex());
	}
	public function getEndDateAsString()
	{
		return $this->project->getDateFromDateIndex($this->getEndDateAsIndex());
	}
    public function getStartDateFormatted()
    {
        return app('dateIndexWithDate')[$this->start_date];
    }
    public function getEndDateAsIndex()
    {
        return $this->end_date;
    }
    public function getEndDateFormatted()
    {
        return $this->end_date ? app('dateIndexWithDate')[$this->end_date] : null;
    }
   
    public function getPaymentTerm()
    {
        return $this->payment_terms ;
    }
    public function getEquityFundingRate()
    {
        return $this->equity_funding_rate ?: 0;
    }
	public function getEquityFunding():float
	{
		return $this->getEquityFundingRate();
	}
	public function getDuration():int 
	{
		return $this->getEndDateAsIndex() - $this->getStartDateAsIndex();
	}
	public function getDepreciationDuration():int
	{
		return $this->depreciation_duration;
	}
		public function getDepreciationDurationInMonths()
	{
		return $this->getDepreciationDuration() * 12;
	}
	public function getManufacturingDepreciationPercentage():float
	{
		return $this->manufacturing_depreciation_percentage;
	}
	public function getAdminDepreciationPercentage():float
	{
		return $this->admin_depreciation_percentage;
	}
	public function getCollectionPolicyValue():array
	{
		if($this->getPaymentTerm() == 'cash'){
			return [
				0 => 100
			];
		}
		return $this->custom_collection_policy;
	}
    public function getPaymentRate(int $rateIndex)
    {
        return array_values($this->custom_collection_policy ?? [])[$rateIndex] ?? 0 ;
    }
    public function getPaymentRateAtDueInDays($rateIndex)
    {
        return array_keys($this->custom_collection_policy ?? [])[$rateIndex] ?? 0 ;
    }
   
    public function getAmount()
    {
        return $this->amount ?: 0 ;
    }

    
	public function getStartDateYearAndMonth()
	{
		$dateAsString = $this->getStartDateAsString() ;
		if(is_null($dateAsString)){
			return now()->format('Y-m');
		}
		return Carbon::make($dateAsString)->format('Y-m');
	}
	public function getEndDateYearAndMonth()
	{
		$dateAsString = $this->getEndDateAsString() ;
		if(is_null($dateAsString)){
			return now()->format('Y-m');
		}
		return Carbon::make($dateAsString)->format('Y-m');
	}
	public function getProductAllocationPercentageForTypeAndProduct(int $productId):?float{
		return $this->getProductAllocations()[$productId]??null;
	}
	public function getProductAllocations():array 
	{
		return $this->product_allocations;
	}
	public function getInterestRate()
	{
		return $this->interest_rate;
	}
	public function getTenor():int
	{
		return $this->tenor ;
	}
	public function getInstallmentInterval():string 
	{
		return $this->installment_interval;
	}
	public function getReplacementCostRate()
	{
		return $this->replacement_cost_rate ;
	}
	public function getReplacementInterval():int 
	{
		return $this->replacement_cost_interval;
	}
		public function getReplacementIntervalInMonths()
	{
		return $this->getReplacementInterval() * 12  ;
	}
	public function getGracePeriod()
	{
		return $this->grace_period?:0;
	}
	public function calculateFFEAssetsForFFE(int  $transferredDateForFFEAsIndex,float  $transferredAmount,array $studyDates,int $studyEndDateAsIndex,Project $project):array 
	{
		
			$assets = [];
			$totalItemsCost = $this->getTotalItemsCost();
		// $this->ffeItems->each(function(FFEItem $this) use ($totalItemsCost,$transferredDateForFFEAsIndex,$transferredAmount,&$assets,$studyDates,$studyEndDateAsIndex){
			$depreciationDurationInMonthsForFFE = $this->getDepreciationDurationInMonths();
			$ffeReplacementCostRateForFFE = $this->getReplacementCostRate()  ;
			$ffeReplacementIntervalInMonthsForFFE = $this->getReplacementIntervalInMonths();
			$totalCost = $this->getTotalCost();
			$thisTransferredAmount = $totalItemsCost ? $transferredAmount*($totalCost / $totalItemsCost) : 0  ;
			$projectUnderProgressForFFE = [
				'transferred_date_and_vales'=>[
					$transferredDateForFFEAsIndex =>  $thisTransferredAmount
				]
			];
			return  $this->calculateFFEAssets($depreciationDurationInMonthsForFFE,$ffeReplacementCostRateForFFE,$ffeReplacementIntervalInMonthsForFFE,$projectUnderProgressForFFE,$studyDates,$studyEndDateAsIndex,$project);
			// $assets[$this->getName()] = $this->calculateFFEAssets($depreciationDurationInMonthsForFFE,$ffeReplacementCostRateForFFE,$ffeReplacementIntervalInMonthsForFFE,$projectUnderProgressForFFE,$studyDates,$studyEndDateAsIndex,$project);
		// });
		return $assets ;
	  
	}
	
	public function calculateFFEAssets(int $propertyDepreciationDurationInMonths,float $propertyReplacementCostRate,int $propertyReplacementIntervalInMonths,array $projectUnderProgressForConstruction,array $studyDates,int $studyEndDateAsIndex  ,Project $project):array 
	{
		$buildingAssets = [];
		$datesAsStringAndIndex = $project->getDatesAsStringAndIndex();
		$operationStartDateAsIndex = $project->getOperationStartDateAsIndex($datesAsStringAndIndex,$project->getOperationStartDateFormatted());
		$fixedAssetEndDateAsIndex = $this->getEndDateAsIndex();
		$operationStartDateAsIndex  =  $operationStartDateAsIndex >= $fixedAssetEndDateAsIndex ? $operationStartDateAsIndex :$fixedAssetEndDateAsIndex;
		$propertyReplacementCostRate = $propertyReplacementCostRate /100;
		$constructionTransferredDateAndValue = $projectUnderProgressForConstruction['transferred_date_and_vales']??[];
		$constructionTransferredDateAsIndex = array_key_last($constructionTransferredDateAndValue);
		$constructionTransferredValue = $constructionTransferredDateAndValue[$constructionTransferredDateAsIndex]??0;
		
		

		$beginningBalance = 0;
		$totalMonthlyDepreciation = [];
		$accumulatedDepreciation = [];
		$replacementDates = calculateReplacementDates($studyDates,$operationStartDateAsIndex ,$studyEndDateAsIndex,$propertyReplacementIntervalInMonths);
		$depreciation = [];
		$index = 0 ;
		$depreciationStartDateAsIndex = null;
		foreach ($studyDates as $dateAsIndex) {
			if($constructionTransferredDateAsIndex < $operationStartDateAsIndex){
				$depreciationStartDateAsIndex = $operationStartDateAsIndex;
			}else{
				$depreciationStartDateAsIndex = $dateAsIndex+1;
			}
			$depreciationEndDateAsIndex = $depreciationStartDateAsIndex >=0  ?  $depreciationStartDateAsIndex+ $propertyDepreciationDurationInMonths - 1 : null;
	
			$buildingAssets['beginning_balance'][$dateAsIndex]= $beginningBalance;
			$buildingAssets['additions'][$dateAsIndex]=  $dateAsIndex ==$constructionTransferredDateAsIndex ? $constructionTransferredValue : 0;
			$buildingAssets['initial_total_gross'][$dateAsIndex] =  $buildingAssets['additions'][$dateAsIndex] +  $beginningBalance;
			$currentInitialTotalGross = $buildingAssets['initial_total_gross'][$dateAsIndex] ??0;
			$replacementCost[$dateAsIndex] =    in_array($dateAsIndex ,$replacementDates)  ? $this->calculateReplacementCost($currentInitialTotalGross,$propertyReplacementCostRate) : 0;
			/**
			 * ! Issue Here
			 */
			if( in_array($dateAsIndex ,$replacementDates) && ( $constructionTransferredDateAsIndex <= $operationStartDateAsIndex)){
				$depreciationEndDateAsIndex = $dateAsIndex+1+$propertyDepreciationDurationInMonths-1;
			}
			// $dateBeforeOperation =  $operationStartDateAsIndex >= $ffeEndDateAsIndex ? $dateBeforeOperation : $ffeEndDateAsIndex-1;
			$replacementValueAtCurrentDate = $replacementCost[$dateAsIndex] ?? 0;
			$buildingAssets['replacement_cost'][$dateAsIndex] = $replacementCost[$dateAsIndex] ;
			$buildingAssets['final_total_gross'][$dateAsIndex] = $buildingAssets['initial_total_gross'][$dateAsIndex]  + $replacementValueAtCurrentDate;
			$depreciation[$dateAsIndex]=$this->calculateMonthlyDepreciation($buildingAssets['additions'][$dateAsIndex],$replacementValueAtCurrentDate,$propertyDepreciationDurationInMonths, $depreciationStartDateAsIndex, $depreciationEndDateAsIndex, $totalMonthlyDepreciation, $accumulatedDepreciation,$studyDates);
			$accumulatedDepreciation = calculateAccumulatedDepreciation($totalMonthlyDepreciation,$studyDates);
			$buildingAssets['total_monthly_depreciation'] =$totalMonthlyDepreciation;
			$buildingAssets['accumulated_depreciation'] =$accumulatedDepreciation;
			$currentAccumulatedDepreciation = $buildingAssets['accumulated_depreciation'][$dateAsIndex] ?? 0;
			$buildingAssets['end_balance'][$dateAsIndex] =  $buildingAssets['final_total_gross'][$dateAsIndex] -  $currentAccumulatedDepreciation;
			$beginningBalance = $buildingAssets['final_total_gross'][$dateAsIndex];
			$index++;
		}
		return $buildingAssets ;
	}
	
	

	protected function calculateReplacementCost(float $totalGross, float $propertyReplacementCostRate,  )
	{
		return $totalGross * $propertyReplacementCostRate ;
	}
	
	protected function calculateMonthlyDepreciation(float $additions,float $replacementCost,int $propertyDepreciationDurationInMonths, ?int $depreciationStartDateAsIndex, ?int $depreciationEndDateAsIndex, &$totalMonthlyDepreciation, &$accumulatedDepreciation,array $studyDates)
	{
		if (is_null($depreciationStartDateAsIndex) || is_null($depreciationEndDateAsIndex)) {
			return [];
		}
		$monthlyDepreciations = [];
		$monthlyDepreciationAtCurrentDate =  ($additions+$replacementCost) / $propertyDepreciationDurationInMonths ;
		$depreciationDates = generateDatesBetweenTwoIndexedDates($depreciationStartDateAsIndex,$depreciationEndDateAsIndex);
		foreach ($studyDates as  $dateAsIndex) {
			$previousDateAsIndex = $dateAsIndex-1;
			if(in_array($dateAsIndex,$depreciationDates)){
				$monthlyDepreciations[$dateAsIndex] = $monthlyDepreciationAtCurrentDate;
				$totalMonthlyDepreciation[$dateAsIndex] = isset($totalMonthlyDepreciation[$dateAsIndex]) ? $totalMonthlyDepreciation[$dateAsIndex] +$monthlyDepreciationAtCurrentDate : $monthlyDepreciationAtCurrentDate;
				$accumulatedDepreciation[$dateAsIndex] = $previousDateAsIndex >=0 ? ($totalMonthlyDepreciation[$dateAsIndex] + $accumulatedDepreciation[$previousDateAsIndex]) : $totalMonthlyDepreciation[$dateAsIndex];
			}else{
				// $monthlyDepreciations[$dateAsString] = 0;
				// $totalMonthlyDepreciation[$dateAsString]  = 0 ;
				$accumulatedDepreciation[$dateAsIndex] = $accumulatedDepreciation[$previousDateAsIndex] ?? 0 ;
			}
		}

		return $monthlyDepreciations;
	}
	public function getLoanType():string
	{
		return 'grace_period_without_capitalization';
	}
	public function getBaseRate()
	{
		return 0 ; 
	}
	public function getMarginRate()
	{
		return $this->interest_rate;
	}
	public function getPricing()
	{
		return $this->getMarginRate();
	}
	public function getFfeEquityPayment()
	{
		return $this->ffe_equity_payment?:[];
	}
	public function getFfeLoanWithdrawal()
	{
		return $this->ffe_loan_withdrawal?:[];
	}
	
}
