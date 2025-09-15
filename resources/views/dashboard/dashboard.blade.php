@extends('layouts.app')

@section('content')


<style>
   

</style>

<div class="col-12">

    <h1 class="bread-crumbs">
        {{ __('ZAVERO Manufacturing') }} > {{ $project->name }} >
    </h1>
    <form action="{{ route('submit.results.dashboard',['project'=>$project->id]) }}" method="post">
	@csrf
	<div class="ProjectList">
        @include('dashboard._content',$project->getDashboardViewVars())

		

     	<div class="mt-3">
            {{-- <a href="{{ route('main.project.page',['project'=>$project->id]) }}" type="submit" class="btn btn-rev float-right main-page-button" name="submit_button" value="save">{{ __('Go To Main Page') }}</a> --}}
			<button type="submit" class="btn btn-rev float-right main-page-button" name="submit_button" value="save">{{__('Save')}}</button>
			<button type="submit" class="btn btn-rev float-right main-page-button" name="submit_button" value="save_and_go_to_main">{{__('Save & Go To Main Page')}}</button>
		{{-- <button type="submit" class="btn btn-rev float-right" name="submit_button" value="next">{{__('Next')}}</button> --}}
			
		</div>

    </div>
	
	
	</form>






    <div class="clearfix"></div>


</div>

@endsection
