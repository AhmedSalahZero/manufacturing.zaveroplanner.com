<?php

use App\Product;
use App\Seasonality;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectIdToSeasonalityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('seasonality', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable();
        });
		foreach(Seasonality::get() as $seasonality){
			$product = Product::where('id',$seasonality->model_id)->first() ;
			if($product){
				$projectId = $product->project_id;
				$seasonality->project_id = $projectId ; 
				$seasonality->save();
			}
		}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('seasonality', function (Blueprint $table) {
            //
        });
    }
}
