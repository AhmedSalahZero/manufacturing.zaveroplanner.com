<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsFixedOpeningColumnToProductExpenseAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_expense_allocations', function (Blueprint $table) {
            $table->boolean('is_opening_depreciation')->after('is_depreciation')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_expense_allocations', function (Blueprint $table) {
            //
        });
    }
}
