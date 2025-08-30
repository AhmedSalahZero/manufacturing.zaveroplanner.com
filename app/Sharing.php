<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sharing extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sharing_links';
    public function project()
    {
        return $this->belongsTo('App\Project', 'project_id');
    }
}
