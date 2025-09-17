<?php

namespace App;

use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LongTermLoanOpeningBalance extends Model
{
	use HasCollectionOrPaymentStatement;
    protected $guarded = ['id'];
	protected $casts = [
		'interests'=>'array',
		'installments'=>'array',
		'statement'=>'array'
	];
		public static function getOpeningBalanceColumnName():string
    {
        return 'amount';
    }
    public static function getPayloadStatementColumn():string
    {
        return 'installments';
    }
    public static function booted()
    {
        parent::boot();
        static::saving(function (self $model) {
            $openingBalance = $model->{self::getOpeningBalanceColumnName()};
            $statementPayload = $model->{self::getPayloadStatementColumn()};
            $dateIndexWithDate = $model->project->getDateIndexWithDate();
			if(!is_null($openingBalance)){
				$extendedStudyEndDate = $model->project->convertDateStringToDateIndex($model->project->getEndDate()) ;
				$dates = range(0,$extendedStudyEndDate);
				$model->statement = self::calculateSettlementStatement($dates,$statementPayload, [], $openingBalance, $dateIndexWithDate);
			}
        });
    }
	
    public function project():BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
	
    public function getAmount():float 
    {
        return $this->amount ;
    }
	public function getInterest():array 
	{
		return $this->interests??[] ;
	}
	public function getInterestAtDateIndex(int $dateAsIndex):float 
	{
		return $this->getInterest()[$dateAsIndex]??0;
	}
	public function getInstallment():array 
	{
		return $this->installments??[] ;
	}
	public function getInstallmentAtDateIndex(int $dateAsIndex):float 
	{
		return $this->getInstallment()[$dateAsIndex]??0;
	}
	
}
