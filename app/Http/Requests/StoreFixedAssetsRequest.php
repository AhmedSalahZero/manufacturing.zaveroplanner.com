<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            
        ];
    }
    protected function prepareForValidation()
    {
        $project = Request()->route('project');
        $fixedAssets = Request()->get('fixedAssets', []);
        foreach ($fixedAssets as $index => &$fixedAssetArr) {
            $productIds = $fixedAssetArr['product_id'];
            $allocationPercentages = $fixedAssetArr['percentage'];
            $fixedAssetArr['amount'] = number_unformat($fixedAssetArr['amount']);
            $fixedAssetArr['start_date'] = $project->getIndexDateFromString($fixedAssetArr['start_date'].'-01');
            $fixedAssetArr['end_date'] = $project->getIndexDateFromString($fixedAssetArr['end_date'].'-01');
            $fixedAssetArr['product_allocations'] = array_combine($productIds, $allocationPercentages);
            unset($fixedAssetArr['product_id']);
            unset($fixedAssetArr['percentage']);
			$fixedAssetArr['due_days'] = array_unique($fixedAssetArr['due_days']??[]);
			// if($fixedAssetArr['depreciation_duration'] == 0){
			// 	$fixedAssetArr
			// }
			
        
            //
            foreach ($fixedAssetArr['due_days']??[] as $index => $dueDay) {
                $paymentRate = $fixedAssetArr['payment_rate'][$index];
                $fixedAssetArr['custom_collection_policy'][$dueDay] = isset($fixedAssetArr['custom_collection_policy'][$dueDay]) ? $fixedAssetArr['custom_collection_policy'][$dueDay]+ $paymentRate :$paymentRate;
				$isFromTotal = $fixedAssetArr['from_total_or_executions'][$index] ?? 0 ;
				unset($fixedAssetArr['from_total_or_executions'][$index]);
				// dd($fixedAssetArr['from_total_or_executions'],$dueDay);
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
