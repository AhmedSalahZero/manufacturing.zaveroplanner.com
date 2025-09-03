@php
	$tableClasses = $isYearsStudy ?  'col-md-6 margin__left' : 'col-md-12';
@endphp
<x-tables.repeater-table :scrollable="false" :table-class="$tableClasses" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
    <x-slot name="ths">
        <x-tables.repeater-table-th class="  header-border-down first-column-th-class" :title="__('Item')"></x-tables.repeater-table-th>
        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
        <x-tables.repeater-table-th class=" interval-class header-border-down " :title="$yearOrMonthFormatted"></x-tables.repeater-table-th>
        @endforeach
    </x-slot>
    <x-slot name="trs">
	







        <tr data-repeat-formatting-decimals="2" data-repeater-style>


            @php
            $currentExpenseType = 'raw-material-cost';

            @endphp
            <td>
                <div class="">
                    <input value="{{ __(' % / REV') }}" disabled class="form-control text-left " type="text">
                </div>


            </td>
            @php
            $columnIndex = 0 ;


            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentExpense = $formattedExpenses[$currentExpenseType][$yearOrMonthAsIndex]??0;
            $currentSalesRevenue = $formattedResult['sales_revenue'][$yearOrMonthAsIndex]??0 ;
            $currentVal = $currentSalesRevenue ? $currentExpense / $currentSalesRevenue * 100 : 0 ;
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

        <tr data-repeat-formatting-decimals="2" data-repeater-style>



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
            $currentExpense = $formattedExpenses['labor-cost'][$yearOrMonthAsIndex]??0;
            $currentSalesRevenue = $formattedResult['sales_revenue'][$yearOrMonthAsIndex]??0 ;
            $currentVal = $currentSalesRevenue ? $currentExpense / $currentSalesRevenue * 100 : 0 ;

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
		
		
		 <tr data-repeat-formatting-decimals="2" data-repeater-style>



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
            $currentExpense = $formattedExpenses['manufacturing-overheads'][$yearOrMonthAsIndex]??0;
            $currentSalesRevenue = $formattedResult['sales_revenue'][$yearOrMonthAsIndex]??0 ;
            $currentVal = $currentSalesRevenue ? $currentExpense / $currentSalesRevenue * 100 : 0 ;

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









        <tr data-repeat-formatting-decimals="2" data-repeater-style>



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
            $currentExpense = $formattedExpenses['marketing-expense'][$yearOrMonthAsIndex]??0;
            $currentSalesRevenue = $formattedResult['sales_revenue'][$yearOrMonthAsIndex]??0 ;
            $currentVal = $currentSalesRevenue ? $currentExpense / $currentSalesRevenue * 100 : 0 ;
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







        <tr data-repeat-formatting-decimals="2" data-repeater-style>



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
            $currentExpense = $formattedExpenses['sales-expense'][$yearOrMonthAsIndex]??0;
            $currentSalesRevenue = $formattedResult['sales_revenue'][$yearOrMonthAsIndex]??0 ;
            $currentVal = $currentSalesRevenue ? $currentExpense / $currentSalesRevenue * 100 : 0 ;
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








        <tr data-repeat-formatting-decimals="2" data-repeater-style>



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
            $currentExpense = $formattedExpenses['general-expense'][$yearOrMonthAsIndex]??0;
            $currentSalesRevenue = $formattedResult['sales_revenue'][$yearOrMonthAsIndex]??0 ;
            $currentVal = $currentSalesRevenue ? $currentExpense / $currentSalesRevenue * 100 : 0 ;
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
