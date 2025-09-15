<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCapitalizedInterestsToFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->json('loan_capitalized_interests')->nullable()->after('ffe_loan_withdrawal');
            $table->json('income_statement_loan_capitalized_interests')->nullable()->after('ffe_loan_withdrawal');
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
