<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductExpenseAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_expense_allocations', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('product_id');
			$table->string('expense_type')->nullable();
			$table->string('expense_category')->nullable();
			$table->string('expense_name')->nullable();
			$table->boolean('is_expense')->default(1);
			$table->boolean('is_depreciation')->default(0);
			$table->json('payload')->nullable();
			$table->json('sensitivity_payload')->nullable();
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
        Schema::dropIfExists('product_expense_allocations');
    }
}
