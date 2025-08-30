<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
            // 'start_date' => 'required|date',
            'duration' => 'required',
            'end_date' => 'required',
            // 'business_sector_id' => 'required',
            // 'business_sub_sector_id' => 'required',
            // 'return_rate' => 'required',
            // 'service_first' => 'required',
            // 'service_second' => 'required',
            // 'service_third' => 'required',
            // 'service_fourth' => 'required',
            // 'service_fifth' => 'required',
            // 'first_capacity' => 'required',

            // 'second_rate' => 'required',
            // 'second_capacity' => 'required',

            // 'third_rate' => 'required',
            // 'third_capacity' => 'required',

            // 'fourth_rate' => 'required',
            // 'fourth_capacity' => 'required',

            // 'fifth_rate' => 'required',
            // 'fifth_capacity' => 'required',

        ];
    }
}
