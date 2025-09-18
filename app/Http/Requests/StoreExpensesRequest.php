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
	$project = $this->route('project');
	$dateIndexWithDates = app('dateWithDateIndex');
	foreach($this->only(getExpensesTypes()) as $expenseTypeId =>$expenses){
		foreach($expenses as $currentExpenseIndex => $currentExpenseArr){
			if(isset($currentExpenseArr['name']) && $currentExpenseArr['name']){
				foreach($currentExpenseArr as $key => $value){
					$expensesFormatted['expenses'][$currentIndex][$key] = $value;					
				}
				if (isset($currentExpenseArr['start_date']) && count(explode('-', $currentExpenseArr['start_date'])) == 2) {
                   $currentExpenseArr['start_date'] = $currentExpenseArr['start_date'].'-01';
					$expensesFormatted['expenses'][$currentIndex]['start_date'] = $dateIndexWithDates[$currentExpenseArr['start_date']];	
                    
                }if (isset($currentExpenseArr['end_date']) && count(explode('-', $currentExpenseArr['end_date'])) == 2) {
                    $currentExpenseArr['end_date'] = $currentExpenseArr['end_date'].'-01';
					// $project->getDate
						$expensesFormatted['expenses'][$currentIndex]['end_date'] =$dateIndexWithDates[$currentExpenseArr['end_date']] ;	
                }
				$expensesFormatted['expenses'][$currentIndex]['type'] = $expenseTypeId;					
				
				
				  $productIds = $currentExpenseArr['product_id'];
                $allocationPercentages = $currentExpenseArr['percentage'];
                $productAllocations = array_combine($productIds, $allocationPercentages);
				$expensesFormatted['expenses'][$currentIndex]['product_allocations'] = $productAllocations;			
				
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
				$expensesFormatted['expenses'][$currentIndex]['collection_statements'] = [];
				$expensesFormatted['expenses'][$currentIndex]['project_id'] = $project->id;
				$expensesFormatted['expenses'][$currentIndex]['model_id'] = $project->id;
				$expensesFormatted['expenses'][$currentIndex]['model_name'] = 'Project';
				$expensesFormatted['expenses'][$currentIndex]['expense_type'] = 'Expense';
				$expensesFormatted['expenses'][$currentIndex]['relation_name'] =$expenseTypeId;
	
				
				$currentIndex++;
			}
		}
	}
	
    $this->merge($expensesFormatted);
}
}
