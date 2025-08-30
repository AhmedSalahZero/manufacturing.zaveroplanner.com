<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixedAssetOpeningBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixed_asset_opening_balances', function (Blueprint $table) {
            $table->id();
			$table->string('name');
			$table->decimal('gross_amount',14,2);
			$table->decimal('accumulated_depreciation',14,2)->default(0);
		//	$table->decimal('gross_depreciation',14,2)->default(0);
			$table->integer('monthly_counts')->default(0);
			$table->decimal('admin_depreciation_percentage',14,2)->default(0);
			$table->decimal('manufacturing_depreciation_percentage',14,2)->default(0);
			$table->json('product_allocations')->nullable();
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
        Schema::dropIfExists('fixed_asset_opening_balances');
    }
}
