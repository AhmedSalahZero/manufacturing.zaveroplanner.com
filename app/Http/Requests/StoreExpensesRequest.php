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
				
				if($currentExpenseArr['payment_terms'] == 'customize'){
					$newDueDays = [];
					$newPaymentRates = [];
					foreach($currentExpenseArr['due_days'] as $index => $dueDay){
						$currentPaymentRate = $currentExpenseArr['payment_rate'][$index]??0;
						if($currentPaymentRate){
							$newDueDays[$dueDay] = $dueDay ;
							$newPaymentRates[$dueDay] = isset($newPaymentRates[$dueDay]) ? $newPaymentRates[$dueDay]  +  $currentPaymentRate : $currentPaymentRate ;
						}
						 
					}
					$newDueDays = array_values($newDueDays);
					$newPaymentRates = array_values($newPaymentRates);
					$expensesFormatted['expenses'][$currentIndex]['due_days'] = $newDueDays;
					$expensesFormatted['expenses'][$currentIndex]['payment_rate'] = $newPaymentRates;
				}
				
				$currentIndex++;
			}
		}
	}
    $this->merge($expensesFormatted);
}
}
