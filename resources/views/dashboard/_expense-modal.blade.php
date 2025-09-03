<div class="modal fade " id="{{ $currentModalId }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <form action="#" class="modal-content" method="post">


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


                                <th class="text-center w-20-percentage text-capitalize th-main-color">{{ __('Expense Name') }}</th>
                             
							
								@foreach($yearWithItsIndexes as $currentYearIndex=> $monthInfos )
								<th class="text-center 
								{{-- w-15-percentage --}}
								 text-capitalize th-main-color">
								{{ $yearIndexWithYear[$currentYearIndex] }}
								</th>
								@endforeach
								
								


                            </tr>
                        </thead>
                        <tbody>

						
                            @foreach($modalData as $expenseName => $expenseWithYearIndexAndValue )
							@if($expenseName == 'total')
							@continue;
							@endif 
                            <tr>
                                <td class="w-20-percentage">
                                    <div class="kt-input-icon ">
                                        <div class="input-group">
                                            <input disabled type="text" step="0.1" class="form-control ignore-global-style" value="{{ $expenseName }}">
                                        </div>
                                    </div>
                                </td>
						
							@foreach($yearWithItsIndexes as  $currentYearIndex => $monthInfos)
							@php
								$currentExpenseValue = $expenseWithYearIndexAndValue[$currentYearIndex]??0 ;
								$currentSalesRevenue = $formattedResult['sales_revenue'][$currentYearIndex]??0;
								$currentPercentageOfSales = $currentSalesRevenue ?  $currentExpenseValue /  $currentSalesRevenue * 100 : 0;
							@endphp
                                <td class="
								{{-- w-10-percentage --}}
								">
                                    <div class="d-flex align-items-center ">
									<div class="kt-input-icon ">
                                        <div class="input-group">
                                            <input disabled type="text" class="form-control text-center ignore-global-style" value="{{  number_format($currentExpenseValue/getDivisionNumber(),2) }}">
                                        </div>
                                    </div>
									
									 <div class="kt-input-icon ml-2 ">
                                        <div class="input-group">
                                            <input disabled type="text" class="form-control text-center ignore-global-style" value="{{  number_format($currentPercentageOfSales,2) . ' %' }}">
                                        </div>
                                    </div>
									</div>
									
                                </td>
								@endforeach 
		


                              

                            </tr>
							
                            @endforeach 
							
					
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary 
				{{-- submit-form-btn --}}
				" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </form>
    </div>
</div>
