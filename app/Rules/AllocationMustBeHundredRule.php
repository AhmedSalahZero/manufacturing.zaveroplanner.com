<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;

class AllocationMustBeHundredRule implements ImplicitRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
	protected $error_title = null ;
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
        foreach(getManpowerTypes() as $id => $manpowerOptionArr){
			if($manpowerOptionArr['has_allocation']){
				$title = $manpowerOptionArr['title'];
				$rows = Request()->get($id);
				$totalAvgSalary = array_sum(array_column($rows,'avg_salary'));
				if($totalAvgSalary > 0){
					$isAsRevenuePercentage = Request()->input('manpower_is_as_revenue_percentages.'.$id,false);
					if($isAsRevenuePercentage){
						continue;
					}
					$totalAllocationPercentage = array_sum(Request()->input('manpower_allocations.'.$id.'.percentages',[]));
					
					if($totalAllocationPercentage != 100){
						$this->error_title = $title;
						return false ;						
					}
				}
				
				
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
        return __('Total Products Allocation Must Be 100% For [ :title ]',['title'=>$this->error_title]);
    }
}
