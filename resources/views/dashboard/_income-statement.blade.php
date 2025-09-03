@php
	$tableClass = $isYearsStudy ? 'col-md-6'  : 'col-md-12';
@endphp
<x-tables.repeater-table :scrollable="false" :table-class="$tableClass" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="''" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
    <x-slot name="ths">
        <x-tables.repeater-table-th class="  header-border-down first-column-th-class" :title="__('Item')"></x-tables.repeater-table-th>
        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
        <x-tables.repeater-table-th class=" interval-class header-border-down" :title="$yearOrMonthFormatted"></x-tables.repeater-table-th>
        @endforeach
    </x-slot>
    <x-slot name="trs">
	


        <tr data-repeat-formatting-decimals="1" data-repeater-style>

            <td>
                <input value="{{ __('Total Revenues') }}" disabled class="form-control text-left " type="text">
            </td>
            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedResult['sales_revenue'][$yearOrMonthAsIndex]??0) / getDivisionNumber() ;
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="0" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed  total-loans-hidden js-recalculate-equity-funding-value'" :is-percentage="false" :mark="' '" :name="'IjaraMortgageRevenueProjectionByCategory['.'ijara_mortgage_transactions_projections'.']['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                </div>
            </td>
            @php
            $columnIndex++ ;
            @endphp

            @endforeach


        </tr>
        <tr data-repeat-formatting-decimals="1" data-repeater-style>
            <td>
                <input value="{{ __('Gross Profit') }}" disabled class="form-control text-left " type="text">
            </td>
            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
		$currentVal = ($formattedResult['gross_profit'][$yearOrMonthAsIndex]??0) / getDivisionNumber();
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="0" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden js-recalculate-equity-funding-value'" :is-percentage="false" :mark="' '" :name="'IjaraMortgageRevenueProjectionByCategory['.'ijara_mortgage_transactions_projections'.']['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                </div>
            </td>
            @php
            $columnIndex++ ;
            @endphp

            @endforeach
        </tr>
        <tr data-repeat-formatting-decimals="1" data-repeater-style>
            <td>
                <input value="{{ __('EBITDA') }}" disabled class="form-control text-left " type="text">
            </td>


            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
		$currentVal = ($formattedResult['ebitda'][$yearOrMonthAsIndex]??0) / getDivisionNumber();
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="0" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden js-recalculate-equity-funding-value'" :is-percentage="false" :mark="' '" :name="'IjaraMortgageRevenueProjectionByCategory['.'ijara_mortgage_transactions_projections'.']['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                </div>
            </td>
            @php
            $columnIndex++ ;
            @endphp

            @endforeach


        </tr>





        <tr data-repeat-formatting-decimals="1" data-repeater-style>

            <td>
                <input value="{{ __('EBIT') }}" disabled class="form-control text-left " type="text">
            </td>


            @php
            $columnIndex = 0 ;
            @endphp
           @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
		$currentVal = ($formattedResult['ebit'][$yearOrMonthAsIndex]??0) / getDivisionNumber();
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="0" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden js-recalculate-equity-funding-value'" :is-percentage="false" :mark="' '" :name="'IjaraMortgageRevenueProjectionByCategory['.'ijara_mortgage_transactions_projections'.']['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                </div>
            </td>
            @php
            $columnIndex++ ;
            @endphp

            @endforeach


        </tr>


        <tr data-repeat-formatting-decimals="1" data-repeater-style>

            <td>
                <input value="{{ __('EBT') }}" disabled class="form-control text-left " type="text">
            </td>


            @php
            $columnIndex = 0 ;
            @endphp
           @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
		$currentVal = ($formattedResult['ebt'][$yearOrMonthAsIndex]??0) / getDivisionNumber();
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="0" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden js-recalculate-equity-funding-value'" :is-percentage="false" :mark="' '" :name="'IjaraMortgageRevenueProjectionByCategory['.'ijara_mortgage_transactions_projections'.']['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                </div>
            </td>
            @php
            $columnIndex++ ;
            @endphp

            @endforeach


        </tr>



        <tr data-repeat-formatting-decimals="1" data-repeater-style>

            <td>
                <input value="{{ __('Net Profit') }}" disabled class="form-control text-left " type="text">
            </td>


            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
		$currentVal = ($formattedResult['net_profit'][$yearOrMonthAsIndex]??0) / getDivisionNumber();
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="0" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden js-recalculate-equity-funding-value'" :is-percentage="false" :mark="' '" :name="'IjaraMortgageRevenueProjectionByCategory['.'ijara_mortgage_transactions_projections'.']['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                </div>
            </td>
            @php
            $columnIndex++ ;
            @endphp

            @endforeach


        </tr>











    </x-slot>




</x-tables.repeater-table>
