<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductRawMaterial extends Model
{
    protected $connection = 'mysql';
	protected $casts = [
		'percentages'=>'array',
	];
    protected $guarded = [];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
	public function product()
    {
        return $this->belongsTo(Product::class);
    }
	public function rawMaterial()
	{
		return $this->belongsTo(RawMaterial::class,'raw_material_id','id');
	}
	public function getPercentageAtYearIndex(int $yearAsIndex)
	{
		return $this->percentages[$yearAsIndex] ?? 0;
	}
}
