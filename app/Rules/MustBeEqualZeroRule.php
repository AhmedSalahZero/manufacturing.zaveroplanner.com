<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;

class MustBeEqualZeroRule implements ImplicitRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
	protected $number;
    public function __construct($number)
    {
        $this->number = $number ;
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
        return $this->number == 0 ;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Total Assets Must Be Equal To Total Liabilities + Owners Equity') . ' [ ' . number_format($this->number)  . ' ]';
    }
}
