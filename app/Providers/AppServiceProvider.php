<?php

namespace App\Providers;

use App\Helpers\HArr;
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
	echo phpinfo();
		// dd(RawMaterial::calculateInventoryQuantityStatement(133));		
    }
}
