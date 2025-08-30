@extends('layouts.app')
@section('content')
<style>

</style>
<div class="col-12">
	<div id="number-of-products" data-value="{{ count($products) }}"></div>
    <h1 class="d-flex justify-content-between steps-span">
        <span><a href="{{ url()->previous() }}" style="color: white">
                < {{ __('Back') }}</a></span>
        <span>{{ __('Step ') . $step_data['place_num'] . '/' . $step_data['count'] }}</span>
    </h1>
    <h1 class="bread-crumbs">
        {{ __('ZAVERO Manufacturing') }} > {{ $project->name }} > {{ __($step_data['route_name']) }}
    </h1>
    <div class="ProjectList">

        <form action="{{ route('expenses.submit', $project) }}" method="POST">
            {{ csrf_field() }}
            <div class="col-12 alert alert-info text-center">
                <span class="red">{{ __('If you have information please fill or click next') }}</span>
            </div>
            <div class="div-title">
                {{ __('Expense As Percentage Of Revenues') }}
            </div>

            <div class="formItem">

                <div class="col-12">
			

                    @php
                    $repeaterId = 'expense_as_percentage';
                    @endphp
                    <div id="{{ $repeaterId }}_repeater" class="rooms-repeater">
                        <div class="form-group  m-form__group row">
                            <div data-repeater-list="{{ $repeaterId }}" class="col-lg-12">

                                @include('expenses._percentage_repeater',['repeaterId'=>$repeaterId,'expenseType'=>$repeaterId,'expenses'=>$expenses->where('relation_name',$repeaterId)])


                            </div>
                        </div>
                        <input data-repeater-create type="button" class="btn btn-success btn-sm " value="{{ __('Add Expense') }}">
                    </div>

                </div>





            </div>


            <div class="div-title">
                {{ __('Monthly Fixed Expenses') }}
            </div>


            <div class="formItem">

                <div class="col-12">
                    @php
                    $repeaterId = 'fixed_monthly_repeating_amount';
                    @endphp
                    <div id="{{ $repeaterId }}_repeater" class="rooms-repeater">
                        <div class="form-group  m-form__group row">
                            <div data-repeater-list="{{ $repeaterId }}" class="col-lg-12">
                                @include('expenses._fixed_monthly_repeater',['repeaterId'=>$repeaterId,'expenseType'=>$repeaterId,'expenses'=>$expenses->where('relation_name',$repeaterId)])
                            </div>
                        </div>
                        <input data-repeater-create type="button" class="btn btn-success btn-sm " value="{{ __('Add Expense') }}">
                    </div>

                </div>





            </div>

            <div class="div-title">
                {{ __('One Time Expense') }}
            </div>


            <div class="formItem">

                <div class="col-12">
                    @php
                    $repeaterId = 'one_time_expense';
                    @endphp
                    <div id="{{ $repeaterId }}_repeater" class="rooms-repeater">
                        <div class="form-group  m-form__group row">
                            <div data-repeater-list="{{ $repeaterId }}" class="col-lg-12">

                                @include('expenses._one_time_repeater',['repeaterId'=>$repeaterId,'expenseType'=>$repeaterId,'expenses'=>$expenses->where('relation_name',$repeaterId)])


                            </div>
                        </div>
                        <input data-repeater-create type="button" class="btn btn-success btn-sm " value="{{ __('Add Expense') }}">
                    </div>

                </div>





            </div>

            <button type="submit" class="btn btn-rev float-right" name="submit_button" value="next">{{ __('Next') }}</button>
            <button type="submit" class="btn btn-rev float-right main-page-button" name="submit_button" value="save">{{ __('Save & Go To Main Page') }}</button>

        </form>
    </div>
</div>
<div class="clearfix"></div>
@endsection

@section('js')


<script src="https://cdn.jsdelivr.net/npm/jquery.repeater@1.2.1/jquery.repeater.min.js"></script>
<script>
  var translations = {
        deleteConfirm: @json(__('Are you sure you want to delete this position?'))
    };
</script>
@foreach(getExpensesTypes() as $expenseType)
<script>
    $(document).ready(function() {
        var selector = "#{{ $expenseType.'_repeater' }}";
        $(selector).repeater({
            initEmpty: false
            , defaultValues: {
                'category_id': 'manufacturing-expenses'
                , 'payment_terms': 'cash'
            , }
            , show: function() {
                $(this).slideDown();
				$('.js-select2-with-one-selection').select2({});
				recalculateAllocations(this);
				
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
@endforeach
{{-- <script>
   $(document).ready(function() {
        var selector = "#fixed_monthly_repeating_amount_repeater";
        $(selector).repeater({
            initEmpty: false
            , defaultValues: {
                'category_id': 'manufacturing-expenses'
                , 'payment_terms': 'cash'
            , }
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


    $(document).ready(function() {
        var selector = "#one_time_expense_repeater";
        $(selector).repeater({
            initEmpty: false
            , defaultValues: {
                'category_id': 'manufacturing-expenses'
                , 'payment_terms': 'cash'
            , }
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
</script> --}}

@endsection
