<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquityOpeningBalance extends Model
{
    protected $guarded = ['id'];

	protected $casts = [
		'payload'=>'array',
		'statement'=>'array',
	];
    public function project():BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
	
    public function getPaidUpCapitalAmount():float 
    {
        return $this->paid_up_capital_amount ;
    } 
	public function getLegalReserveAmount():float 
    {
        return $this->legal_reserve ;
    }
	public function getRetainedEarningAmount():float 
    {
        return $this->retained_earnings ;
    }
}
