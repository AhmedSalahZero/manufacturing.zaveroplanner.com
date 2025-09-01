@extends('layouts.app')
@section('content')
<style>

</style>
<div class="container-main-width">
    <h1 class="d-flex justify-content-between steps-span">
        <span><a href="{{ url()->previous() }}" style="color: white">
                < {{__("Back")}}</a></span>
        <span>{{ __('Step ') . $step_data['place_num'] .'/'. $step_data['count'] }}</span>
    </h1>
    <h1 class="bread-crumbs">
        {{ __("ZAVERO Manufacturing") }} > {{$project->name}} > {{ __($step_data['route_name'])}}
    </h1>
    <div class="ProjectList">
        <form method="POST" action="{{ route('projects.update' , $project) }}">



            @csrf
            @method('put')

          @include('projects._content',$project->getViewVars())







    </div>

    <button type="submit" class="btn btn-rev float-right" name="submit_button" value="next">{{__('Next')}}</button>
    <button type="submit" class="btn btn-rev float-right main-page-button" name="submit_button" value="save">{{__('Save & Go To Main Page')}}</button>

    </form>
</Div>
</div>

@endsection
@section('js')
<script>
    // old end date
    if (("{{$start_date}}" != "" && "{{$duration_data}}" != "")) {
        var duration = $('#duration').val();

        var monthYear = $('#month-year').val();
        var month = monthYear.split('-')[1];
        var year = monthYear.split('-')[0];

        if (duration == 1) {
            $('#return_rate').attr('disabled', 'true');
            $('#return_rate').val('');
        } else {
            $('#return_rate').removeAttr('disabled');
        }
        if (month != '' && year != '') {
            getdate(duration, month, year, 'start_date', 'end_date');
        }
    }
    //  if the duration or start date Changed : CALL The Function [ end_date ] To Auto Calculate The End date From the Duration + The Start Date
    $('#duration,#month-year').on('keyup change', function() {

        var duration = $('#duration').val();
        var monthYear = $('#month-year').val();
        var month = monthYear.split('-')[1];
        var year = monthYear.split('-')[0];


        if (duration == 1) {
            $('#return_rate').attr('disabled', 'true');
            $('#return_rate').val('');
        } else {
            $('#return_rate').removeAttr('disabled');
        }
        if (month != '' && year != '') {
            getdate(duration, month, year, 'start_date', 'end_date');
        }
    });
    $('#selling_start_month,#selling_start_year').on('keyup change', function() {
        var month = $('#selling_start_month').val();
        var year = $('#selling_start_year').val();
        if (month != '' && year != '') {
            getdate(0, month, year, 'selling_start_date');
        }
    });


    $('#duration').on('change', function() {
        var duration = $('#duration').val();
        if (duration != "" && duration < 3) {
            $('#perpetual_growth_rate').val('');
            $('#perpetual_growth_rate').attr('readonly', 'true');
        } else if (duration >= 3) {
            $('#perpetual_growth_rate').removeAttr("readonly");
        }
    });
    //date
    function getdate(duration, month, year, hidden_input, view_input = null) {
        var data;

        $.ajax({
            type: 'GET'
            , data: {
                'duration': duration
                , 'month': month
                , 'year': year
            }
            , url: "{{ route('get.date') }}"
            , dataType: 'json'
            , accepts: 'application/json'
        }).done(function(data) {
            console.log(data);
            if (view_input != null) {
                $('#' + view_input).val(data.view_date);
                $('#full_end_date').val(data.end_date);
            }
            $('#' + hidden_input).val(data.full_date);
        });

    }
    //Alert
    $(document).on('change', '.form-check-inline', function() {
        val = $('input[name="new_company"]:checked').val();
        if (val == 1) {
            $('.msg-alert').removeClass('hidden');
        } else {
            $('.msg-alert').addClass('hidden');
        }
    });

</script>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });



    var translations = {
        deleteConfirm: @json(__('Are you sure you want to delete this position?'))
    };

    $(document).ready(function() {
        var selector = ".repeater_products";
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



    $(document).ready(function() {
        var selector = ".repeater_rawMaterials";
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
