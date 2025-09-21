@foreach(count($fixedAssets) ? $fixedAssets : [null] as $fixedAsset)
<div data-repeater-item class="form-group  row align-items-center 
 m-form__group
closest-parent
 repeater_item
common-parent
 ">

    <input type="hidden" name="id" value="{{ isset($fixedAsset) ? $fixedAsset->id:0 }}">
    <div class="col-md-2 pr-2 pl-4">
        <label class="form-label ">{{ __('Name') }} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="text" class="form-control name-field-js"   name="name" value="{{ isset($fixedAsset) ? $fixedAsset->getName() : old('name') }}">
            </div>
        </div>
    </div>

    <div class="max-w-10 pr-2 pl-2">
        <label class="form-label ">{{ __('Count') }} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="text" class="form-control number_field_1 only-greater-than-or-equal-zero-allowed " name="counts" value="{{ isset($fixedAsset) ? $fixedAsset->getCounts() : old('counts',0) }}" step="1">
            </div>
        </div>
    </div>

    <div class="max-w-10 pr-2 pl-2">
        <label class="form-label ">{{ __('Amount') }} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="text" class="form-control number_field_2 only-greater-than-or-equal-zero-allowed name-required-when-greater-than-zero-js" name="amount" value="{{ isset($fixedAsset) ? $fixedAsset->getAmount() : old('amount',0) }}" step="0.5">
            </div>
        </div>
    </div>

    <div class="max-w-15 pr-2 pl-2">
        <label class="form-label ">{{ __('Total') }} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input data-number-format="0" readonly type="text" class="form-control number_multiple_number" value="{{ 0 }}" step="0.5">
            </div>
        </div>
    </div>


    <div class="max-w-15 pr-2 pl-2 closest-parent">
        <label class="form-label ">{{ __('Depreciation Duration') }} </label>
        @php
        $currentVal = $fixedAsset ? $fixedAsset->getDepreciationDuration() : null;
        @endphp
        <select name="depreciation_duration" class="form-control">
            <option value="0" selected>{{ __('No Depreciation') }}</option>
            @for($year = 3 ; $year<=25 ;$year++) <option value="{{ $year }}" @if($currentVal==$year) selected @endif> {{ $year . ' ' . __('Years')  }} </option>
                @endfor
        </select>

    </div>


    <div class="max-w-12 pr-2 pl-2">
        <label class="form-label ">{{ __('Start Date') }} </label>

        @include('components.calendar-month-year',[
        'name'=>'start_date',
        'value'=>$fixedAsset ? $fixedAsset->getStartDateYearAndMonth() : now()->format('Y-m')
        ])


    </div>

    <div class="max-w-12 pr-2 pl-2">
        <label class="form-label ">{{ __('End Date') }} </label>
        @include('components.calendar-month-year',[
        'name'=>'end_date',
        'value'=>$fixedAsset ? $fixedAsset->getEndDateYearAndMonth() : now()->format('Y-m')
        ])

    </div>



    <div class="col-md-2 pr-2 pl-4 mt-4">
        <label class="form-label "> {!! __('Administration <br> Depreciation %') !!} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="text" class="form-control only-greater-than-or-equal-zero-allowed hundred-minus-number-one" name="admin_depreciation_percentage" value="{{ isset($fixedAsset) ? $fixedAsset->getAdminDepreciationPercentage() : old('admin_depreciation_percentage',0) }}" step="0.5">
            </div>
        </div>
    </div>

    <div class="max-w-10 pr-2 pl-2 mt-4">
        <label class="form-label ">{!! __('Manufacturing <br> Depreciation %') !!} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="text" readonly class="form-control  only-greater-than-or-equal-zero-allowed hundred-minus-number-result-one" name="manufacturing_depreciation_percentage" value="{{ isset($fixedAsset) ? $fixedAsset->getManufacturingDepreciationPercentage() : old('manufacturing_depreciation_percentage',0) }}" step="0.5">
            </div>
        </div>
    </div>


    <div class="max-w-10 pr-2 pl-2 mt-4  allocate-parent">
        <label class="form-label  ">{!! __('Products <br> Allocation') !!}</label>
        <div class="kt-input-icon ">
            <div class="input-group ">
                <button class="btn btn-primary btn-md allocate-parent-trigger text-nowrap w-full" type="button" data-toggle="modal" data-target="#modal-allocate-{{ $repeaterId }}">{{ __('Allocate') }}</button>
            </div>
        </div>

		@include('expenses._allocate_modal',['subModel'=>$fixedAsset])
        {{-- <div class="modal fade allocate-parent-modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel-{{ $repeaterId }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header header-border">
                        <h5 class="modal-title font-size-1rem">{{ __('Allocate') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <table class="table w-full closest-parent">
                            <tbody>

                                @foreach($products as $product)
                                @php
                                $percentage = $fixedAsset ? $fixedAsset->getProductAllocationPercentageForTypeAndProduct($product->id) : null;
                                $percentage = is_null($percentage) ? 1/count($products)*100 : $percentage;
                                @endphp
                                <tr>

                                    <td>
                                        <div class="form-group d-flex dep-parent  text-center">

                                            <div class="col-9 text-left">
                                                <label>{{ __('Product') }}</label>
                                                <input readonly class="form-control" type="text" value="{{ $product->getName() }}">
                                                <input multiple class="form-control product-id-class" data-product-id="{{ $product->id }}" name="product_id" type="hidden" value="{{ $product->id }}">
                                            </div>
                                            <div class="col-3 text-left">
                                                <label>{{ __('Perc.%') }}</label>
                                                <input multiple class="form-control percentage-allocation total_input input-border" name="percentage" value="{{ $percentage }}">
                                            </div>


                                        </div>
                                    </td>

                                </tr>
                                @endforeach

                                <tr>

                                    <td>
                                        <div class="form-group d-flex   text-center">

                                            <div class="col-9 text-left">
                                                <label>{{ __('Total') }}</label>
                                                <input readonly class="form-control" type="text" value="{{ __('Total') }}">
                                            </div>
                                            <div class="col-3	 text-left">
                                                <label>{{ __('Total %') }}</label>
                                                <input readonly class="form-control must-not-exceed-100 total_row_result input-border" value="{{ 0 }}">
                                            </div>


                                        </div>
                                    </td>

                                </tr>


                            </tbody>
                        </table>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn save-modal btn-primary" data-dismiss="modal">{{ __('Save') }}</button>
                    </div>

                </div>
            </div>
        </div> --}}

    </div>


    <div class="max-w-15 pr-2 pl-2 mt-4">
        <label class="form-label "> {!! __('Replacement <br> Cost %') !!} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="text" class="form-control only-percentage-allowed " name="replacement_cost_rate" value="{{ isset($fixedAsset) ? $fixedAsset->getReplacementCostRate() : old('replacement_cost_rate',0) }}" step="any">
            </div>
        </div>
    </div>

    <div class="max-w-15 pr-2 pl-2 mt-4 closest-parent">
        <label class="form-label "> {!! __('Replacement <br> Interval') !!} </label>
        <select name="replacement_cost_interval" class="form-control">
            @for($year = 1 ; $year<=5 ;$year++) <option value="{{ $year }}" @if($fixedAsset ? $fixedAsset->getReplacementInterval() ==$year : false ) selected @endif> {{ $year . ' ' . __('Years')  }} </option>
                @endfor
        </select>

    </div>



    <div class="max-w-20 pr-2 pl-2 mt-4 closest-parent">
        <label class="form-label "> {!! __('Payment <br> Term') !!} </label>
        <x-form.select :selectedValue="isset($fixedAsset) ? $fixedAsset->getPaymentTerm() : 'cash'" :options="getPaymentTermsForFixedAssets()" :add-new="false" class="select2-select repeater-select payment_terms " :all="false" name="payment_terms"></x-form.select>
        <x-modal.fixed-asset-custom-collection :title="__('Custom Payment')" :subModel="isset($fixedAsset) ? $fixedAsset : null "></x-modal.fixed-asset-custom-collection>
    </div>
    <div class="col-md-2 pr-2 pl-4 mt-4">
        <label class="form-label ">{{ __('Equity Funding %') }} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input value="{{  isset($fixedAsset) ? $fixedAsset->getEquityFundingRate() : old('equity_funding_rate')  }}" name="equity_funding_rate" type="text" class="form-control only-percentage-allowed reclculate-equity-amount   hundred-minus-number" step="0.1">
            </div>
        </div>

    </div>

    <div class="max-w-10 pr-2 pl-2 mt-4 ">
        <label class="form-label ">{{ __('Debt Funding %') }} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input readonly type="text" class="form-control loan-form-trigger hundred-minus-number-result" value="{{   isset($fixedAsset) ?100- $fixedAsset->getEquityFundingRate() : 0  }}" step="0.1">
            </div>
        </div>
    </div>


    <div class="max-w-10 pr-2 pl-2 mt-4">
        <label class="form-label ">{{ __('Interest %') }} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input name="interest_rate" type="text" class="form-control only-percentage-allowed " value="{{  $fixedAsset ? $fixedAsset->getInterestRate() : 0  }}" step="0.1">
            </div>
        </div>

    </div>


    <div class="max-w-15 pr-2 pl-2 mt-4">
        <label class="form-label ">{{ __('Grace Period (Months)') }} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input name="grace_period" type="text" class="form-control only-greater-than-or-equal-zero-allowed " value="{{  $fixedAsset ? $fixedAsset->getGracePeriod() : 0  }}" step="1">
            </div>
        </div>
    </div>

    <div class="max-w-15 pr-2 pl-2 mt-4">
        <label class="form-label ">{{ __('Tenor (Months)') }} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input name="tenor" type="text" class="form-control only-greater-than-zero-allowed " value="{{  $fixedAsset ? $fixedAsset->getTenor() : 60  }}" step="1">
            </div>
        </div>
    </div>

    <div class="max-w-20 pr-2 pl-2 mt-4 ">
        <label class="form-label ">{{ __('Installment Interval') }} </label>
        @php
        $currentVal = $fixedAsset ? $fixedAsset->getInstallmentInterval():'monthly';
        @endphp
        <select name="installment_interval" class="form-control ">
            @foreach( ['monthly'=>__('Monthly'),'quartly'=>__('Quarterly'),'semi annually'=>__('Semi-annually'),'annually'=>__('Annually')] as $id => $title)
            <option {{ $id == $currentVal ? 'selected'  : ''}} value="{{ $id }}">{{ $title }}</option>
            @endforeach
        </select>
    </div>

    <div style="max-width:40px;" class=" ">
        <div class="d-flex flex-column">
            <label for="" class="visibility-hidden">delete</label>
            <input data-repeater-delete type="button" class="btn btn-danger btn-md ml-2" value="{{ __('X') }}">
        </div>
    </div>
    <div class="col-md-12">
        <hr style="background-color:blue;margin-bottom:30px">
    </div>
    {{-- <div class="">
        <i data-repeater-delete="" class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill trash_icon fas fa-times-circle">

        </i>
    </div> --}}
</div>
@endforeach
