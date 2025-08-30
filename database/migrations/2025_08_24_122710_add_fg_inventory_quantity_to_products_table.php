<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFgInventoryQuantityToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('fg_inventory_quantity',14,5)->after('fg_inventory_value')->default(0);
            $table->json('monthly_sales_target_quantities',14,5)->after('monthly_sales_target_values')->nullable();
            $table->json('sensitivity_monthly_sales_target_quantities',14,5)->after('monthly_sales_target_quantities')->nullable();
            $table->json('fg_beginning_inventory_breakdowns')->after('fg_inventory_value')->nullable();
            $table->json('product_inventory_qt_statement')->after('fg_beginning_inventory_breakdowns')->nullable();
            $table->json('product_inventory_value_statement')->after('fg_beginning_inventory_breakdowns')->nullable();
            $table->json('product_manpower_allocation')->after('fg_beginning_inventory_breakdowns')->nullable();
            $table->json('product_manpower_statement')->after('fg_beginning_inventory_breakdowns')->nullable();
			$table->json('product_overheads_allocation')->after('fg_beginning_inventory_breakdowns')->nullable();
            $table->json('product_overheads_statement')->after('fg_beginning_inventory_breakdowns')->nullable();
			
			$table->json('product_raw_material_consumed')->nullable();
        });
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
