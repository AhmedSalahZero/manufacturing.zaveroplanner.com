<?php

namespace App;

use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;

class ManPower extends Model
{
	use HasCollectionOrPaymentStatement;
    protected $connection = 'mysql';
    protected $guarded = [];
	protected $table ='manpowers';
	protected $casts = [
		'hirings'=>'array',
		'accumulated_manpower_counts'=>'array',
		'salary_payments'=>'array',
		'salary_expenses'=>'array',
		'tax_and_social_insurance_statement'=>'array',
	];
	public function project()
	{
		return $this->belongsTo(Project::class,'project_id','id');
	}
	public function getPositionName():string 
	{
		return $this->position ;
	}
	public function getAvgSalary()
	{
		return $this->avg_salary;
	}
	public function getExistingCount()
	{
		return $this->existing_count;
	}
	public function getHiringAtDate(string $date)
	{
		return $this->hirings[$date]??0;
	}
	public function getSalaryPayments():?array 
	{
		return $this->salary_payments;
	}
	public function getSalaryExpenses():?array 
	{
		return $this->salary_expenses;
	}
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    // protected $casts = [
    //     'general_first_capacity' => 'array',
    //     'sales_first_capacity' => 'array',
    //     'operational_salaries_first_capacity' => 'array',
    // ];
}
