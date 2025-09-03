@php
	$tableClass = $isYearsStudy ? 'col-md-6 margin__left'  : 'col-md-12';
@endphp

<x-tables.repeater-table :scrollable="false" :table-class="$tableClass" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                                <x-slot name="ths">
                                    <x-tables.repeater-table-th class="  header-border-down first-column-th-class" :title="__('Item')"></x-tables.repeater-table-th>
                                   @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                    <x-tables.repeater-table-th class=" interval-class header-border-down " :title="$yearOrMonthFormatted"></x-tables.repeater-table-th>
                                    @endforeach
                                </x-slot>
                                <x-slot name="trs">
								







                                    <tr data-repeat-formatting-decimals="1" data-repeater-style>



                                        <td>
                                            <div class="">
                                                <input value="{{ __('Growth Rate %') }}" disabled class="form-control text-left " type="text">
                                            </div>


                                        </td>
                                        @php
                                        $columnIndex = 0 ;

                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                        @php
                                        $currentVal = $formattedResult['growth_rate'][$yearOrMonthAsIndex] ?? 0;
                                        @endphp
                                        <td>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <x-repeat-right-dot-inputs :disabled="true" numberFormatDecimals="1" :removeThreeDotsClass="true" :removeThreeDots="true" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                            </div>
                                        </td>
                                        @php
                                        $columnIndex++;
                                        @endphp
                                        @endforeach



                                    </tr>
















                                    <tr data-repeat-formatting-decimals="1" data-repeater-style>



                                        <td>
                                            <div class="">
                                                <input value="{{ __('% / REV.') }}" disabled class="form-control text-left " type="text">
                                            </div>


                                        </td>
                                        @php
                                        $columnIndex = 0 ;


                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                        @php
                                        $currentVal = $formattedResult['gross_profit_percentage_of_sales'][$yearOrMonthAsIndex] ?? 0;
                                        @endphp
                                        <td>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="1" :removeThreeDotsClass="true" :removeThreeDots="true" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                                            </div>
                                        </td>
                                        @php
                                        $columnIndex++;
                                        @endphp
                                        @endforeach



                                    </tr>












                                    <tr data-repeat-formatting-decimals="1" data-repeater-style>



                                        <td>
                                            <div class="">
                                                <input value="{{ __('% / REV.') }}" disabled class="form-control text-left " type="text">
                                            </div>


                                        </td>
                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                        @php
                                        $currentVal = $formattedResult['ebitda_percentage_of_sales'][$yearOrMonthAsIndex] ?? 0;
                                        @endphp
                                        <td>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="1" :removeThreeDotsClass="true" :removeThreeDots="true" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                                            </div>
                                        </td>
                                        @php
                                        $columnIndex++;
                                        @endphp
                                        @endforeach



                                    </tr>







                                    <tr data-repeat-formatting-decimals="1" data-repeater-style>

                                        <td>
                                            <div class="">
                                                <input value="{{ __('% / REV.') }}" disabled class="form-control text-left " type="text">
                                            </div>


                                        </td>
                                        @php
                                        $columnIndex = 0 ;

                                        @endphp
                                       @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                        @php
                                        $currentVal = $formattedResult['ebit_percentage_of_sales'][$yearOrMonthAsIndex] ?? 0;
                                        @endphp
                                        <td>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="1" :removeThreeDotsClass="true" :removeThreeDots="true" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                                            </div>
                                        </td>
                                        @php
                                        $columnIndex++;
                                        @endphp
                                        @endforeach



                                    </tr>







                                    <tr data-repeat-formatting-decimals="1" data-repeater-style>



                                        <td>
                                            <div class="">
                                                <input value="{{ __('% / REV.') }}" disabled class="form-control text-left " type="text">
                                            </div>


                                        </td>
                                        @php
                                        $columnIndex = 0 ;


                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                        @php
                                        $currentVal = $formattedResult['ebt_percentage_of_sales'][$yearOrMonthAsIndex] ?? 0;
                                        @endphp
                                        <td>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="1" :removeThreeDotsClass="true" :removeThreeDots="true" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                                            </div>
                                        </td>
                                        @php
                                        $columnIndex++;
                                        @endphp
                                        @endforeach



                                    </tr>








                                    <tr data-repeat-formatting-decimals="1" data-repeater-style>



                                        <td>
                                            <div class="">
                                                <input value="{{ __('% / REV.') }}" disabled class="form-control text-left " type="text">
                                            </div>


                                        </td>
                                        @php
                                        $columnIndex = 0 ;


                                        @endphp
                                       @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                        @php
                                        $currentVal = $formattedResult['net_profit_percentage_of_sales'][$yearOrMonthAsIndex] ?? 0;
                                        @endphp

                                        <td>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="1" :removeThreeDotsClass="true" :removeThreeDots="true" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                                            </div>
                                        </td>
                                        @php
                                        $columnIndex++;
                                        @endphp
                                        @endforeach



                                    </tr>







                                </x-slot>




                            </x-tables.repeater-table>
