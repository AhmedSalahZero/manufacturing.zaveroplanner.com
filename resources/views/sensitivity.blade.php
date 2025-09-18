@extends('layouts.app')
@section('content')




<div class="col-10 offset-1">
    <h1 class="d-flex justify-content-between steps-span ">
        <span><a href="{{ url()->previous() }}" style="color: white">< {{__("Back")}}</a></span>
        <span>{{ __('Sensitivity')}}</span>
    </h1>
    <h1  class="bread-crumbs" >
            {{ __("ZAVERO Manufacturing") }}    >     {{$project->name}}  >    {{ __('Sensitivity')}}
    </h1>
    <Div class="ProjectList">
        <ul class="nav nav-tabs">
            <li class="text-center"><a data-toggle="tab" href="#menu2" class="active">{{__('Target Sensitivity (+/-)')}}</a></li>
            <li class="text-center"><a data-toggle="tab" href="#menu3">{{__('Raw Material Cost % Sensitivity (+/-)')}}</a></li>
            <li class="text-center"><a data-toggle="tab" href="#menu4">{{__('Collections Sensitivity (+)')}}</a></li>
        </ul>
            <form method="POST" action="{{ route('sensitivity.submit' , $project->id) }}">
                <div class="formItem align-left">

                    @csrf
                    <div class="tab-content">

                        <div id="menu2" class="tab-pane active show col-md-12">
                            @foreach ($products as $key => $product_name)
                                <?php $name = "target_".$product_name;?>

                                    <label>{{$project->$product_name .' '. __('Target Sensitivity (+/-)')}}</label>
                                    <div class="form-group">
                                        <input type="number"  class="form-control " name="{{$name}}" value="{{isset($sensitivity->$name) ?  $sensitivity->$name : ""}}" placeholder="{{__('Please Enter Rate')}}">
                                    </div>
                            @endforeach
                        </div>

                        <div id="menu3" class="tab-pane fade col-md-12">
                            @foreach ($products as $key => $product_name)
                                <?php  $name = "rm_".$product_name; ?>
                                    <label> {{$project->$product_name .' '. __('Raw Material Cost % Sensitivity (+/-)')}}</label>
                                    <div class="form-group ">
                                        <input type="number"  class="form-control " name="{{$name}}" value="{{isset($sensitivity->$name) ?  $sensitivity->$name : ""}}" placeholder="{{__('Please Enter Rate')}} ">
                                    </div>
                            @endforeach
                        </div>

                        <div id="menu4" class="tab-pane fade col-md-12">
                            @foreach ($products as $product_name)
                                    <?php
                                        $name = "collections_".$product_name;
                                        $collection = isset($sensitivity->$name) ?  $sensitivity->$name : old($name);
                                    ?>
                                    <label>{{$project->$product_name  .' '. __('Collections Sensitivity (+)')}}</label>
                                    <div class="form-group ">
                                        <select class="form-control" name="{{$name}}">
                                            <option value="" >{{__('Select')}}</option>
                                            <option  value="0"  {{$collection == 0  ? "selected" : ""}} >0</option>
                                            <option  value="30" {{$collection == 30 ? "selected" : ""}} >30</option>
                                            <option  value="45" {{$collection == 45 ? "selected" : ""}} >45</option>
                                            <option  value="60" {{$collection == 60 ? "selected" : ""}} >60</option>
                                            <option  value="90" {{$collection == 90 ? "selected" : ""}} >90</option>
                                        </select>
                                    </div>
                            @endforeach
                        </div>

                        <div class="col-md-12">
                            @include('save_buttons')
                        </div>
                    </div>
                    <div class="clearfix"></div>

               </div>
            </form>
    </Div>
</div>












    <div class="clearfix"></div>
@endsection
