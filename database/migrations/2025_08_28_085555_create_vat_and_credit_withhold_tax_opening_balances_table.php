<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVatAndCreditWithholdTaxOpeningBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vat_and_credit_withhold_tax_opening_balances', function (Blueprint $table) {
            $table->id();
			$table->decimal('vat_amount',14,2)->default(0);
			$table->decimal('credit_withhold_taxes',14,2)->default(0);
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
    }
}
