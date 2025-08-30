<?php

namespace App\Http\Requests;

use App\Rules\AllocationMustBeHundredRule;
use App\Rules\ShouldNotExceedHundredRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOpeningBalancesRequest extends FormRequest
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
         //   'salaries_tax_rate'=>new ShouldNotExceedHundredRule(),
	//		'allocation_must_be_hundred' => new AllocationMustBeHundredRule()
        ];
    }
	
	protected function prepareForValidation()
    {
        // $project = Request()->route('project');
        $fixedAssets = Request()->get('fixedAssetOpeningBalances', []);
        foreach ($fixedAssets as $index => &$fixedAssetOpeningArr) {
            $productIds = $fixedAssetOpeningArr['product_id'];
            $allocationPercentages = $fixedAssetOpeningArr['percentage'];
            $fixedAssetOpeningArr['gross_amount'] = number_unformat($fixedAssetOpeningArr['gross_amount']);
            $fixedAssetOpeningArr['accumulated_depreciation'] = number_unformat($fixedAssetOpeningArr['accumulated_depreciation']);
            $fixedAssetOpeningArr['product_allocations'] = array_combine($productIds, $allocationPercentages);
            unset($fixedAssetOpeningArr['product_id']);
            unset($fixedAssetOpeningArr['percentage']);
        }
        $this->merge([
            'fixedAssetOpeningBalances'=>$fixedAssets
        ]);
    
    }
	
}
