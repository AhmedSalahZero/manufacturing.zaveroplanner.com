<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLongTermLoanOpeningBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('long_term_loan_opening_balances', function (Blueprint $table) {
            $table->id();
			$table->decimal('amount',14,2)->default(0);
			// $table->decimal('customer_receivable_amount',14,2)->default(0);
			// $table->decimal('inventory_amount',14,2)->default(0);
			$table->json('interests')->nullable();
			$table->json('installments')->nullable();
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
