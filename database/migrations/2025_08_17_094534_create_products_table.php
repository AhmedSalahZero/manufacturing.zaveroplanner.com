<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
			$table->string('name');
			// $table->tinyInteger('selling_start_month');
			// $table->tinyInteger('selling_start_year');
			$table->date('selling_start_date');
			$table->json('max_capacity')->nullable();
			$table->json('target_percentages')->nullable();
			$table->json('target_quantities')->nullable();
			$table->json('growth_rates')->nullable();
			$table->json('price_per_unit')->nullable();
			$table->json('sales_target_values')->nullable();
	
			// $table->string('seasonality_type')->nullable();
			// $table->json('quarterly_seasonality')->nullable();
			// $table->json('monthly_seasonality')->nullable();
			// $table->json('raw_materials')->nullable();
			$table->string('fg_inventory_value')->nullable();
			$table->string('fg_inventory_coverage_days')->nullable();
			$table->json('collection_down_payments')->nullable();
			$table->json('collection_rates')->nullable();
			$table->json('collection_due_days')->nullable();
					$table->longText('comment_for_sales')->nullable();
			$table->longText('comment_for_raw_material')->nullable();
			$table->longText('comment_for_collections')->nullable();
			
			$table->unsignedBigInteger('project_id');
			$table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
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
        Schema::dropIfExists('product_sales');
    }
}
