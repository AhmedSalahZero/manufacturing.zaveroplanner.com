@extends('layouts.app')
@section('content')

<?php $manPower = isset($manPower) ? $manPower : old(); ?>

<div class="container-main-width ">
    <h1 class="d-flex justify-content-between steps-span">
        <span><a href="{{ url()->previous() }}" style="color: white">
                < {{ __('Back') }}</a></span>
        <span>{{ __('Step ') . $step_data['place_num'] . '/' . $step_data['count'] }}</span>
    </h1>
    <h1 class="bread-crumbs">
        {{ __('ZAVERO Manufacturing') }} > {{ $project->name }} > {{ __($step_data['route_name']) }}
    </h1>
    <Div class="ProjectList">
        <form method="POST" action="{{ route('manPower.submit', $project) }}">
            @csrf
           @include('manPower._content',$project->getManpowerViewVars())
            <button type="submit" class="btn btn-rev float-right" name="submit_button" value="next">{{ __('Next') }}</button>
            <button type="submit" class="btn btn-rev float-right main-page-button" name="submit_button" value="save">{{ __('Save & Go To Main Page') }}</button>
        </form>
    </Div>
</div>
@endsection
@section('js')




<script src="https://cdn.jsdelivr.net/npm/jquery.repeater@1.2.1/jquery.repeater.min.js"></script>
@foreach ( getManpowerTypes() as $id => $manpowerOptionArr)
<script>
    // Define translated strings for JavaScript
    var translations = {
        deleteConfirm: @json(__('Are you sure you want to delete this position?'))
    };
    /**
     * * direct_labor[1][hirings][] -> to -> direct_labor[1][hirings][2030-05-01] 
     */


    $(document).ready(function() {
        var selector = ".repeater{{ $id }}";
        $(selector).repeater({
            initEmpty: false
            , defaultValues: {
                'position': ''
                , 'avg_salary': '0'
                , 'existing_count': '0'
            }
            , show: function() {
                $(this).slideDown();
                // Update modal IDs to ensure uniqueness
                const $item = $(this);
                const index = $item.index();
                const modalId = `modal-${index}-${Date.now()}`;
                $item.find('.modal').attr('id', modalId);
                $item.find('[data-toggle="modal"]').attr('data-target', `#${modalId}`);
                $item.find('.modal').find('.modal-title').attr('id', `modalLabel-${index}-${Date.now()}`);
                replaceRepeaterIndex(this)
            }
            , ready: function(setIndexes) {
                $(selector + " [data-repeater-item]").each(function(index, element) {
                    replaceRepeaterIndex(element)
                })
            }
            , hide: function(deleteElement) {
                if (confirm(translations.deleteConfirm)) {

                    $(this).slideUp(deleteElement);
                    setTimeout(function() {
                        $(selector + " [data-repeater-item]").each(function(index, element) {
                            replaceRepeaterIndex(element)
                        })
                    }, 1000)

                }

            }
            , isFirstItemUndeletable: true
        });
    });

</script>
@endforeach

@endsection
