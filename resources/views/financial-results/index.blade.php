@extends('layouts.app')
@section('content')
<style>
input.form-control[type="text"][readonly] {
    background-color: white !important;
    color: black !important;
    font-weight: 400 !important;
}
</style>
<div class="col-12">

    <h1 class="bread-crumbs">
        {{ __('ZAVERO Manufacturing') }} > {{ $project->name }} > {{ $title }}
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
