@php
$tableClasses =  'col-md-12';
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
            $key ='ebit';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('+/- EBIT (Fig In Million)') ;
            @endphp
            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('+/- EBIT') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedDcfMethod[$key] ?? []])
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
            $currentVal = ($formattedDcfMethod[$key][$yearOrMonthAsIndex]??0)  / getDivisionNumber();
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
            $key ='taxes';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('(-) Taxes (Fig In Million)') ;
            @endphp
            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('(-) Taxes') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedDcfMethod[$key] ?? []])
                    </div>



                </div>
            </td>


            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedDcfMethod[$key][$yearOrMonthAsIndex]??0)/ getDivisionNumber() ;
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
            $key ='depreciation';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('(+) Depreciation (Fig In Million)') ;
            @endphp
            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('(+) Depreciation') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedDcfMethod[$key] ?? []])
                    </div>



                </div>
            </td>


            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedDcfMethod[$key][$yearOrMonthAsIndex]??0) / getDivisionNumber();
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
            $key ='net-change-in-working-capital';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('(+) Net Change In Working Capital (Fig In Million)') ;
            @endphp
            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('(+) Net Change In Working Capital') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedDcfMethod[$key] ?? []])
                    </div>



                </div>
            </td>


            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedDcfMethod[$key][$yearOrMonthAsIndex]??0)/ getDivisionNumber() ;
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
            $key ='capex';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('(-) CAPEX (Fig In Million)') ;
            @endphp
            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('(-) CAPEX') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedDcfMethod[$key] ?? []])
                    </div>



                </div>
            </td>


            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedDcfMethod[$key][$yearOrMonthAsIndex]??0)/ getDivisionNumber() ;
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
            $key ='free-cashflow';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('(+/-) Free Cash flow (Fig In Million)') ;
            @endphp
            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('(+/-) Free Cash flow') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedDcfMethod[$key] ?? []])
                    </div>



                </div>
            </td>


            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedDcfMethod[$key][$yearOrMonthAsIndex]??0) / getDivisionNumber();
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
            $key ='terminal-value';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('(+) Terminal Value (Fig In Million)') ;
            @endphp
            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('(+) Terminal Value') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedDcfMethod[$key] ?? []])
                    </div>



                </div>
            </td>


            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedDcfMethod[$key][$yearOrMonthAsIndex]??0) / getDivisionNumber();
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
            $key ='free-cashflow-with-terminal';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('(+) Free Cashflow With Terminal (Fig In Million)') ;
            @endphp
            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('(+) Free Cashflow With Terminal') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedDcfMethod[$key] ?? []])
                    </div>



                </div>
            </td>


            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedDcfMethod[$key][$yearOrMonthAsIndex]??0)/ getDivisionNumber() ;
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
		
		
		 <tr data-repeat-formatting-decimals="4" data-repeater-style>
            @php
            $key ='discount-factor';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('Discount Factor (Fig In Million)') ;
            @endphp
            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('Discount Factor') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedDcfMethod[$key] ?? []])
                    </div>



                </div>
            </td>


            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedDcfMethod[$key][$yearOrMonthAsIndex]??0) ;
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="4" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden js-recalculate-equity-funding-value'" :is-percentage="false" :mark="' '" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                </div>
            </td>
            @php
            $columnIndex++ ;
            @endphp

            @endforeach


        </tr>
		
		
		 <tr data-repeat-formatting-decimals="0" data-repeater-style>
            @php
            $key ='npv';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('Net Present Value (Fig In Million)') ;
            @endphp
            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('Net Present Value (NPV)') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedDcfMethod[$key] ?? []])
                    </div>



                </div>
            </td>


            @php
            $columnIndex = 0 ;
			$isFirstLoop=true;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedDcfMethod[$key][$yearOrMonthAsIndex]??0) / getDivisionNumber();
            @endphp
            <td>
					@if($isFirstLoop)
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="0" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden js-recalculate-equity-funding-value'" :is-percentage="false" :mark="' '" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                </div>
				@php
					$isFirstLoop = false;
				@endphp
				
				@endif
            </td>
            @php
            $columnIndex++ ;
            @endphp

            @endforeach


        </tr>
		
		@if($project->isNewCompany())
		
		 <tr data-repeat-formatting-decimals="2" data-repeater-style>
            @php
            $key ='irr';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('IRR %') ;
            @endphp
            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('IRR %') }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedDcfMethod[$key] ?? []])
                    </div>



                </div>
            </td>


            @php
            $columnIndex = 0 ;
			$isFirstLoop=true;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = ($formattedDcfMethod[$key][$yearOrMonthAsIndex]??0);
            @endphp
            <td>
			@if($isFirstLoop)
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :isNumber="false" :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="0" :currentVal="number_format($currentVal,2).'%'" :classes="''" :is-percentage="false" :mark="' '" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                </div>
				@php
					$isFirstLoop = false;
				@endphp
            @endif 
			</td>
			
            @php
            $columnIndex++ ;
            @endphp

            @endforeach


        </tr>
		
		@endif 
		
		

    </x-slot>




</x-tables.repeater-table>
