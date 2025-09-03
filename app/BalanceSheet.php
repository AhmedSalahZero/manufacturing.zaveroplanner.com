<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BalanceSheet extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
	         'change_in_customer_receivables'=>'array',
	         'change_in_fg_inventory'=>'array',
	         'change_in_raw_material_inventory'=>'array',
	         'change_in_other_debtors'=>'array',
	         'change_in_supplier_payables'=>'array',
	         'change_in_other_creditors'=>'array',
	         'net_change_in_working_capital'=>'array',        
	         'equity_funding_percentages'=>'array',        
	         'debit_funding_percentages'=>'array',        
    ];
    
    public function project():BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
    

}
