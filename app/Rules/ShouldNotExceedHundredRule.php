<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;

class ShouldNotExceedHundredRule implements ImplicitRule
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
        return Request('salaries_tax_rate') + Request('social_insurance_rate') < 100 ;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The Summation Salary Tax Rate & Social Insurance Rate Can Not Exceed 100%');
    }
}
