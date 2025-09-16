@props([
'subModel',
'title'
])

<div class="modal collection-modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-blue" id="exampleModalLongTitle">{{ $title }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="customize-elements">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-center text-nowrap">{{ __('Payment Rate %') }}</th>
                                <th class="text-center text-nowrap">{{ __('Due In Days') }}</th>
                                <th class="text-center text-nowrap">{{ __('From Total Amount') }}</th>
                                <th class="text-center text-nowrap">{{ __('From Execution Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($rateIndex= 0 ;$rateIndex<5 ; $rateIndex++) <tr class="closest-parent">
                                @php
                                $dueInDay = isset($subModel) ? $subModel->getPaymentRateAtDueInDays($rateIndex) : 0 ;
                                @endphp
                                <td>
                                    <div class="max-w-selector-popup">
                                        <input multiple name="payment_rate" class="form-control only-percentage-allowed rate-element" value="{{ isset($subModel) ? $subModel->getPaymentRate($rateIndex) :  0 }}" placeholder="{{ __('Rate') .  ' ' . $rateIndex }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="">
                                        <x-form.select :multiple="true" :maxOptions="1" :selectedValue="$dueInDay" :options="dueInDays()" :add-new="false" class="js-due_in_days repeater-select js-select2-with-one-selection" :all="false" name="due_days"></x-form.select>
                                    </div>
                                </td>

                                <td class="text-center">

                                    <div class="kt-radio-inline">

                                        <label class="kt-radio kt-radio--success text-black font-size-18px">
                                            @php
                                            $isFromTotal = isset($subModel) && $subModel->isFromTotal($dueInDay) ;
                                            @endphp
                                            <input type="checkbox" class="parent-checkbox" value="1" name="from_total_or_executions" @if( $isFromTotal) checked @endisset>
                                            <span></span>
                                        </label>




                                    </div>


                                </td>
                                <td class="text-center">
                                    <div class="kt-radio-inline">
                                        <label class="kt-radio kt-radio--danger text-black font-size-18px">
                                            <input class="parent-checkbox" type="checkbox" value="0" name="from_total_or_executions" @if(!$isFromTotal) checked @endisset>
                                            <span></span>
                                        </label>


                                    </div>
                                </td>
                                </tr>
                                @endfor
                                <tr style="border-top:1px solid gray;padding-top:5px;text-align:center">
                                    <td class="td-for-total-payment-rate " disabled readonly>
                                        0
                                    </td>
                                    <td class="">-</td>
                                    <td class="">-</td>
                                    <td class="">-</td>
                                </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn save-modal btn-primary" data-dismiss="modal">{{ __('Save') }}</button>
            </div>
        </div>
    </div>
</div>


<div class="modal installment-modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-blue" id="exampleModalLongTitle">{{ __('Installments') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row row-gap" >
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Reservation %') }} </label>
                        <div class="kt-input-icon">
                            <div class="input-group">
                                <input type="text" class="form-control only-greater-than-or-equal-zero-allowed hundred-minus-number1" name="reservation_rate" value="{{ isset($subModel) ? $subModel->getReservationRate() : old('reservation_rate') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ __('Contractual %') }} </label>
                        <div class="kt-input-icon">
                            <div class="input-group">
                                <input type="text" class="form-control only-greater-than-or-equal-zero-allowed hundred-minus-number2" name="contractual_rate" value="{{ isset($subModel) ? $subModel->getContractualRate() : old('contractual_rate') }}">
                            </div>
                        </div>
                    </div>
					
					  <div class="col-md-3">
                        <label class="form-label">{{ __('After Months') }} </label>
                        <div class="kt-input-icon">
                            <div class="input-group">
                                <input type="text" class="form-control only-greater-than-or-equal-zero-allowed" name="after_months" value="{{ isset($subModel) ? $subModel->getAfterMonths() : old('after_months') }}">
                            </div>
                        </div>
                    </div>
					
					<div class="col-md-3">
                        <label class="form-label">{{ __('Remaining Balance %') }} </label>
                        <div class="kt-input-icon">
                            <div class="input-group">
                                <input type="text" readonly class="form-control only-greater-than-or-equal-zero-allowed hundred-minus-two-number-result"  value="{{ isset($subModel) ? $subModel->getRemainingBalanceRate() : old('remaining_balance') }}">
                            </div>
                        </div>
                    </div>
					
					<div class="col-md-3">
                        <label class="form-label">{{ __('Grace Period') }} </label>
                        <div class="kt-input-icon">
                            <div class="input-group">
                                <input type="text"  class="form-control only-greater-than-or-equal-zero-allowed" name="installment_grace_period" value="{{ isset($subModel) ? $subModel->getInstallmentGracePeriod() : 0 }}">
                            </div>
                        </div>
                    </div>
						
					<div class="col-md-3">
                        <label class="form-label">{{ __('Installment Count') }} </label>
                        <div class="kt-input-icon">
                            <div class="input-group">
                                <input type="numeric" step="any" class="form-control only-greater-than-zero-allowed" name="installment_count" value="{{ isset($subModel) ? $subModel->getInstallmentCount() : 1 }}">
                            </div>
                        </div>
                    </div>
					
					{{-- <div class="col-md-3">
                        <label class="form-label">{{ __('Interest %') }} </label>
                        <div class="kt-input-icon">
                            <div class="input-group">
                                <input type="numeric" step="any" class="form-control only-greater-than-zero-or-equal-allowed" name="installment_interest_rate" value="{{ isset($subModel) ? $subModel->getInstallmentInterestRate() : 0 }}">
                            </div>
                        </div>
                    </div> --}}
					
					
					
					  <div class="col-md-3">
        <label class="form-label">{{ __('Installment Interval') }} </label>
        @php
        $currentVal = $subModel ? $subModel->getPaymentInstallmentInterval():'monthly';
        @endphp
        <select name="payment_installment_interval" class="form-control ">
            @foreach( ['monthly'=>__('Monthly'),'quarterly'=>__('Quarterly'),'semi-annually'=>__('Semi-annually'),'annually'=>__('Annually')] as $id => $title)
            <option {{ $id == $currentVal ? 'selected'  : ''}} value="{{ $id }}">{{ $title }}</option>
            @endforeach
        </select>
    </div>
					
					

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn save-modal btn-primary" data-dismiss="modal">{{ __('Save') }}</button>
            </div>
        </div>
    </div>
</div>
