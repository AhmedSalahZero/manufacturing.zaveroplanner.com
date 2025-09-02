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
		'collection_statement'=>'array',
		'credit_withhold_statement'=>'array',
		'inventory_value_statement'=>'array'
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
		return $this->beginning_inventory_value?:0;
	}
	public static function calculateInventoryQuantityStatement(int $projectId)
	{
		$inventoryQuantityStatement = new InventoryQuantityStatement;
		$dateIndexWithDate = Project::find($projectId)->getDateIndexWithDate();
		
		$rawMaterialIds = DB::table('product_raw_material')->where('project_id',$projectId)->pluck('raw_material_id','raw_material_id')->toArray();
		$productConsumedRawMaterials = Product::where('project_id',$projectId)->where('product_raw_material_consumed','!=',null)->pluck('product_raw_material_consumed')->toArray();
	
		foreach($rawMaterialIds as $rawMaterialId){
			$rawMaterial = RawMaterial::find($rawMaterialId);
			/**
			 * @var RawMaterial $rawMaterial
			 */
			$totalConsumed = HArr::sumAtDates(array_column($productConsumedRawMaterials,$rawMaterialId)) ;
			$beginningBalance = $rawMaterial->getBeginningInventoryValue();
			$monthsToCover = $rawMaterial->getRmInventoryCoverageDays() / 30;
			$currentInventoryQuantityStatement = $inventoryQuantityStatement->createInventoryQuantityStatement($totalConsumed,$beginningBalance,$monthsToCover); 
			$manufacturingQuantity = $currentInventoryQuantityStatement['manufacturing_quantity']??[];
			// $dueDayWithAnd
			// (new self)->calculateCollectionOrPaymentForMultiCustomizedAmounts();
			$collectionPolicyStatement = $rawMaterial->calculateMultiYearsCollectionPolicy($manufacturingQuantity,null);
			$withholdAmount = $collectionPolicyStatement['monthly']['withhold_amount']??[] ;
			$rawMaterial->update([
				'collection_statement'=>$collectionPolicyStatement,
				'credit_withhold_statement'=>$rawMaterial->calculateWithholdStatement($withholdAmount,0,$dateIndexWithDate),
				'inventory_value_statement'=>$currentInventoryQuantityStatement
			]);
	//		$inventoryQuantityStatements[$rawMaterialId]=$currentInventoryQuantityStatement;
		}
		
	}
	public function getSalesActiveYearsIndexWithItsMonths()
	{
		return $this->project->getOperationDurationPerYearFromIndexes();
	}
	public function getCollectionStatement():array
	{
		return $this->collection_statement?:[];
	}
	
}
