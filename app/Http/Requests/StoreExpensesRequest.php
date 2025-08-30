<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpensesRequest extends FormRequest
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
            
        ];
    }
	protected function prepareForValidation()
{
	$currentIndex= 0 ;
	$expensesFormatted =[];
	foreach($this->only(getExpensesTypes()) as $expenseTypeId =>$expenses){
		foreach($expenses as $currentExpenseIndex => $currentExpenseArr){
			if(isset($currentExpenseArr['name']) && $currentExpenseArr['name']){
				foreach($currentExpenseArr as $key => $value){
					$expensesFormatted['expenses'][$currentIndex][$key] = $value;					
				}
				$expensesFormatted['expenses'][$currentIndex]['type'] = $expenseTypeId;					
				$currentIndex++;
			}
		}
	}
    $this->merge($expensesFormatted);
}
}
