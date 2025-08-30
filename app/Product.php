<?php

namespace App;

use App\ReadyFunctions\Manufacturing\InventoryQuantityStatement;
use App\ReadyFunctions\SeasonalityService;
use App\Traits\HasBasicStoreRequest;
use App\Traits\HasCollectionOrPaymentStatement;
use App\Traits\HasCollectionPolicy;
use App\Traits\HasSeasonality;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
	use HasBasicStoreRequest,HasCollectionOrPaymentStatement,HasSeasonality,HasCollectionPolicy;
    protected $connection = 'mysql';
	public static function booted()
	{
		parent::boot();
		static::saving(function(Product $product){
			$directionInYears =count($product->project->getYearIndexWithYear())-2;
			$endDateAsIndex = $product->project->getViewStudyEndDateAsIndex();
			$config = [
				true => [
					'position'=>$endDateAsIndex ,
					'no_repeats'=>12 
				],
				false => [
					'position'=> $directionInYears,
					'no_repeats'=>1 
				]
			];
			
			foreach([
				'max_capacity'=>false , 
				'target_percentages'=>false ,
				'target_quantities'=>false ,
				'growth_rates'=>false ,
				'price_per_unit'=>false ,
				'sales_target_values'=>false ,
				'monthly_sales_target_values'=>true,
				'sensitivity_monthly_sales_target_values'=>true,	
				'monthly_sales_target_quantities'=>true,
				'sensitivity_monthly_sales_target_quantities'=>true
				
				// is monthly column ? 
			] as $columnName => $isMonthlyColumn){
				if($product->isDirty($columnName)){
					$product->{$columnName}  = extendArr($product->{$columnName},$config[$isMonthlyColumn]['position'],$config[$isMonthlyColumn]['no_repeats']);
				}
			}
			// foreach($jsonColumns )
			
		});
	}
	protected $casts = [
		'max_capacity'=>'array',
		'target_percentages'=>'array',
		'target_quantities'=>'array',
		'growth_rates'=>'array',
		'price_per_unit'=>'array',
		'sales_target_values'=>'array',
		// 'quarterly_seasonality'=>'array',
		// 'monthly_seasonality'=>'array',
		'collection_policy_value'=>'array',
		'monthly_sales_target_values'=>'array',
		'sensitivity_monthly_sales_target_values'=>'array',
		'monthly_sales_target_quantities'=>'array',
		'sensitivity_monthly_sales_target_quantities'=>'array',
		'fg_beginning_inventory_breakdowns'=>'array',
		'product_inventory_qt_statement'=>'array',
		'product_inventory_value_statement'=>'array',
		'product_manpower_allocation'=>'array',
		'product_manpower_statement'=>'array',
		'product_overheads_allocation'=>'array',
		'product_overheads_statement'=>'array',
		'product_raw_material_consumed'=>'array',
		'product_overheads_statement'=>'array',
		'collection_statement'=>'array'
		
	];
    protected $guarded = [];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
	public function getName():string 
	{
		return $this->name ; 
	}
	public function getMeasurementUnit():?string 
	{
		return $this->measurement_unit ;
	}
	
	/**
	 * ! Need To Be Changed
	 */
	public function isComplete()
	{
		return false ;
	}
	/**
	 * * بتبتدا من سنه بيع المنتج لحد سنه نهايه الدراسة
	 * * returns
	 * * 
	 */

	public function getYearIndexWithYear():array 
	{
		/**
		 * @var Project $project
		 */
		$project = $this->project ;
		$sellingStartYearAsIndex = $this->getSellingStartYearAsIndex();
		$studyEndYearAsIndex = $project->getStudyEndYearAsIndex() ;
		$years = [];
		foreach(range($sellingStartYearAsIndex,$studyEndYearAsIndex) as $index => $yearAsIndex){
			$years[$yearAsIndex] = $this->project->getYearFromYearIndex($yearAsIndex);
		}
		return $years;
		
	}
	public function getViewYearIndexWithYear():array 
	{
		$sellingStartYearAsIndex = $this->getSellingStartYearAsIndex();
		$studyEndYearAsIndex = $this->project->getStudyEndYearAsIndex() ;
		$years = [];
		foreach(range($sellingStartYearAsIndex,$studyEndYearAsIndex) as $index => $yearAsIndex){
			$years[$yearAsIndex] = $this->project->getYearFromYearIndex($yearAsIndex);
		}
		return $years;
		
	}
	public function isFirstOne():bool 
	{
		return $this->project->products[0]->id == $this->id ; 
	}
	public function getMaxCapacityAtYearIndex(int $yearAsIndex)
	{
		return $this->max_capacity[$yearAsIndex] ?? 0;
	}
	public function getTargetPercentageAtYearIndex(int $yearAsIndex)
	{
		return $this->target_percentages[$yearAsIndex] ?? 0;
	}
	
	public function getTargetQuantity():array
	{
		return $this->target_quantities;
	}
	public function getTargetQuantityAtYearIndex(int $yearAsIndex)
	{
		return $this->getTargetQuantity()[$yearAsIndex] ?? 0;
	}
	public function getGrowthRateAtYearIndex(int $yearAsIndex)
	{
		return $this->growth_rates[$yearAsIndex] ?? 0;
	}
	public function getPricePerUnit():array 
	{
		return $this->price_per_unit;
	}
	public function getPricePerUnitAtYearIndex(int $yearAsIndex)
	{
		return $this->getPricePerUnit()[$yearAsIndex] ?? 0;
	}
	public function getSalesTargetValuesAtYearIndex(int $yearAsIndex)
	{
		return $this->sales_target_values[$yearAsIndex] ?? 0;
	}
	
	// public function getRawMaterialAtYearIndex(int $yearAsIndex)
	// {
	// 	return $this->monthly_seasonality[$yearAsIndex] ?? 0;
	// }
	public function rawMaterials():BelongsToMany
	{
		return $this->belongsToMany(RawMaterial::class,'product_raw_material','product_id','raw_material_id')->withTimestamps()->withPivot(['percentages']);
	}
	
	
	
	public function getNextProduct():?Product
	{
		return $this->project->products->where('id','>',$this->id)->first();
	}
	public function getSellingStartDateAsString():string 
	{
		$dateAsIndex  = $this->getSellingStartDateAsIndex();
		$dateIndexWithDate = $this->project->getDateIndexWithDate();
		return $dateIndexWithDate[$dateAsIndex];
	}
	public function getSellingStartDateAsIndex():int 
	{
		return $this->selling_start_date ; 
	}
	public function getSellingStartYearAsIndex():int 
	{
		return $this->project->getYearIndexFromDateIndex($this->getSellingStartDateAsIndex()) ; 
	}
	public function getSellingStartDateYearAndMonth()
	{
		$dateAsString = $this->getSellingStartDateAsString() ;
		if(is_null($dateAsString)){
			return now()->format('Y-m');
		}
		return Carbon::make($dateAsString)->format('Y-m');
	}
	// public function prepareForSeasonalityService()
	// {
	// 	$items = [];
	// 	$seasonalityType = $this->getSeasonalityType();
	// 	$items['seasonality'] = $seasonalityType;
	// 	if($seasonalityType == 'flat'){
		
	// 	}
	// 	elseif($seasonalityType == 'quarterly'){
	// 		// convert to dates 
	// 		$items['quarters'] = $this->quarterly_seasonality;
	// 	}else{
	// 		// convert to dates 
	// 		$items['distribution_months_values'] = $this->monthly_seasonality ;
	// 	}
	// 	return $items; 
		
	// }
	public function getSalesActiveYearsIndexWithItsMonths()
	{
		$project = $this->project;
		$datesAsStringAndIndex = $project->getDatesAsStringAndIndex();
		$datesAndIndexesHelpers = $project->getDatesIndexesHelper();
        $yearIndexWithYear = $datesAndIndexesHelpers['yearIndexWithYear'];
		$datesIndexWithYearIndex = $datesAndIndexesHelpers['datesIndexWithYearIndex'];
		$dateIndexWithDate = $datesAndIndexesHelpers['dateIndexWithDate'];
		$dateWithMonthNumber = $datesAndIndexesHelpers['dateWithMonthNumber'];
		$operationDurationPerYear = $project->getOperationDurationPerYear($datesAsStringAndIndex,$datesIndexWithYearIndex,$yearIndexWithYear,$dateIndexWithDate,$dateWithMonthNumber);
		$sellingStartDateAsIndex = $this->getSellingStartDateAsIndex() ;
		return zeroValuesBefore($operationDurationPerYear,$sellingStartDateAsIndex);
		
		
	}
	public function calculateSeasonality():array 
	{
		$datesAndIndexesHelpers = $this->project->getDatesIndexesHelper();
		$dateIndexWithDate = $datesAndIndexesHelpers['dateIndexWithDate'];
		$distributionPercentages = $this->getDistributedPercentages() ;
		$operationDurationPerYear = $this->getSalesActiveYearsIndexWithItsMonths();
	
		return SeasonalityService::salesSeasonality($distributionPercentages,$operationDurationPerYear,$dateIndexWithDate);
	}
	public function getSensitivityPricePerUnitRate():float 
	{
		return $this->sensitivity_price_rate?:0 ; 
	}
	/**
	 * * TargetQuantityMultipliedBySeasonalityAndPricePerUnit
	 */
	public function calculateMonthlySalesTargetValue(bool $isSensitivity = false ):array
	{
		$sensitivityPriceRate = $this->getSensitivityPricePerUnitRate();
		$monthlySalesTargetValue=[];
		$seasonalityArray = $this->calculateSeasonality();
		$targetSellingArray = $this->getTargetQuantity();	
		$pricePerUnit = $this->getPricePerUnit();
		$datesIndexWithYearIndex = $this->project->getDatesIndexWithYearIndex();
		foreach($seasonalityArray as $dateAsIndex => $seasonalityValue){
			$currentYearIndex = $datesIndexWithYearIndex[$dateAsIndex];
			$targetSellingQuantityForThisYear = $targetSellingArray[$currentYearIndex];
			$pricePerUnitForThisYear = $pricePerUnit[$currentYearIndex];
			$pricePerUnitForThisYear = $isSensitivity ? ($pricePerUnitForThisYear*(1+$sensitivityPriceRate / 100)) : $pricePerUnitForThisYear;
			// dump($seasonalityValue , $targetSellingQuantityForThisYear);
			$monthlySalesTargetQuantity = $seasonalityValue * $targetSellingQuantityForThisYear ; 
			$monthlySalesTargetQuantities[$dateAsIndex] = $monthlySalesTargetQuantity;
			$monthlySalesTargetValue[$dateAsIndex] = $monthlySalesTargetQuantity *$pricePerUnitForThisYear ;
		}
		$monthlySalesTargetValueColumnName = $isSensitivity ? 'sensitivity_monthly_sales_target_values' :  'monthly_sales_target_values';
		$monthlySalesTargetQuantityColumnName = $isSensitivity ? 'sensitivity_monthly_sales_target_quantities' :  'monthly_sales_target_quantities';
		$beginningBalance = $this->getFgInventoryQuantity();
		$monthsToCover = $this->getMonthsToCover();
		// $monthlySalesTargetValue
		$productRawMaterialConsumed = [];
		foreach($monthlySalesTargetValue as $dateAsIndex => $monthlySalesValue){
			$currentYearIndex = $datesIndexWithYearIndex[$dateAsIndex];
			foreach($this->rawMaterials as $rawMaterial ){
				$percentagesForCurrentDateIndex = json_decode($rawMaterial->pivot->percentages)[$currentYearIndex] / 100;
				$productRawMaterialConsumed[$rawMaterial->id][$dateAsIndex]  = $percentagesForCurrentDateIndex * $monthlySalesValue ;
				$productRawMaterialConsumed['total'][$dateAsIndex] = isset($productRawMaterialConsumed['total'][$dateAsIndex]) ? $productRawMaterialConsumed['total'][$dateAsIndex] + $productRawMaterialConsumed[$rawMaterial->id][$dateAsIndex] : $productRawMaterialConsumed[$rawMaterial->id][$dateAsIndex];
			}
		}
		$inventoryQuantityStatement  = (new InventoryQuantityStatement())->createInventoryQuantityStatement($monthlySalesTargetQuantities,$beginningBalance,$monthsToCover);
		$this->update([
			$monthlySalesTargetValueColumnName=>$monthlySalesTargetValue,
			$monthlySalesTargetQuantityColumnName=>$monthlySalesTargetQuantities,
			'product_inventory_qt_statement'=>$inventoryQuantityStatement,
			'product_raw_material_consumed'=>$productRawMaterialConsumed
		]);
		
		return $monthlySalesTargetValue;
	}
	/**
	 * * vat  rate 20 for example NOT 0.20
	 */
	
	
	public function getVatRate():float 
	{
		return $this->vat_rate ;
	}
	public function getWithholdTaxRate():float 
	{
		return $this->withhold_tax_rate ;
	}
	
	//Finished Goods Beginning Inventory Quantity
	public function getFgInventoryQuantity()
	{
		return $this->fg_inventory_quantity;
	}	
		public function getFgInventoryValue()
	{
		return $this->fg_inventory_value;
	}	
	public function fgInventoryCoverageDays()
	{
		return $this->fg_inventory_coverage_days;
	}
	public function getMonthsToCover()
	{
		return $this->fgInventoryCoverageDays() / 30 ;
	}
	public function getFgBeginningInventoryBreakdowns():array
	{
		return $this->fg_beginning_inventory_breakdowns;
	}
	public function getFgBeginningInventoryBreakdownPercentageForType(string $inventoryType)
	{
		return $this->getFgBeginningInventoryBreakdowns()[$inventoryType]['percentage']??0;
	}
	public function getFgBeginningInventoryBreakdownValueForType(string $inventoryType)
	{
		return $this->getFgBeginningInventoryBreakdownPercentageForType($inventoryType)/100 * $this->getFgInventoryValue();
	}
	
}
