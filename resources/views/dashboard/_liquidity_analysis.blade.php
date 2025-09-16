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
		@foreach($formattedDcfMethod as $key => $arrItem)
		 @php
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = $arrItem['title'] ;
			$isDivided = $arrItem['is_divided'];
			$numberDecimals = $arrItem['number-format'];
			$mark = $arrItem['mark'];
			$isNumber = $arrItem['is_number'];
            @endphp
        <tr data-repeat-formatting-decimals="{{ $numberDecimals }}" data-repeater-style>
           
            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ $arrItem['title'] }}" disabled class="form-control text-left " type="text">
                    <div>
                        <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                        @include('dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$arrItem['data'] ?? []])
                    </div>
                </div>
            </td>
            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
			$currentVal = $arrItem['data'][$yearOrMonthAsIndex]??0 ;
			if($isDivided){
				$currentVal = $currentVal / getDivisionNumber();
			}
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :isNumber="$isNumber" :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="$numberDecimals" :currentVal="$currentVal" :classes="''" :is-percentage="false" :mark="$mark" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
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
