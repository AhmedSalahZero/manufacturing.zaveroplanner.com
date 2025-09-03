@extends('layouts.app')
@section('css')
<style>
    table{
        overflow: scroll;
        font-size : 13px !important;
    }
    .tr-color{

        color: white;
        background-color: #3B368C;
    }
    .td-style{
        width: 50%;
        background-color: lightgrey;
        font-size : 15px !important;
    }
    .nav-tabs{ background: #fff0 !important;width: 108% !important;}
    .nav li{ width: 45% !important;margin: 5px !important ;}
</style>
@endsection
@section('header_components')

    @if (request()->is('*/view'))
        <a title="{{__('Contact With Study Owner')}}" class="logout float-left" href="{{route('ContactProjectOwner',[$slug])}}">
            <i class="fas fa-envelope"></i>
        </a>
    @endif
@endsection
@section('content')

    <div class="col-13" >
        <h1 class="d-flex justify-content-between steps-span">
            <span>{{ __('View Data')}}</span>
        </h1>
        <h1  class="bread-crumbs" >
            {{ __("ZAVERO Manufacturing") }}    >     {{$project->name}}  >  {{ __('View Data')}}
        </h1>
        <div class="container">
 
                <div class="tab-content dash-tap">
                    {{-- DASHBOARD  --}}
                    <div id="home" class="fade in active show tableItem ">
                        @yield('tab_content')
                    </div>

                    <br>
                </div>
            </form>
        </div>
    </div>
    <div class="clearfix"></div>

@endsection
