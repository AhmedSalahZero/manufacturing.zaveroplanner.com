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
