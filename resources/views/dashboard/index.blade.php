@extends('layouts.app')
<?php
@$product_first = $project->product_first;
@$product_second = $project->product_second;
@$product_third = $project->product_third;
@$product_fourth = $project->product_fourth;
@$product_fifth = $project->product_fifth;
?>
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css"
        crossorigin="anonymous">
    <style>
        table thead{
            /* table-layout:fixed; */
            white-space: nowrap;
        }
        .dataTables_scrollHeadInner,table{
            width: 100% !important;
        }
    </style>
@endsection
@section('content')
    <div class="col-13  text-center">
        <div class="container">
            <h1 class="d-flex justify-content-between steps-span">
                <span>{{ __('Study Results') }}</span>
            </h1>
            <h1 class="bread-crumbs">
                {{ __('ZAVERO Manufacturing') }} > {{ $project->name }} > {{ __('Study Results') }}
            </h1>
            @if (isset($slug))
                <a href="{{ route('study_info.view', [$slug]) }}" class="btn btn-rev mb-2" name="submit_button"
                    value="next">{{ __('View Data') }}</a>
            @endif
            @csrf
            <ul class="nav nav-tabs">
                <li class="text-center"><a data-toggle="tab" href="#home" class="active">{{ __('Study Results') }}</a></li>
                <li class="text-center"><a data-toggle="tab" href="#balance_sheet_results">{{ __('Balance Sheet') }}</a>
                </li>
                <li class="text-center"><a data-toggle="tab" href="#cash_flow_statement">{{ __('Cash Flow Statement') }}</a>
                </li>
                <li class="text-center"><a data-toggle="tab" href="#ratio_analysis">{{ __('Ratio Analysis Report') }}</a>
                </li>
                <li class="text-center"><a data-toggle="tab"
                        href="#feasibility_results">{{ __('Feasibility Results') }}</a></li>
                <li class="text-center"><a data-toggle="tab" href="#charts">{{ __('Charts') }}</a></li>
                @auth
                    @if (Auth::user()->id == $project->user_id)
                        <li class="text-center"><a data-toggle="tab" href="#target">{{ __('Target Sensitivity (+/-)') }}</a>
                        </li>
                        <li class="text-center"><a data-toggle="tab"
                                href="#cost">{{ __('Raw Material Cost % Sensitivity (+/-)') }}</a></li>
                        <li class="text-center"><a data-toggle="tab"
                                href="#collections">{{ __('Collections Sensitivity (+)') }}</a></li>
                    @endif
                @endauth
            </ul>
            <?php $years_count = @count($duration_years) == 1 ? 5 : @count($duration_years); ?>
            <?php $years_count_with_out_total = @count($duration_years) == 1 ? 4 : @count($duration_years); ?>
            <div class="tab-content dash-tap">
                {{-- DASHBOARD --}}
                <div id="home" class="tab-pane fade in active show tableItem ">
                    <br>
                    <div class="col-md-10 offset-md-1">
                        <table border="1" class="table  dashboardTable dashboard-table  datatablejs " class="display" >
                            <thead>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <th>{{ @count($duration_years) == 1 ? '' : __('Yr -') }} {{ __($year) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Sales Values --}}
                                 <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}">{{ __('(+) Sales Values') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    <?php $multi_line_chart = []; ?>
                                    @foreach ($full_duration as $date => $year)
                                        <td>{{ number_format(@$project_sales_in_years[$year]) }}</td>
                                        @if (@count($duration_years) != 1)
                                            <?php $multi_line_chart[] = ['date' => $year . '-12-01', __('Sales Values') => isset($project_sales_in_years[$year]) ? number_format($project_sales_in_years[$year]) : 0]; ?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Raw Material Cost --}}
                                 <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}">{{ __('(-) Raw Material Cost') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td>{{ number_format(@$products_rm_cost_total_in_years[$year]) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td class="sub-title">
                                            {{ isset($project_sales_in_years[$year]) && $project_sales_in_years[$year] != 0 ? number_format((@$products_rm_cost_total_in_years[$year] / @$project_sales_in_years[$year]) * 100, 2) : 0 }}
                                            %</td>
                                    @endforeach
                                </tr>
                                {{-- Labor Cost --}}
                                 <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}">{{ __('(-) Labor Cost') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td>{{ number_format(@$products_labor_cost_total_in_years[$year]) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td class="sub-title">
                                            {{ isset($project_sales_in_years[$year]) && $project_sales_in_years[$year] != 0 ? number_format((@$products_labor_cost_total_in_years[$year] / @$project_sales_in_years[$year]) * 100, 2) : 0 }}
                                            %</td>
                                    @endforeach
                                </tr>
                                {{-- Manufacturing Overheads Cost --}}
                                 <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}">{{ __('(-) Manufacturing Overheads Cost') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td>{{ number_format(@$products_moh_cost_total_in_years[$year]) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td class="sub-title">
                                            {{ isset($project_sales_in_years[$year]) && $project_sales_in_years[$year] != 0 ? number_format((@$products_moh_cost_total_in_years[$year] / @$project_sales_in_years[$year]) * 100, 2) : 0 }}
                                            %</td>
                                    @endforeach
                                </tr>

                                {{-- Operation Depreciation --}}
                                 <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}">{{ __('(-) Operation Depreciation') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    <?php $result_of_operation_deprication = []; ?>
                                    @foreach ($full_duration as $year)
                                        <?php $result_of_operation_deprication[$year] = @$opening_monthly_deprication_75_in_years[$year] + @$monthly_deprication_75_in_years[$year]; ?>
                                        <td>{{ number_format($result_of_operation_deprication[$year] ?? 0) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td class="sub-title">
                                            {{ isset($project_sales_in_years[$year]) && $project_sales_in_years[$year] != 0 ? number_format((@$result_of_operation_deprication[$year] / @$project_sales_in_years[$year]) * 100, 2) : 0 }}
                                            %</td>
                                    @endforeach
                                </tr>
                                {{-- Cost Of Goods Sold --}}
                                 <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}">{{ __('(-) Cost Of Goods Sold') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td>{{ number_format(@$cost_of_products_in_years[$year]) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td class="sub-title">
                                            {{ isset($project_sales_in_years[$year]) && $project_sales_in_years[$year] != 0 ? number_format((@$cost_of_products_in_years[$year] / @$project_sales_in_years[$year]) * 100, 2) : 0 }}
                                            %</td>
                                    @endforeach
                                </tr>
                                {{-- Gross Profit --}}
                                 <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}">{{ __('(+/-) Gross Profit') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td>{{ number_format(@$gross_profit_in_years[$year]) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <?php $gross_ratio[$year] = isset($project_sales_in_years[$year]) && $project_sales_in_years[$year] != 0 ? (@$gross_profit_in_years[$year] / @$project_sales_in_years[$year]) * 100 : 0 ?>
                                    <td class="sub-title">{{ number_format($gross_ratio[$year],2) }} %</td>
                                    @endforeach
                                </tr>
                                {{-- Total Sales Marketing --}}
                                 <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}">{{ __('(-) Total Sales Marketing Expenses') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td>{{ number_format(@$total_sales_markting_in_years[$year]) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td class="sub-title">
                                            {{ isset($project_sales_in_years[$year]) && $project_sales_in_years[$year] != 0 ? number_format((@$total_sales_markting_in_years[$year] / @$project_sales_in_years[$year]) * 100, 2) : 0 }}
                                            %</td>
                                    @endforeach
                                </tr>
                                {{-- Total Distribution --}}
                                 <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}">{{ '(-) '.__('Total Distribution Expenses') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td>{{ number_format(@$total_distribution_expenses_in_years[$year]) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td class="sub-title">
                                            {{ isset($project_sales_in_years[$year]) && $project_sales_in_years[$year] != 0 ? number_format((@$total_distribution_expenses_in_years[$year] / @$project_sales_in_years[$year]) * 100, 2) : 0 }}
                                            %</td>
                                    @endforeach
                                </tr>
                                {{-- Total General Expenses --}}
                                 <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}">{{ __('(-) Total General Expenses') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td>{{ number_format(@$total_general_expenses_in_years[$year]) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td class="sub-title">
                                            {{ isset($project_sales_in_years[$year]) && $project_sales_in_years[$year] != 0 ? number_format((@$total_general_expenses_in_years[$year] / @$project_sales_in_years[$year]) * 100, 2) : 0 }}
                                            %</td>
                                    @endforeach
                                </tr>

                                {{-- Administrative Depreciation --}}
                                 <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}">{{ __('(-) Administrative Depreciation') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    <?php $result_of_administrative_deprication = []; ?>
                                    @foreach ($full_duration as $year)
                                        <?php $result_of_administrative_deprication[$year] = @$opening_monthly_deprication_25_in_years[$year] + @$monthly_deprication_25_in_years[$year]; ?>
                                        <td>{{ number_format(@$opening_monthly_deprication_25_in_years[$year] + @$monthly_deprication_25_in_years[$year]) }}
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td class="sub-title">
                                            {{ isset($project_sales_in_years[$year]) && $project_sales_in_years[$year] != 0 ? number_format((@$result_of_administrative_deprication[$year] / @$project_sales_in_years[$year]) * 100, 2) : 0 }}
                                            %</td>
                                    @endforeach
                                </tr>
                                {{-- (+/-) EBITDA --}}
                                 <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}">{{ __("(+/-) Earnings Before Interest Taxes Depreciation & Amortization 'EBITDA'") }} </th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td>{{ number_format(@$ebitda_in_years[$year]) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)

                                        <?php   $ebitda[$year] = isset($project_sales_in_years[$year]) && $project_sales_in_years[$year] != 0 ? ((@$ebitda_in_years[$year] / @$project_sales_in_years[$year]) * 100) : 0 ?>
                                            <td class="sub-title"> {{number_format($ebitda[$year],2)}}%</td>
                                    @endforeach
                                </tr>
                                {{-- (+/-) EBIT --}}
                                 <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}">{{ __("(+/-) Earnings Before Interest Taxes 'EBIT'") }}
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                    </th>
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td>{{ number_format(@$ebit_in_years[$year]) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)

                                        <?php $ebit_ratio[$year] = isset($project_sales_in_years[$year]) && $project_sales_in_years[$year] != 0 ? ((@$ebit_in_years[$year] / @$project_sales_in_years[$year]) * 100) : 0 ?>
                                            <td class="sub-title"> {{number_format($ebit_ratio[$year],2)}}%</td>
                                    @endforeach
                                </tr>

                                {{-- (+/-) EBT --}}
                                 <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}">{{ __("(+/-) Earnings Before Taxes 'EBT'") }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td>{{ number_format(@$ebt_in_years[$year]) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td class="sub-title">
                                            {{ isset($project_sales_in_years[$year]) && $project_sales_in_years[$year] != 0 ? number_format((@$ebt_in_years[$year] / @$project_sales_in_years[$year]) * 100, 2) : 0 }}
                                            %</td>
                                    @endforeach
                                </tr>
                                {{-- (-) EBT Taxes --}}
                                 <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}">{{ __('(-) Taxes') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td>{{ number_format(@$ebt_taxes_in_years[$year]) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td class="sub-title">
                                            {{ isset($project_sales_in_years[$year]) && $project_sales_in_years[$year] != 0 ? number_format((@$ebt_taxes_in_years[$year] / @$project_sales_in_years[$year]) * 100, 2) : 0 }}
                                            %</td>
                                    @endforeach
                                </tr>

                                {{-- (+/-) Net Profit --}}
                                 <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}">{{ __('(+/-) Net Profit') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td>{{ number_format(@$net_profit_in_years[$year]) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $date => $year)
                                        <?php $net_profit_percent[$year] = isset($project_sales_in_years[$year]) && $project_sales_in_years[$year] != 0 ? (@$net_profit_in_years[$year] / @$project_sales_in_years[$year]) * 100  : 0; ?>
                                        <td class="sub-title">{{ number_format($net_profit_percent[$year]) }} %</td>
                                        <?php
                                        if (@count($duration_years) != 1) {
                                            $current_date = $year . '-12-01';
                                            if (false !== ($found = array_search($current_date, array_column($multi_line_chart, 'date')))) {
                                                $multi_line_chart[$found][__('Net Profit %')] = number_format($net_profit_percent[$year], 2);
                                            } else {
                                                $multi_line_chart[] = ['date' => $current_date, __('Net Profit %') => number_format($net_profit_percent[$year], 2)];
                                            }
                                        }
                                        ?>
                                    @endforeach

                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Balance Sheet Results --}}
                <div id="balance_sheet_results" class="tab-pane fade tableItem">
                    <br>
                    <div class="col-md-10 offset-md-1">
                        <table border="1" class="table  dashboardTable dashboard-table  datatablejs " class="display" >
                            <thead>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <th>{{ @count($duration_years) == 1 ? '' : __('Yr -') }} {{ __($year) }}</th>
                                        @endif
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Fixed Assets Gross Value') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_fixed_assets_per_year[$year] ?? 0)) }}</td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_fixed_assets_per_year[$year] = ($balance_sheet_fixed_assets_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Accumulated Deprication') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_accumulated_deprication_per_year[$year] ?? 0)) }}
                                            </td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_accumulated_deprication_per_year[$year] = ($balance_sheet_accumulated_deprication_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Net Fixed Assets') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_net_fixed_assets_per_year[$year] ?? 0)) }} </td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_net_fixed_assets_per_year[$year] = ($balance_sheet_net_fixed_assets_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Cash & Banks Balance') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_cash_and_banks_per_year[$year] ?? 0)) }}</td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_cash_and_banks_per_year[$year] = ($balance_sheet_cash_and_banks_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Customers Receivables') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_customers_receivables_per_year[$year] ?? 0)) }}</td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_customers_receivables_per_year[$year] = ($balance_sheet_customers_receivables_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Inventory') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_inventory_per_year[$year] ?? 0)) }}</td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_inventory_per_year[$year] = ($balance_sheet_inventory_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Other Debtors') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_other_debtors_per_year[$year] ?? 0)) }}</td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_other_debtors_per_year[$year] = ($balance_sheet_other_debtors_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Total Current Assets') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_total_current_assets_per_year[$year] ?? 0)) }}
                                            </td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_total_current_assets_per_year[$year] = ($balance_sheet_total_current_assets_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Total Assets') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_total_assets_per_year[$year] ?? 0)) }}</td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_total_assets_per_year[$year] = ($balance_sheet_total_assets_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Suppliers Payables') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_suppliers_payables_per_year[$year] ?? 0)) }}</td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_suppliers_payables_per_year[$year] = ($balance_sheet_suppliers_payables_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Other Creditors') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_other_creditors_per_year[$year] ?? 0)) }}</td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_other_creditors_per_year[$year] = ($balance_sheet_other_creditors_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Total Current Liabilities') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_total_current_liabilities_per_year[$year] ?? 0)) }}</td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_total_current_liabilities_per_year[$year] = ($balance_sheet_total_current_liabilities_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Long Term Loans') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_long_term_loans_per_year[$year] ?? 0)) }}</td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_long_term_loans_per_year[$year] = ($balance_sheet_long_term_loans_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Other Long Term Liabilities') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_other_long_liabilities_per_year[$year] ?? 0)) }}
                                            </td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_other_long_liabilities_per_year[$year] = ($balance_sheet_other_long_liabilities_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Total Long Term Liabilities') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_total_long_liabilities_per_year[$year] ?? 0)) }}
                                            </td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_total_long_liabilities_per_year[$year] = ($balance_sheet_total_long_liabilities_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Paid up Capital') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_paid_up_capital_per_year[$year] ?? 0)) }}</td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_paid_up_capital_per_year[$year] = ($balance_sheet_paid_up_capital_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Additional Paid up Capital') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_additional_paid_up_capital_per_year[$year] ?? 0)) }}
                                            </td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_additional_paid_up_capital_per_year[$year] = ($balance_sheet_additional_paid_up_capital_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Retained Earning') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>

                                    <?php $accumulated = 0; ?>
                                    @foreach ($full_duration as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@$accumulated) }}</td>
                                            <?php $accumulated += $net_profit_in_years[$year] ?? 0; ?>

                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}

                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Profit of the Period') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@$net_profit_in_years[$year]) }}</td>

                                        @endif
                                    @endforeach

                                </tr>


                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Total Owners Equity') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <td>{{ number_format(@($balance_sheet_total_owners_equity_per_year[$year] ?? 0)) }}
                                            </td>
                                        @elseif($key === 'Total')
                                            <?php $balance_sheet_total_owners_equity_per_year[$year] = ($balance_sheet_total_owners_equity_per_year["Q4"]??0);?>
                                        @endif
                                    @endforeach

                                </tr>

                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}"> {{ __('Check Error') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>

                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <?php $check_error = ($balance_sheet_total_assets_per_year[$year] ?? 0) - ($balance_sheet_total_current_liabilities_per_year[$year] ?? 0) - ($balance_sheet_total_long_liabilities_per_year[$year] ?? 0) - ($balance_sheet_total_owners_equity_per_year[$year] ?? 0); ?>
                                            <td>{{ number_format(@$check_error) }}</td>

                                        @endif
                                    @endforeach

                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Cash Flow Sheet Results --}}
                <div id="cash_flow_statement" class="tab-pane fade tableItem">
                    <br>
                    <div class="col-md-10 offset-md-1">
                        <table border="1" class="table  dashboardTable dashboard-table  datatablejs" class="display" style="width: 100%;">
                            <thead>
                                <tr>
                                    @foreach ($full_duration_for_balances as $year)
                                        <th>{{ @count($duration_years) == 1 ? '' : __('Yr -') }} {{ __($year) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('Net Profit') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <td>{{ number_format(@$net_profit_in_years[$year]) }}</td>
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('Add Depreciation Amount') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    <?php $deprciation_amount_result = []; ?>
                                    @foreach ($full_duration as $year)
                                        <?php $deprciation_amount_result[$year] = ($result_of_operation_deprication[$year] ?? 0) + ($result_of_administrative_deprication[$year] ?? 0); ?>
                                        <td>{{ number_format(@($deprciation_amount_result[$year] ?? 0)) }}</td>
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('(Increase) / Decrease In Customers Receivables') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    <?php $previous_year = $checks_balance_value; ?>

                                    @foreach ($full_duration_for_balances as $key => $year)
                                        <?php $change_in_customers_receivables[$year] = $previous_year - ($balance_sheet_customers_receivables_per_year[$year] ?? 0);
                                        if ($key == 'Total') {
                                            unset($change_in_customers_receivables['Total']);
                                            $change_in_customers_receivables['Total'] = array_sum($change_in_customers_receivables);
                                        }
                                        ?>

                                        <td>{{ number_format(@$change_in_customers_receivables[$year]) }}</td>
                                        <?php $previous_year = $balance_sheet_customers_receivables_per_year[$year] ?? 0; ?>
                                    @endforeach
                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('(Increase) / Decrease In Inventory') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>

                                <tr>
                                    <?php $previous_year = $total_beginning_inventory; ?>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        <?php $change_in_inventory[$year] = $previous_year - ($balance_sheet_inventory_per_year[$year] ?? 0);
                                        if ($key == 'Total') {
                                            unset($change_in_inventory['Total']);
                                            $change_in_inventory['Total'] = array_sum($change_in_inventory);
                                        }
                                        ?>
                                        <td>{{ number_format(@$change_in_inventory[$year]) }}</td>
                                        <?php $previous_year = $balance_sheet_inventory_per_year[$year] ?? 0; ?>
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('(Increase) / Decrease In Other Debtors') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    <?php $previous_year = $other_debtors_balance_value; ?>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        <?php $change_in_other_debtors[$year] = $previous_year - ($balance_sheet_other_debtors_per_year[$year] ?? 0);
                                        if ($key == 'Total') {
                                            unset($change_in_other_debtors['Total']);
                                            $change_in_other_debtors['Total'] = array_sum($change_in_other_debtors);
                                        }
                                        ?>
                                        <td>{{ number_format(@$change_in_other_debtors[$year]) }}</td>
                                        <?php $previous_year = $balance_sheet_other_debtors_per_year[$year] ?? 0; ?>
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('Increase / (Decrease) In Suppliers Payables') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    <?php $previous_year = $suppliers_checks_balance_value; ?>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        <?php $change_in_suppliers[$year] = ($balance_sheet_suppliers_payables_per_year[$year] ?? 0) - $previous_year;
                                        if ($key == 'Total') {
                                            unset($change_in_suppliers['Total']);
                                            $change_in_suppliers['Total'] = array_sum($change_in_suppliers);
                                        }
                                        ?>
                                        <td>{{ number_format(@$change_in_suppliers[$year]) }}</td>
                                        <?php $previous_year = $balance_sheet_suppliers_payables_per_year[$year] ?? 0; ?>
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('Increase / (Decrease) In Other Creditors') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    <?php $previous_year = $other_creditors_balance_value; ?>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        <?php $change_in_other_creditors[$year] = ($balance_sheet_other_creditors_per_year[$year] ?? 0) - $previous_year;
                                        if ($key == 'Total') {
                                            unset($change_in_other_creditors['Total']);
                                            $change_in_other_creditors['Total'] = array_sum($change_in_other_creditors);
                                        }
                                        ?>
                                        <td>{{ number_format(@$change_in_other_creditors[$year]) }}</td>
                                        <?php $previous_year = $balance_sheet_other_creditors_per_year[$year] ?? 0; ?>
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('Increase / (Decrease) In Other Long Term Liabilities') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    <?php $previous_year = $other_long_term_liabilites_value; ?>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        <?php $change_in_long_term_liabilities[$year] = ($balance_sheet_other_long_liabilities_per_year[$year] ?? 0) - $previous_year;
                                        if ($key == 'Total') {
                                            unset($change_in_long_term_liabilities['Total']);
                                            $change_in_long_term_liabilities['Total'] = array_sum($change_in_long_term_liabilities);
                                        }
                                        ?>
                                        <td>{{ number_format(@$change_in_long_term_liabilities[$year]) }}</td>
                                        <?php $previous_year = $balance_sheet_other_long_liabilities_per_year[$year] ?? 0; ?>
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('Net Change In Working Capital') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        <?php
                                        $net_change_in_wc[$year] = ($change_in_customers_receivables[$year] ?? 0) + ($change_in_inventory[$year] ?? 0) + ($change_in_other_debtors[$year] ?? 0) + ($change_in_suppliers[$year] ?? 0) + ($change_in_other_creditors[$year] ?? 0) + ($change_in_long_term_liabilities[$year] ?? 0);

                                        if ($key == 'Total') {
                                            unset($net_change_in_wc['Total']);
                                            $net_change_in_wc['Total'] = array_sum($net_change_in_wc);
                                        }
                                        ?>
                                        <td>{{ number_format(@($net_change_in_wc[$year] ?? 0)) }}</td>
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('Cash Flow From Operations') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        <?php
                                        $only_year = str_contains($year, 'Q') || str_contains($year, 'Total') ? $year : date('Y', strtotime($year));

                                        $cash_flow_operation[$year] = $net_change_in_wc[$year] + $net_profit_in_years[$only_year] + $deprciation_amount_result[$only_year];
                                        if ($key == 'Total') {
                                            unset($cash_flow_operation['Total']);
                                            $cash_flow_operation['Total'] = array_sum($cash_flow_operation);
                                        }
                                        ?>
                                        <td>{{ number_format(@($cash_flow_operation[$year] ?? 0)) }}</td>
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}

                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('(Increase) / Decrease In Fixed Assets') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    <?php $previous_year = $gross_value; ?>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        <?php $change_in_fixed_assets[$year] = $previous_year - ($balance_sheet_fixed_assets_per_year[$year] ?? 0 );
                                        if ($key == 'Total') {
                                            unset($change_in_fixed_assets['Total']);
                                            $change_in_fixed_assets['Total'] = array_sum($change_in_fixed_assets);
                                        }
                                        ?>
                                        <td>{{ number_format(@$change_in_fixed_assets[$year]) }}</td>
                                        <?php $previous_year = $balance_sheet_fixed_assets_per_year[$year] ?? 0; ?>
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('Increase / (Decrease) In Long Term Loans') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>

                                <tr>
                                    <?php $previous_year = $long_term_loan_amount_value; ?>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        <?php $change_in_long_term_loans[$year] = ($balance_sheet_long_term_loans_per_year[$year] ?? 0) - $previous_year;
                                        if ($key == 'Total') {
                                            unset($change_in_long_term_loans['Total']);
                                            $change_in_long_term_loans['Total'] = array_sum($change_in_long_term_loans);
                                        }
                                        ?>
                                        <td>{{ number_format(@$change_in_long_term_loans[$year]) }}</td>
                                        <?php $previous_year = $balance_sheet_long_term_loans_per_year[$year] ?? 0; ?>
                                    @endforeach

                                </tr>



                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('Increase / (Decrease) In Additional Paid up Capital') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    <?php $previous_year = 0; ?>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        <?php $change_in_capital[$year] = ($balance_sheet_additional_paid_up_capital_per_year[$year] ?? 0) - $previous_year;
                                        if ($key == 'Total') {
                                            unset($change_in_capital['Total']);
                                            $change_in_capital['Total'] = array_sum($change_in_capital);
                                        }
                                        ?>
                                        <td>{{ number_format(@$change_in_capital[$year]) }}</td>
                                        <?php $previous_year = $balance_sheet_additional_paid_up_capital_per_year[$year] ?? 0; ?>
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('Cash Flow From Financing') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $year)
                                        <?php
                                        $cash_flow_financing[$year] = ($change_in_long_term_loans[$year] ?? 0) + ($change_in_capital[$year] ?? 0);

                                        ?>
                                        <td>{{ number_format(@($cash_flow_financing[$year] ?? 0)) }}</td>
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('Net Change In Cash') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $year)
                                        <?php
                                        $net_change_in_cash[$year] = ($cash_flow_financing[$year] ?? 0) + ($change_in_fixed_assets[$year] ?? 0) + ($cash_flow_operation[$year] ?? 0);

                                        ?>
                                        <td>{{ number_format(@($net_change_in_cash[$year] ?? 0)) }}</td>
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('Cash & Banks At The Beginning') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    <?php $previous_year = $cash_banks_balance_value; ?>

                                    @foreach ($full_duration_for_balances as $key => $year)
                                        <?php $cash_at_beginning[$year] = $previous_year;
                                            if ($key === "Total") {

                                                $cash_at_beginning[$year] = $cash_at_beginning["Q1"];
                                            }

                                        ?>
                                        <td>{{ number_format(@($cash_at_beginning[$year] ?? 0)) }}</td>
                                        <?php $previous_year = $balance_sheet_cash_and_banks_per_year[$year] ?? 0; ?>
                                    @endforeach

                                </tr>
                                {{-- Sales Values --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}"> {{ __('Cash & Banks At The End') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>

                                    @foreach ($full_duration_for_balances as $year)
                                        <?php $cash_at_end[$year] = ($net_change_in_cash[$year] ?? 0) + $cash_at_beginning[$year]; ?>
                                        <td>{{ number_format(@($cash_at_end[$year] ?? 0)) }}
                                        </td>
                                    @endforeach

                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Feasibility Results --}}
                <div id="feasibility_results" class="tab-pane fade ">
                    <div class="col-10  offset-1 text-center">
                        <Div class="ProjectList">
                            <div class="row2">
                                <div class="dashboard_item">
                                    <div class="projectItem">
                                        {{ __('Required Equity Investment') }}
                                    </div>

                                    <div class="formItem">
                                        {{ $capital != 'NA' ? number_format(@$capital) : @$capital }} |
                                        {{ $capital != 'NA' ? date('M-Y', strtotime($capital_date)) : @$capital_date }}
                                    </div>

                                </div>
                                @if (($project->duration ?? 0) > 1)
                                    {{-- npv --}}
                                    <div class="dashboard_item">
                                        <div class="projectItem">
                                            {{ __("Net Present Value 'NPV' / Equity Value") }}
                                        </div>
                                        <div class="formItem">
                                            {{ @$net_present_value != 'NA' ? number_format(@$net_present_value) : @$net_present_value }}
                                        </div>
                                    </div>
                                    {{-- irr --}}
                                    @if ($project->new_company == 1)
                                        <div class="dashboard_item">
                                            <div class="projectItem">
                                                {{ __("Internal Rate Of Return 'IRR'") }}
                                            </div>
                                            <div class="formItem">

                                                {{ is_numeric($irr) ? number_format(@$irr, 2) : @$irr }} %
                                            </div>
                                        </div>
                                    @endif
                                    {{-- wacc --}}
                                    <div class="dashboard_item">
                                        <div class="projectItem">
                                            {{ __("Cost Of Equity") }}
                                        </div>
                                        <div class="formItem">
                                            {{ number_format(@$discount_rate_value, 2) }} %
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </Div>
                    </div>
                </div>
                {{-- ratio_analysis --}}
                <div id="ratio_analysis" class="tab-pane fade tableItem">
                    <br>

                    <div class="col-md-10 offset-md-1">
                        <h1>{{ __('Profitability Ratios') }}</h1>
                        <table border="1" class="table  dashboardTable dashboard-table  datatablejs" class="display" style="width: 100%;">
                            <thead>
                                <tr>
                                    @foreach ($full_duration as $year)
                                        <th>{{ @count($duration_years) == 1 ? '' : __('Yr -') }} {{ __($year) }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Return On Sales (ROS) --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}" class="sub-title">{{ __('Return On Sales (ROS)') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $date => $year)
                                        <td>{{ number_format(@$ebit_ratio[$year], 2) }} %</td>
                                    @endforeach

                                </tr>
                                {{-- Return On Assets (ROA) --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}" class="sub-title">{{ __('Return On Assets (ROA)') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $year)
                                        <?php
                                        $only_year = str_contains($year, 'Q') || str_contains($year, 'Total') ? $year : date('Y', strtotime($year));

                                        $roa[$year] = isset($balance_sheet_total_assets_per_year[$year]) && $balance_sheet_total_assets_per_year[$year] != 0 ? (@$net_profit_in_years[$only_year] / @$balance_sheet_total_assets_per_year[$year]) * 100 : 0;

                                        ?>
                                        <td>{{ number_format($roa[$year] ?? 0, 2) }} %</td>
                                    @endforeach
                                </tr>
                                {{-- Return On Investment (ROI) --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}" class="sub-title">{{ __('Return On Investment (ROI)') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $year)
                                        <?php
                                        $only_year = str_contains($year, 'Q') || str_contains($year, 'Total') ? $year : date('Y', strtotime($year));

                                        $total_investment[$year] = ($balance_sheet_total_assets_per_year[$year] ?? 0) - ($balance_sheet_total_current_liabilities_per_year[$year] ?? 0);

                                        $roi[$year] = isset($total_investment[$year]) && $total_investment[$year] != 0 ? (@$ebit_in_years[$only_year] / @$total_investment[$year]) * 100 : 0;

                                        ?>
                                        <td>{{ number_format($roi[$year] ?? 0, 2) }} %</td>
                                    @endforeach
                                </tr>
                                {{-- Return On Equity (ROE) --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}" class="sub-title">{{ __('Return On Equity (ROE)') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $year)
                                        <?php
                                        $only_year = str_contains($year, 'Q') || str_contains($year, 'Total') ? $year : date('Y', strtotime($year));

                                        $roe[$year] = isset($balance_sheet_total_owners_equity_per_year[$year]) && $balance_sheet_total_owners_equity_per_year[$year] != 0 ? (@$net_profit_in_years[$only_year] / @$balance_sheet_total_owners_equity_per_year[$year]) * 100 : 0;
                                        ?>
                                        <td>{{ number_format($roe[$year] ?? 0, 2) }} %</td>
                                    @endforeach
                                </tr>
                                {{-- Gross Profit Margin --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}" class="sub-title">{{ __('Gross Profit Margin') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $date => $year)
                                        <td>{{ number_format(@$gross_ratio[$year], 2) }} %</td>
                                    @endforeach

                                </tr>
                                {{-- EBITDA Margin --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}" class="sub-title">{{ __('EBITDA Margin') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $date => $year)
                                        <td>{{ number_format(@$ebitda[$year], 2) }} %</td>
                                    @endforeach

                                </tr>
                                {{-- Net Profit Margin --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count }}" class="sub-title">{{ __('Net Profit Margin') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration as $date => $year)
                                        <td>{{ number_format(@$net_profit_percent[$year], 2) }} %</td>
                                    @endforeach

                                </tr>
                            <tbody>
                        </table>
                    </div>
                    <br>
                    <br>


                    <br>
                    <div class="col-md-10 offset-md-1">
                        <h1>{{ __('Liquidity Ratios') }}</h1>
                        <table border="1" class="table  dashboardTable dashboard-table  datatablejs" class="display" style="width: 100%;">
                            <thead>
                                <tr>
                                    @foreach ($full_duration as $key => $year)
                                        @if ($key !== 'Total')
                                            <th>{{ @count($duration_years) == 1 ? '' : __('Yr -') }} {{ __($year) }}
                                            </th>
                                        @endif
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Current Ratio --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}" class="sub-title">{{ __('Current Ratio') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <?php
                                            $current_ratio[$year] = isset($balance_sheet_total_current_liabilities_per_year[$year]) && $balance_sheet_total_current_liabilities_per_year[$year] != 0 ? @$balance_sheet_total_current_assets_per_year[$year] / @$balance_sheet_total_current_liabilities_per_year[$year] : 0;
                                            ?>
                                            <td>{{ number_format(@($current_ratio[$year] ?? 0), 2) }} <b>:</b> 1</td>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Quick Ratio --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}" class="sub-title">{{ __('Quick Ratio') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <?php
                                            $quick_assets[$year] = ($balance_sheet_cash_and_banks_per_year[$year] ?? 0) + ($balance_sheet_customers_receivables_per_year[$year] ?? 0);

                                            $quick_ratio[$year] = isset($balance_sheet_total_current_liabilities_per_year[$year]) && $balance_sheet_total_current_liabilities_per_year[$year] != 0 ? @$quick_assets[$year] / @$balance_sheet_total_current_liabilities_per_year[$year] : 0;

                                            ?>
                                            <td>{{ number_format($quick_ratio[$year] ?? 0, 2) }} <b>:</b> 1</td>
                                        @endif
                                    @endforeach
                                </tr>
                                {{-- Cash Ratio --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}" class="sub-title">{{ __('Cash Ratio') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <?php

                                            $cash_ratio[$year] = isset($balance_sheet_total_current_liabilities_per_year[$year]) && $balance_sheet_total_current_liabilities_per_year[$year] != 0 ?  @$balance_sheet_cash_and_banks_per_year[$year] / @$balance_sheet_total_current_liabilities_per_year[$year] : 0;

                                            ?>
                                            <td>{{ number_format($cash_ratio[$year] ?? 0, 2) }} <b>:</b> 1</td>
                                        @endif
                                    @endforeach
                                </tr>
                                {{-- Working Capital --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}" class="sub-title">{{ __('Working Capital') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <?php
                                            $working_capital[$year] = ($balance_sheet_total_current_assets_per_year[$year] ?? 0) - ($balance_sheet_total_current_liabilities_per_year[$year] ?? 0);
                                            ?>
                                            <td>{{ number_format(@($working_capital[$year] ?? 0)) }} </td>
                                        @endif
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <br>



                    <div class="col-md-10 offset-md-1">
                        <h1>{{ __('Efficiency Ratios') }}</h1>

                        <table border="1" class="table  dashboardTable dashboard-table  datatablejs" class="display" style="width: 100%;">
                            <thead>

                                <tr>
                                    @foreach ($full_duration as $key => $year)
                                        @if ($key !== 'Total')
                                            <th>{{ @count($duration_years) == 1 ? '' : __('Yr -') }} {{ __($year) }}
                                            </th>
                                        @endif
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Days Sales Outstanding (DSO) --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}" class="sub-title">{{ __('Days Sales Outstanding (DSO)') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    <?php $accumulated_sales = 0 ; ?>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <?php
                                            $only_year = str_contains($year, 'Q') || str_contains($year, 'Total') ? $year : date('Y', strtotime($year));


                                            if(str_contains($year, 'Q')){
                                                $accumulated_sales += $project_sales_in_years[$only_year] ?? 0;
                                                $dso_result[$year] = $accumulated_sales != 0 ? @$dso[$year] / $accumulated_sales : 0;
                                            }else {
                                                $dso_result[$year] =  ($project_sales_in_years[$only_year] ?? 0) != 0 ? @$dso[$year] / $project_sales_in_years[$only_year] : 0;
                                            }
                                            ?>
                                            <td>{{ number_format($dso_result[$year] ?? 0, 2) }} {{ __('Days') }}</td>

                                        @endif
                                    @endforeach

                                </tr>

                                {{-- Days Inventory Outstanding (DIO) --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}" class="sub-title">{{ __('Days Inventory Outstanding (DIO)') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    <?php $accumulated_cost = 0 ; ?>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <?php
                                            $only_year = str_contains($year, 'Q') || str_contains($year, 'Total') ? $year : date('Y', strtotime($year));

                                            if(str_contains($year, 'Q')){
                                                $accumulated_cost += ($cost_of_products_in_years[$only_year]??0);
                                                    $dio_result[$year] = $accumulated_cost != 0 ? @$dio[$year] / $accumulated_cost : 0;
                                            }else {
                                                $dio_result[$year] =  ($cost_of_products_in_years[$only_year] ?? 0) != 0 ? @$dio[$year] / $cost_of_products_in_years[$only_year] : 0;
                                            }
                                            ?>
                                            <td>{{ number_format($dio_result[$year] ?? 0, 2) }} {{ __('Days') }}</td>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Days Payables Outstanding (DPO) --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}" class="sub-title">{{ __('Days Payables Outstanding (DPO)') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    <?php $accumulated_cost = 0 ; ?>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <?php
                                            $only_year = str_contains($year, 'Q') || str_contains($year, 'Total') ? $year : date('Y', strtotime($year));
                                                if(str_contains($year, 'Q')){
                                                    $accumulated_cost += $cost_of_products_in_years[$only_year] ?? 0;
                                                    $dpo_result[$year] = $accumulated_cost != 0 ? @$dpo[$year] / @$accumulated_cost : 0;
                                                }else {
                                                    $dpo_result[$year] =  ($cost_of_products_in_years[$only_year] ?? 0) != 0 ? @$dpo[$year] / $cost_of_products_in_years[$only_year] : 0;
                                                }
                                            ?>

                                            <td>{{ number_format($dpo_result[$year] ?? 0, 2) }} {{ __('Days') }}</td>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Cash Conversion Cycle (CCC) --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}" class="sub-title">{{ __('Cash Conversion Cycle (CCC)') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <?php
                                            $ccc[$year] = $dso_result[$year] + $dio_result[$year] - $dpo_result[$year];
                                            ?>
                                            <td>{{ number_format(@($ccc[$year] ?? 0)) }} {{ __('Days') }} </td>
                                        @endif
                                    @endforeach
                                </tr>
                            </tbody>

                        </table>
                    </div>
                    <br>
                    <br>



                    <div class="col-md-10 offset-md-1">
                        <h1>{{ __('Leverage Ratios') }}</h1>
                        <table border="1" class="table  dashboardTable dashboard-table  datatablejs" class="display" style="width: 100%;">
                            <thead>
                                <tr>
                                    @foreach ($full_duration as $key => $year)
                                        @if ($key !== 'Total')
                                            <th>{{ @count($duration_years) == 1 ? '' : __('Yr -') }} {{ __($year) }}
                                            </th>
                                        @endif
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                {{-- Debt To Asset Ratio --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}" class="sub-title">{{ __('Debt To Asset Ratio') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <?php
                                            $total_liabelitis[$year] = ($balance_sheet_total_current_liabilities_per_year[$year] ?? 0) + ($balance_sheet_total_long_liabilities_per_year[$year] ?? 0);

                                            $dept_to_asset[$year] = isset($balance_sheet_total_assets_per_year[$year]) && $balance_sheet_total_assets_per_year[$year] != 0 ? @$total_liabelitis[$year] / @$balance_sheet_total_assets_per_year[$year] : 0;

                                            ?>
                                            <td>{{ number_format(@($dept_to_asset[$year] ?? 0), 2) }} : 1</td>
                                        @endif
                                    @endforeach

                                </tr>
                                {{-- Debt To Equity Ratio --}}
                                <tr>
                                    @for ($num = 0; $num < $years_count_with_out_total; $num++)
                                        @if ($num == 0)
                                            <th colspan="{{ $years_count_with_out_total }}" class="sub-title">{{ __('Debt To Equity Ratio') }}</th>
                                        @else
                                            <td style="display: none"></td>
                                        @endif
                                    @endfor
                                </tr>
                                <tr>
                                    @foreach ($full_duration_for_balances as $key => $year)
                                        @if ($key !== 'Total')
                                            <?php

                                            $quick_ratio[$year] = isset($balance_sheet_total_owners_equity_per_year[$year]) && $balance_sheet_total_owners_equity_per_year[$year] != 0 ? @$total_liabelitis[$year] / @$balance_sheet_total_owners_equity_per_year[$year]: 0;

                                            ?>
                                            <td>{{ number_format($quick_ratio[$year] ?? 0, 2) }} <b>:</b> 1</td>
                                        @endif
                                    @endforeach
                                </tr>
                            </tbody>

                        </table>
                    </div>
                </div>
                {{-- Charts --}}
                <div id="charts" class="tab-pane fade ">
                    <!-- HTML -->
                    <Div class="ProjectList">
                        <h1>{{ __('Accumulated Cash Flow - First 36 Months') }}</h1>
                        <div class="chartBackGround">
                            <div id="chartdiv"></div>
                        </div>
                    </Div>
                    @if (@count($duration_years) != 1)
                        {
                        <!-- HTML -->
                        <Div class="ProjectList">
                            <h1>{{ __('Sales Value Versus Net Profit %') }}</h1>
                            <div class="chartBackGround">
                                <div id="chartdiv1"></div>
                            </div>
                        </Div>
                    @endif
                </div>
                {{-- Target Sensitivity (+/-)' --}}
                <div id="target" class="tab-pane fade ">
                    <br>
                    <div class="col-10 offset-1">
                        @foreach ($products as $key => $product_name)
                            <?php $name = 'target_' . $product_name; ?>
                            <label class="black">{{ $project->$product_name . ' ' . __('Target Sensitivity (+/-)') }}
                            </label>
                            <div class="form-group">
                                <input type="number" class="form-control " name="{{ $name }}"
                                    value="{{ isset($sensitivity->$name) ? $sensitivity->$name : '' }}"
                                    placeholder="{{ __('Please Enter Rate') }}">
                            </div>
                        @endforeach
                    </div>
                    <br>
                    <button type="submit" class="btn btn-rev float-right position" name="submit_button"
                        value="next">{{ __('Save') }}</button>
                </div>
                {{-- Cost % Sensitivity --}}
                <div id="cost" class="tab-pane fade ">
                    <br>
                    <div class="col-10 offset-1">
                        @foreach ($products as $key => $product_name)
                            <?php $name = 'rm_' . $product_name; ?>
                            <label class="black">
                                {{ $project->$product_name . ' ' . __('Raw Material Cost % Sensitivity (+/-)') }}</label>
                            <div class="form-group ">
                                <input type="number" class="form-control " name="{{ $name }}"
                                    value="{{ isset($sensitivity->$name) ? $sensitivity->$name : '' }}"
                                    placeholder="{{ __('Please Enter Rate') }}">
                            </div>
                        @endforeach
                    </div>
                    <br>
                    <button type="submit" class="btn btn-rev float-right position" name="submit_button"
                        value="next">{{ __('Save') }}</button>
                </div>
                {{-- Collections Sensitivity (+)  --}}
                <div id="collections" class="tab-pane fade ">
                    <br>
                    <div class="col-10 offset-1">
                        @foreach ($products as $product_name)
                            <?php
                            $name = 'collections_' . $product_name;
                            $collection = isset($sensitivity->$name) ? $sensitivity->$name : old($name);
                            ?>
                            <label class="black">
                                {{ $project->$product_name . ' ' . __('Collections Sensitivity (+)') }}
                            </label>
                            <div class="form-group ">
                                <select class="form-control" name="{{ $name }}">
                                    <option value="">{{ __('Select') }}</option>
                                    <option value="0" {{ $collection == 0 ? 'selected' : '' }}>0</option>
                                    <option value="30" {{ $collection == 30 ? 'selected' : '' }}>30</option>
                                    <option value="45" {{ $collection == 45 ? 'selected' : '' }}>45</option>
                                    <option value="60" {{ $collection == 60 ? 'selected' : '' }}>60</option>
                                    <option value="90" {{ $collection == 90 ? 'selected' : '' }}>90</option>
                                </select>
                            </div>
                        @endforeach
                    </div>
                    <br>
                    <button type="submit" class="btn btn-rev float-right position" name="submit_button"
                        value="next">{{ __('Save') }}</button>
                </div>
                <br>
            </div>
            </form>
            @auth
                @if (Auth::user()->id == $project->user_id)
                    <a href="{{ route('main.project.page', $project) }}" class="btn btn-rev float-right main-page-button"
                        name="submit_button" value="save">{{ __('Go To Main Page') }}</a>
                @endif
            @endauth
        </div>
    </div>
    <div class="clearfix"></div>
    <input type="hidden" id="accumelated_net_cash_flow_chart"
        data-total="{{ json_encode($accumelated_net_cash_flow_chart) }}">
    <input type="hidden" id="multi_line_chart" data-total="{{ json_encode($multi_line_chart) }}">
@endsection
@section('js')
    <script src="https://cdn.amcharts.com/lib/4/core.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/dataviz.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('.datatablejs').DataTable({
                searching: false,
                paging: false,
                scrollX: true,
                ordering: false,
                autoWidth : true,
                fixedColumns:   {
                    left: 1,
                    right: 1
                },
                dom: 'Bfrtip',
                buttons: [
                    // , 'excel'
                    'copy', 'csv', 'pdf', getExportKey(), 'print'
                ]
            });
        });
        table.on( 'buttons-action', function ( e, indicator ) {

            if ( indicator ) {
                overlay.appendTo('body');
            }
            else {
                overlay.remove();
            }
        } );
        function getExportKey() {

            return {
                "extend": "excel",
                title: '',
                // action: function ( e, dt, node, config ) {
                //     var currentTableTitle =  $(node).closest('button').attr('aria-controls') ; console.log(currentTableTitle);},
                filename: 'ZAVERO Service',
                customize: function (xlsx,item,item2,title) {
                    exportToExcel(xlsx)

                }
            };
        }
    </script>
    <script>
        $(function()  {
            $('#copy').click(function(e){
                var content = $('#content').html();
                var newdiv = $("<div>");
                    copyText.setSelectionRange(0, 99999);
                    document.execCommand("copy");
            });
        });

        function exportToExcel(xlsx){
            // alert('2');
            numberOfRows = 0 ;
            eachInRow = 0 ;

            let companyName ="Study Name [ "+"{{ isset($project) && isset($project->name) ? $project->name : '' }}" +" ]";
            if(companyName)
            {
                eachInRow += 1 ;
            }
            let reportName = '';
            if(reportName)
            {
                companyName += (' (' +  reportName + ' )');
            }

            var sheet = xlsx.xl.worksheets['sheet1.xml'];
            var downrows = eachInRow;
            var clRow = $('row', sheet);

            clRow.each(function () {
                var attr = $(this).attr('r');
                var ind = parseInt(attr);
                ind = ind + downrows;
                $(this).attr("r",ind);
            });


            $('row c ', sheet).each(function () {
                var attr = $(this).attr('r');
                var pre = attr.substring(0, 1);
                var ind = parseInt(attr.substring(1, attr.length));
                ind = ind + downrows;
                $(this).attr("r", pre + ind);
            });

            function Addrow(index,data ) {
                msg='<row  r="'+index+'">'
                for(i=0;i<data.length;i++){
                    var key=data[i].k;
                    var value=data[i].v;
                    msg += '<c   t="inlineStr" r="' + key + index + '" s="2">';
                    msg += '<is>';
                    msg +=  '<t >'+value+'</t>';
                    msg+=  '</is>';
                    msg+='</c>';
                }
                msg += '</row>';
                return msg;
            }
            // let visiables = [];
            let headers = [];
            currentColumn = 'A'
            currentColumnHeaders = 'A'
            rows = ' ';

            rows += Addrow(1, [
                { k:'A', v:companyName }
            ]);

            sheet.childNodes[0].childNodes[1].innerHTML = rows + sheet.childNodes[0].childNodes[1].innerHTML;

        }
    </script>
    <script>
        am4core.ready(function() {

            // Themes begin
            am4core.useTheme(am4themes_dataviz);
            am4core.useTheme(am4themes_animated);
            // Themes end

            // Create chart instance
            var chart = am4core.create("chartdiv", am4charts.XYChart);
            chart.fontSize = 9;

            // Add data
            chart.data = $('#accumelated_net_cash_flow_chart').data('total');

            // Set input format for the dates
            chart.dateFormatter.inputDateFormat = "yyyy-MM-dd";


            // Create axes
            var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
            var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

            // Create series
            var series = chart.series.push(new am4charts.LineSeries());
            series.dataFields.valueY = "price";
            series.dataFields.dateX = "date";
            series.tooltipText = "{price}"
            series.strokeWidth = 2;
            series.minBulletDistance = 5;

            // Drop-shaped tooltips
            series.tooltip.background.cornerRadius = 20;
            series.tooltip.background.strokeOpacity = 0;
            series.tooltip.pointerOrientation = "vertical";
            series.tooltip.label.minWidth = 40;
            series.tooltip.label.minHeight = 40;
            series.tooltip.label.textAlign = "middle";
            series.tooltip.label.textValign = "middle";

            // Make bullets grow on hover
            var bullet = series.bullets.push(new am4charts.CircleBullet());
            bullet.circle.strokeWidth = 2;
            bullet.circle.radius = 4;
            bullet.circle.fill = am4core.color("#fff");

            var bullethover = bullet.states.create("hover");
            bullethover.properties.scale = 1.3;

            // Make a panning cursor
            chart.cursor = new am4charts.XYCursor();
            chart.cursor.behavior = "panXY";
            chart.cursor.xAxis = dateAxis;
            chart.cursor.snapToSeries = series;
            valueAxis.cursorTooltipEnabled = false;

            // Create vertical scrollbar and place it before the value axis
            // chart.scrollbarY = new am4core.Scrollbar();
            // chart.scrollbarY.parent = chart.leftAxesContainer;
            // chart.scrollbarY.toBack();

            // Create a horizontal scrollbar with previe and place it underneath the date axis
            chart.scrollbarX = new am4charts.XYChartScrollbar();
            chart.scrollbarX.series.push(series);
            chart.scrollbarX.parent = chart.bottomAxesContainer;

            dateAxis.start = 0.01;
            dateAxis.keepSelection = true;


        }); // end am4core.ready()
    </script>
    <script>
        am4core.ready(function() {

            // Themes begin
            am4core.useTheme(am4themes_animated);
            // Themes end

            // Create chart instance
            var chart = am4core.create("chartdiv1", am4charts.XYChart);

            // Increase contrast by taking evey second color
            chart.colors.step = 2;

            // Add data
            chart.data = $('#multi_line_chart').data('total');
            chart.dateFormatter.inputDateFormat = "yyyy-MM-dd";

            // Create axes
            var dateAxis = chart.xAxes.push(new am4charts.DateAxis());

            dateAxis.renderer.minGridDistance = 50;

            // Create series
            function createAxisAndSeries(field, name, opposite, bullet) {
                var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
                if (chart.yAxes.indexOf(valueAxis) != 0) {
                    valueAxis.syncWithAxis = chart.yAxes.getIndex(0);
                }

                var series = chart.series.push(new am4charts.LineSeries());
                series.dataFields.valueY = field;
                series.dataFields.dateX = "date";
                series.strokeWidth = 2;
                series.yAxis = valueAxis;
                series.name = name;
                series.tooltipText = "{name}: [bold]{valueY}[/]";
                series.tensionX = 0.8;
                series.showOnInit = true;

                var interfaceColors = new am4core.InterfaceColorSet();

                switch (bullet) {
                    case "triangle":
                        var bullet = series.bullets.push(new am4charts.Bullet());
                        bullet.width = 12;
                        bullet.height = 12;
                        bullet.horizontalCenter = "middle";
                        bullet.verticalCenter = "middle";

                        var triangle = bullet.createChild(am4core.Triangle);
                        triangle.stroke = interfaceColors.getFor("background");
                        triangle.strokeWidth = 2;
                        triangle.direction = "top";
                        triangle.width = 12;
                        triangle.height = 12;
                        break;
                    case "rectangle":
                        var bullet = series.bullets.push(new am4charts.Bullet());
                        bullet.width = 10;
                        bullet.height = 10;
                        bullet.horizontalCenter = "middle";
                        bullet.verticalCenter = "middle";

                        var rectangle = bullet.createChild(am4core.Rectangle);
                        rectangle.stroke = interfaceColors.getFor("background");
                        rectangle.strokeWidth = 2;
                        rectangle.width = 10;
                        rectangle.height = 10;
                        break;
                    default:
                        var bullet = series.bullets.push(new am4charts.CircleBullet());
                        bullet.circle.stroke = interfaceColors.getFor("background");
                        bullet.circle.strokeWidth = 2;
                        break;
                }

                valueAxis.renderer.line.strokeOpacity = 1;
                valueAxis.renderer.line.strokeWidth = 2;
                valueAxis.renderer.line.stroke = series.stroke;
                valueAxis.renderer.labels.template.fill = series.stroke;
                valueAxis.renderer.opposite = opposite;
            }
            console.log(chart.data[0]);
            $.each(chart.data[0], function(key, val) {
                if (key != 'date') {
                    createAxisAndSeries(key, key, true, "circle");
                }
            });



            // Add legend
            chart.legend = new am4charts.Legend();

            // Add cursor
            chart.cursor = new am4charts.XYCursor();


        }); // end am4core.ready()
    </script>
@endsection
