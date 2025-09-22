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
		$isNewCompany = $this->new_company;
		$operationStartDateValidation = 'date_equals:start_date';
		if($isNewCompany){
			$operationStartDateValidation ='after_or_equal:start_date';
		}
		$duration = $this->project->duration ;
		$perpetualIsRequired = $duration >  1  ? 'required' : 'sometimes';
        return [
            'operation_start_date'=>['required',$operationStartDateValidation,'before:end_date'],
			'products.*.selling_start_date'=>['required','after_or_equal:operation_start_date','before:end_date'],
			'products.*.name'=>['required'],
			'rawMaterials.*.name'=>['required'],
			'return_rate'=>[$perpetualIsRequired,'gt:0'], 
			'perpetual_growth_rate'=>[$perpetualIsRequired,'gt:0'], 
        ];
    }
	public function messages()
	{
		return [
			'operation_start_date.required'=>__('Please Enter Operation Start Date'),
			'operation_start_date.date_equals'=>__('Operation Start Date Must Be Equal To Study Start Date'),
			'operation_start_date.after_or_equal'=>__('Operation Start Date Must Be Greater Than Or Equal Study Start Date'),
			'operation_start_date.before'=>__('Operation Start Date Must Be Less Than Study End Date'),
			
			'products.*.name.required'=>__('Please Enter Product Name'),
			'products.*.selling_start_date.required'=>__('Please Enter Selling Start Date'),
			'products.*.selling_start_date.after_or_equal'=>__('Selling Start Date Must Be Greater Than Or Equal Study Start Date'),
			'products.*.selling_start_date.before'=>__('Selling Start Date Must Be Less Than Study End Date'),
			
				'rawMaterials.*.name.required'=>__('Please Enter Raw Material Name'),
				
				'return_rate.required'=>__('Please Enter Required Investment Return %'),
				'return_rate.gt'=>__('Required Investment Return Must Be Greater Than Zero'),		
				
				'perpetual_growth_rate.required'=>__('Please Enter Perpetual Growth Rate %'),
				'perpetual_growth_rate.gt'=>__('Perpetual Growth Rate Must Be Greater Than Zero'),
		];
	}
	public function prepareForValidation()
	{
		$endDate = $this->get('end_date');
		$year = explode('-',$endDate)[0];
		if(!$year){
			return [];
		}
		$this->merge([
			'start_date'=>$this->get('start_date').'-01',
			'operation_start_date'=>$this->get('operation_start_date').'-01',
			'end_date'=>$year .'-12-01',
			'extended_end_date' => ($year+1) .'-12-01'
		]);
	}
}
