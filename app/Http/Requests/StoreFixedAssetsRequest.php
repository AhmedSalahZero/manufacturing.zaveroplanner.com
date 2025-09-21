<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class StoreFixedAssetsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'fixedAssets.*.'
        ];
    }
    protected function prepareForValidation()
    {
        $project = Request()->route('project');
        $fixedAssets = Request()->get('fixedAssets', []);
        foreach ($fixedAssets as $index => &$fixedAssetArr) {

			if(is_null($fixedAssetArr['name'])){
				unset($fixedAssets[$index]);
				continue;
			}
            $productIds = $fixedAssetArr['product_id']??[];
            $allocationPercentages = $fixedAssetArr['percentage']??[];
            $fixedAssetArr['product_allocations'] = $productAllocations =  array_combine($productIds, $allocationPercentages);
			$fixedAssetArr['is_as_revenue_percentages'] =$isAsRevenuePercentage = isset($fixedAssetArr['is_as_revenue_percentages']) ? Arr::first($fixedAssetArr['is_as_revenue_percentages']) : 0;	
			$fixedAssetArr['monthly_product_allocations'] = $isAsRevenuePercentage ? [] :   $project->calculateMonthlyProductAllocations($productAllocations);
			
            $fixedAssetArr['amount'] = number_unformat($fixedAssetArr['amount']);
            $fixedAssetArr['start_date'] = $project->getIndexDateFromString($fixedAssetArr['start_date'].'-01');
            $fixedAssetArr['end_date'] = $project->getIndexDateFromString($fixedAssetArr['end_date'].'-01');
			
            unset($fixedAssetArr['product_id']);
            unset($fixedAssetArr['percentage']);
			$fixedAssetArr['due_days'] = array_unique($fixedAssetArr['due_days']??[]);
			
            foreach ($fixedAssetArr['due_days']??[] as $index => $dueDay) {
                $paymentRate = $fixedAssetArr['payment_rate'][$index];
                $fixedAssetArr['custom_collection_policy'][$dueDay] = isset($fixedAssetArr['custom_collection_policy'][$dueDay]) ? $fixedAssetArr['custom_collection_policy'][$dueDay]+ $paymentRate :$paymentRate;
				$isFromTotal = $fixedAssetArr['from_total_or_executions'][$index] ?? 0 ;
				unset($fixedAssetArr['from_total_or_executions'][$index]);
				$fixedAssetArr['from_total_or_executions'][$dueDay] = $isFromTotal;
				
            }
			foreach($fixedAssetArr['from_total_or_executions']??[] as $currentDueDate => $isFromTotal){
				if(!in_array($currentDueDate,$fixedAssetArr['due_days'])){
					unset($fixedAssetArr['from_total_or_executions'][$currentDueDate]);	
				}
			}
			// foreach($fixedAssetArr['from_total_or_executions'] as )
			// if(!in_array($index,$fixedAssetArr['due_days'])){
			// 		unset([$index]);
			// 	}
        }
        $this->merge([
            'fixedAssets'=>$fixedAssets
        ]);
    
    }
}
