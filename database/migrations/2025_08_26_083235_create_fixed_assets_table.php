<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            // $table->id();
			// $table->string('name');
			// $table->integer('counts');
			// $table->decimal('amount',14,2)->default(0);
			// $table->integer('depreciation_duration');
			// $table->integer('start_date');
			// $table->integer('end_date');
			// $table->string('payment_terms');
			// $table->decimal('admin_depreciation_percentage',14,2)->default(0);
			// $table->decimal('manufacturing_depreciation_percentage',14,2)->default(0);
			// $table->decimal('equity_funding_rate',14,2)->default(0);
			// $table->decimal('interest_rate',14,2)->default(0);
			// $table->integer('tenor');
			// $table->string('installment_interval');
			// $table->unsignedBigInteger('project_id');
			// $table->json('product_allocations')->nullable();
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fixed_assets');
    }
}
