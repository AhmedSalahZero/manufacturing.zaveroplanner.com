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
           @include('expenses._content',$project->getExpensesViewVars())

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
		 }
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
@endsection
