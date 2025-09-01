@extends('layouts.app')
@section('content')
<style>
</style>
<div class="col-12">

    <h1 class="d-flex justify-content-between steps-span">
        <span><a href="{{ url()->previous() }}" style="color: white">
                < {{ __('Back') }}</a></span>
        <span>{{ __('Step ') . $step_data['place_num'] . '/' . $step_data['count'] }}</span>
    </h1>
    <h1 class="bread-crumbs">
        {{ __('ZAVERO Manufacturing') }} > {{ $project->name }} > {{ __($step_data['route_name']) }}
    </h1>


    @include('financial-results._content')

</div>
<div class="clearfix"></div>
@endsection

@section('js')


<script src="https://cdn.jsdelivr.net/npm/jquery.repeater@1.2.1/jquery.repeater.min.js"></script>
<script>
    // Define translated strings for JavaScript
    var translations = {
        deleteConfirm: @json(__('Are you sure you want to delete this position?'))
    };

    $(document).ready(function() {
        var selector = "#fixedAssets_repeater";
        $(selector).repeater({
            initEmpty: false
            , defaultValues: {
                'category_id': 'manufacturing-expenses'
                , 'payment_terms': 'cash'
                , 'equity_funding_rate': 0
                , 'replacement_cost_rate': 1
                , 'depreciation_duration': 5
                , 'amount': 0
                , 'tenor': 60
                , 'interest_rate': 0
                , 'installment_interval': 'monthly'
                , 'replacement_cost_interval': 1
            }
            , show: function() {
                $(this).slideDown();
                $('.js-select2-with-one-selection').select2({});
                $('.hundred-minus-number').trigger('change')
                recalculateAllocations(this);
                // $('.hundred-minus-number').trigger('change')
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


@endsection
