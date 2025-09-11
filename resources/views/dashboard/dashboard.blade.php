@extends('layouts.app')

@section('content')


<style>
   

</style>

<div class="col-12">

    <h1 class="bread-crumbs">
        {{ __('ZAVERO Manufacturing') }} > {{ $project->name }} >
    </h1>
    <div class="ProjectList">
        @include('dashboard._content',$project->getDashboardViewVars())

		

     	<div class="mt-3">
            <a href="{{ route('main.project.page',['project'=>$project->id]) }}" type="submit" class="btn btn-rev float-right main-page-button" name="submit_button" value="save">{{ __('Go To Main Page') }}</a>
		</div>

    </div>






    <div class="clearfix"></div>


</div>

@endsection
