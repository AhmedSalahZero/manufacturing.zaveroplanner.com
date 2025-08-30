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
            <ul class="nav nav-tabs">
                <?php
                    $views = [  'study_info'        =>['view'=> 'study_info.view' ],
                                'products'          =>['view'=> 'products.view'],
                                'manpower_plan'     =>['view'=> 'manpower_plan.view' , 'relation'=>'manPower'],
                                'expenses_plan'     =>['view'=> 'expenses_plan.view' , 'relation'=>'expense'],
                                'assets_plan'       =>['view'=> 'assets_plan.view' , 'relation'=>'assets'],
                                'opening_balances'  =>['view'=> 'opening_balances.view' , 'relation'=>'openingBalance'],
                                'study_results'     =>['view'=> 'study_result.view'] ];
                ?>

                @foreach ($views as $name => $model_name)
                    @if ($name == 'products')
                        @foreach ($products as $product)
                            <li class="text-center"><a href="{{route($model_name['view'],[$slug,$product])}}"
                                class="{{ request()->is('*/'.$product.'/'.$name.'/view') ?  'active' : ''}}">
                                {{  $project->$product .' '. __("Product")}}</a>
                            </li>
                        @endforeach
                    @else
                        <?php $name_of_relation = @$model_name['relation']; ?>
                        @if ((isset($model_name['relation']) && isset($project->$name_of_relation)) ||  !isset($model_name['relation']) )
                            @if (($name == "study_results" && $project->start_date != null && $project->start_date != null   &&
                            ( (($project->product_first_selling_date !== null )
                            || ($project->product_second_selling_date !== null )
                            || ($project->product_third_selling_date !== null )
                            || ($project->product_fourth_selling_date !== null )
                            || ($project->product_fifth_selling_date !== null )
                            ))
                            && !isset($model_name['relation']))  || $name != "study_results" )
                                <li class="text-center"><a href="{{route($model_name['view'],[$slug])}}" class="{{ request()->is('*/'.$name.'/view') ?  'active' : ''}}">{{__(ucwords(str_replace('_',' ',$name)))}}</a>
                                </li>
                            @endif
                        @endif
                    @endif
                @endforeach
            </ul>
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
