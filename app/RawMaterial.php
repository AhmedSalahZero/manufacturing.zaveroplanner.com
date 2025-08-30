<?php

namespace App;

use App\Helpers\HArr;
use App\ReadyFunctions\Manufacturing\InventoryQuantityStatement;
use App\Traits\HasCollectionOrPaymentStatement;
use App\Traits\HasCollectionPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class RawMaterial extends Model
{
	use HasCollectionPolicy,HasCollectionOrPaymentStatement;
    protected $connection = 'mysql';
    protected $guarded = [];
	protected $casts = [
		'collection_policy_value'=>'array',
		'collection_statement'=>'array'
	];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
	public function getName():string 
	{
		return $this->name ; 
	}
	public function products():BelongsToMany
	{
		return $this->belongsToMany(Product::class,'product_raw_material','raw_material_id','product_id')
		->withTimestamps()->withPivot(['percentages'])
		;
		
	}
	public function getRmInventoryCoverageDays()
	{
		return $this->rm_inventory_coverage_days ;
	}
	public function getMeasurementUnit()
	{
		return $this->measurement_unit ;
	}
	public function getPercentageAtYearAsIndex($yearAsIndex)
	{
		$percentages = $this->pivot->percentages;
		return json_decode($percentages)[$yearAsIndex]??0;
	}
		public function getVatRate():float 
	{
		return $this->vat_rate ;
	}
	public function getWithholdTaxRate():float 
	{
		return $this->withhold_tax_rate ;
	}
	public function getBeginningInventoryValue()
	{
		return $this->beginning_inventory_value;
	}
	public static function calculateInventoryQuantityStatement(int $projectId)
	{
		$inventoryQuantityStatement = new InventoryQuantityStatement;
		$rawMaterialIds = DB::table('product_raw_material')->where('project_id',$projectId)->pluck('raw_material_id','raw_material_id')->toArray();
		$productConsumedRawMaterials = Product::where('project_id',$projectId)->where('product_raw_material_consumed','!=',null)->pluck('product_raw_material_consumed')->toArray();
		$inventoryQuantityStatements = [];
		foreach($rawMaterialIds as $rawMaterialId){
			$rawMaterial = RawMaterial::find($rawMaterialId);
			$totalConsumed = HArr::sumAtDates(array_column($productConsumedRawMaterials,$rawMaterialId)) ;
			$beginningBalance = $rawMaterial->getBeginningInventoryValue();
			$monthsToCover = $rawMaterial->getRmInventoryCoverageDays() / 30;
			$currentInventoryQuantityStatement = $inventoryQuantityStatement->createInventoryQuantityStatement($totalConsumed,$beginningBalance,$monthsToCover); 
			$manufacturingQuantity = $currentInventoryQuantityStatement['manufacturing_quantity']??[];
			// $dueDayWithAnd
			// (new self)->calculateCollectionOrPaymentForMultiCustomizedAmounts();
			$collectionPolicyStatement = $rawMaterial->calculateMultiYearsCollectionPolicy($manufacturingQuantity);
			$rawMaterial->update([
				'collection_statement'=>$collectionPolicyStatement
			]);
	//		$inventoryQuantityStatements[$rawMaterialId]=$currentInventoryQuantityStatement;
		}
	//	dd($inventoryQuantityStatements);
		
	}
	public function getSalesActiveYearsIndexWithItsMonths()
	{
		return $this->project->getOperationDurationPerYearFromIndexes();
	}
	// public function calculateMultiYearsCollectionPolicy(array $manufacturingQuantity)
	// {
		// return $this->calculateMultiYearsCollectionPolicy($manufacturingQuantity);
		// $monthlySalesTargetValueBeforeVat  = $manufacturingQuantity;
        // $withholdRate = $this->getWithholdTaxRate() / 100;
        // $vatRate  = $this->getVatRate() / 100;
        // $withholdAmounts = HArr::MultiplyWithNumber($monthlySalesTargetValueBeforeVat, $withholdRate);
        // $vatAmounts = HArr::MultiplyWithNumber($monthlySalesTargetValueBeforeVat, $vatRate);
		// $monthlySalesTargetValueAfterVat = HArr::sumAtDates([$monthlySalesTargetValueBeforeVat,$vatAmounts], array_keys($monthlySalesTargetValueBeforeVat));
        // $salesActiveYearsIndexWithItsMonths=  $this->project->getOperationDurationPerYearFromIndexes();
        // $hasMultiYear = array_key_exists(1, $salesActiveYearsIndexWithItsMonths) ;
        // $monthlySalesTargetValueAfterVatForFirstYearMonths = array_intersect_key($monthlySalesTargetValueAfterVat, array_flip(array_keys($salesActiveYearsIndexWithItsMonths[0])));
        // $dueDayWithRates = $this->getDueDayWithRates(0);
        // $amountAfterVat = [];
        // $amountAfterVatForFirstYear = $this->calculateCollectionOrPaymentForMultiCustomizedAmounts($dueDayWithRates, $monthlySalesTargetValueAfterVatForFirstYearMonths);
        // $amountAfterVat = $amountAfterVatForFirstYear;
        // if ($hasMultiYear) {
        //     $secondYearStartMonthIndex = array_key_last(($salesActiveYearsIndexWithItsMonths[0])) + 1 ;
        //     $monthlySalesTargetValueAfterVatForMultiYearMonths = array_slice($monthlySalesTargetValueAfterVat, $secondYearStartMonthIndex, null, true);
        //     $dueDayWithRates = $this->getDueDayWithRates(1);
        //     $amountAfterVatForMultiYear = $this->calculateCollectionOrPaymentForMultiCustomizedAmounts($dueDayWithRates, $monthlySalesTargetValueAfterVatForMultiYearMonths);
        //     $amountAfterVat = HArr::sumAtDates([$amountAfterVatForFirstYear,$amountAfterVatForMultiYear], array_keys($monthlySalesTargetValueAfterVat));
        // }
		
		// $salesActiveYearsIndexWithItsMonths=  $this->getSalesActiveYearsIndexWithItsMonths();
        // $withholdAmountsForFirstYearMonths = array_intersect_key($withholdAmounts, array_flip(array_keys($salesActiveYearsIndexWithItsMonths[0])));
        // $dueDayWithRates = $this->getDueDayWithRates(0);
    
        // $withholdAmountsForFirstYear = $this->calculateCollectionOrPaymentForMultiCustomizedAmounts($dueDayWithRates, $withholdAmountsForFirstYearMonths);
        // $withholdPayments = $withholdAmountsForFirstYear;
        // if ($hasMultiYear) {
        //     $secondYearStartMonthIndex = array_key_last(($salesActiveYearsIndexWithItsMonths[0])) + 1 ;
        //     $withholdAmountsForMultiYearMonths = array_slice($withholdAmounts, $secondYearStartMonthIndex, null, true);
        //     $dueDayWithRates = $this->getDueDayWithRates(1);
        //     $amountAfterVatForMultiYear = $this->calculateCollectionOrPaymentForMultiCustomizedAmounts($dueDayWithRates, $withholdAmountsForMultiYearMonths);
        //     $withholdPayments = HArr::sumAtDates([$withholdAmountsForFirstYear,$amountAfterVatForMultiYear], array_keys($withholdAmounts));
        // }
        // $netPaymentsAfterWithhold = HArr::subtractAtDates([$amountAfterVat,$withholdPayments], array_keys($monthlySalesTargetValueBeforeVat));
		
        //     $collectionStatement = Product::calculateStatement($monthlySalesTargetValueBeforeVat, $vatAmounts, $netPaymentsAfterWithhold, $withholdPayments, $dateIndexWithDate);

		
		// return $amountAfterVat;
		// return HArr::sumAtDates([$amountAfterVatForFirstYear,$amountAfterVatForMultiYear],array_keys($monthlySalesTargetValueAfterVat));
		// dd($amountAfterVatForFirstYear,$amountAfterVatForMultiYear);
	// }
	
}
