<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
			$table->string('type');
			
			$table->string('name');
			$table->decimal('percentage')->nullable();
			$table->string('category_id');
			$table->string('start_date');
			$table->string('end_date');
			$table->string('payment_term');
			$table->json('allocations')->nullable();
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
        Schema::dropIfExists('expenses');
    }
}
