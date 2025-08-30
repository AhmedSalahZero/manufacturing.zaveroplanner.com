<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBeginningInventoryValueToRawMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->decimal('beginning_inventory_value',14,2)->default(0)->after('id');
            $table->json('collection_policy_value')->nullable()->after('id');
			$table->string('collection_policy_type')->after('collection_policy_value')->default('customize');
			$table->string('collection_policy_interval')->after('collection_policy_type')->default('monthly');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            //
        });
    }
}
