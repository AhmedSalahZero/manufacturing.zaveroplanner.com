<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquityOpeningBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equity_opening_balances', function (Blueprint $table) {
            $table->id();
			$table->decimal('paid_up_capital_amount',14,2)->default(0);
			$table->decimal('legal_reserve',14,2)->default(0);
			$table->decimal('retained_earnings',14,2)->default(0);
			// $table->json('payload')->nullable();
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
