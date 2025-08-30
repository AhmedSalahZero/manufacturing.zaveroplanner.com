<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVatRateToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['products','raw_materials'] as $tableName){
			Schema::table($tableName, function (Blueprint $table) {
            foreach(['vat_rate','withhold_tax_rate'] as $columnName){
				$table->decimal($columnName,14,4)->default(0);
			}
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
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
}
