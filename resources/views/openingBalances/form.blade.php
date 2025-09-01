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

        <form action="{{ route('openingBalances.submit', $project) }}" method="POST">
            {{ csrf_field() }}
            <div class="col-12 alert alert-info text-center">
                <span class="red">{{ __('If you have information please fill or click next') }}</span>
            </div>

			@include('openingBalances._content',$project->getOpeningBalancesViewVars())
            <button type="submit" class="btn btn-rev float-right" name="submit_button" value="next">{{ __('Next') }}</button>
            <button type="submit" class="btn btn-rev float-right main-page-button" name="submit_button" value="save">{{ __('Save & Go To Main Page') }}</button>

        </form>

    </div>
</div>
<div class="clearfix"></div>
@endsection
