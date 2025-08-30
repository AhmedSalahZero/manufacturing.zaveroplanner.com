<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Seasonality extends Model
{
	protected $table ='seasonality';
	protected $guarded =[
		'id'
	];	
    protected $connection = 'mysql';
	protected $casts = [
		'percentages'=>'array',
		'distributed_percentages'=>'array'
	];
	public function getType()
	{
		return $this->type;
	}
}
