@extends('layouts.app')
@section('content')
<style>

</style>
<div class="container-main-width">
    {{-- <h1 class="d-flex justify-content-between steps-span">
        <span><a href="{{ url()->previous() }}" style="color: white">
                < {{__("Back")}}</a></span>
        <span>{{ __('Step ') . $currentStepNumber .'/'. $totalSteps }}</span>
    </h1> --}}
    <h1 class="bread-crumbs">
       {{ __('Study Info') }}
    </h1>
    <div class="ProjectList">
	
		@include('projects._content',$project->getViewVars())

		@foreach($project->products as $product)
        @include('products._content',$product->getViewVars())
		@endforeach
	@include('raw-material-payments._content',$project->getRawMaterialViewVars())
	@include('manPower._content',$project->getManpowerViewVars())
	     @include('expenses._content',$project->getExpensesViewVars())
		 @include('fixed-assets._content',$project->getFixedAssetsViewVars())
		 
		    <div class="div-title">
                {{ __('Opening Balances') }}
            </div>
			
		 @include('openingBalances._content',$project->getOpeningBalancesViewVars())
		   <div class="div-title">
                {{ __('Income Statement') }}
            </div>

  @include('financial-results._content',$project->calculateIncomeStatement())
  
  
  	   <div class="div-title mt-4">
                {{ __('Cash In Out Statement') }}
            </div>

  @include('financial-results._content',$project->getCashInOutFlowViewVars())
  

        {{-- <button type="submit" class="btn btn-rev float-right" name="submit_button" value="next">{{__('Next')}}</button>
        <button type="submit" class="btn btn-rev float-right main-page-button" name="submit_button" value="save">{{__('Save & Go To Main Page')}}</button> --}}


    </div>
</div>
<div class="clearfix"></div>
@endsection

@push('js_end')
<script>
	$('[data-repeater-create],[data-repeater-delete],.cash-flow-btn').addClass('hidden-important');
	$('input,select').prop('disabled',true)
	$('button.save-modal').addClass('hidden-important')
$(function(){
})
</script>
@endpush 
