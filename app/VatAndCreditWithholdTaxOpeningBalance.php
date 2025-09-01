<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VatAndCreditWithholdTaxOpeningBalance extends Model
{
    protected $guarded = ['id'];

	protected $casts = [
		
	];
    public function project():BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
	
    public function getVatAmount():float 
    {
        return $this->vat_amount ;
    } 
	public function getCreditWithholdTaxes():float 
    {
        return $this->credit_withhold_taxes ;
    }
	
}
