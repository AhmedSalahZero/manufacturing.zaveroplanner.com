<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
			$table->json('local_target_quantity_percentages')->nullable();
			$table->json('export_target_quantity_percentages')->nullable();
            foreach(['growth_rates'=>'local_growth_rates','price_per_unit'=>'local_price_per_unit'] as $oldName => $newName){
				$table->renameColumn($oldName,$newName);
				
			}
			$table->json('export_growth_rates')->nullable();
			$table->json('export_price_per_unit')->nullable();
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
