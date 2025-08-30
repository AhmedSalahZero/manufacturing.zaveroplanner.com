<?php

namespace App\Http\Requests;

use App\Rules\AllocationMustBeHundredRule;
use App\Rules\ShouldNotExceedHundredRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreManpowerRequest extends FormRequest
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
            'salaries_tax_rate'=>new ShouldNotExceedHundredRule(),
			'allocation_must_be_hundred' => new AllocationMustBeHundredRule()
        ];
    }
}
