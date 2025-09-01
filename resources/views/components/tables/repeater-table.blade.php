@props([
	'aboveThsTrs'=>'',
'showAddBtnAndPlus'=>true,
'repeater-with-select2'=>true,
'isRepeater'=>$isRepeater,
'relationName'=>$relationName,
'repeaterId'=>$repeaterId,
'tableName'=>$tableName ?? '',
'parentClass'=>$parentClass ?? '',
'initialJs'=>true ,
'initEmpty'=>false,
'firstElementDeletable'=>false,
'hideAddBtn'=>false,
'canAddNewItem'=>true,
'removeActionBtn'=>false,
'tableClass'=>'col-md-12',
'tableClasses'=>'',
'actionBtnTitle'=>__('Action'),
'appendSaveOrBackBtn'=>false,
'addExpenseName'=>false,
'showRows'=>true,
'fontSizeClass'=>'',
'addExpenseType'=>false,
'hideByDefault'=>true,
'scrollable'=>true
])
<style>
    .overflow-scroll {
        overflow: scroll !important;
    }

    html body table.table tbody td * {
        font-size: 12px !important;
    }

    .max-w-200 {
        max-width: 200px !important;
        width: 200px !important;
        min-width: 200px !important;
    }


    .max-w-100 {
        max-width: 100px !important;
        width: 100px !important;
        min-width: 100px !important;
    }

    .max-w-125 {
        max-width: 125px !important;
        width: 125px !important;
        min-width: 125px !important;
    }

    .max-w-150 {
        max-width: 150px !important;
        width: 150px !important;
        min-width: 150px !important;
    }

    .max-w-75 {
        max-width: 75px !important;
        width: 75px !important;
        min-width: 75px !important;
    }

    .form-label {
        white-space: nowrap !important;
    }

    .visibility-hidden {
        visibility: hidden !important;
    }

    .three-dots-parent {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 0 !important;
        margin-top: 10px;
    }

    .blue-select {
        border-color: #7096f6 !important;
    }

    .div-for-percentage {
        flex-wrap: nowrap !important;
    }

    b {
        white-space: nowrap;
    }

    i.row-repeater-icon {
        margin-left: -60px;
    }
	[data-repeater-create]{
		display:flex ;
		align-items:center !important;
		justify-content:center !important;
	}

    .total-tr {
        background-color: #074fa4 !important;
    }

    .table-striped th,
    .table-striped2 th {
        background-color: #074fa4 !important;
    }

    .total-tr td {
        color: white !important;
    }

    .total-tr .three-dots-parent {
        margin-top: 0 !important;
    }

    input.form-control[readonly] {
        background-color: #cce2fd !important;
        font-weight: bold !important;
    }

    .table-striped th,
    .table-striped2 th {
        background-color: #074da5 !important;
        color: #fff !important;
        vertical-align: middle !important;
        border: #074da5;
        text-align: center;
    }

    .table-striped td:first-child,
    .table-striped td,
    .table-striped2 td:first-child {
        text-align: center;
    }

    .table-striped td,
    .table-striped2 td {
        border: 0;
    }

    .table-striped td .btn {
        padding: 0;
        width: fit-content !important;
    }

    table:not(.removeGlobalStyle) th {
        border-color: rgb(235, 237, 242) !important;
        border-style: solid !important;
        border-width: 1px !important;
    }

    table:not(.removeGlobalStyle) td {
        border-color: rgb(225, 225, 225) !important;
        border-style: solid !important;
        border-width: 1px !important;
        border-bottom-color: rgb(225, 225, 225) !important;
        border-bottom-style: solid !important;
        border-bottom-width: 1px !important;
    }

    .three-dots-parent {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 0 !important;
        margin-top: 10px;
    }

    .expandable-percentage-input {
        max-width: 75px !important;
        min-width: 75px !important;
        text-align: center !important;
    }

    .expandable-amount-input {
        max-width: 130px !important;
        min-width: 130px !important;
        text-align: center !important;
    }

    .row-repeater-icon {
        cursor: pointer;
    }

    table td {
        vertical-align: middle !important;
    }

    .td-classes {
        vertical-align: middle !important;
        text-transform: capitalize !important;
        text-align: left !important;
    }

    html body i:not(.exclude-icon):not([data-repeating-direction="column"]) {
        color: #055dac !important;
    }

    .select-inside-repeating-table-css {
        max-width: 150px !important;
    }

    html body .js-remove-hidden {
        display: flex !important;
    }

    html body .header-border-down,
    html body .action-class {
        border-bottom: 1px solid green !important;
        /* border-bottom:1px solid #CCE2FD !important; */
        border-top: 0 !important;
        border-left: 0 !important;
        border-right: 0 !important;
    }

    html body table:not(.removeGlobalStyle) td {
        border-style: none !important;
    }

    html body table i:not([data-repeating-direction="column"]):not(.exclude-icon):not(.row-repeater-icon) {
        color: white !important;
    }

    html body [data-repeater-create] i:not([data-repeating-direction="column"]):not(.exclude-icon):not(.row-repeater-icon) {
        color: white !important
    }

    .max-w-checkbox {

        min-width: 25px !important;
        width: 25px !important;
    }

    .rate-class {
        min-width: 5% !important;
        max-width: 5% !important;
        width: 5% !important;
    }

    .interval-class {
        min-width: 10% !important;
        max-width: 10% !important;
        width: 10% !important;
    }

    .category-selector-class {
        min-width: 30% !important;
        max-width: 30% !important;
        width: 30% !important;
    }

    .reverse-category-selector-class {
        min-width: 30% !important;
        max-width: 30% !important;
        width: 30% !important;
    }

    .loan-type-class {
        min-width: 20% !important;
        max-width: 20% !important;
        width: 20% !important;
    }

    .action-class {
        min-width: 2% !important;
        max-width: 2% !important;
        width: 2% !important;
    }

    button.dropdown-toggle {
        /* border:1px solid #CCE2FD !important; */
    }

    [data-repeater-style]:nth-child(even) button.dropdown-toggle,
    [data-repeater-style]:nth-child(even) input {
        border: 1px solid #54aaa6 !important;
    }

    [data-repeater-style]:nth-child(odd) button.dropdown-toggle,
    [data-repeater-style]:nth-child(odd) input {
        border: 1px solid #4d9afa !important;
    }

    .first-th-width {
        width: 600px !important;
        min-width: 600px !important;
        max-width: 600px !important;
    }

    .currency-class {
        color: #094ea2 !important;
    }

    .font-size-18px {
        font-size: 18px !important;
    }

    html body table tr,
    html body table tr>*,
    html body table tr td {
        border-color: #CCE2FD !important;
        border: 1px solid #CCE2FD !important;
    }

    table:not(.table-condensed) thead th,
    table:not(.table-condensed) tbody td {
        padding: 5px !important;
   
        font-size: 14px !important;
    }

    .show-hide-style {
        background-color: green !important;
        color: white !important;
        cursor: pointer;
        font-weight: bold;
    }

    .show-hide-style:hover {
        background-color: #4d9afa !important;
    }

    .first-column-th-class {
        width: 30% !important;
        min-width: 30% !important;
        max-width: 30% !important;
    }

    .first-column-th-class-medium {
        width: 20% !important;
        min-width: 20% !important;
        max-width: 20% !important;
    }
	.max-w-20 {
        width: 20% !important;
        min-width: 20% !important;
        max-width: 20% !important;
    }
	.max-w-15 {
        width: 15% !important;
        min-width: 15% !important;
        max-width: 15% !important;
    }
	.max-w-10 {
        width: 10% !important;
        min-width: 10% !important;
        max-width: 10% !important;
    }
	.max-w-5 {
        width: 5% !important;
        min-width: 5% !important;
        max-width: 5% !important;
    }
    .tenor-selector-class {
        width: 5% !important;
        min-width: 5% !important;
        max-width: 5% !important;
    }

    .w-10 {
        width: 10%;
    }

    .scrollable-table {
        overflow: scroll;
    }

    .w-10 {
        width: 10%;
    }

    .btn-sm-width {
        width: 80px !important;
    }

    .btn-1-bg {
        background-color: white;
        border: 2px solid #4d9afa;
        color: black;
        font-weight: bold;
    }

    .btn-2-bg {
        background-color: white;
        border: 2px solid green;
        color: black;
        font-weight: bold;
    }

    .btn-2-bg:hover {
        background-color: green;
        border: 2px solid green;
    }

    .btn-3-bg {
        background-color: white;
        border: 2px solid #3cf5f8;
        color: black;
        font-weight: bold;
    }

    .btn-3-bg:hover {
        background-color: #3cf5f8;
        border: 2px solid #3cf5f8;
        color: black;
    }

    .btn-4-bg {
        background-color: white;
        border: 2px solid rgb(50, 12, 186);
        color: black;
        font-weight: bold;
    }

    .btn-4-bg:hover {
        background-color: rgb(50, 12, 186);
        ;
        border: 2px solid rgb(50, 12, 186);
        ;
        color: white;
    }

    .new-record-class:hover,
    .new-record-class:hover i {
        background-color: #007bff !important;
        color: white !important;

    }

    input.custom-input-numeric-width {
        width: 120px !important;
        min-width: 120px !important;
        max-width: 120px !important;
    }

    input.custom-input-string-width {
        width: 200px !important;
        min-width: 200px !important;
        max-width: 200px !important;
    }

    .w-header-400 {
        width: 400px !important;
        min-width: 400px !important;
        max-width: 400px !important;
    }



    .btn-div {
        padding: 0 !important;
        width: 30px !important;
        height: 30px !important;
    }

    .btn-div span {
        font-size: 20px !important;
        cursor: pointer;
    }

    .trash_icon {
        width: 30px;
        height: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
    }

