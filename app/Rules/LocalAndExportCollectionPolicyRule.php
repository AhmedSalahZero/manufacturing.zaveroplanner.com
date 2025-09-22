<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class LocalAndExportCollectionPolicyRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
		$project = Request()->route('project') ;
		$duration = $project->duration  > 1 ?  1 : 0 ;
		foreach(['local','export'] as $localOrExport){
			for($i = 0 ; $i<= $duration ; $i++){
			$firstLocalCollectionPolicy = Request('collection_policy_value')[$localOrExport][$i]??[];
			$cashRate = $firstLocalCollectionPolicy['cash_payment'] ?? 0 ; 
			$rates = array_sum($firstLocalCollectionPolicy['rate']??[]);
			$validLocalCollection =   $cashRate + $rates  == 100 ;
			$firstExportCollectionPolicy = Request('collection_policy_value')[$localOrExport][$i]??[];
			$cashRate = $firstExportCollectionPolicy['cash_payment'] ?? 0 ; 
			$rates = array_sum($firstExportCollectionPolicy['rate']??[]);
			$validExportCollection =   $cashRate + $rates  == 100 ;
			if(!($validExportCollection && $validLocalCollection)){
				return false ; 
			}
		}
		}
		return true ;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Collection Rates Must Be Equal 100%');
    }
}
