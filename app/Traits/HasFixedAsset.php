<?php
namespace App\Traits;


use App\FixedAsset;
use App\Project;


trait HasFixedAsset
{

	public function getFixedAssetStructureForFixAssetType(string $fixedAssetType)
    {
		/**
		 * @var Project $this
		 */
        if ($fixedAssetType == FixedAsset::FFE) {
            return [
				'loan_amount'=>$this->getLoanAmount(),
				'interest_rate'=>$this->getInterestRate(),
				'grace_period'=>$this->getGracePeriod(),
				'installment_interval'=>$this->getInstallmentInterval(),
				'tenor'=>$this->getTenor()
			];
        } 
		// elseif ($fixedAssetType == FixedAsset::NEW_BRANCH) {
        //     return $this->newBranchFixedAssetsFundingStructure;
        // } elseif ($fixedAssetType == FixedAsset::PER_EMPLOYEE) {
        //     return $this->perEmployeeFixedAssetsFundingStructure;
        // }
        dd('not supported fixed asset type');
        // return $this->fixedAssetsFundingStructure->where('fixed_asset_type',$fixedAssetType)->first();
    }
	
} 
