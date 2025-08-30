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
                    <th colspan="2" class="share_header"> {{ __('Salaries Plan') }}</th>
                </tr>
                <tr class="tr-color">
                    <th class="tr-color">{{ __('Salaries Annual Increase %') }}</th>
                    <td class="h5 text-dark td-style">{{ $manPower->salaries_annual_increase ?? '-' }} %</td>
                </tr>
                {{-- Salaries Expenses --}}
                @foreach ($expenses_array as $expense_name => $field_name)
                    <tr>
                        <th colspan="2" class="share_header"> {{ __(ucwords(str_replace('_', ' ', $expense_name))) }}</th>
                    </tr>

                    @foreach ($years as $num => $data)
                        <?php $count_name = array_key_first($data);
                        $year = $data[$count_name]; ?>
                        <?php $year_format = date('y', strtotime('01-01-' . $year)); ?>
                        @if ($count_name != 'first')
                            <?php $rate_name = $field_name . '_' . $count_name . '_rate'; ?>
                            <tr class="tr-color">
                                <th class="tr-color">{{ __('Capacity Increase Rate % Yr-') . $year_format }}</th>
                                <td class="h5 text-dark td-style">{{ $manPower->$rate_name ?? '-' }} %</td>
                            </tr>
                        @endif

                        @if ($count_name == 'first')
                            <tr>
                                <th class="tr-color" colspan="2">{{ __('Monthly Salaries Year ') . $year_format }}</th>
                                {{-- <td class="h5 text-dark td-style"> --}}
                                </td>

                            </tr>
                            <?php $quarters = ['one', 'two', 'three', 'four']; ?>
                            @foreach ($quarters as $qr_count)
                                <?php $capacity_name = $field_name . '_' . $count_name . '_capacity'; ?>
                                <tr class="tr-color">

                                    <th class="tr-color">
                                        {{ __('Quarter ' . ucwords($qr_count)) }}{{ __('Quarter ' . ucwords($qr_count)) }}
                                    </th>
                                    <td class="h5 text-dark td-style">
                                        {{ number_format($manPower->$capacity_name[$qr_count] ?? null, 0) ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                            @if (@count($years) > 1)
                                <tr>
                                    <td></td>
                                </tr>
                                <tr>
                                    <?php
                                    $key = array_key_first($years[$num + 1]);
                                    $last_key = array_key_last($years);
                                    $key_name = array_key_first($years[$last_key]);

                                    ?>
                                    <th class="tr-color" colspan="2">
                                        {{ __('Monthly Salaries from Year ') . $years[$num + 1][$key] . ' To ' . $years[$last_key][$key_name] }}
                                    </th>

                                </tr>
                            @endif
                        @else
                            <?php $capacity_name = $field_name . '_' . $count_name . '_capacity'; ?>
                            <tr class="tr-color">
                                <th class="tr-color">{{ __('Monthly Salaries Year ') . $year_format }}</th>
                                <td class="h5 text-dark td-style">{{ number_format($manPower->$capacity_name, 0) ?? '-' }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
