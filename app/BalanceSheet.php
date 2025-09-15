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
			 'total_current_assets'=>'array'  , 
			 'total_current_liabilities'=>'array'  , 
			 'cash_and_banks'=>'array'  , 
			 'customer_receivables'=>'array'  , 
			 'total_fgs'=>'array'  , 
			 'raw_material_inventory'=>'array'  , 
			 'other_debtors'=>'array'  , 
			 'supplier_payables'=>'array'  , 
    ];
    
    public function project():BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
    

}
