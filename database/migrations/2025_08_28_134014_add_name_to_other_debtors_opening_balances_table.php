<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameToOtherDebtorsOpeningBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach([
			'other_debtors_opening_balances',
			'other_credits_opening_balances'
		] as $tableName){
			Schema::table($tableName, function (Blueprint $table) {
				$table->string('name')->after('id')->nullable();
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
        Schema::table('other_debtors_opening_balances', function (Blueprint $table) {
            //
        });
    }
}
