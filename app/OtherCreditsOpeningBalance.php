<?php

namespace App;

use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtherCreditsOpeningBalance extends Model
{
	  use HasCollectionOrPaymentStatement;
    protected $guarded = ['id'];

	protected $casts = [
		'payload'=>'array',
		'statement'=>'array'
		
	];
	public static function getOpeningBalanceColumnName():string
    {
        return 'amount';
    }
    public static function getPayloadStatementColumn():string
    {
        return 'payload';
    }
    public static function booted()
    {
        parent::boot();
        static::saving(function (self $model) {
            $openingBalance = $model->{self::getOpeningBalanceColumnName()};
            $statementPayload = $model->{self::getPayloadStatementColumn()};
            $dateIndexWithDate = $model->project->getDateIndexWithDatE();
				$extendedStudyEndDate = $model->project->convertDateStringToDateIndex($model->project->getEndDate()) ;
				$dates = range(0,$extendedStudyEndDate);
				
            $model->statement = self::calculateSettlementStatement($dates,$statementPayload, [], $openingBalance, $dateIndexWithDate);
        });
    }
	
    public function project():BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
	
	public function getName()
	{
		return $this->name;
	}
    public function getAmount():float 
    {
        return $this->amount ;
    }
	public function getPayload():array 
	{
		return $this->payload ;
	}
	public function getPayloadAtDateIndex(int $dateAsIndex):float 
	{
		return $this->getPayload()[$dateAsIndex]??0;
	}
	
	
}
