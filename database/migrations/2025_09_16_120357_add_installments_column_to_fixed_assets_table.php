<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInstallmentsColumnToFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->decimal('reservation_rate',14,2)->default(0);
            $table->decimal('contractual_rate',14,2)->default(0);
            $table->integer('after_months')->default(0);
 //           $table->decimal('remaining_balance_rate',14,2)->default(0);
            $table->decimal('installment_grace_period',14,2)->default(0);
            $table->integer('installment_count')->default(0);
            $table->string('payment_installment_interval')->nullable();
        });
    }

   
    public function down()
    {
    }
}
