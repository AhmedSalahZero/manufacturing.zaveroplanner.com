<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BusinessSector extends Model
{
    protected $connection = 'mysql';
	
	protected static function booted()
{
    static::addGlobalScope(function ($query) {
        $query
            ->orderBy('name_en');
    });
}

}
