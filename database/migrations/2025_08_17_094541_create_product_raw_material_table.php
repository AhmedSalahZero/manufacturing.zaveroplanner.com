<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductRawMaterialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_raw_material', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('product_id');
			$table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
			$table->unsignedBigInteger('raw_material_id');
			$table->foreign('raw_material_id')->references('id')->on('raw_materials')->cascadeOnDelete();
			$table->json('percentages')->nullable();
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
    }
}
