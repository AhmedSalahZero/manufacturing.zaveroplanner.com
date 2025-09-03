@extends('layouts.app')

@section('content')


<div class="col-10 offset-1">
    <Div class="ProjectList">
        <Div class="ProjectList">
            <h1 class="d-flex justify-content-between steps-span">
                <span class="align-self-center">{{__("Study Main Page")}}</span>
                {{-- <button style="margin : 0px !important" class="btn btn-rev" data-toggle="modal" data-target="#shareModal{{$project->id}}"><i class="fas fa-share"></i></button> --}}
            </h1>
            <h1  class="bread-crumbs" >
                {{ __("ZAVERO Manufacturing") }}   >     {{$project->name}} > {{__("Study Main Page")}}
            </h1>
          @php
			$project_complete = $project->isCompleted();
		  @endphp

            <a href="{{route('projects.edit',[$project->id])}}">
                <div class="projectItem" >
                    {{__('Study Info')}} {{ $project_complete == 0 ?  " - ". __('Incomplete') : "" }}
                </div>
            </a>

            <?php $all_products_complete = 0;
                $product_controller_obj = (new App\Http\Controllers\ProjectController);

            ?>
			
            @foreach ($products as $product)
			
			<a href="{{route('products.form',['project'=>$project,'product'=>$product->id])}}" >
                    <div class="projectItem  " >
					{{ $product->getName() }}
					@if(!$product->isComplete())
					{{ __('Incomplete') }}
					@endif 
                    </div>
                </a>
				
                {{-- @php
                    $product_complete =0;
                    $rm_cost_rates = [];
                    $labor_cost_rates = [];
                    $moh_cost_rates = [];
                    $years = $product_controller_obj->years($project,null,$product);
				
                    $productplan = $project->product($product);
                    if (isset($productplan)) {
                        foreach ($years as $key => $year) {
                            $name_of_year = array_key_first($year);
                            $year_num = $year[$name_of_year];
                            $rm_cost_rate_field = 'rm_cost_'.$name_of_year.'_rate' ;
                            $labor_cost_rate_field = 'labor_cost_'.$name_of_year.'_rate' ;
                            $moh_cost_rate_field = 'moh_cost_'.$name_of_year.'_rate' ;
                            $rm_cost_rates[$year_num] = ($productplan->$rm_cost_rate_field)??null;
                            $labor_cost_rates[$year_num] = ($productplan->$labor_cost_rate_field)??null;
                            $moh_cost_rates[$year_num] =( $productplan->$moh_cost_rate_field)??null;
                        }
                    }

                    $any_nulls_in_rm_cost_rates = array_search(null ,$rm_cost_rates);
                    $any_nulls_in_labor_cost_rates = array_search(null,$labor_cost_rates);
                    $any_nulls_in_moh_cost_rates = array_search(null,$moh_cost_rates);


                    $product_complete = ( !isset($productplan) || (isset($productplan) && ( $productplan->seasonality == null || $productplan->first_contract== null ||  $productplan->collection_down_payment == null
                    || ($productplan->final_collection_rate > 0 && $productplan->final_collection_days === null) || ($productplan->initial_collection_rate > 0 && $productplan->initial_collection_days === null||
                    false !== $any_nulls_in_rm_cost_rates ||
                    false !== $any_nulls_in_labor_cost_rates ||
                    false !== $any_nulls_in_moh_cost_rates  )
                    || ((false === $any_nulls_in_rm_cost_rates) && ($productplan->outsourcing_down_payment == null || ($productplan->balance_rate_one > 0 && $productplan->balance_one_due_in === null))) ))) ? 0 : 1 ;

                    //Check the Total percentage
                    $total_percentage= 0 ;
                    if (isset($productplan)) {
                        $total_percentage = (new App\Http\Controllers\ValidationsController)->totalPercentages($productplan ,[],"total_percentage");
                    }

                    $product_complete = ($product_complete == 0 || $total_percentage != 100 ) ? 0 : 1 ;
                @endphp 
                <a @if($project_complete == 0) data-toggle="modal" data-target="#exampleModal"  @else href="{{route('products.form',['project'=>$project,'type'=>$product])}}" @endif>
                    <div class="projectItem {{$project_complete == 0 ? "disabled-class" : ""}} " >

                        {{ @$project->$product}}
                        {{ $product_complete == 0 ?  " - ". __('Incomplete') : "" }}

                    </div>
                </a>
                @php
				 $all_products_complete =  $product_complete ||  $all_products_complete 
				@endphp --}}
            @endforeach
			<a   href="{{route('raw.material.payments.form', $project->id)}}" >
                <div class="projectItem" >
                    {{__("Raw Materials")}}
                </div>
            </a>
            <a  @if($project_complete == 0 ) data-toggle="modal" data-target="#exampleModal"  @else href="{{route('manPower.form', $project->id)}}" @endif>
                <div class="projectItem {{$project_complete == 0  ? "disabled-class" : ""}}" >
                    {{__("Manpower Plan")}}
                </div>
            </a>  

            <a  
			{{-- @if($project_complete == 0 || $all_products_complete == 0 ) --}}
			 {{-- data-toggle="modal" data-target="#exampleModal"  @else  --}}
			 href="{{route('expenses.form', $project->id )}}" 
			 {{-- @endif --}}
			 >
                <div class="projectItem 
				{{-- {{$project_complete == 0 || $all_products_complete == 0  ? "disabled-class" : ""}} --}}
				" >
                    {{__("Expenses Plan")}}
                </div>
            </a>
            <?php
                $asset_complete = 0;
                $assets = $project->assets;
                $asset_complete =(isset($assets)&&($assets->fixed_assets_value > 0 && $assets->fixed_assets_value !== null && (( $assets->down_payment  + $assets->balance_rate) != 100)||
                ($assets->fixed_assets_value_two > 0 && $assets->fixed_assets_value_two !== null && (( $assets->down_payment_two  + $assets->balance_rate_two) != 100)))) ?0:1;
            ?>
            <a  @if($project_complete == 0 ) data-toggle="modal" data-target="#exampleModal"  @else href="{{route('assets.form', $project->id)}}" @endif>
                <div class="projectItem {{$project_complete == 0  ? "disabled-class" : ""}}" >
                    {{__("Assets Plan")}}  {{ $asset_complete == 0  ?  " - ". __('Incomplete') : "" }}
                </div>
            </a>
            @if ($project->new_company == 0)
                <a  @if($project_complete == 0 ) data-toggle="modal" data-target="#exampleModal"  @else href="{{route('openingBalances.form',$project->id)}}" @endif>
                    <div class="projectItem {{$project_complete == 0  ? "disabled-class" : ""}}" >
                        {{__("Opening Balances")}}
                    </div>
                </a>
            @endif

 <a  href="{{route('financial.result',[$project->id])}}"  >
                <div class="projectItem {{$project_complete == 0  ? "disabled-class" : ""}}" >
                    {{__('Financial Results')}}
                </div>
            </a>
			
            <a @if($project_complete == 0 ) data-toggle="modal" data-target="#exampleModal"  @else href="{{route('view.results.dashboard',[$project->id])}}" @endif >
                <div class="projectItem {{$project_complete == 0  ? "disabled-class" : ""}}" >
                    {{__('Study Results')}}
                </div>
            </a>
            {{-- @if (\Auth::user()->email == "emanmotaz@gmail.com" || \Auth::user()->email =="mahmoud.youssef@squadbcc.com" || \Auth::user()->email =="Investment@squadbcc.com"
            
            
            || \Auth()->user()->email == "samer.tawfik@squadbcc.com"
            ||\Auth()->user()->email == 'itc_ebrd@squadbcc.com'
            )
                <a href="{{route('table.index',[$project->id])}}" >
                    <div class="projectItem " >
                        {{__('Table Results')}}
                    </div>
                </a>
            @endif --}}
            <a @if($project_complete == 0 ) data-toggle="modal" data-target="#exampleModal"  @else href="{{route('sensitivity.form',[$project->id])}}" @endif>
                <div class="projectItem {{$project_complete == 0  ? "disabled-class" : ""}}">
                    {{__("Sensitivity")}}
                </div>
            </a>
        </Div>


        <div class="modal fade " id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header d-none">
                        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body modal-text text-center">{{__('Please fill the incomplete page')}}
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>


        {{-- Share Modal --}}
        <div class="modal fade" id="shareModal{{ $project->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="col-md-12">
                            <?php $project_slug = $project->slug . '-' . $project->id; ?>
                            <input class="form-control" type="text"
                                value="{{ route('study_info.view', [$project_slug]) }}"
                                id="myInput{{ $project->id }}">
                        </div>
                        <br>
                        <button class="btn btn-rev" onclick="myFunction({{ $project->id }})"
                            onmouseout="outFunc({{ $project->id }})">
                            <span class="tooltiptext" ><i id="myTooltip{{ $project->id }}" class="fas fa-copy "> Copy Link</i>
                                </span>

                        </button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </Div>
</div>
<div class="clearfix"></div>
@endsection
@section('js')
<script>
    function myFunction(project_id) {
      var copyText = document.getElementById("myInput"+project_id);
      copyText.select();
      copyText.setSelectionRange(0, 99999);
      document.execCommand("copy");

      var tooltip = document.getElementById("myTooltip"+project_id);
      tooltip.innerHTML = "";
      tooltip.innerHTML = " Copied";
    }

    function outFunc(project_id) {
      var tooltip = document.getElementById("myTooltip"+project_id);
      tooltip.innerHTML = " Copy Link";
    }
</script>
@endsection