</style>
@php

$canAddNewItem = true;
@endphp

<div class="{{ $tableClass }} {{ $parentClass }}  {{ $scrollable ? 'overflow-scroll' :'' }}  js-parent-to-table" data-table-id="{{ $repeaterId??'' }}" @if($hideByDefault) style="display:none" @endif>


    @if($showRows)

    <table @if($initialJs) id="{{ $repeaterId }}" @endif class="table  {{ $repeaterId }} {{ $tableClasses }} table-white  repeater-class repeater {{ $tableName }}">
        <thead>
			{{ $aboveThsTrs }}
            <tr>
                @if(!$removeActionBtn)
                <x-tables.repeater-table-th :fontSizeClass="$fontSizeClass" class="col-md-1 action-class" :title="'+/-'"></x-tables.repeater-table-th>
                @endif
                {{ $ths }}
            </tr>
        </thead>
        <tbody data-repeater-list="{{$tableName}}">
            @if(isset($model) && $model->{$relationName}->count() )

            @foreach($model->{$relationName} as $subModel)
            <x-tables.repeater-table-tr :isRepeater="true" :model="$subModel"></x-tables.repeater-table-tr>

            @endforeach
            @else
            <x-tables.repeater-table-tr :trs="$trs" :isRepeater="true">

            </x-tables.repeater-table-tr>

            @endif

        </tbody>
        <td>
            @if($showAddBtnAndPlus)
            @if($canAddNewItem && !$removeActionBtn)
            <div data-repeater-create="" class="btn btn btn-sm text-white add-row btn-div  border-green bg-green  m-btn m-btn--icon m-btn--pill m-btn--wide {{__('right')}}">
                <span>
                    +
                    {{-- <i class="fa fa-plus"> </i> --}}
                    <span>
                        {{-- @if(!$hideAddBtn)
                        {{ __('Add') }}
                        @endif --}}
                    </span>
                </span>
            </div>
            @endif
            @endif
        </td>

    </table>
    @endif

</div>

<input type="hidden" id="initi-empty-{{ $repeaterId }}" value="{{ $initEmpty }}">
<input type="hidden" id="first-element-deleteable-{{ $repeaterId }}" value="{{ $firstElementDeletable }}">
@if($initialJs)
@push('js_end')

<script>
    var initEmpty = $("#initi-empty-{{ $repeaterId }}").val() === "1" ? true : false;
    var firstElementDeleteable = $("#first-element-deleteable-{{ $repeaterId }}").val() === "1" ? true : false;
    var studyStartDate = $('#study-start-date').val()
    var studyEndDate = $('#study-end-date').val()



    $(document).ready(function() {
        var selector = "#{{ $repeaterId }}";
	
        $(selector).repeater({
            initEmpty: initEmpty
            , defaultValues: {
                'position': ''
                , 'avg_salary': '0'
                , 'existing_count': '0'
            }
            , show: function() {
                $(this).slideDown();

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
@endpush
@endif
