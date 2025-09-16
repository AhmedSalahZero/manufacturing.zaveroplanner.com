<?php

namespace App\Http\Requests;

use App\Rules\CollectionPolicyRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRawMaterialPaymentsRequest extends FormRequest
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
			'rawMaterials.*.collection_policy_value'=>['required',new CollectionPolicyRule()]
        ];
    }
	protected function prepareForValidation(){
		$rawMaterialPayments  = $this->get('rawMaterials');
		

		foreach($rawMaterialPayments as $id => &$collectionPolicyArrs){
			foreach($collectionPolicyArrs['collection_policy_value'] as $collectionTypeString=>&$collectionArr){
				$dueWithRate = [];
				foreach($collectionArr['due_in_days'] as $currentDueIndex => $currentDueValue){
					$currentRate = $collectionArr['rate'][$currentDueIndex]; 
					$dueWithRate[$currentDueValue] = isset($dueWithRate[$currentDueValue]) ? $dueWithRate[$currentDueValue] + $currentRate : $currentRate;
				}
				$collectionArr['rate'] = array_values($dueWithRate);
				$collectionArr['due_in_days'] = array_keys($dueWithRate);
				
			}
		}
		
	
        $this->merge([
            'rawMaterials'=>$rawMaterialPayments,
        ]);
		
	}
}
