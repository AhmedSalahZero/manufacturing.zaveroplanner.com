<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalCurrentAssetsToBalanceSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('balance_sheets', function (Blueprint $table) {
            $table->json('total_current_assets')->nullable()->after('id');
            $table->json('total_current_liabilities')->nullable()->after('id');
            $table->json('cash_and_banks')->nullable()->after('id');
            $table->json('customer_receivables')->nullable()->after('id');
            $table->json('total_fgs')->nullable()->after('id');
            $table->json('raw_material_inventory')->nullable()->after('id');
            $table->json('other_debtors')->nullable()->after('id');
            $table->json('supplier_payables')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('balance_sheets', function (Blueprint $table) {
            //
        });
    }
}
