<div class="modal fade " id="{{ $currentModalId }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <form 
		action="#"
		{{-- action="{{ route('calculate.spread.rate.sensitivity',['study'=>$study->id]) }}" --}}
		 class="modal-content" method="post">


            @csrf
            <div class="modal-header">
                <h5 class="modal-title" style="color:#0741A5 !important" id="exampleModalLongTitle"> {{$currentModalTitle }} </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="customize-elements">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-center w-30-percentage text-capitalize th-main-color align-middle">{{ __('Revenue Stream') }}</th>
                                <th class="text-center w-50-percentage text-capitalize th-main-color align-middle">{{ __('Name') }}</th>
                                <th class="text-center w-10-percentage text-capitalize th-main-color align-middle">{{ __('Spread Rate') }}</th>
                                <th class="text-center w-10-percentage text-capitalize th-main-color align-middle">{{ __('Sensitivity Spread Rate') }}</th>
                            </tr>
                        </thead>
                        <tbody>

							@foreach([] as $relationName)
							@php
								$revenueStreamTitle = __('Sub title');
							@endphp
							@foreach ($study->{$relationName} as $index=>$currentLeasingRevenueStreamBreakdown)
							@php
								$name = $currentLeasingRevenueStreamBreakdown->getReviewForTable();
								$id = $currentLeasingRevenueStreamBreakdown->id ;
								$marginRate = $currentLeasingRevenueStreamBreakdown->getMarginRate() ;
								$sensitivityMarginRate = $currentLeasingRevenueStreamBreakdown->getSensitivityMarginRate() ;
								$isMarginRateEqualToSensitivityMarginRate = $marginRate == $sensitivityMarginRate ;
							@endphp
                            <tr>
                                <td class="w-30-percentage">
                                    <div class="kt-input-icon ">
                                        <div class="input-group">
                                            <input disabled type="text" step="0.1" class="form-control ignore-global-style" value="{{ $revenueStreamTitle }}">
                                        </div>
                                    </div>
                                </td> <td class="w-50-percentage">
                                    <div class="kt-input-icon ">
                                        <div class="input-group">
                                            <input disabled type="text" step="0.1" class="form-control ignore-global-style
											
											@if(!$isMarginRateEqualToSensitivityMarginRate)
												bg-green text-white												
												@endif 
												
											" value="{{ $name }}">
                                        </div>
                                    </div>
                                </td>

                                     
                                <td class="w-10-percentage">
                                    <div class="d-flex align-items-center ">
                                        <div class="kt-input-icon ml-2 ">
                                            <div class="input-group">
                                                <input readonly type="text" class="form-control text-center ignore-global-style" value="{{  number_format($marginRate,2) . ' %' }}">
                                            </div>
                                        </div>
                                    </div>

                                </td>
								
								<td class="w-10-percentage">
                                    <div class="d-flex align-items-center ">
                                        <div class="kt-input-icon ml-2 ">
                                            <div class="input-group">
                                                <input name="sensitivity_margin_rate[{{ $relationName }}][{{ $id }}]" type="text" class="form-control text-center ignore-global-style
												
												" value="{{ number_format($sensitivityMarginRate,2) }}">
                                            </div>
                                        </div>
                                    </div>

                                </td>





                            </tr>

                            @endforeach
                            @endforeach


                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary 
				{{-- submit-form-btn --}}
				" 
				{{-- data-dismiss="modal" --}}
				>{{ __('Calculate') }}</button>
            </div>
        </form>
    </div>
</div>
