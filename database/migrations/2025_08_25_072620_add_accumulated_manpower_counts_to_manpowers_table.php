<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccumulatedManpowerCountsToManpowersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manpowers', function (Blueprint $table) {
            $table->json('accumulated_manpower_counts')->nullable()->after('hirings');
            $table->json('salary_payments')->nullable()->after('hirings');
            $table->json('salary_expenses')->nullable()->after('hirings');
            // $table->json('social_insurances')->nullable()->after('hirings');
            $table->json('tax_and_social_insurance_statement')->nullable()->after('salary_expenses');
			
			
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
