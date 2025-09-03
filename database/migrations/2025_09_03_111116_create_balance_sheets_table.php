<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalanceSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('balance_sheets', function (Blueprint $table) {
            $table->id();
			foreach([
				'change_in_customer_receivables'=>'array',
				'change_in_fg_inventory'=>'array',
				'change_in_raw_material_inventory'=>'array',
				'change_in_other_debtors'=>'array',
				'change_in_supplier_payables'=>'array',
				'change_in_other_creditors'=>'array',
				'net_change_in_working_capital'=>'array',
				'debit_funding_percentages'=>'array',
				'equity_funding_percentages'=>'array',
				
			] as $columnName => $columnCast)
			{
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
        Schema::dropIfExists('balance_sheets');
    }
}
