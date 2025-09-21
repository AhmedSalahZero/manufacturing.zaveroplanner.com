<?php

namespace App;

use App\Traits\HasCollectionOrPaymentStatement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
      use HasCollectionOrPaymentStatement;
    protected $guarded = ['id'];
    protected $casts = [
        'monthly_repeating_amounts'=>'array',
        'expense_as_percentages'=>'array',
        'payload'=>'array',
        'custom_collection_policy'=>'array',
        'revenue_stream_type'=>'array',
        'stream_category_ids'=>'array',
		'total_vat'=>'array',
		'total_after_vat'=>'array',
		'payment_amounts'=>'array',
		'collection_statements'=>'array',
		'net_payments_after_withhold'=>'array',
		'withhold_payments'=>'array',
		'withhold_amounts'=>'array',
		'product_allocations'=>'array',
		'prepaid_expense_statement'=>'array',
		'products'=>'array',
		'monthly_product_allocations'=>'array',
    ];
    
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
	
    public function model()
    {
        $modelName = '\App\Models\\'.$this->model_name ;
        return $this->belongsTo($modelName, 'model_id', 'id');
        
    }
   
    public function getName()
    {
        return $this->name ;
    }
    public function getCategoryName()
    {
        return $this->category_name ;
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
    public function getMonthlyAmount()
    {
        return $this->monthly_amount ?: 0 ;
    }
    public function getPaymentTerm()
    {
        return $this->payment_terms ;
    }
    public function getVatRate()
    {
        return $this->vat_rate ?: 0;
    }
    public function getWithholdTaxRate()
    {
        return $this->withhold_tax_rate?:0;
    }
    public function getIncreaseRate()
    {
        return $this->increase_rate ?: 0;
        
    }
    public function getIncreaseInterval()
    {
        return $this->increase_interval ;
    }
    public function getPayloadAtDate(string $date)
    {
        
        return $this->payload[$date] ?? 0 ;
    }

    public function getRevenueStreamTypes():array
    {
        return (array)$this->revenue_stream_type ;
    }
    public function getMonthlyPercentage()
    {
        return $this->monthly_percentage ?:0;
    }
    public function getMonthlyCostOfUnit()
    {
        return $this->monthly_cost_of_unit ?:0;
    }
    public function getDepartment()
    {
        // this must be multiple
        return '';
    }
    public function getEmployee()
    {
        // this must be multiple
        return '';
    }
    public function getInterval()
    {
        return $this->interval ;
    }
    public function getAllocationBaseOne()
    {
        return $this->allocation_base_1 ;
    }
    public function getAllocationBaseTwo()
    {
        return $this->allocation_base_2;
    }

    public function getAllocationBaseThree()
    {
        return $this->allocation_base_3;
    }
    public function getConditionalTo()
    {
        return $this->conditional_to ;
    }
    public function getConditionalValueA()
    {
        return $this->conditional_value_a ;
    }
    public function getConditionalValueB()
    {
        return $this->conditional_value_b ;
    }
    public function getPaymentRate(int $rateIndex)
    {
        return array_values($this->custom_collection_policy ?? [])[$rateIndex] ?? 0 ;
    }
    public function getPaymentRateAtDueInDays($rateIndex)
    {
        return array_keys($this->custom_collection_policy ?? [])[$rateIndex] ?? 0 ;
    }
    public function isDeductible()
    {
        return $this->is_deductible;
    }
    public function getAmount()
    {
        return $this->amount ?: 0 ;
    }
    public function getCategoryId()
    {
        return $this->category_id ;
    }
    public function getPercentageOf()
    {
        return $this->percentage_of;
    }
    public function getStreamCategoryIds():array
    {
        return (array)$this->stream_category_ids;
    }
    public function getPositionId()
    {
        return $this->position ? $this->position->id : 0 ;
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
		return $this->product_allocations[$productId]??null;
	}
	public function getProductArr():array 
	{
		return $this->products?:[];
	}
	public function isAsRevenuePercentage():bool 
	{
		return $this->is_as_revenue_percentages;
	}
	public function getAmortizationMonths():int 
	{
		return $this->amortization_months?:12;
	}
}
