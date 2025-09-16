<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CollectionPolicyRule implements Rule
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
		for($i = 0 ; $i<= 1 ; $i++){
			$firstLocalCollectionPolicy = $value[$i]??[];
			$cashRate = $firstLocalCollectionPolicy['cash_payment'] ?? 0 ; 
			$rates = array_sum($firstLocalCollectionPolicy['rate']??[]);
			$validLocalCollection =   $cashRate + $rates  == 100 ;
			
			$firstExportCollectionPolicy = $value[$i]??[];
			$cashRate = $firstExportCollectionPolicy['cash_payment'] ?? 0 ; 
			$rates = array_sum($firstExportCollectionPolicy['rate']??[]);
			$validExportCollection =   $cashRate + $rates  == 100 ;
			if(!($validExportCollection && $validLocalCollection)){
				return false ; 
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
