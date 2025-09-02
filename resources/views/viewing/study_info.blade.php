@extends('layouts.view_layout')
@section('tab_content')

    <div class="table-responsive">

        <table class="table table-hover text-center" style="width: 100%;">

            <thead>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Description')}}</th>
                    <th class="tr-color">{{__('Value')}}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th colspan="2" class="share_header"> {{$project->name ." ".__("Info")}}</th>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Company Type')}}</th>
                    <td class="h5 text-dark td-style">{{$project->new_company == 1 ? __("New Company") : __("Existing Company")  }}</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Study Start Date')}}</th>
                    <td class="h5 text-dark td-style">{{isset($project->start_date) ?date('M-Y',strtotime($project->start_date)) : '-' }}</td>
                </tr>
           

                @if (isset($project->business_sector_id))
                    <?php $name =  "name_".app()->getLocale();?>
                    <tr class="tr-color">
                        <th class="tr-color">{{__('Business Sector')}}</th>
                        <td class="h5 text-dark td-style">{{$project->sector->$name}}</td>
                    </tr>
                @endif
                <tr class="tr-color">
                    <th class="tr-color">{{__('Corporate Tax Rate %')}}</th>
                    <td class="h5 text-dark td-style">{{$project->tax_rate}} %</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Required Investment Return %')}}</th>
                    <td class="h5 text-dark td-style">{{$project->return_rate}} %</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Perpetual Growth Rate %')}}</th>
                    <td class="h5 text-dark td-style">{{$project->perpetual_growth_rate}} %</td>
                </tr>
                <tr>
                    <th colspan="2" class="share_header">  {{__('Products Names')}}</th>
                </tr>
                @foreach ($products as $product)

                    <?php
                        $product_name_array = explode('_',$product);
                        $product_name =ucwords(implode(' ',array_reverse($product_name_array)));
                        $field_name_of_selling_date = $product."_selling_date";
                    ?>
                    <tr class="tr-color">
                        <th class="tr-color">{{__($product_name)}}</th>
                        <td class="h5 text-dark td-style">{{$project->$product}}   </td>
                    </tr>

                    <tr class="tr-color">
                        <th class="tr-color">{{__('Selling Start Date')}}</th>
                        <td class="h5 text-dark td-style">{{date('M-Y',strtotime($project->$field_name_of_selling_date))}}  </td>
                    </tr>
                    <tr><td></td></tr>
                @endforeach
            </tbody>
        </table>
    </div>


@endsection
