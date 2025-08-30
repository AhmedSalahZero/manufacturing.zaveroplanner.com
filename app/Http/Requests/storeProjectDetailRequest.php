<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class storeProjectDetailRequest extends FormRequest
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
            //
        ];
    }
	public function prepareForValidation()
	{
		$endDate = $this->get('end_date');
		$year = explode('-',$endDate)[0];
		
		$this->merge([
			'start_date'=>$this->get('start_date').'-01',
			'operation_start_date'=>$this->get('operation_start_date').'-01',
			'end_date'=>$year .'-12-01',
			'extended_end_date' => ($year+1) .'-12-01'
		]);
	}
}
