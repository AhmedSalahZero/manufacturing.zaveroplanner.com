@php
$tableClasses = $isYearsStudy ? 'col-md-6' : 'col-md-12';
@endphp

<x-tables.repeater-table :scrollable="false" :table-class="$tableClasses" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
    <x-slot name="ths">
        <x-tables.repeater-table-th class="  header-border-down max-column-th-class" :title="__('Item')"></x-tables.repeater-table-th>
        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
        <x-tables.repeater-table-th class=" interval-class header-border-down " :title="$yearOrMonthFormatted"></x-tables.repeater-table-th>
        @endforeach
    </x-slot>
    <x-slot name="trs">



        <tr data-repeat-formatting-decimals="0" data-repeater-style>
            @php
            $key ='raw-material-cost';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('Raw Material Cost (Fig In Million)') ;
            @endphp
            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('Raw Material Cost') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedExpenses[$key] ?? []])
                    </div>

                    {{-- <button class="btn btn-sm btn-brand btn-elevate btn-pill text-white ml-3" data-toggle="modal" data-target="#id">
													</button>   --}}

                </div>
            </td>


            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedExpenses[$key][$yearOrMonthAsIndex]??0) / getDivisionNumber();
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="0" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden js-recalculate-equity-funding-value'" :is-percentage="false" :mark="' '" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                </div>
            </td>
            @php
            $columnIndex++ ;
            @endphp

            @endforeach


        </tr>







        <tr data-repeat-formatting-decimals="0" data-repeater-style>
            @php
            $key ='labor-cost';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('Labor Cost (Fig In Million)' ) ;
            @endphp

            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('Labor Cost') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedExpenses[$key] ?? []])

                    </div>

                </div>

            </td>


            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedExpenses[$key][$yearOrMonthAsIndex]??0) / getDivisionNumber();
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="0" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden js-recalculate-equity-funding-value'" :is-percentage="false" :mark="' '" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                </div>
            </td>
            @php
            $columnIndex++ ;
            @endphp

            @endforeach


        </tr>






        <tr data-repeat-formatting-decimals="0" data-repeater-style>
            @php
            $key ='manufacturing-overheads';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('Manufacturing Overheads (Fig In Million)' ) ;
            @endphp

            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('Manufacturing Overheads') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedExpenses[$key] ?? []])

                    </div>

                </div>

            </td>


            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedExpenses[$key][$yearOrMonthAsIndex]??0) / getDivisionNumber();
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="0" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden js-recalculate-equity-funding-value'" :is-percentage="false" :mark="' '" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                </div>
            </td>
            @php
            $columnIndex++ ;
            @endphp

            @endforeach


        </tr>



        <tr data-repeat-formatting-decimals="0" data-repeater-style>

            @php
            $key ='marketing-expense';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('Marketing Expenses (Fig In Million)') ;
            @endphp

            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('Marketing Expenses') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedExpenses[$key] ?? []])
                    </div>

                </div>

            </td>


            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedExpenses['marketing-expense'][$yearOrMonthAsIndex]??0) / getDivisionNumber();
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="0" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden js-recalculate-equity-funding-value'" :is-percentage="false" :mark="' '" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                </div>

            </td>
            @php
            $columnIndex++ ;
            @endphp

            @endforeach


        </tr>





        <tr data-repeat-formatting-decimals="0" data-repeater-style>

            @php
            $key ='sales-expense';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('Sales Expense (Fig In Million)') ;
            @endphp

            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('Sales Expenses') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedExpenses[$key] ?? []])
                    </div>

                </div>
            </td>


            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedExpenses['sales-expense'][$yearOrMonthAsIndex]??0) / getDivisionNumber();
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="0" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden js-recalculate-equity-funding-value'" :is-percentage="false" :mark="' '" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                </div>
            </td>
            @php
            $columnIndex++ ;
            @endphp

            @endforeach


        </tr>


        <tr data-repeat-formatting-decimals="0" data-repeater-style>
            @php
            $key ='general-expense';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('General Expenses (Fig In Million)') ;
            @endphp

            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('General Expenses') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedExpenses[$key] ?? []])

                    </div>
                </div>


            </td>


            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedExpenses['general-expense'][$yearOrMonthAsIndex]??0) / getDivisionNumber();
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="0" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden js-recalculate-equity-funding-value'" :is-percentage="false" :mark="' '" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                </div>
            </td>
            @php
            $columnIndex++ ;
            @endphp

            @endforeach


        </tr>














    </x-slot>




</x-tables.repeater-table>
