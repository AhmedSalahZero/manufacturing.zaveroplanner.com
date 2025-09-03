@extends('layouts.app')
@section('content')
<style>

</style>
<div class="container-main-width">
    <h1 class="d-flex justify-content-between steps-span">
        <span><a href="{{ url()->previous() }}" style="color: white">
                < {{__("Back")}}</a></span>
        <span>{{ __('Step ') . $currentStepNumber .'/'. $totalSteps }}</span>
    </h1>
    <h1 class="bread-crumbs">
        {{ __("ZAVERO Manufacturing") }} > {{$project->name}} > {{ $product->getName() }}
    </h1>
    <div class="ProjectList">
        <form action="{{route('products.submit',[$project,$product->id])}}" method="POST">
            {{ csrf_field() }}

			@include('products._content')
          



    <button type="submit" class="btn btn-rev float-right" name="submit_button" value="next">{{__('Next')}}</button>
    <button type="submit" class="btn btn-rev float-right main-page-button" name="submit_button" value="save">{{__('Save & Go To Main Page')}}</button>

    </form>
</div>
</div>
<div class="clearfix"></div>
@endsection

@section('js')
<script>
    $('#seasonality').on('change', function() {
        var seasonality = $(this).val();
        if (seasonality == 'flat') {
            $('.flat_section').removeClass('hidden');
            $('.quarterly_section').addClass('hidden');
            $('.monthly_section').addClass('hidden');
            $('.percentage').addClass('hidden');
            $('.quarterly').val('');
            $('.monthly').val('');
        } else if (seasonality == 'quarterly') {
            $('.flat_section').addClass('hidden');
            $('.monthly_section').addClass('hidden');
            $('.quarterly_section').removeClass('hidden');
            $('.percentage').removeClass('hidden');
            $('.monthly').val('');
        } else if (seasonality == 'monthly') {
            $('.flat_section').addClass('hidden');
            $('.quarterly_section').addClass('hidden');
            $('.monthly_section').removeClass('hidden');
            $('.percentage').removeClass('hidden');
            $('.quarterly').val('');
        } else {
            $('.flat_section').addClass('hidden');
            $('.quarterly_section').addClass('hidden');
            $('.monthly_section').addClass('hidden');
            $('.percentage').removeClass('hidden');
            $('.quarterly').val('');
            $('.monthly').val('');
        }
    });
    ////////////////////////////////////////////////////////
    $('#month').on('change', function() {
        var month = $(this).val();

    });

</script>

<script>
    // Down Payment
    $('#outsourcing_down_payment,#balance_rate_one').on('change', function() {
        var down_payment = $('#outsourcing_down_payment').val();
        var balance_rate_one = $('#balance_rate_one').val();
        if (down_payment > 100) {
            $('#outsourcing_down_payment').val(100);
            $('#balance_rate_one').val(0);
            // $('#balance_rate_two').val(0);
        } else if (balance_rate_one > 100) {
            $('#outsourcing_down_payment').val(0);
            $('#balance_rate_one').val(100);
            // $('#balance_rate_two').val(0);
        } else {
            var Max_Nubmer_Annual_Collection = 100 - (parseFloat(down_payment));
            document.getElementById("balance_rate_one").max = Max_Nubmer_Annual_Collection;

            if (balance_rate_one == '') {
                balance_rate_one = 0
            }
            $('#balance_rate_one').val(100 - (parseFloat(down_payment)));
        }
    });
    // Down Payment
    $('#collection_down_payment,#initial_collection_rate').on('change', function() {
        var down_payment = $('#collection_down_payment').val();
        var initial_collection_rate = $('#initial_collection_rate').val();
        if (down_payment > 100) {
            $('#collection_down_payment').val(100);
            $('#initial_collection_rate').val(0);
            $('#final_collection_rate').val(0);
        } else if (initial_collection_rate > 100) {
            $('#collection_down_payment').val(0);
            $('#initial_collection_rate').val(100);
            $('#final_collection_rate').val(0);
        } else {
            var Max_Nubmer_Annual_Collection = 100 - (parseFloat(down_payment));
            document.getElementById("initial_collection_rate").max = Max_Nubmer_Annual_Collection;

            if (initial_collection_rate == '') {
                initial_collection_rate = 0
            }
            $('#final_collection_rate').val(100 - (parseFloat(down_payment) + parseFloat(initial_collection_rate)));
        }
    });
    // Down Payment
    $('#initial_phase_rate,#final_phase_rate').on('change', function() {
        var initial_phase_rate = $('#initial_phase_rate').val();
        var final_phase_rate = $('#final_phase_rate').val();
        if (initial_phase_rate > 100) {
            $('#initial_phase_rate').val(100);
            $('#final_phase_rate').val(0);
        } else {
            var Max_Nubmer_Annual_Collection = 100 - (parseFloat(initial_phase_rate));
            document.getElementById("final_phase_rate").max = Max_Nubmer_Annual_Collection;

            $('#final_phase_rate').val(Max_Nubmer_Annual_Collection);
        }
    });

</script>
<script>
    var translations = {
        deleteConfirm: @json(__('Are you sure you want to delete this position?'))
    };

    $(document).ready(function() {
        var selector = ".repeater_raw_materials";
        $(selector).repeater({
            initEmpty: false
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
@endsection
