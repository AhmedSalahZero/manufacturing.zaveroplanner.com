<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomeStatement extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'total_sales_revenues'=>'array',
                'annually_sales_revenues_growth_rates'=>'array',
                'gross_profit'=>'array',
                'annually_gross_profit_revenue_percentages'=>'array',
                'ebitda'=>'array',
                'annually_ebitda_revenue_percentages'=>'array',
                'ebit'=>'array',
                'annually_ebit_revenue_percentages'=>'array',
                'ebt'=>'array',
                'annually_ebt_revenue_percentages'=>'array',
                'net_profit'=>'array',
                'annually_net_profit'=>'array',
                'annually_net_profit_revenue_percentages'=>'array',
                'accumulated_retained_earnings'=>'array',
                'total_depreciation'=>'array',
                'total_cogs'=>'array',
                'total_percentages_cogs'=>'array',
					'sganda'=>'array',
				'sganda_revenues_percentages'=>'array',
        
    ];
    
    public function project():BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
    

}
