@extends('layouts.view_layout')
@section('tab_content')
    <div class="text-center">
        <div class="container">

            <ul class="nav nav-tabs">
                <li class="col"><a data-toggle="tab" href="#assets" class="active">{{ __('Assets') }}</a></li>
                <li class="col"><a data-toggle="tab" href="#liabilities">{{ __('Liabilities & Equity') }}</a></li>
            </ul>
        </div>
        <div class="tab-content">
            <div id="assets" class="tab-pane fade in active show">
                <div class="ProjectList">
                    <div class="row2">

                        <div class="dashboard_item">
                            {{-- <div class="projectItem">
                                {{ __('Fixed Assets') }}
                            </div> --}}
                            {{-- <div class="col-10 offset-1"> --}}
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
                                                <th colspan="2" class="share_header">{{__('Fixed Assets')}}</th>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Fixed Assets Gross Value') }}</th>
                                                <td class="h5 text-dark td-style">{{ number_format($openning->gross_value) ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Accumulated Deprication') }}</th>
                                                <td class="h5 text-dark td-style">
                                                    {{ number_format($openning->accumulated_deprication) ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Net Fixed Assets') }}</th>
                                                <td class="h5 text-dark td-style">
                                                    {{ number_format($openning->net_fixed_assets) ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Monthly Depreciation') }}</th>
                                                <td class="h5 text-dark td-style">
                                                    {{ number_format($openning->monthly_deprication) ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Deprication Duration (Months)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->duration ?? '-' }}</td>
                                            </tr>



                                            <tr>
                                                <th colspan="2" class="share_header">{{__('Current Assets')}}</th>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Cash & Banks Balance') }}</th>
                                                <td class="h5 text-dark td-style">{{ number_format($openning->cash_banks_balance) ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Customers Invoices & Checks Balance') }}</th>
                                                <td class="h5 text-dark td-style">{{ number_format($openning->checks_balance) ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Collection Rate A (%)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->collection_rate_a ?? '-' }} %</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Collected Within (Days)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->Collected_days_a ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Collection Rate B (%)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->collection_rate_b ?? '-' }} %</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Collected Within (Days)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->Collected_days_b ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Collection Rate C (%)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->collection_rate_c ?? '-' }} %</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Collected Within (Days)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->Collected_days_c ?? '-' }}</td>
                                            </tr>


                                            <tr>
                                                <th colspan="2" class="share_header">{{__('Other Debtors Balance')}}</th>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Other Debtors Balance') }}</th>
                                                <td class="h5 text-dark td-style">{{ number_format($openning->other_bebtors_balance) ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Settlement Rate A (%)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->assets_settlment_rate_a ?? '-' }} %</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Settled Within (Days)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->assets_settled_within_days_a ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Settlement Rate B (%)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->assets_settlment_rate_b ?? '-' }} %</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Settled Within (Days)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->assets_settled_within_days_b ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Settlement Rate C (%)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->assets_settlment_rate_c ?? '-' }}  %</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Settled Within (Days)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->assets_settled_within_days_c ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Total Assets') }}</th>
                                                <td class="h5 text-dark td-style">{{ number_format($openning->total_assets) ?? '-' }}</td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            {{-- </div> --}}
                        </div>
                    </div>
                </div>
            </div>



            <div id="liabilities" class="tab-pane fade in">
                <div class="ProjectList">
                    <div class="row2">

                        <div class="dashboard_item">
                            {{-- <div class="projectItem">
                                {{ __('Fixed Assets') }}
                            </div> --}}
                            {{-- <div class="col-10 offset-1"> --}}
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
                                                <th colspan="2" class="share_header">{{__('Current Liabilities')}}</th>
                                            </tr>

                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Suppliers Invoices & Checks Balance') }}</th>
                                                <td class="h5 text-dark td-style">{{ number_format($openning->suppliers_checks_balance) ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Payment Rate A (%)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->payment_rate_a ?? '-' }} %</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Paid Within (Days)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->paid_within_a ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Payment Rate B (%)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->payment_rate_b ?? '-' }} %</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Paid Within (Days)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->paid_within_b ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Payment Rate C (%)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->payment_rate_c ?? '-' }} %</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Paid Within (Days)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->paid_within_c ?? '-' }}</td>
                                            </tr>



                                            <tr>
                                                <th colspan="2" class="share_header"></th>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Other Creditors Balance') }}</th>
                                                <td class="h5 text-dark td-style">{{ number_format($openning->other_creditors_balance) ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Settlement Rate A (%)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->liabilities_settlment_rate_a ?? '-' }} %</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Settled Within (Days)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->liabilities_settled_within_days_a ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Settlement Rate B (%)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->liabilities_settlment_rate_b ?? '-' }} %</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Settled Within (Days)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->liabilities_settled_within_days_b ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Settlement Rate C (%)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->liabilities_settlment_rate_c ?? '-' }} %</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Settled Within (Days)') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->liabilities_settled_within_days_c ?? '-' }}</td>
                                            </tr>


                                            <tr>
                                                <th colspan="2" class="share_header">{{__('Long Term Liabilities')}}</th>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Long Term Loan Amount') }}</th>
                                                <td class="h5 text-dark td-style">{{ number_format($openning->long_term_loan_amount) ?? '-' }}</td>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{__('Installment Interval')}}</th>
                                                <td class="h5 text-dark td-style">{{ __(ucfirst($openning->installment_interval)) ?? '-' }}</td>
                                            </tr>
                                            @foreach ($years as $num => $data)
                                                <?php $count_name = array_key_first($data) ; $year = $data[$count_name];?>
                                                <?php $year_format = date('y',strtotime('01-01-'.$year)); ?>

                                                <tr class="tr-color">
                                                    <?php $name = $count_name."_interest_amount"?>
                                                    <th class="tr-color">{{ __("Interest Amount Yr-") .$year_format }} </th>
                                                    <td class="h5 text-dark td-style">{{number_format($openning->$name) ?? '-' }}</td>
                                                </tr>
                                                <tr class="tr-color">
                                                    <?php $end_balance_name = $count_name."_end_balance"?>
                                                    <th class="tr-color">{{ __("Balance At The End Of Yr-") . $year_format }} </th>
                                                    <td class="h5 text-dark td-style">{{ number_format($openning->$end_balance_name) ?? '-' }}</td>
                                                </tr>
                                            @endforeach






                                            <tr>
                                                <th colspan="2" class="share_header"></th>
                                            </tr>
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Other Long Term Liabilities') }}</th>
                                                <td class="h5 text-dark td-style">{{ $openning->other_long_term_liabilites ?? '-' }}</td>
                                            </tr>
                                            @foreach ($years as $num => $data)
                                                <?php $count_name = array_key_first($data) ; $year = $data[$count_name];?>
                                                <?php $year_format = date('y',strtotime('01-01-'.$year)); ?>
                                                <tr class="tr-color">
                                                    <?php $name = $count_name."_settlment_amount"?>
                                                    <th class="tr-color">{{ __("Settlement Amount Yr-") .$year_format }} </th>
                                                    <td class="h5 text-dark td-style">{{number_format($openning->$name) ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="tr-color">
                                                <th class="tr-color">{{ __('Owners Equity') }}</th>
                                                <td class="h5 text-dark td-style">{{ number_format($openning->owners_equity) ?? '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            {{-- </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
