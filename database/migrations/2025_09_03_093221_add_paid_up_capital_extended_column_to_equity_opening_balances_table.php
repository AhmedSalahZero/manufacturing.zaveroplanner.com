<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaidUpCapitalExtendedColumnToEquityOpeningBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equity_opening_balances', function (Blueprint $table) {
            $table->json('paid_up_capital_extended')->nullable()->after('paid_up_capital_amount');
            $table->json('legal_reserve_extended')->nullable()->after('paid_up_capital_amount');
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
