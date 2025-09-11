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
			
        
            //
            foreach ($fixedAssetArr['due_days']??[] as $index => $dueDay) {
                $paymentRate = $fixedAssetArr['payment_rate'][$index];
                $fixedAssetArr['custom_collection_policy'][$dueDay] = $paymentRate;
				$isFromTotal = $fixedAssetArr['from_total_or_executions'][$index] ?? 0 ;
				unset($fixedAssetArr['from_total_or_executions'][$index]);
				$fixedAssetArr['from_total_or_executions'][$dueDay] = $isFromTotal;
            }
        }
        $this->merge([
            'fixedAssets'=>$fixedAssets
        ]);
    
    }
}
