<?php

namespace App;

use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashInOutStatement extends Model
{
    protected $guarded = ['id'];
	protected $casts = [
		'cash_end_balance'=>'array',
		'working_capital_injection'=>'array',
		'equity_injection'=>'array',
		'loan_withdrawal'=>'array',
		'customer_collection'=>'array',
		'supplier_payments'=>'array',
		'taxes'=>'array',
		'expenses'=>'array',
		'fixed_asset_payments'=>'array',
		'loan_installments'=>'array',
		'total_cash_out'=>'array',
	];
	
    public function project():BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
	public function getCashEndBalance():array 
	{
		return $this->cash_end_balance?:[];
	}
	
}
