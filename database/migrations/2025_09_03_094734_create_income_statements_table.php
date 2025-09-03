<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomeStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('income_statements', function (Blueprint $table) {
            $table->id();
			foreach([
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
				'annually_net_profit_revenue_percentages'=>'array',
				'accumulated_retained_earnings'=>'array',
				'total_depreciation'=>'array',
				'total_cogs'=>'array',
				'total_percentages_cogs'=>'array',
				'sganda'=>'array',
				'sganda_revenues_percentages'=>'array',
				
			] as $columnName => $type){
				
				$table->json($columnName)->nullable();
			}
			$table->unsignedBigInteger('project_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('income_statement');
    }
}
