<?php

namespace App\Providers;

use App\Helpers\HArr;
use App\Http\CustomResponse;
use App\Project;
use App\RawMaterial;
use App\ReadyFunctions\SeasonalityService;
use ErrorException;
use Illuminate\Database\Eloquent\Collection;
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
			Collection::macro('formattedForSelect',function(bool $isFunction , string $idAttrOrFunction ,string $titleAttrOrFunction ){
			/**
			 * @var Collection $this 
			 */
			return $this->map(function($item) use ($isFunction , $idAttrOrFunction ,$titleAttrOrFunction ){
				return [
					'value' => $isFunction ? $item->$idAttrOrFunction() : $item->{$idAttrOrFunction} ,
					'title' => $isFunction ? $item->$titleAttrOrFunction() : $item->{$titleAttrOrFunction}
				];
			})->toArray();
		});
    }
}
