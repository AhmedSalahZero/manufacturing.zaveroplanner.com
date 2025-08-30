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
                    <th colspan="2" class="share_header"> {{ __('Marketing & Sales Expenses')  }}</th>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Marketing Campaign Cost Amount')}}</th>
                    <td class="h5 text-dark td-style">{{ number_format($expense->marketing_campain,0) ?? '-' }}</td>
                </tr>

                <tr class="tr-color">
                    <th class="tr-color">{{__('Sales Commission Rate %').__(" (Of Sales)")}} </th>
                    <td class="h5 text-dark td-style">{{ $expense->sales_commission_rate ?? '-' }} %</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Sales Commission Payment Interval')}}</th>
                    <td class="h5 text-dark td-style">{{ __(ucfirst($expense->sales_commission_payment_interval)) ?? '-' }}</td>
                </tr>

                <tr>
                    <th colspan="2" class="share_header"> {{__('Monthly Marketing Expenses Rate %')}}{{__(" (Of Sales)")}} </th>
                </tr>
                @foreach ($years as $data)
                    <?php $count_name = array_key_first($data) ; $year = $data[$count_name];?>
                    <?php $year_format = date('y',strtotime('01-01-'.$year)); ?>
                    <?php $rate_name = 'monthly_marketing_expenses_'.$count_name."_rate"?>
                    <tr class="tr-color">
                        <th class="tr-color">{{__('Yr-').$year_format}}(%) </th>
                        <td class="h5 text-dark td-style">{{ $expense->$rate_name ?? '-' }} %</td>
                    </tr>
                    @endforeach
                <tr>
                <tr>
                    <th colspan="2" class="share_header"> {{ __('Monthly Distribution Expenses  Rate %').__(" (Of Sales)")}} </th>
                </tr>
                @foreach ($years as $data)
                    <?php $count_name = array_key_first($data) ; $year = $data[$count_name];?>
                    <?php $year_format = date('y',strtotime('01-01-'.$year)); ?>
                    <?php $rate_name = 'monthly_distribution_expenses_'.$count_name."_rate"?>
                    <tr class="tr-color">
                        <th class="tr-color">{{__('Yr-').$year_format}}(%) </th>
                        <td class="h5 text-dark td-style">{{ $expense->$rate_name ?? '-' }} %</td>
                    </tr>
                    @endforeach
                <tr>
                    <th colspan="2" class="share_header"> {{ __('General Expenses')  }}</th>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Monthly General Expenses Amount')}}</th>
                    <td class="h5 text-dark td-style">{{ number_format($expense->monthly_general_expenses,0) ?? '-' }}</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Operations Cost Rate %')}}  </th>
                    <td class="h5 text-dark td-style">{{ $expense->technical_cost_rate ?? '-' }} %</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Other G&A Rate %').__(" (Of Sales)")}} </th>
                    <td class="h5 text-dark td-style">{{ $expense->other_rate ?? '-' }} %</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Start-up Fees')}} </th>
                    <td class="h5 text-dark td-style">{{ number_format($expense->start_up_fees) ?? '-' }}</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Expenses Annual Progression %')}}</th>
                    <td class="h5 text-dark td-style">{{ $expense->expenses_annual_progression ?? '-' }} %</td>
                </tr>

            </tbody>
        </table>
    </div>


@endsection
