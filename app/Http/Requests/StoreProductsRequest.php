<?php

namespace App\Http\Requests;

use App\Rules\LocalAndExportCollectionPolicyRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductsRequest extends FormRequest
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
			'collection_policy_value'=>['required',new LocalAndExportCollectionPolicyRule()]
        ];
    }
	protected function prepareForValidation(){
		$collectionPolicyValue = $this->get('collection_policy_value');
		foreach($collectionPolicyValue as $localOrExport => &$collectionPolicyArrs){
			foreach($collectionPolicyArrs as $loopIndex=>&$collectionArr){
				$dueWithRate = [];
				foreach($collectionArr['due_in_days'] as $currentDueIndex => $currentDueValue){
					$currentRate = $collectionArr['rate'][$currentDueIndex]; 
					$dueWithRate[$currentDueValue] = isset($dueWithRate[$currentDueValue]) ? $dueWithRate[$currentDueValue] + $currentRate : $currentRate;
				}
				$collectionArr['rate'] = array_values($dueWithRate);
				$collectionArr['due_in_days'] = array_keys($dueWithRate);
				
			}
		}
		$rawMaterials = [];
		$product = $this->route('product');
		$years = $product->getViewYearIndexWithYear();
		$yearsAsIndexes =array_keys($years);
		 foreach ($this->get('rawMaterials') as $index => &$rawMaterialArr) {
			$rawMaterialArr['product_id'] = $product->id;
            if ($rawMaterialArr['raw_material_id']) {
				$rawMaterialArr['percentages'] = array_combine($yearsAsIndexes,$rawMaterialArr['percentages']); 
                $rawMaterials[$index] = $rawMaterialArr;
            }
        }
        $this->merge([
            'rawMaterials'=>$rawMaterials,
			'collection_policy_value'=>$collectionPolicyValue
        ]);
		
	}
}
