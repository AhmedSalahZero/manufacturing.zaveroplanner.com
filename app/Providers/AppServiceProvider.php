<?php

namespace App\Providers;

use App\Helpers\HArr;
use App\Http\CustomResponse;
use App\Project;
use App\RawMaterial;
use App\ReadyFunctions\SeasonalityService;
use ErrorException;
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
		// error_reporting(E_ALL); // فعل كل الأخطاء
		// ini_set('display_errors', 1);
		// set_error_handler(function ($severity, $message, $file, $line) {
		// 	if (error_reporting() & $severity) {
		// 		throw new ErrorException($message, 0, $severity, $file, $line);
		// 	}
		// });
		// $this->app->bind(\Illuminate\Http\Response::class, CustomResponse::class);
//	echo phpinfo();
		// dd(RawMaterial::calculateInventoryQuantityStatement(133));		
    }
}
