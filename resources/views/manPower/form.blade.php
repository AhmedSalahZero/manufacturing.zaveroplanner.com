@extends('layouts.app')
@section('content')

<?php $manPower = isset($manPower) ? $manPower : old(); ?>

<div class="container-main-width ">
    <h1 class="d-flex justify-content-between steps-span">
        <span><a href="{{ url()->previous() }}" style="color: white">
                < {{ __('Back') }}</a></span>
        <span>{{ __('Step ') . $step_data['place_num'] . '/' . $step_data['count'] }}</span>
    </h1>
    <h1 class="bread-crumbs">
        {{ __('ZAVERO Manufacturing') }} > {{ $project->name }} > {{ __($step_data['route_name']) }}
    </h1>
    <Div class="ProjectList">
        <form method="POST" action="{{ route('manPower.submit', $project) }}">
            @csrf
            {{-- Salaries Plan --}}
            @if ($project->duration !== 1)
            <div class="div-title">
                {{ __('Salaries Plan') }}
            </div>
            {{-- <div class="projectItem">
                {{ __('Salaries Plan') }}
    </div> --}}

    <div class="formItem ">
        <div class="row ml-1 mr-1">
            <div class="col-3">
                <div class="form-group">
                    <label>{{ __('Salaries Annual Increase %') }}</label>
                    <input type="number" step="any" class="form-control growth_percentage_in_diff_parent only-greater-than-or-equal-zero-allowed" name="salaries_annual_increase" value="{{ $project->getSalaryIncreaseRate() }}" id="salaries_annual_increase">
                </div>



            </div>
            <div class="col-3">
                <div class="form-group">
                    <label>{{ __('Salaries Tax Rate %') }}</label>
                    <input type="number" step="any" class="form-control only-greater-than-or-equal-zero-allowed" name="salaries_tax_rate" value="{{ $project->getSalaryTaxRate() }}">
                </div>

            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>{{ __('Social Insurance Rate %') }}</label>
                    <input type="number" step="any" class="form-control only-greater-than-or-equal-zero-allowed" name="social_insurance_rate" value="{{ $project->getSocialInsuranceRate() }}">
                </div>
            </div>
        </div>



    </div>
    @else
    {{-- <input type="hidden" value="0" name="salaries_annual_increase" id="salaries_annual_increase"> --}}
    @endif
    @php
    $index = 0 ;
    @endphp
    {{-- Salaries Expenses --}}
    @foreach ( getManpowerTypes() as $id => $manpowerOptionArr)
    <div class="div-title">
        {{$manpowerOptionArr['title']}}
    </div>

    <div class="formItem repeater{{ $id }}">

        <div data-repeater-list="{{ $id }}">
            @foreach(count($manpowers->where('type',$id)) ? $manpowers->where('type',$id) : [null] as $currentRowIndex=>$currentManpower)
            <div data-repeater-item class="container parent-for-salary-amount">
                <input type="hidden" name="id" value="{{ $currentManpower ? $currentManpower->id : 0 }}">
                <div class="row closest-parent pb-2  col-12">
                    <div class="col-3">
                        <label>{{ __('Position') }}</label>
                        <input type="text" name="position" class="form-control" value="{{ $currentManpower ? $currentManpower->getPositionName() : '' }}">
                    </div>

                    <div class="col-2">
                        <label>{{ __('Avg. Salary') }}</label>
                        <input type="text" name="avg_salary" class="form-control number_growth_amount_in_diff_parent only-greater-than-or-equal-zero-allowed" value="{{ $currentManpower ? $currentManpower->getAvgSalary() : 0 }}">
                    </div>

                    <div class="col-2">
                        <label>{{ __('Existing Count') }}</label>
                        <input type="text" name="existing_count" class="form-control only-greater-than-or-equal-zero-allowed" value="{{ $currentManpower ? $currentManpower->getExistingCount() : 0 }}">
                    </div>

                    <div class="col-3 common-parent">
                        <label class="visible-hidden">{{ __('Hirings') }}</label>
                        <div>
                            <button class="btn btn-primary btn-md text-nowrap " type="button" data-toggle="modal" data-target="#modal-{{ $id }}-{{ $currentRowIndex }}">{{ __('New Hirings') }}</button>
                            <input data-repeater-delete type="button" class="btn btn-danger btn-md ml-2" value="{{ __('Delete') }}">
                        </div>
                    </div>
                </div>

                <!-- Modal for New Hirings -->
                <div class="modal fade" id="modal-{{ $id }}-{{ $currentRowIndex }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel-{{ $id }}-{{ $currentRowIndex }}" aria-hidden="true">
                    <div class="modal-dialog modal-full" role="document">
                        <div class="modal-content">
                            <div class="modal-header header-border">
                                <h5 class="modal-title font-size-1rem text-blue" id="modalLabel-{{ $id }}">{{ __('New Hiring For [Position]') }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <table>
                                    <tbody>
                                        @php
                                        $startIndex = 1 ;
                                        $yearIndexWithItsMonthsAsIndexAndString = $project->getYearIndexWithItsMonthsAsIndexAndString();
                                        @endphp
                                        @foreach($yearIndexWithItsMonthsAsIndexAndString as $yearIndex => $itsMonths)
                                        <tr>
                                            @foreach($itsMonths as $dateAsIndex => $dateAsString )
                                            @php $dateFormatted=\Carbon\Carbon::make($dateAsString)->format('M`Y');
                                            @endphp
                                            <td>
                                                <div class="form-group text-center">
                                                    <label>{{ $dateFormatted }}</label>
                                                    <div class="ml-2">
                                                        <input class="form-control input-border" data-main-category="{{ $id }}" data-sub-category="hirings" data-last-index="{{ $dateAsIndex }}" name="[hirings]" multiple value="{{ $currentManpower ? $currentManpower->getHiringAtDate($dateAsIndex):0 }}">
                                                    </div>
                                                </div>
                                            </td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-dismiss="modal">{{ __('Save') }}</button>
                                {{-- <button type="button" class="btn btn-primary">{{ __('Save changes') }}</button> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="ml-5 mt-4 d-flex justify-content-between" style="width:94%">
            <input data-repeater-create type="button" class="btn btn-success btn-sm " value="{{ __('Add Position') }}">
            @if($manpowerOptionArr['has_allocation'])
            <button class="btn bg-green btn-md text-nowrap " type="button" data-toggle="modal" data-target="#modal-allocate-{{ $id }}">{{ __('Allocate On Products') }}</button>
            <div class="modal fade" id="modal-allocate-{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel-{{ $id }}" aria-hidden="true">
                <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header header-border">
                            <h5 class="modal-title font-size-1rem" id="modalLabel-{{ $id }}">{{ __('Allocate') }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <table class="table w-full closest-parent">
                                <tbody>
                                    @php
                                    $allocations = $project->getManpowerAllocationForType($id) ;
                                    @endphp
                                    @foreach($products as $product)
                                    @php
                                    $percentage = $allocations[$product->id]??(100/count($products));
                                    @endphp
                                    <tr>

                                        <td>
                                            <div class="form-group d-flex  dep-parent text-center">

                                                <div class="col-9 text-left">
                                                    <label>{{ __('Product') }}</label>
                                                    <input readonly class="form-control" name="manpower_allocations[{{ $id }}][products][{{ $product->id }}]" type="text" value="{{ $product->getName() }}">
                                                    <input multiple class="form-control " name="product_id" type="hidden" value="{{ $product->id }}">


                                                </div>
                                                <div class="col-3 text-left">
                                                    <label>{{ __('Perc.%') }}</label>
                                                    <input class="form-control total_input input-border" name="manpower_allocations[{{ $id }}][percentages][{{ $product->id }}]" value="{{ $percentage }}">
                                                </div>


                                            </div>
                                        </td>

                                    </tr>
                                    @endforeach

                                    <tr>

                                        <td>
                                            <div class="form-group d-flex   text-center">

                                                <div class="col-9 text-left">
                                                    <label>{{ __('Total') }}</label>
                                                    <input readonly class="form-control" type="text" value="{{ __('Total') }}">
                                                </div>
                                                <div class="col-3	 text-left">
                                                    <label>{{ __('Total %') }}</label>
                                                    <input readonly class="form-control must-not-exceed-100 total_row_result input-border" value="{{ 0 }}">
                                                </div>


                                            </div>
                                        </td>

                                    </tr>


                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal">{{ __('Save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    @endforeach
    <button type="submit" class="btn btn-rev float-right" name="submit_button" value="next">{{ __('Next') }}</button>
    <button type="submit" class="btn btn-rev float-right main-page-button" name="submit_button" value="save">{{ __('Save & Go To Main Page') }}</button>
    </form>
</Div>
</div>
@endsection
@section('js')




<script src="https://cdn.jsdelivr.net/npm/jquery.repeater@1.2.1/jquery.repeater.min.js"></script>
@foreach ( getManpowerTypes() as $id => $manpowerOptionArr)
<script>
    // Define translated strings for JavaScript
    var translations = {
        deleteConfirm: @json(__('Are you sure you want to delete this position?'))
    };
    /**
     * * direct_labor[1][hirings][] -> to -> direct_labor[1][hirings][2030-05-01] 
     */
    

    $(document).ready(function() {
        var selector = ".repeater{{ $id }}";
        $(selector).repeater({
            initEmpty: false
            , defaultValues: {
                'position': ''
                , 'avg_salary': '0'
                , 'existing_count': '0'
            }
            , show: function() {
                $(this).slideDown();
                // Update modal IDs to ensure uniqueness
                const $item = $(this);
                const index = $item.index();
                const modalId = `modal-${index}-${Date.now()}`;
                $item.find('.modal').attr('id', modalId);
                $item.find('[data-toggle="modal"]').attr('data-target', `#${modalId}`);
                $item.find('.modal').find('.modal-title').attr('id', `modalLabel-${index}-${Date.now()}`);
                replaceRepeaterIndex(this)
            }
            , ready: function(setIndexes) {
                $(selector + " [data-repeater-item]").each(function(index, element) {
                    replaceRepeaterIndex(element)
                })
            }
            , hide: function(deleteElement) {
                if (confirm(translations.deleteConfirm)) {

                    $(this).slideUp(deleteElement);
                    setTimeout(function() {
                        $(selector + " [data-repeater-item]").each(function(index, element) {
                            replaceRepeaterIndex(element)
                        })
                    }, 1000)

                }

            }
            , isFirstItemUndeletable: true
        });
    });

</script>
@endforeach

@endsection
