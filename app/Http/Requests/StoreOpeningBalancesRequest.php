<?php

namespace App\Http\Requests;

use App\Project;
use App\Rules\AllocationMustBeHundredRule;
use App\Rules\MustBeEqualZeroRule;
use App\Rules\ShouldNotExceedHundredRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOpeningBalancesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
		$data  = Request()->all() ;
		$netFixedAsset = 0 ;
		$fixedAssetsArrs  = $data['fixedAssetOpeningBalances']??[];
		foreach($fixedAssetsArrs as $fixedAssetsArr){
			$currentNetFixedAsset = ($fixedAssetsArr['gross_amount']??0)  - ($fixedAssetsArr['accumulated_depreciation']??0);
			$netFixedAsset+= $currentNetFixedAsset ; 
		}
		$totalCashAndBanks  = array_sum(array_column($data['cashAndBankOpeningBalances']??[],'cash_and_bank_amount'));
		$totalCustomerReceivableAmount  = array_sum(array_column($data['cashAndBankOpeningBalances']??[],'customer_receivable_amount'));
		$totalInventoryAmount  = Project::find(Request()->segment(2))->getInventoryAmount() ;
		
		$totalOtherDebtorsAmount  = array_sum(array_column($data['otherDebtorsOpeningBalances']??[],'amount'));
		$totalSupplierPayableAmount  = array_sum(array_column($data['supplierPayableOpeningBalances']??[],'amount'));
		$totalCreditorPayableAmount  = array_sum(array_column($data['otherCreditorsOpeningBalances']??[],'amount'));
		$totalVatAmount  = array_sum(array_column($data['vatAndCreditWithholdTaxesOpeningBalances']??[],'vat_amount'));
		$totalWithholdAmount  = array_sum(array_column($data['vatAndCreditWithholdTaxesOpeningBalances']??[],'credit_withhold_taxes'));
		$totalLoanAmount  = array_sum(array_column($data['longTermLoanOpeningBalances']??[],'amount'));
		$totalOtherLongAmount  = array_sum(array_column($data['otherLongTermLiabilitiesOpeningBalances']??[],'amount'));
		$totalPaidUpAmount  = array_sum(array_column($data['equityOpeningBalances']??[],'paid_up_capital_amount'));
		$totalLegalReserveAmount  = array_sum(array_column($data['equityOpeningBalances']??[],'legal_reserve'));
		$totalRetainedEarningsAmount  = array_sum(array_column($data['equityOpeningBalances']??[],'retained_earnings'));
		$totalAssets = $netFixedAsset + $totalCashAndBanks + $totalCustomerReceivableAmount+$totalInventoryAmount+$totalOtherDebtorsAmount;
		$totalLiabilitiesAndEquity = $totalSupplierPayableAmount+$totalCreditorPayableAmount+$totalVatAmount+$totalWithholdAmount+$totalLoanAmount+$totalOtherLongAmount+$totalPaidUpAmount+$totalLegalReserveAmount+$totalRetainedEarningsAmount;
		
		return [
			'must_be_zero_rule'=> [new MustBeEqualZeroRule($totalAssets-$totalLiabilitiesAndEquity)]  
		];

    }
	
	protected function prepareForValidation()
    {
        // $project = Request()->route('project');
        $fixedAssets = Request()->get('fixedAssetOpeningBalances', []);
        foreach ($fixedAssets as $index => &$fixedAssetOpeningArr) {
            $productIds = $fixedAssetOpeningArr['product_id'];
            $allocationPercentages = $fixedAssetOpeningArr['percentage'];
            $fixedAssetOpeningArr['gross_amount'] = number_unformat($fixedAssetOpeningArr['gross_amount']);
            $fixedAssetOpeningArr['accumulated_depreciation'] = number_unformat($fixedAssetOpeningArr['accumulated_depreciation']);
            $fixedAssetOpeningArr['product_allocations'] = array_combine($productIds, $allocationPercentages);
            unset($fixedAssetOpeningArr['product_id']);
            unset($fixedAssetOpeningArr['percentage']);
        }
        $this->merge([
            'fixedAssetOpeningBalances'=>$fixedAssets
        ]);
    
    }
	
}
