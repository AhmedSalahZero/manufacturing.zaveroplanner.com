<?php

namespace App\Providers;

use App\Helpers\HArr;
use App\Http\CustomResponse;
use App\Project;
use App\RawMaterial;
use App\ReadyFunctions\SeasonalityService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
		$this->app->bind(\Illuminate\Http\Response::class, CustomResponse::class);
//	echo phpinfo();
		// dd(RawMaterial::calculateInventoryQuantityStatement(133));		
    }
}
