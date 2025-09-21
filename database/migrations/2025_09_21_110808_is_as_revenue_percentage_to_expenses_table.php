<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IsAsRevenuePercentageToExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ([
            'expenses',
            'fixed_assets',
            'fixed_asset_opening_balances'
        ] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('is_as_revenue_percentages')->default(1)->after('product_allocations');
                $table->json('monthly_product_allocations')->nullable()->after('product_allocations');
            });
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       
    }
}
