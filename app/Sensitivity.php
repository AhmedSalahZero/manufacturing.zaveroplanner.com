<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sensitivity extends Model
{
    protected $connection = 'mysql';
    protected $guarded = [];
    public function project()
    {
        return $this->belongsTo('App\Project');
    }
}
