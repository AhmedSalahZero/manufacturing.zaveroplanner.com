@extends('layouts.app')
@section('content')
<style>
</style>
<div class="col-12">

    <h1 class="d-flex justify-content-between steps-span">
        <span><a href="{{ url()->previous() }}" style="color: white">
                < {{ __('Back') }}</a></span>
        <span>{{ __('Step ') . $step_data['place_num'] . '/' . $step_data['count'] }}</span>
    </h1>
    <h1 class="bread-crumbs">
        {{ __('ZAVERO Manufacturing') }} > {{ $project->name }} > {{ __($step_data['route_name']) }}
    </h1>
	
	
	 <div class="kt-portlet__body">
        <div class="tab-content  kt-margin-t-20">

            @php
            $currentType = 'study' ;
            @endphp
            <!--Begin:: Tab Content-->
            <div class="tab-pane {{  !Request('active') || Request('active') == $currentType ?'active':'' }}" id="{{ $currentType }}" role="tabpanel">
                <div class="kt-portlet kt-portlet--mobile">





                   <x-tables.repeater-table :tableClasses="'table-condensed table-row-spacing income-class-table'" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden scrollable-table'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                        <x-slot name="ths">
                            <x-tables.repeater-table-th class="  header-border-down first-column-th-class" :title="__('+/-')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th class="  header-border-down first-column-th-class" :title="__('Name')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Add')"></x-tables.repeater-table-th>
                            @foreach($studyMonthsForViews as $dateAsIndex=>$dateAsString)
                            @php
                            $currentMonthNumber = explode('-',$dateAsString)[1];
                            $currentYear= explode('-',$dateAsString)[0];
                            $currentYearRepeaterIndex = 0 ;
                            @endphp
                            <x-tables.repeater-table-th data-column-index="{{ $dateAsIndex }}" class=" interval-class header-border-down " :title="dateFormatting($dateAsString, 'M\' Y')"></x-tables.repeater-table-th>

                            @if($financialYearEndMonthNumber == $currentMonthNumber || $loop->last)
                            <x-tables.repeater-table-th :icon="true" data-column-index="{{ $dateAsIndex }}" :font-size-class="'font-14px'" class=" interval-class header-border-down {{ 'year-repeater-index-'.$currentYearRepeaterIndex }} collapse-before-me exclude-from-collapse " :title="__('Total Yr.').' <br> '. $currentYear"></x-tables.repeater-table-th>
                            @php
                            $currentYearRepeaterIndex ++;
                            @endphp
                            @endif

                            @endforeach
                            <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Total')"></x-tables.repeater-table-th>
                        </x-slot>
                        <x-slot name="trs">

                            @php
                            @endphp
                            @foreach($tableDataFormatted as $tableIndex => $currentTableData)
                            @php
                            $subItems = $currentTableData['sub_items']??[] ;
                            $hasSubItems = count($subItems);
                            @endphp
                            <tr data-is-main-row data-repeat-formatting-decimals="0" data-repeater-style>
                                <td>
                                    @if($hasSubItems)
                                    <a href="#" class="btn btn-1-bg btn-sm btn-brand add-btn-class  text-center add-btn-js">
                                        <i class="fas fa-angle-double-down expand-icon   exclude-icon"></i>
                                    </a>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center flex-column " style="gap:10px">
                                        @php
                                        $currentIndex = 0 ;
                                        @endphp
                                        @foreach($currentTableData['main_items'] as $mainItemId => $mainItemArr)
                                        <div class="input-hidden-parent">
                                            <input data-number-of-decimals="0" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

						  expandable-amount-input 			  repeat-to-right-input-formatted  custom-input-string-width input-text-left  " type="text" value="{{ $mainItemArr['options']['title']??$mainItemId }}" data-column-index="-1">
                                        </div>
                                        @php
                                        $currentIndex++;
                                        @endphp
                                        @endforeach

                                    </div>
                                </td>
                                <td>
                                    @if($hasSubItems)
                                    <div class="d-flex align-items-center justify-content-center">
                                        <a data-toggle="modal" data-target="#add-new-cost-of-good" href="#" class="btn btn-2-bg btn-sm btn-brand btn-pill">{{ __('+') }}</a>


                                    </div>
                                    @endif
                                </td>
                                @php
                                $currentYearRepeaterIndex = 0 ;
                                @endphp

                                @foreach($studyMonthsForViews as $dateAsIndex=>$dateAsString)

                                <td data-column-index="{{ $dateAsIndex }}">

                                    <div data-column-index="{{ $dateAsIndex }}" class="d-flex align-items-center justify-content-center flex-column" style="gap:10px">
                                        @php
                                        $currentIndex = 0 ;
                                        @endphp
                                        @foreach($currentTableData['main_items'] as $mainItemTitle => $mainItemArr)
                                        @php
                                        $isPercentage = $mainItemArr['options']['is-percentage']??$defaultClasses[$currentIndex]['is-percentage'] ;
                                        @endphp
                                        @if($isPercentage)
                                        <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                                            <div class="input-hidden-parent">
                                                <input disabled data-number-of-decimals="2" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

							  expandable-percentage-input  			  repeat-to-right-input-formatted   " type="text" value="{{ number_format($mainItemArr['data'][$dateAsIndex]??0,2) }}" data-column-index="{{ $dateAsIndex }}">
                                                <input data-number-of-decimals="2" data-group-index="{{ $currentIndex ==0   ? $currentYearRepeaterIndex : -1 }}" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  " value="{{ $mainItemArr['data'][$dateAsIndex]??0 }}" data-column-index="{{ $dateAsIndex }}">
                                            </div>
                                            <span class="ml-2 currency-class">%</span>
                                        </div>
                                        @else
                                        <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                                            <div class="input-hidden-parent">
                                                <input disabled data-number-of-decimals="0" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

							  expandable-amount-input 			  repeat-to-right-input-formatted  custom-input-numeric-width  " type="text" value="{{ number_format($mainItemArr['data'][$dateAsIndex]??0) }}" data-column-index="{{ $dateAsIndex }}">
                                                <input data-number-of-decimals="0" data-group-index="{{ $currentIndex ==0   ? $currentYearRepeaterIndex : -1 }}" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  repeater-with-collapse-input" value="{{ $mainItemArr['data'][$dateAsIndex]??0 }}" data-column-index="{{ $dateAsIndex }}">
                                            </div>
                                        </div>
                                        @endif


                                        {{-- <x-repeat-right-dot-inputs :readonly="false" :classes="$mainItemArr['options']['classes']??$defaultClasses[$currentIndex]['classes']" data-group-index="{{ $currentIndex ==0   ? $currentYearRepeaterIndex : -1 }}" :formattedInputClasses="$mainItemArr['options']['formatted-input-classes']??$defaultClasses[$currentIndex]['formatted-input-classes']" :removeThreeDots="true" :removeCurrency="true" :mark="$isPercentage ? '%' : ''" :is-number="true" :removeThreeDotsClass="true" :numberFormatDecimals="$mainItemArr['options']['number-format-decimals']??$defaultClasses[$currentIndex]['number-format-decimals']" :currentVal="$mainItemArr['data'][$dateAsIndex]??0" :is-percentage="$isPercentage" :name="''" :columnIndex="$dateAsIndex"></x-repeat-right-dot-inputs> --}}
                                        @php
                                        $currentIndex++;
                                        @endphp
                                        @endforeach

                                    </div>




                                </td>

                                @php
                                $currentMonthNumber = explode('-',$dateAsString)[1];
                                $currentYear= explode('-',$dateAsString)[0];
                                @endphp
                                @if($financialYearEndMonthNumber == $currentMonthNumber || $loop->last)
                                <td data-column-index="{{ $dateAsIndex }}" class="exclude-from-collapse">
                                    @php
                                    $currentIndex =0 ;
                                    @endphp

                                    @foreach($currentTableData['main_items'] as $mainItemId => $mainItemArr)
                                    <div class="d-flex align-items-center justify-content-center">
                                        @if($currentIndex == 0)
                                        <div class="

								form-group 
								three-dots-parent
 

							">
                                            <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                                                <div class="input-hidden-parent">
                                                    <input disabled data-number-of-decimals="0" readonly="" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

							  expandable-amount-input 			  repeat-to-right-input-formatted  exclude-from-collapse repeat-group-year " type="text" value="{{ number_format($mainItemArr['total'][$dateAsIndex]??0,0) }}" data-column-index="{{ $dateAsIndex }}">
                                                    <input data-number-of-decimals="0" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  year-repeater-index-{{ $currentYearRepeaterIndex }}  exclude-from-collapse" value="{{ $mainItemArr['total'][$dateAsIndex]??0 }}" data-column-index="{{ $dateAsIndex }}">
                                                </div>

                                                <span class="ml-2 currency-class">
                                                    EGP
                                                </span>

                                            </div>



                                            {{-- <i class="fa fa-ellipsis-h pull-left repeat-to-right row-repeater-icon visibility-hidden"></i> --}}

                                        </div>
                                        @else
                                        <div class="

											form-group 
											three-dots-parent
						

								">
                                            <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                                                <div class="input-hidden-parent">
                                                    <input disabled data-number-of-decimals="0" readonly="" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

								  expandable-percentage-input  			  repeat-to-right-input-formatted  exclude-from-collapse repeat-group-year " type="text" value="{{ number_format($mainItemArr['total'][$dateAsIndex]??0,2) }}" data-column-index="{{ $dateAsIndex }}">
                                                    <input data-number-of-decimals="0" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  year-repeater-index-{{ $currentYearRepeaterIndex }}  exclude-from-collapse" value="{{ $mainItemArr['total'][$dateAsIndex]??0 }}" data-column-index="{{ $dateAsIndex }}">
                                                </div>
                                                <span class="ml-2">%</span>
                                            </div>



                                            <i class="fa fa-ellipsis-h pull-left repeat-to-right row-repeater-icon visibility-hidden"></i>

                                        </div>
                                        @endif
                                        {{-- <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="0" :mark="''" :currentVal="$mainItemArr['total'][$dateAsIndex]??0 " :formattedInputClasses="'exclude-from-collapse repeat-group-year'" :classes="'year-repeater-index-'.$currentYearRepeaterIndex.' ' .' exclude-from-collapse'" :is-percentage="$mainItemArr['options']['is-percentage']??$defaultClasses[$currentIndex]['is-percentage']" :name="''" :columnIndex="$dateAsIndex"></x-repeat-right-dot-inputs> --}}
                                    </div>
                                    @php
                                    $currentIndex++;
                                    @endphp
                                    @endforeach



                                </td>
                                @php
                                $currentYearRepeaterIndex++;
                                @endphp
                                @endif

                                @endforeach
                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="">
                                            <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                                                <div class="input-hidden-parent">
                                                    <input disabled readonly data-number-of-decimals="0" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

								  expandable-amount-input 			  repeat-to-right-input-formatted   " type="text" value="0" data-column-index="-1">
                                                    <input data-number-of-decimals="0" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  total-td" value="0" data-column-index="-1">
                                                </div>
                                                <span class="ml-2 currency-class"> </span>
                                            </div>

                                        </div>

                                        {{-- <x-repeat-right-dot-inputs :removeThreeDots="true" :removeCurrency="true" :mark="' '" :is-number="true" :removeThreeDotsClass="true" :number-format-decimals="0" :currentVal="0" :classes="'total-td'" :is-percentage="false" :name="''" :columnIndex="0"></x-repeat-right-dot-inputs> --}}
                                    </div>
                                </td>
                            </tr>
                            @foreach($subItems as $subItemId => $subItemArr)
                            <tr class="hidden" data-is-sub-row data-repeat-formatting-decimals="0">
                                <td>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center flex-column ml-5" style="gap:10px">

                                        <div class="

 

									">
                                            <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                                                <div class="input-hidden-parent">
                                                    <input disabled data-number-of-decimals="0" readonly="" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

										  expandable-amount-input 			  repeat-to-right-input-formatted  custom-input-string-width input-text-left  " type="text" value="{{ $subItemArr['options']['title']??$subItemId }}" data-column-index="-1">
                                                    <input data-number-of-decimals="0" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  " value="{{ $subItemArr['options']['title']??$subItemId }}" data-column-index="-1">
                                                </div>
                                                <span class="ml-2 currency-class"> </span>
                                            </div>

                                        </div>

                                    </div>
                                </td>
                                <td>

                                </td>

                                @foreach($studyMonthsForViews as $dateAsIndex=>$dateAsString)
                                <td data-column-index="{{ $dateAsIndex }}">

                                    <div data-column-index="{{ $dateAsIndex }}" class="d-flex align-items-center justify-content-center flex-column" style="gap:10px">

                                        <div class="">
                                            <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                                                <div class="input-hidden-parent">
                                                    <input disabled data-number-of-decimals="0" readonly="" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

			 							 expandable-amount-input 			  repeat-to-right-input-formatted  custom-input-numeric-width  " type="text" value="{{ number_format($subItemArr['data'][$dateAsIndex]??0) }}" data-column-index="{{ $dateAsIndex }}">
                                                    <input data-number-of-decimals="0" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  repeater-with-collapse-input" value="{{ $subItemArr['data'][$dateAsIndex]??0 }}" data-column-index="{{ $dateAsIndex }}">
                                                </div>
                                                <span class="ml-2 currency-class"> </span>
                                            </div>

                                        </div>
                                    </div>
                                </td>
                                @php
                                $currentMonthNumber = explode('-',$dateAsString)[1];
                                $currentYear= explode('-',$dateAsString)[0];
                                @endphp

                                @if($financialYearEndMonthNumber == $currentMonthNumber || $loop->last)
                                <td data-column-index="{{ $dateAsIndex }}" class="exclude-from-collapse">
                                    <div class="d-flex align-items-center justify-content-center">

                                        <div class="

 

							">
                                            <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                                                <div class="input-hidden-parent">
                                                    <input disabled readonly data-number-of-decimals="0" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

										  expandable-amount-input 			  repeat-to-right-input-formatted   " type="text" value="{{ number_format($subItemArr['total'][$dateAsIndex]??0) }}" data-column-index="{{ $dateAsIndex }}">
                                                    <input data-number-of-decimals="0" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  total-td" value="{{ $subItemArr['total'][$dateAsIndex]??0 }}" data-column-index="{{ $dateAsIndex }}">
                                                </div>
                                                <span class="ml-2 currency-class"> </span>
                                            </div>

                                        </div>

                                        {{-- <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="0" :mark="' '" :currentVal="$subItemArr['total'][$dateAsIndex]??0 " :formattedInputClasses="'exclude-from-collapse '" :classes="'year-repeater-index-'.$currentYearRepeaterIndex.' ' .' exclude-from-collapse'" :is-percentage="false" :name="''" :columnIndex="$dateAsIndex"></x-repeat-right-dot-inputs> --}}
                                    </div>

                                </td>
                                @php
                                $currentYearRepeaterIndex++;
                                @endphp
                                @endif

                                @endforeach

                                <td>
                                    <div class="d-flex align-items-center justify-content-center">

                                        <div class="

 

												">
                                            <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                                                <div class="input-hidden-parent">
                                                    <input disabled readonly data-number-of-decimals="0" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

							  expandable-amount-input 			  repeat-to-right-input-formatted   " type="text" value="0" data-column-index="-1">
                                                    <input data-number-of-decimals="0" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  total-td" value="0" data-column-index="-1">
                                                </div>
                                                <span class="ml-2 currency-class"> </span>
                                            </div>

                                        </div>

                                        {{-- <x-repeat-right-dot-inputs :removeThreeDots="true" :removeCurrency="true" :mark="' '" :is-number="true" :removeThreeDotsClass="true" :number-format-decimals="0" :currentVal="0" :classes="'total-td'" :is-percentage="false" :name="''" :columnIndex="-1"></x-repeat-right-dot-inputs> --}}
                                    </div>
                                </td>
                            </tr>


                            @endforeach
                            @endforeach



                        </x-slot>




                    </x-tables.repeater-table>


                </div>
            </div>




            <!--End:: Tab Content-->



            <!--End:: Tab Content-->
        </div>
    </div>
   
