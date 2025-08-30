<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatementsColumnToCashAndBankOpeningBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach([
			'cash_and_bank_opening_balances',
			'equity_opening_balances',
			'long_term_loan_opening_balances',
			'other_credits_opening_balances',
			'other_debtors_opening_balances',
			'other_long_term_liabilities_opening_balances',
			'supplier_payable_opening_balances'
		] as $tableName){
			Schema::table($tableName, function (Blueprint $table) {
				$table->json('statement');;
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
        Schema::table('cash_and_bank_opening_balances', function (Blueprint $table) {
            //
        });
    }
}
