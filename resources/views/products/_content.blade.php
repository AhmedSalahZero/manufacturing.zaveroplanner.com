
  <div class="div-title">
                {{ $product->getName()  }}
            </div>

            <div class="formItem">
                <div class="row ml-1 mr-1 closest-parent ">
                    <div class="col-12">
                        <label>{{__('Selling Start Date')}}</label>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <input type="text" readonly class="form-control " value="{{ date("M/Y",strtotime($product->getSellingStartDateAsString()))}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">


                        @php
                        $isYearsStudy = false;
                        $tableClass = $isYearsStudy ? 'col-md-6 closest-parent' : 'col-md-12 closest-parent';
                        @endphp


                        <x-tables.repeater-table :table-class="$tableClass" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="''" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                            <x-slot name="ths">
                                <x-tables.repeater-table-th :subParentClass="'text-left '" class="  header-border-down text-left" :title="__('Item')"></x-tables.repeater-table-th>
                                @foreach($years as $yearAsIndex=>$yearAsString)
                                <x-tables.repeater-table-th class=" header-border-down" :title="'Yr-'.$yearAsString"></x-tables.repeater-table-th>
                                @endforeach
                            </x-slot>
                            <x-slot name="trs">

                                <tr data-repeat-formatting-decimals="1" data-repeater-style>

                                    <td>
                                        <input value="{{ __('Max Capacity') }}" disabled class="form-control text-left " type="text">
                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp
                                    @foreach($years as $yearAsIndex=>$yearAsString)
                                    @php
                                    $currentVal = $product->getMaxCapacityAtYearIndex($yearAsIndex);
                                    @endphp
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :formattedInputClasses="'max-w-100 text-center '" :disabled="false" :removeThreeDotsClass="false" :removeThreeDots="false" :number-format-decimals="1" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed  number_field  '" :is-percentage="false" :mark="' '" :name="'max_capacity['.($yearAsIndex).']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endforeach


                                </tr>


                                <tr data-repeat-formatting-decimals="2" data-repeater-style>

                                    <td>
                                        <input value="{{ __('Target Selling Quantity %') }}" disabled class="form-control text-left " type="text">
                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp
                                    @foreach($years as $yearAsIndex=>$yearAsString)
                                    @php
                                    $currentVal = $product->getTargetPercentageAtYearIndex($yearAsIndex);
                                    @endphp
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :formattedInputClasses="'expandable-percentage-input   text-center'" :disabled="false" :removeThreeDotsClass="false" :removeThreeDots="false" :number-format-decimals="2" :currentVal="$currentVal" :classes="' only-percentage-allowed  percentage_field'" :is-percentage="true" :mark="' %'" :name="'target_percentages['.($yearAsIndex).']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endforeach


                                </tr>



                                <tr data-repeat-formatting-decimals="1" data-repeater-style>

                                    <td>
                                        <input value="{{ __('Target Selling Quantity') }}" disabled class="form-control text-left " type="text">
                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp
                                    @foreach($years as $yearAsIndex=>$yearAsString)
                                    @php
                                    $currentVal = $product->getTargetQuantityAtYearIndex($yearAsIndex);
                                    @endphp
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :name="'target_quantities['.($yearAsIndex).']'" :formattedInputClasses="'max-w-100 text-center '" :readonly="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="1" :currentVal="$currentVal" :classes="' only-greater-than-or-equal-zero-allowed number_multiple_percentage number_field2 number_field3'" :is-percentage="false" :mark="' '" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endforeach


                                </tr>




                                <tr data-repeat-formatting-decimals="2" data-repeater-style>

                                    <td>
                                        <input value="{{ __('Local Selling Quantity %') }}" disabled class="form-control text-left " type="text">
                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp
                                    @foreach($years as $yearAsIndex=>$yearAsString)
                                    @php
                                    $currentVal = $product->getLocalTargetQuantityPercentagesAtYearIndex($yearAsIndex);
                                    @endphp
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :name="'local_target_quantity_percentages['.($yearAsIndex).']'" :formattedInputClasses="'expandable-percentage-input   text-center'" :readonly="false" :removeThreeDotsClass="false" :removeThreeDots="false" :number-format-decimals="2" :currentVal="$currentVal" :classes="' only-percentage-allowed  percentage_field2 hundred-minus-number'" :is-percentage="true" :mark="' %'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endforeach


                                </tr>



                                <tr data-repeat-formatting-decimals="1" data-repeater-style>

                                    <td>
                                        <input value="{{ __('Local Target Selling Quantity') }}" disabled class="form-control text-left " type="text">
                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp
                                    @foreach($years as $yearAsIndex=>$yearAsString)
                                    @php
                                    $currentVal = $product->getLocalTargetQuantityAtYearIndex($yearAsIndex);
                                    @endphp
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :name="'local_target_quantities['.($yearAsIndex).']'" :formattedInputClasses="'max-w-100 text-center '" :readonly="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="1" :currentVal="$currentVal" :classes="' only-greater-than-or-equal-zero-allowed number_multiple_percentage2  sum_product_quantity_1'" :is-percentage="false" :mark="' '" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endforeach


                                </tr>


                                <tr data-repeat-formatting-decimals="2" data-repeater-style>

                                    <td>
                                        <input value="{{ __('Local Selling Price Growth %') }}" disabled class="form-control text-left " type="text">
                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp
                                    @foreach($years as $yearAsIndex=>$yearAsString)
                                    @php
                                    $currentVal = $product->getLocalGrowthRateAtYearIndex($yearAsIndex);
                                    @endphp
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :formattedInputClasses="'expandable-percentage-input  text-center'" :classes="' only-greater-than-or-equal-zero-allowed gr-field recalculate-gr'" :disabled="false" :removeThreeDotsClass="false" :removeThreeDots="false" :number-format-decimals="2" :currentVal="$currentVal" :is-percentage="true" :mark="' %'" :name="'local_growth_rates['.($yearAsIndex).']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endforeach


                                </tr>


                                <tr data-repeat-formatting-decimals="1" data-repeater-style>

                                    <td>
                                        <input value="{{ __('Local Selling Price / Unit') }}" disabled class="form-control text-left " type="text">
                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp
                                    @foreach($years as $yearAsIndex=>$yearAsString)
                                    @php
                                    $currentVal = $product->getLocalPricePerUnitAtYearIndex($yearAsIndex);
                                    @endphp
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :classes="'only-greater-than-or-equal-zero-allowed number_field_1 current-growth-rate-result-value sum_product_value_1'" :formattedInputClasses="'max-w-100 text-center current-growth-rate-result-value-formatted'" :name="'local_price_per_unit['.($yearAsIndex).']'" :disabled="false" :removeThreeDotsClass="false" :removeThreeDots="false" :number-format-decimals="1" :currentVal="$currentVal" :is-percentage="false" :mark="' '" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endforeach


                                </tr>


                                <tr data-repeat-formatting-decimals="2" data-repeater-style>

                                    <td>
                                        <input value="{{ __('Export Selling Quantity %') }}" disabled class="form-control text-left " type="text">
                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp
                                    @foreach($years as $yearAsIndex=>$yearAsString)
                                    @php
                                    $currentVal = $product->getExportTargetQuantityPercentagesAtYearIndex($yearAsIndex);
                                    @endphp
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :name="'export_target_quantity_percentages['.($yearAsIndex).']'" :formattedInputClasses="'expandable-percentage-input   text-center'" :readonly="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="2" :currentVal="$currentVal" :classes="' only-greater-than-or-equal-zero-allowed hundred-minus-number-result  percentage_field3 '" :is-percentage="true" :mark="' %'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endforeach


                                </tr>





                                <tr data-repeat-formatting-decimals="1" data-repeater-style>

                                    <td>
                                        <input value="{{ __('Export Target Selling Quantity') }}" disabled class="form-control text-left " type="text">
                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp
                                    @foreach($years as $yearAsIndex=>$yearAsString)
                                    @php
                                    $currentVal = $product->getExportTargetQuantityAtYearIndex($yearAsIndex);
                                    @endphp
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :name="'export_target_quantities['.($yearAsIndex).']'" :formattedInputClasses="'max-w-100 text-center '" :readonly="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="1" :currentVal="$currentVal" :classes="' only-greater-than-or-equal-zero-allowed number_multiple_percentage3 sum_product_quantity_2'" :is-percentage="false" :mark="' '" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endforeach


                                </tr>



                                <tr data-repeat-formatting-decimals="2" data-repeater-style>

                                    <td>
                                        <input value="{{ __('Export Selling Price Growth %') }}" disabled class="form-control text-left " type="text">
                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp
                                    @foreach($years as $yearAsIndex=>$yearAsString)
                                    @php
                                    $currentVal = $product->getExportGrowthRateAtYearIndex($yearAsIndex);
                                    @endphp
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :formattedInputClasses="'expandable-percentage-input  text-center'" :classes="' only-greater-than-or-equal-zero-allowed gr-field2 recalculate-gr2'" :disabled="false" :removeThreeDotsClass="false" :removeThreeDots="false" :number-format-decimals="2" :currentVal="$currentVal" :is-percentage="true" :mark="' %'" :name="'export_growth_rates['.($yearAsIndex).']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endforeach


                                </tr>


                                <tr data-repeat-formatting-decimals="1" data-repeater-style>

                                    <td>
                                        <input value="{{ __('Export Selling Price / Unit') }}" disabled class="form-control text-left " type="text">
                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp
                                    @foreach($years as $yearAsIndex=>$yearAsString)
                                    @php
                                    $currentVal = $product->getExportPricePerUnitAtYearIndex($yearAsIndex);
                                    @endphp
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :classes="'only-greater-than-or-equal-zero-allowed current-growth-rate-result-value2 sum_product_value_2'" :formattedInputClasses="'max-w-100 text-center current-growth-rate-result-value-formatted2'" :name="'export_price_per_unit['.($yearAsIndex).']'" :disabled="false" :removeThreeDotsClass="false" :removeThreeDots="false" :number-format-decimals="1" :currentVal="$currentVal" :is-percentage="false" :mark="' '" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endforeach


                                </tr>





                                <tr data-repeat-formatting-decimals="0" data-repeater-style>

                                    <td>
                                        <input value="{{ __('Target Sales Value') }}" disabled class="form-control text-left " type="text">
                                    </td>
                                    @php
                                    $columnIndex = 0 ;
                                    @endphp
                                    @foreach($years as $yearAsIndex=>$yearAsString)
                                    @php
                                    $currentVal = $product->getSalesTargetValuesAtYearIndex($yearAsIndex);
                                    @endphp
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <x-repeat-right-dot-inputs :classes="' only-greater-than-or-equal-zero-allowed two_sum_product_result '" :name="'sales_target_values['.($yearAsIndex).']'" :formattedInputClasses="'max-w-100 text-center '" :readonly="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="0" :currentVal="$currentVal" :is-percentage="false" :mark="' '" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                        </div>
                                    </td>
                                    @php
                                    $columnIndex++ ;
                                    @endphp

                                    @endforeach


                                </tr>















                            </x-slot>




                        </x-tables.repeater-table>


                    </div>
                    @php
                    $index = -1 ;
                    @endphp
                    @foreach ($years as $yearAsIndex => $year)
                    @php
                    $index++;
                    $isLastLoop = count($years)-1 == $index;
                    @endphp
                    <?php $yearAsTwoNumbers = date('y',strtotime('01-01-'.$year)); ?>


                    @endforeach


                </div>
                {{-- Selling Start Date --}}

                {{-- Contract Target --}}

            </div>


            {{-- Sales Seasonality --}}
            <div class="div-title">
			{{ $product->getName() }}
			[{{__('Sales Seasonality')}}]
            </div>
            <div class="formItem">
                <div class="col-12">
                    {{-- Section Of Months --}}
                    <?php $seasonalityType = $product->getSeasonalityType(); ?>
                    <div class="form-group">
                        <label>{{__('Choose Seasonality')}}</label><span class="red">*</span>
                        <select name="seasonality[type]" id="seasonality" class="form-control @error('seasonality_type') is-invalid @enderror">
                            <option value="flat" {{$seasonalityType == 'flat' ?'selected' : '' }}>{{__('Flat Monthly')}}</option>
                            <option value="quarterly" {{$seasonalityType == 'quarterly' ?'selected' : '' }}>{{__('Distribute Quarterly')}}</option>
                            <option value="monthly" {{$seasonalityType == 'monthly' ?'selected' : '' }}>{{__('Distribute Monthly')}}</option>
                        </select>
                        @error('seasonality_type')
                        <div class="alert alert-danger" role="alert">
                            {{$message}}
                        </div>
                        @enderror
                    </div>
                    @error('monthly_total_percentage')
                    <div class="alert alert-danger" role="alert">
                        {{$message}}
                    </div>
                    @enderror
                    @error('quarterly_total_percentage')
                    <div class="alert alert-danger" role="alert">
                        {{$message}}
                    </div>
                    @enderror
                    @error('seasonality_constant')
                    <div class="alert alert-danger" role="alert">
                        {{$message}}
                    </div>
                    @enderror
                    {{-- flat --}}
                    <div class="form-group flat_section {{$seasonalityType == 'flat' ?'' : 'hidden' }}">
                        <label for="annual_collection_rate">{{__('Monthly Seasonality %')}}</label>
                        <input type="number" step="any" class="form-control" value="{{ number_format(1/12*100 , 2)}}" readonly>
                        {{-- <input type="hidden" name="seasonality_constant" id="seasonality_constant" value="{{ 1/12*100}}" readonly> --}}
                    </div>
                    {{-- quarterly --}}
                    <div class="form-group quarterly_section {{$seasonalityType == 'quarterly' ?'' : 'hidden' }}">

                        <div class="row closest-parent">

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>{{__('First Quarter %')}}</label>
                                    <input type="number" step="any" class="form-control total_input quarterly" placeholder="{{__('First Quarter %')}}" value="{{$product->isQuarterlySeasonality() ? $product->getSeasonalityPercentagesAtIndex(0): 0}}" name="seasonality[quarterly][0]" id="first_quarter">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>{{__('Second Quarter %')}}</label>
                                    <input type="number" step="any" class="form-control total_input quarterly" placeholder="{{__('Second Quarter %')}}" value="{{$product->isQuarterlySeasonality() ? $product->getSeasonalityPercentagesAtIndex(1): 0}}" name="seasonality[quarterly][1]" id="second_quarter">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>{{__('Third Quarter %')}}</label>
                                    <input type="number" step="any" class="form-control total_input quarterly" placeholder="{{__('Third Quarter %')}}" value="{{$product->isQuarterlySeasonality() ? $product->getSeasonalityPercentagesAtIndex(2): 0}}" name="seasonality[quarterly][2]" id="third_quarter">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>{{__('Fourth Quarter %')}}</label>
                                    <input type="number" step="any" class="form-control total_input quarterly" placeholder="{{__('Fourth Quarter %')}}" value="{{$product->isQuarterlySeasonality() ? $product->getSeasonalityPercentagesAtIndex(3) : 0}}" name="seasonality[quarterly][3]" id="fourth_quarter">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>{{__('Total %')}}</label>
                                    <input type="text" step="any" class="form-control total_row_result" value="{{ $product->isQuarterlySeasonality() ? 100 :0 }}" readonly>
                                </div>
                            </div>

                        </div>
                    </div>
                    {{-- monthly --}}
                    <div class="form-group monthly_section {{$seasonalityType == 'monthly' ?'' : 'hidden' }}">

                        <?php $month_num = date('d-m-y',strtotime('01-01-2020')); ?>
                        <div class="row closest-parent">
                            @foreach(getMonthsList() as $index => $monthName)

                            @php
                            $currentFieldName = "seasonality[monthly][$index]";
                            $nameToOld = generateOldNameFromFieldName($currentFieldName) ;
                            $currentVal = $product->isMonthlySeasonality() ? $product->getSeasonalityPercentagesAtIndex($index) : 0 ;
                            @endphp

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>{{__($monthName ." %")}}</label>
                                    <input type="number" step="any" class="form-control total_input monthly" placeholder="{{__($monthName." %")}}" value="{{$currentVal}}" name="{{$currentFieldName}}" id="{{$nameToOld}}">
                                    <input type="hidden" class="form-control total_input flat" value="{{ (1/12)*100 }}" name="seasonality[flat][{{ $index }}]" id="{{$nameToOld}}">
                                </div>
                            </div>


                            @endforeach
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>{{__('Total %')}}</label>
                                    <input type="text" step="any" class="form-control total_row_result" value="{{ $product->isMonthlySeasonality() ? 100 :0 }}" readonly>
                                </div>
                            </div>

                        </div>



                    </div>

                    <div class="percentage mt--15 {{ ($seasonalityType == 'quarterly' || $seasonalityType == 'monthly') ? '' : 'hidden'}}">
                        <span class="red" style="color: green">{{"* ".__('Total Percentages Must Equal 100%')}}</span>
                    </div>


                </div>
            </div>

            <div class="div-title">
				{{ $product->getName() }}
                [ {{__('Raw Material Cost Rate %')}} ]
            </div>
            <div class="formItem">
                <div class="col-12">



                    <x-tables.repeater-table :table-class="$tableClass" :removeActionBtn="false" :removeRepeater="false" :initialJs="true" :repeater-with-select2="true" :canAddNewItem="true" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="'rawMaterials'" :repeaterId="'rawMaterials'" :relationName="''" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                        <x-slot name="ths">
                            <x-tables.repeater-table-th class="  header-border-down first-column-th-class" :title="__('Name')"></x-tables.repeater-table-th>
                            @foreach($years as $yearAsIndex=>$yearAsString)

                            <x-tables.repeater-table-th class=" interval-class header-border-down" :title="'Yr-'.$yearAsString"></x-tables.repeater-table-th>
                            @endforeach
                        </x-slot>
                        <x-slot name="trs">
                            @foreach(count($rawMaterials) ? $rawMaterials : [null] as $rawMaterial )
                            <tr data-repeater-item data-repeat-formatting-decimals="2" data-repeater-style>

                                <td class="text-center">
                                    <div class="">
                                        <i data-repeater-delete="" class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill trash_icon fas fa-times-circle">
                                        </i>
                                    </div>
                                </td>


                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        @php
                                        $currentFieldName = "raw_material_id";
                                        $nameToOld = generateOldNameFromFieldName($currentFieldName) ;
                                        $currentVal = isset($rawMaterial) ? $rawMaterial->getName() : '' ;
                                        @endphp
                                        <div class="form-group w-full">
                                            {{-- <label class="text-green">{{ __('Raw Material Name') }} @include('required') </label> --}}
                                            <select class="form-control" name="{{$currentFieldName}}">
                                                @foreach($rawMaterialNames as $rawMaterialName)
                                                <option {{ isset($rawMaterial) && $rawMaterialName->id == $rawMaterial->id ? 'selected' : ''  }} value="{{ $rawMaterialName->id }}">{{ $rawMaterialName->getName() }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </td>
                                @php
                                $columnIndex++ ;
                                @endphp
                                @php
                                $columnIndex = 0 ;
                                @endphp
                                @foreach($years as $yearAsIndex=>$yearAsString)
                                @php
                                $currentVal = isset($rawMaterial) ? $rawMaterial->getPercentageAtYearAsIndex($yearAsIndex) : 0;

                                @endphp
                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="form-group">
                                            <x-repeat-right-dot-inputs :formattedInputClasses="'expandable-percentage-input   text-center'" :disabled="false" :removeThreeDotsClass="false" :removeThreeDots="false" :number-format-decimals="2" :currentVal="$currentVal" :classes="' only-percentage-allowed  percentage_field'" :is-percentage="true" :label="'dd'" :mark="' %'" :name="'percentages'" :multiple="true" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                        </div>
                                    </div>

                                </td>
                                @php
                                $columnIndex++ ;
                                @endphp

                                @endforeach


                            </tr>
                            @endforeach

                        </x-slot>




                    </x-tables.repeater-table>



                </div>
            </div>


            <div class="div-title">
                {{ $product->getName() }} [ {{__('Inventory Value & Coverage Days')}} ]
            </div>
            <div class="formItem">
                <div class="col-12">
                    <div class="row">
                        @if ($project->new_company == 0)
                        <div class="col-3">
                            <div class="form-group">
                                <label>{{__('Finished Goods Beginning Inventory Quantity')}}</label>
                                <input type="text" class="form-control  only-greater-than-or-equal-zero-allowed @error('fg_inventory_quantity') is-invalid @enderror" value="{{isset($product->fg_inventory_quantity) ? number_format(number_unformat($product->fg_inventory_quantity),2) :old('fg_inventory_quantity')}}" name="fg_inventory_quantity" id="fg_inventory_quantity">
                                @error('fg_inventory_value')
                                <div class="alert alert-danger" role="alert">
                                    {{$message}}
                                </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label>{{__('Finished Goods Beginning Inventory Value')}}</label>
                                <input type="text" class="form-control fg-beginning-inventory-original-value-class only-greater-than-or-equal-zero-allowed @error('fg_inventory_value') is-invalid @enderror" value="{{isset($product->fg_inventory_value) ? $product->fg_inventory_value :old('fg_inventory_value')}}" name="fg_inventory_value" id="fg_inventory_value">
                                @error('fg_inventory_value')
                                <div class="alert alert-danger" role="alert">
                                    {{$message}}
                                </div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{__('Breakdown')}}</label>
                                <div>
                                    <button class="btn bg-green btn-md text-nowrap " type="button" data-toggle="modal" data-target="#modal-allocate-finished-goods">{{ __('FG Inventory Breakdown ') }}</button>

                                    <div class="modal fade" id="modal-allocate-finished-goods" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header header-border">
                                                    <h5 class="modal-title font-size-1rem">{{ __('Breakdown') }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table w-full">
                                                        <tbody>
                                                            @php
                                                            $allocations = [] ;
                                                            @endphp
                                                            @foreach( getFgInventoryBreakdownTypes() as $fgInventoryType => $fgInventoryTitle)
                                                            @php
                                                            $percentage = 0;
                                                            @endphp
                                                            <tr>

                                                                <td>
                                                                    <div class="form-group d-flex closest-parent   text-center">
                                                                        <input type="hidden" value="{{ $product->getFgBeginningInventoryBreakdownValueForType($fgInventoryType) }}" class="number_field fg-beginning-inventory-value-class">
                                                                        <div class="col-6 text-left">
                                                                            <label>{{ __('Item') }}</label>
                                                                            <input readonly class="form-control" name="fg_beginning_inventory_breakdowns[{{ $fgInventoryType }}][name]" type="text" value="{{ $fgInventoryTitle }}">


                                                                        </div>
                                                                        <div class="col-2 text-left">
                                                                            <label>{{ __('Percentage %') }}</label>
                                                                            <input class="form-control  percentage_field input-border" name="fg_beginning_inventory_breakdowns[{{ $fgInventoryType }}][percentage]" value="{{ $product->getFgBeginningInventoryBreakdownPercentageForType($fgInventoryType) }}">
                                                                        </div>
                                                                        <div class="col-4 text-left">
                                                                            <label>{{ __('Value') }}</label>
                                                                            <input class="form-control  number_multiple_percentage input-border" name="fg_beginning_inventory_breakdowns[{{ $fgInventoryType }}][value]" value="{{ $product->getFgBeginningInventoryBreakdownValueForType($fgInventoryType) }}">
                                                                        </div>


                                                                    </div>
                                                                </td>

                                                            </tr>
                                                            @endforeach

                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn save-modal btn-primary" data-dismiss="modal">{{ __('Save') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>
                        @endif
                        <?php $fg_inventory_coverage_days = isset($product->fg_inventory_coverage_days) ? $product->fg_inventory_coverage_days :old('fg_inventory_coverage_days',30); ?>
                        <div class="col-md-3">

                            <div class="form-group">
                                <label>{{__('Finished Goods Inventory Coverage Days')}} <span class="red">*</span></label>
                                <select required class="form-control  @error('fg_inventory_coverage_days') is-invalid @enderror" name="fg_inventory_coverage_days" id="fg_inventory_coverage_days">
                                    <option value="0" {{$fg_inventory_coverage_days == '0'   ?'selected' : '' }}>0</option>
                                    <option value="15" {{$fg_inventory_coverage_days == '15'  ?'selected' : '' }}>15</option>
                                    <option value="30" {{$fg_inventory_coverage_days == '30'  ?'selected' : '' }}>30</option>
                                    <option value="45" {{$fg_inventory_coverage_days == '45'  ?'selected' : '' }}>45</option>
                                    <option value="60" {{$fg_inventory_coverage_days == '60'  ?'selected' : '' }}>60</option>
                                    <option value="75" {{$fg_inventory_coverage_days == '75'  ?'selected' : '' }}>75</option>
                                    <option value="90" {{$fg_inventory_coverage_days == '90'  ?'selected' : '' }}>90</option>
                                    <option value="120" {{$fg_inventory_coverage_days == '120' ?'selected' : '' }}>120</option>
                                    <option value="150" {{$fg_inventory_coverage_days == '150' ?'selected' : '' }}>150</option>
                                    <option value="180" {{$fg_inventory_coverage_days == '180' ?'selected' : '' }}>180</option>
                                </select>
                                @error('fg_inventory_coverage_days')
                                <div class="alert alert-danger" role="alert">
                                    {{$message}}
                                </div>
                                @enderror




                            </div>

                        </div>
                    </div>



                </div>
            </div>



            <div class="div-title">
			{{ $product->getName() }}
			[{{__('Collections Plan ')}}]
                <span class="red">*</span>
            </div>
            <div class="formItem ">
                <div class="row">


                    @foreach(['local'=>__('Local') , 'export'=>__('Export')] as $localOrExport => $localOrExportTitle )
                    <div class="col-6 pl-5 @if($localOrExport == 'export') border-left-export @endif ">
                        @foreach($product->project->getFirstYearUntilLast($product->getSellingStartDateAsIndex()) as $index=>$title)
                        @php
                        $currentFieldName = "collection_policy_value[$localOrExport][$index][cash_payment]";
                        $nameToOld = generateOldNameFromFieldName($currentFieldName) ;
                        $currentVal = $product->getCollectionDownPaymentAtIndex($index,$localOrExport);

                        @endphp
                        <div class="row">
                            <div class="col-12 ">
                                {{ $title }} {{ $localOrExportTitle }} {{ __('Sales') }}
                                @include('hr')
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>{{__('Cash %')}} </label>
                                    <input type="number" step="any" class="form-control @error($nameToOld) is-invalid @enderror" value="{{$currentVal}}" name="{{ $currentFieldName }}" id="{{ $currentFieldName }}">
                                    @error($nameToOld)
                                    <div class="alert alert-danger" role="alert">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>







                            </div>


                            <input type="hidden" name="collection_policy_type" value="customize">
                            <input type="hidden" name="collection_policy_interval" value="monthly">
                            @for($numberOfCollections = 0 ; $numberOfCollections<= 1 ; $numberOfCollections++) <div class="col-md-2">

                                <div class="form-group">
                                    <label>{{__('Rate %')}} </label>
                                    @php
                                    $currentFieldName = "collection_policy_value[$localOrExport][$index][rate][$numberOfCollections]";
                                    $nameToOld = generateOldNameFromFieldName($currentFieldName) ;
                                    $currentVal = $product->getCollectionRateAtIndex($index,$numberOfCollections,$localOrExport);
                                    @endphp

                                    <input type="number" step="any" class="form-control @error($nameToOld) is-invalid @enderror" value="{{$currentVal}}" name="{{ $currentFieldName }}" id="{{ $nameToOld }}">
                                    @error($nameToOld)
                                    <div class="alert alert-danger" role="alert">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>

                        </div>


                        @php
                        $currentFieldName = "collection_policy_value[$localOrExport][$index][due_in_days][$numberOfCollections]";
                        $nameToOld = generateOldNameFromFieldName($currentFieldName) ;
                        $currentVal = $product->getCollectionDueDaysAtIndex($index,$numberOfCollections,$localOrExport);

                        @endphp

                        <div class="col-2">

                            <div class="form-group">
                                <label>{{__('Due In (Days)')}} </label>
                                <select class="form-control  @error($nameToOld) is-invalid @enderror" name="{{ $currentFieldName }}" id="{{ $nameToOld }}">
                                    @foreach(collectionDueDays() as $dueDay => $formattedDueDay )
                                    <option value="{{ $dueDay }}" {{$currentVal == $dueDay  ?'selected' : '' }}>{{ $formattedDueDay }}</option>
                                    @endforeach
                                </select>
                                @error($nameToOld)
                                <div class="alert alert-danger" role="alert">
                                    {{$message}}
                                </div>
                                @enderror
                            </div>

                        </div>
                        @endfor










                    </div>
                    @endforeach
                </div>

                @endforeach
            </div>
    </div>

    <div class="div-title toggle-show-hide position-relative" data-query=".comment-for-sales-class">

        {{__('Insert Comment')}}

    </div>
    <div id="myCard" class="formItem comment-for-sales-class">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="form-group ">
                    <button title="{{ __('Full Screen') }}" type="button" id="toggleBtn" class="fullscreen-btn">⛶</button>
                    <textarea data-is-ck-editor name="comment_for_sales">{!! $product->comment_for_sales !!}</textarea>
                </div>
            </div>

        </div>

    </div>
	
	
	<div>
	

	</div>
