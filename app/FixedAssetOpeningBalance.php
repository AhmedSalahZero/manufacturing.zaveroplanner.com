<?php

namespace App;

use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedAssetOpeningBalance extends Model
{
	use HasCollectionOrPaymentStatement;
    protected $guarded = ['id'];
	protected $casts = [
		'product_allocations'=>'array',
		'admin_depreciations'=>'array',
		'manufacturing_depreciations'=>'array',
		'monthly_accumulated_depreciations'=>'array',
		'statement'=>'array',
		'monthly_product_allocations'=>'array',
	];
	
	public static function getOpeningBalanceColumnName():string
	{
		return 'gross_amount';
	}
	public static function getPayloadStatementColumn():string 
	{
		return 'monthly_accumulated_depreciations';
	}
	public static function booted()
	{
			parent::boot();
			static::saving(function(self $model){
				$statementPayload = $model->{self::getPayloadStatementColumn()} ?: [];
				$openingBalance = $model->{self::getOpeningBalanceColumnName()};
				$dateIndexWithDate = $model->project->getDateIndexWithDate();
				$extendedStudyEndDate = $model->project->convertDateStringToDateIndex($model->project->getEndDate()) ;
				$dates = range(0,$extendedStudyEndDate);
				$debug = false ;
				
				$model->statement = self::calculateSettlementStatement($dates,$statementPayload,[],$openingBalance,$dateIndexWithDate,true,$debug);
			});
	}
	
	
    public function project():BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
	
    public function getName():string 
    {
        return $this->name ;
    }
	public function getMonthlyCounts():int 
	{
		return $this->monthly_counts;
	}
	
	public function getGrossAmount():float
	{
		return $this->gross_amount;
	}
	public function getAccumulatedDepreciation():float
	{
		return $this->accumulated_depreciation?:0;
	}
	public function getNetAmount():float
	{
		return $this->getGrossAmount() - $this->getAccumulatedDepreciation();
	}
    public function getMonthlyDepreciation():float 
	{
		
		return $this->monthly_depreciation;
	}  public function getAdminDepreciationPercentage()
	{
		return $this->admin_depreciation_percentage;
	}
	 public function getManufacturingDepreciationPercentage()
	{
		return $this->manufacturing_depreciation_percentage;
	}
	public function getProductAllocationPercentageForTypeAndProduct(int $productId):?float{
		return $this->getProductAllocations()[$productId]??null;
	}
	public function getProductAllocations():array 
	{
		return $this->product_allocations;
	}
	public function isAsRevenuePercentage():bool 
	{
		return $this->is_as_revenue_percentages;
	}
}
