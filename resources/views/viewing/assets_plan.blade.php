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
                    <th colspan="2" class="share_header">{{ __('Fixed Assets One')  }}</th>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Fixed Assets Value')}}</th>
                    <td class="h5 text-dark td-style">{{ number_format($assets->fixed_assets_value,0) ?? '-' }}</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Life Duration (Months)')}}</th>
                    <td class="h5 text-dark td-style">{{ $assets->life_duration_months ?? '-' }}</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Monthly Depreciation')}}</th>
                    <td class="h5 text-dark td-style">{{ number_format($assets->monthly_deprication,0) ?? '-' }}</td>
                </tr>

                <tr>
                    <th colspan="2" class="share_header">{{ __('Fixed Assets One Payment')  }}</th>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Down Payment %')}}</th>
                    <td class="h5 text-dark td-style">{{ $assets->down_payment ?? '-' }} %</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Balance Rate %')}}</th>
                    <td class="h5 text-dark td-style">{{ $assets->balance_rate ?? '-' }} %</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Installment Count')}}</th>
                    <td class="h5 text-dark td-style">{{ $assets->installment_count ?? '-' }}</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Installment Interval')}}</th>
                    <td class="h5 text-dark td-style">{{ __(ucfirst($assets->balance_rate_period)) ?? '-' }}</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Interest Rate %')}}</th>
                    <td class="h5 text-dark td-style">{{ $assets->interest_rate ?? '-' }} %</td>
                </tr>



                <tr>
                    <th colspan="2" class="share_header">{{ __('Fixed Assets Two')  }}</th>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Fixed Assets Value')}}</th>
                    <td class="h5 text-dark td-style">{{ number_format($assets->fixed_assets_value_two,0) ?? '-' }}</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Life Duration (Months)')}}</th>
                    <td class="h5 text-dark td-style">{{ $assets->life_duration_months_two ?? '-' }}</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Monthly Depreciation')}}</th>
                    <td class="h5 text-dark td-style">{{ number_format($assets->monthly_deprication_two,0) ?? '-' }}</td>
                </tr>

                <tr>
                    <th colspan="2" class="share_header">{{ __('Fixed Assets Two Payment')  }}</th>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Down Payment %')}}</th>
                    <td class="h5 text-dark td-style">{{ $assets->down_payment_two ?? '-' }} %</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Balance Rate %')}}</th>
                    <td class="h5 text-dark td-style">{{ $assets->balance_rate_two ?? '-' }} %</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Installment Count')}}</th>
                    <td class="h5 text-dark td-style">{{ $assets->installment_count_two ?? '-' }}</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Installment Interval')}}</th>
                    <td class="h5 text-dark td-style">{{ __(ucfirst($assets->balance_rate_period_two)) ?? '-' }}</td>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{__('Interest Rate %')}}</th>
                    <td class="h5 text-dark td-style">{{ $assets->interest_rate_two ?? '-' }} %</td>
                </tr>
            </tbody>
        </table>
    </div>


@endsection
