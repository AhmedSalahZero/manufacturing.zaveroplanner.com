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
                        <th colspan="2" class="share_header"> {{ __('Annual Contract Target') }}</th>
                    </tr>


                    @foreach ($years as $num => $data)
                        <?php
                        $count_name = array_key_first($data);
                        $year = $data[$count_name];
                        $year_format = date('y', strtotime('01-01-' . $year));
                        ?>

                        <?php
                        $contract_name = $count_name . '_contract';
                        $contract = isset($backlog->$contract_name) ? $backlog->$contract_name : old($contract_name);
                        ?>
                        <tr class="tr-color">
                            <th class="tr-color">{{ __('Contracts Target Value Yr-') . $year_format }}</th>
                            <td class="h5 text-dark td-style">{{ $contract }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <th colspan="2" class="share_header">{{__('Backlog Products Breakdown Rate %')}}</th>
                    </tr>

                    @foreach ($percentages_products as $item)
                        <?php
                            $rate_name = "backlog_rate_".$item;
                            $rate_value = isset($backlog->$rate_name) ? $backlog->$rate_name :  old($rate_name);
                        ?>
                        <tr class="tr-color">
                            <th class="tr-color">{{$project->$item .' '. __('Backlog Rate %')}} </th>
                            <td class="h5 text-dark td-style">
                                {{ $rate_value ?? '-' }} %
                            </td>
                        </tr>
                    @endforeach

                    <tr>
                        <th colspan="2" class="share_header">{{__('Backlog Delivery')}}</th>
                    </tr>
                    <tr class="tr-color">
                        <th class="tr-color">{{__('First Quarter Delivery Rate %')}} </th>
                        <td class="h5 text-dark td-style">
                            {{ $backlog->first_quarter_excution_rate ?? '-' }} %</td>
                    </tr>
                    <tr class="tr-color">
                        <th class="tr-color">{{__('Second Quarter Delivery Rate %')}} </th>
                        <td class="h5 text-dark td-style">
                            {{ $backlog->second_quarter_excution_rate ?? '-' }} %</td>
                    </tr>
                    <tr class="tr-color">
                        <th class="tr-color">{{__('Third Quarter Delivery Rate %')}} </th>
                        <td class="h5 text-dark td-style">
                            {{ $backlog->third_quarter_excution_rate ?? '-' }} %</td>
                    </tr>
                    <tr class="tr-color">
                        <th class="tr-color">{{__('Fourth Quarter Delivery Rate %')}} </th>
                        <td class="h5 text-dark td-style">
                            {{ $backlog->fourth_quarter_excution_rate ?? '-' }} %</td>
                    </tr>

                    <tr>
                        <th colspan="2" class="share_header">{{__('Collections Plan')}}</th>
                    </tr>
                    <tr class="tr-color">
                        <th class="tr-color">{{__('Initial Rate %')}} </th>
                        <td class="h5 text-dark td-style">
                            {{ $backlog->initial_collection_rate ?? '-' }} %</td>
                    </tr>
                    <tr class="tr-color">
                        <th class="tr-color">{{__('Due In (Days)')}}</th>
                        <td class="h5 text-dark td-style">
                            {{ $backlog->initial_collection_days ?? '-' }} {{ __('Days') }}</td>
                    </tr>
                    <tr class="tr-color">
                        <th class="tr-color">{{__('Final Rate %')}} </th>
                        <td class="h5 text-dark td-style">
                            {{ $backlog->final_collection_rate ?? '-' }} %</td>
                    </tr>
                    <tr class="tr-color">
                        <th class="tr-color">{{__('Due In (Days)')}}</th>
                        <td class="h5 text-dark td-style">
                            {{ $backlog->final_collection_days ?? '-' }} {{ __('Days') }}</td>
                    </tr>

                </tbody>
            </table>
        </div>


@endsection
