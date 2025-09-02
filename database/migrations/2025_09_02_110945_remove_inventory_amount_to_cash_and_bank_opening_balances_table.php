<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveInventoryAmountToCashAndBankOpeningBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_and_bank_opening_balances', function (Blueprint $table) {
            $table->removeColumn('inventory_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash_and_bank_opening_balances', function (Blueprint $table) {
            //
        });
    }
}
