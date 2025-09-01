@extends('layouts.app')
@section('content')

<div class="container-main-width">
    <h1 class="d-flex justify-content-between steps-span">
        <span><a href="{{ url()->previous() }}" style="color: white">
                < {{__("Back")}}</a></span>
        <span>{{ __('Step ') . $currentStepNumber .'/'. $totalSteps }}</span>
    </h1>
    <h1 class="bread-crumbs">
        {{ __("ZAVERO Manufacturing") }} > {{$project->name}} > {{ __('Raw Material Payments') }}
    </h1>
    <div class="ProjectList">
        <form action="{{route('raw.material.payments.submit',[$project->id])}}" method="POST">
            {{ csrf_field() }}

			@include('raw-material-payments._content',$project->getRawMaterialViewVars())
            
            <button type="submit" class="btn btn-rev float-right" name="submit_button" value="next">{{__('Next')}}</button>
            <button type="submit" class="btn btn-rev float-right main-page-button" name="submit_button" value="save">{{__('Save & Go To Main Page')}}</button>
        </form>
    </div>







</div>




{{-- </form>
</div>
</div> --}}
<div class="clearfix"></div>
@endsection

@section('js')

@endsection