</div>
<div class="clearfix"></div>
@endsection

@section('js')


<script src="https://cdn.jsdelivr.net/npm/jquery.repeater@1.2.1/jquery.repeater.min.js"></script>
<script>
    // Define translated strings for JavaScript
    var translations = {
        deleteConfirm: @json(__('Are you sure you want to delete this position?'))
    };
    
	 $(document).ready(function() {
        var selector = "#fixedAssets_repeater";
        $(selector).repeater({
            initEmpty: false
            , defaultValues: {
                'category_id': 'manufacturing-expenses'
                , 'payment_terms': 'cash',
				'equity_funding_rate':0,
				'replacement_cost_rate':1,
				'depreciation_duration':5,
				'amount':0,
				'tenor':60,
				'interest_rate':0,
				'installment_interval':'monthly',
				'replacement_cost_interval':1
             }
            , show: function() {
                $(this).slideDown();
				$('.js-select2-with-one-selection').select2({});
				$('.hundred-minus-number').trigger('change')
				recalculateAllocations(this);
				// $('.hundred-minus-number').trigger('change')
            }
            , ready: function(setIndexes) {
					
            }
            , hide: function(deleteElement) {
                if (confirm(translations.deleteConfirm)) {
                    $(this).slideUp(deleteElement);


                }

            }
            , isFirstItemUndeletable: true
        });
    });
	
	

</script>


@endsection
