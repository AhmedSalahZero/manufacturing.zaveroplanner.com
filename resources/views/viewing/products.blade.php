@extends('layouts.view_layout')
@section('tab_content')


    <div class="table-responsive">

        <table class="table table-hover text-center" style="width: 100%;">

            <thead>
                <tr class="tr-color">
                    <th class="tr-color">{{ __('Description') }}</th>
                    <th class="tr-color">{{ __('Value') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th colspan="2" class="share_header"> {{ __('Annual Sales Target') }}</th>
                </tr>


                @foreach ($years as $num => $data)
                    <?php
                    $count_name = array_key_first($data);
                    $year = $data[$count_name];
                    $year_format = date('y', strtotime('01-01-' . $year));
                    ?>
                    @if ($count_name != 'first')
                        <?php
                        $rate_name = $count_name . '_rate';
                        $rate = number_format($product->$rate_name, 2) ??  '';
                        ?>
                        <tr class="tr-color">
                            <th class="tr-color">{{__('Growth Rate % Yr-').$year_format}}</th>
                            <td class="h5 text-dark td-style">{{ $rate }} %</td>
                        </tr>
                    @endif
                    <?php
                    $contract_name = $count_name . '_contract';
                    $contract = number_format($product->$contract_name, 0) ?? '';
                    ?>
                    <tr class="tr-color">
                        <th class="tr-color">{{__('Sales Target Value Yr-').$year_format}}</th>
                        <td class="h5 text-dark td-style">{{ $contract }}</td>
                    </tr>
                @endforeach
                {{-- ######################################################################### --}}
                <tr>
                    <th colspan="2" class="share_header"> {{ __('Sales Seasonality') }}</th>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{ __('Monthly Seasonality %') }}</th>
                    <td class="h5 text-dark td-style">{{ $seasonality = @$sesonalities[$product->seasonality] }}</td>
                </tr>
                @if ($product->seasonality == 'flat')
                    <tr class="tr-color">
                        <th class="tr-color">{{ __('Seasonality') }}</th>
                        <td class="h5 text-dark td-style">{{ number_format((1 / 12) * 100, 2) }} %</td>
                    </tr>

                @elseif ($product->seasonality == 'quarterly')
                    <tr class="tr-color">
                        <th class="tr-color">{{ __('First Quarter %') }}</th>
                        <td class="h5 text-dark td-style">
                            {{ $product->first_quarter ??  '-' }} %</td>
                    </tr>
                    <tr class="tr-color">
                        <th class="tr-color">{{ __('Second Quarter %') }}</th>
                        <td class="h5 text-dark td-style">
                            {{ $product->second_quarter ??  '-' }} %</td>
                    </tr>
                    <tr class="tr-color">
                        <th class="tr-color">{{ __('Third Quarter %') }}</th>
                        <td class="h5 text-dark td-style">
                            {{ $product->third_quarter ??  '-' }} %</td>
                    </tr>
                    <tr class="tr-color">
                        <th class="tr-color">{{ __('Fourth Quarter %') }}</th>
                        <td class="h5 text-dark td-style">
                            {{ $product->fourth_quarter ??  '-' }} %</td>
                    </tr>
                @elseif ($product->seasonality == 'monthly')
                    <?php $month_num = date('d-m-y', strtotime('01-01-2020')); ?>
                    @for ($month = 0; $month < 12; $month++)
                        <?php
                        $month_name = date('F', strtotime(date('d-m-y', strtotime($month_num)) . " +$month month"));
                        $name = 'monthly_' . strtolower($month_name);
                        $value = $product->$name ??  '-';
                        ?>
                        <tr class="tr-color">
                            <th class="tr-color">{{ __($month_name . ' %') }}</th>
                            <td class="h5 text-dark td-style">
                                {{ $value }} %</td>
                        </tr>
                    @endfor
                @endif


                <tr>
                    <th colspan="2" class="share_header">{{__('Raw Material Cost Rate %')}}</th>
                </tr>
                @foreach ($years as $num => $data)
                    <?php
                    $count_name = array_key_first($data);
                    $year = $data[$count_name];
                    $year_format = date('y', strtotime('01-01-' . $year));
                    $field_name = 'rm_cost_'.$count_name."_rate";
                    $field = number_format($product->$field_name, 2) ?? '';
                    ?>
                    <tr class="tr-color">
                        <th class="tr-color">{{__('Yr-').$year_format}}(%)</th>
                        <td class="h5 text-dark td-style">{{$field}} %</td>
                    </tr>
                @endforeach
                <tr>
                    <th colspan="2" class="share_header">{{__('Labor Cost Rate %')}}</th>
                </tr>
                @foreach ($years as $num => $data)
                    <?php
                    $count_name = array_key_first($data);
                    $year = $data[$count_name];
                    $year_format = date('y', strtotime('01-01-' . $year));
                    $field_name = 'labor_cost_'.$count_name."_rate";
                    $field = number_format($product->$field_name, 2) ?? '';
                    ?>
                    <tr class="tr-color">
                        <th class="tr-color">{{__('Yr-').$year_format}}(%)</th>
                        <td class="h5 text-dark td-style">{{$field}} %</td>
                    </tr>
                @endforeach
                <tr>
                    <th colspan="2" class="share_header">{{__('Manufacturing Overheads Cost Rate %')}}</th>
                </tr>
                @foreach ($years as $num => $data)
                    <?php
                    $count_name = array_key_first($data);
                    $year = $data[$count_name];
                    $year_format = date('y', strtotime('01-01-' . $year));
                    $field_name = 'moh_cost_'.$count_name."_rate";
                    $field = number_format($product->$field_name, 2) ?? '';
                    ?>
                    <tr class="tr-color">
                        <th class="tr-color">{{__('Yr-').$year_format}}(%)</th>
                        <td class="h5 text-dark td-style">{{$field}} %</td>
                    </tr>
                @endforeach




                <tr>
                    <th colspan="2" class="share_header"> {{__('Inventory & Purchases')}}</th>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Finished Goods Beginning Inventory Value')}}</th>
                    <td class="h5 text-dark td-style">
                        {{ number_format($product->fg_inventory_value,1) ??  '-' }}</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Finished Goods Inventory Coverage Days')}}</th>
                    <td class="h5 text-dark td-style">
                        {{$product->fg_inventory_coverage_days,0 ?? '-'}} {{ __('Days') }}</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Raw Materials Beginning Inventory Value')}}</th>
                    <td class="h5 text-dark td-style">
                        {{number_format($product->rm_inventory_value) ?? '-'}}</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Raw Materials Inventory Coverage Days')}}  </th>
                    <td class="h5 text-dark td-style">
                        {{$product->inventory_coverage_days ?? '-'}} {{ __('Days') }}</td>
                </tr>





                <tr>
                    <th colspan="2" class="share_header"> {{__('Collections Plan')}}</th>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Down Payment %')}}  </th>
                    <td class="h5 text-dark td-style">
                        {{$product->collection_down_payment ?? '-'}} %</td>
                </tr>

                <tr class="tr-color">
                    <th class="tr-color">{{__('Collection Rate One%')}} </th>
                    <td class="h5 text-dark td-style">
                        {{ $product->initial_collection_rate ??  '-' }} %</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Due In (Days)')}}</th>
                    <td class="h5 text-dark td-style">
                        {{ $product->initial_collection_days ??  '-' }}  {{ __('Days') }}</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Collection Rate Two %')}}</th>
                    <td class="h5 text-dark td-style">
                        {{ $product->final_collection_rate ??  '-' }} %</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Due In (Days)')}}</th>
                    <td class="h5 text-dark td-style">
                        {{ $product->final_collection_days ??  '-' }}  {{ __('Days') }}</td>
                </tr>

                <tr>
                    <th colspan="2" class="share_header"> {{__('Suppliers Payments')}}</th>
                </tr>

                <tr class="tr-color">
                    <th class="tr-color">{{__('Down Payment %')}}  </th>
                    <td class="h5 text-dark td-style">
                        {{$product->outsourcing_down_payment ??  '-'}} %</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Remaining Balance Rate %')}}</th>
                    <td class="h5 text-dark td-style">
                        {{$product->balance_rate_one ?? '-'}} %</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Due In (Days)')}}</th>
                    <td class="h5 text-dark td-style">
                        {{$product->balance_one_due_in ??  '-'}}  {{ __('Days') }}</td>
                </tr>
            </tbody>
        </table>
    </div>


@endsection
