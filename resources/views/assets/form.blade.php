@extends('layouts.app')
@section('content')

    <div class="col-10 offset-1">
        {{-- <a href="{{ url()->previous() }}"  class="btn btn-danger">< {{__("Back")}}</a>
        <a class="btn step">{{  __('Step ') . $step_data['place_num'] .'/'. $step_data['count'] }} </a>
        <h1 class="text-left info-bar text-center">
            <span > {{ __("ZAVERO Manufacturing") }}    >     {{$project->name}}  >  {{ __($step_data['route_name'])}} </span>
        </h1> --}}
        <h1 class="d-flex justify-content-between steps-span">
            <span><a href="{{ url()->previous() }}" style="color: white">< {{__("Back")}}</a></span>
            <span>{{  __('Step ') . $step_data['place_num'] .'/'. $step_data['count'] }}</span>
        </h1>
        <h1  class="bread-crumbs" >
                {{ __("ZAVERO Manufacturing") }}    >     {{$project->name}}  >  {{ __($step_data['route_name'])}}
        </h1>
        <Div class="ProjectList">
            <form  action="{{route('assets.submit',$project)}}" method="POST">
                {{ csrf_field() }}
                {{-- One --}}
                    <div class="projectItem" >
                        {{ __('Fixed Assets One')  }}
                    </div>
                    <div class="formItem">
                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label>{{__('Fixed Assets Value')}}</label>
                                <input type="number" step="any" class="form-control @error('fixed_assets_value') is-invalid @enderror" placeholder="{{__('Fixed Assets Value')}}" name="fixed_assets_value" value="{{isset($assets->fixed_assets_value) ? $assets->fixed_assets_value :old('fixed_assets_value')}}" id="fixed_assets_value">
                                @error('fixed_assets_value')
                                    <div class="alert alert-danger" role="alert">
                                        {{$message}}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label>{{__('Life Duration (Months)')}}  </label>
                                <input type="number" step="any" class="form-control" name="life_duration_months" value="{{isset($assets->life_duration_months) ? $assets->life_duration_months :old('life_duration_months')}}" id="life_duration_months" placeholder="{{__('Life Duration (Months)')}}" min="1">
                            </div>
                        </div>

                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label>{{__('Monthly Depreciation')}} </label>
                                <input type="text" step="any" class="form-control" name="monthly_deprication" value="{{isset($assets->monthly_deprication) ? $assets->monthly_deprication  :old('monthly_deprication')}}" id="monthly_deprication" placeholder="{{__('Monthly Depreciation')}}" readonly>
                            </div>
                        </div>

                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label for="date_of_addition_one">{{__("Year Of Addition")}}</label>
                                <select name="date_of_addition_one" id="date_of_addition_one" class="form-control @error('date_of_addition_one') is-invalid @enderror"  >

                                    @foreach($years as $count => $year)
                                        <?php $date_of_addition_one = isset($assets->date_of_addition_one)  ? @$assets->date_of_addition_one :old('date_of_addition_one'); ?>
                                        <option value="{{$count}}" {{ $date_of_addition_one == $count ? 'selected' : ''}}>{{$year}}</option>
                                    @endforeach
                                </select>
                                {{-- @error($year_field_per_product)
                                    <div class="alert alert-danger" role="alert">
                                        {{$message}}
                                    </div>
                                @enderror --}}
                            </div>
                        </div>
                    </div>

                    <div class="projectItem" >
                        {{ __('Fixed Assets One Payment')  }}
                    </div>
                    <div class="formItem">
                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label>{{__('Down Payment %')}}</label>
                                <input type="number" step="any" class="form-control @error('down_payment') is-invalid @enderror" placeholder="{{__('Down Payment %')}}" name="down_payment" value="{{isset($assets->down_payment) ? $assets->down_payment :old('down_payment')}}" id="down_payment" min="0" max="100" step="0.01">
                                @error('down_payment')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{$message}}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label>{{__('Balance Rate %')}} </label>
                                <input type="number" step="any" class="form-control" name="balance_rate" value="{{isset($assets->balance_rate) ? $assets->balance_rate :old('balance_rate')}}" id="balance_rate" placeholder="{{__('Balance Rate %')}}" readonly>
                            </div>
                        </div>
                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label>{{__('Installment Count')}} </label>
                                <input type="number" step="any" class="form-control" name="installment_count" value="{{isset($assets->installment_count) ? $assets->installment_count :old('installment_count')}}" id="installment_count" placeholder="{{__('Installment Count')}}">
                            </div>
                        </div>
                        <?php $balance_rate_period = isset($assets->balance_rate_period) ? $assets->balance_rate_period :old('balance_rate_period');?>
                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label>{{__('Installment Interval')}} </label>
                                <select name="balance_rate_period" id="balance_rate_period" class="form-control">
                                    <option selected disabled>{{__('Select')}}</option>
                                    <option value="monthly"  {{ $balance_rate_period  =='monthly' ? 'selected' : ''}}>{{__('Monthly')}}</option>
                                    <option value="quarterly" {{ $balance_rate_period  =='quarterly' ? 'selected' : ''}}>{{__('Quarterly')}}</option>

                                </select>
                            </div>
                        </div>
                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label>{{__('Interest Rate %')}} </label>
                                <input type="number" step="any" class="form-control" name="interest_rate" value="{{isset($assets->interest_rate) ? $assets->interest_rate :old('interest_rate')}}" id="interest_rate" placeholder="{{__('Interest Rate %')}}" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                {{-- Two --}}
                    <div class="projectItem" >
                        {{ __('Fixed Assets Two')  }}
                    </div>
                    <div class="formItem">
                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label>{{__('Fixed Assets Value')}}</label>
                                <input type="number" step="any" class="form-control @error('fixed_assets_value_two') is-invalid @enderror" placeholder="{{__('Fixed Assets Value')}}" name="fixed_assets_value_two" value="{{isset($assets->fixed_assets_value_two) ? $assets->fixed_assets_value_two :old('fixed_assets_value_two')}}" id="fixed_assets_value_two">
                                @error('fixed_assets_value_two')
                                    <div class="alert alert-danger" role="alert">
                                        {{$message}}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label>{{__('Life Duration (Months)')}}  </label>
                                <input type="number" step="any" class="form-control" name="life_duration_months_two" value="{{isset($assets->life_duration_months_two) ? $assets->life_duration_months_two :old('life_duration_months_two')}}" id="life_duration_months_two" placeholder="{{__('Life Duration (Months)')}}" min="1">
                            </div>
                        </div>

                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label>{{__('Monthly Depreciation')}} </label>
                                <input type="text" step="any" class="form-control" name="monthly_deprication_two" value="{{isset($assets->monthly_deprication_two) ? $assets->monthly_deprication_two  :old('monthly_deprication_two')}}" id="monthly_deprication_two" placeholder="{{__('Monthly Depreciation')}}" readonly>
                            </div>
                        </div>
                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label for="date_of_addition_two">{{__("Year Of Addition")}}</label>
                                <select name="date_of_addition_two" id="date_of_addition_two" class="form-control @error('date_of_addition_two') is-invalid @enderror"  >

                                    @foreach($years as $count => $year)
                                        <?php $date_of_addition_two =isset($assets->date_of_addition_two) ? @$assets->date_of_addition_two :old('date_of_addition_two'); ?>
                                        <option value="{{$count}}" {{ $date_of_addition_two == $count ? 'selected' : ''}}>{{$year}}</option>
                                    @endforeach
                                </select>
                                {{-- @error($year_field_per_product)
                                    <div class="alert alert-danger" role="alert">
                                        {{$message}}
                                    </div>
                                @enderror --}}
                            </div>
                        </div>
                    </div>

                    <div class="projectItem" >
                        {{ __('Fixed Assets Two Payment')  }}
                    </div>
                    <div class="formItem">
                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label>{{__('Down Payment %')}}</label>
                                <input type="number" step="any" class="form-control @error('down_payment_two') is-invalid @enderror" placeholder="{{__('Down Payment %')}}" name="down_payment_two" value="{{isset($assets->down_payment_two) ? $assets->down_payment_two :old('down_payment_two')}}" id="down_payment_two" min="0" max="100" step="0.01">
                                @error('down_payment_two')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{$message}}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label>{{__('Balance Rate %')}} </label>
                                <input type="number" step="any" class="form-control" name="balance_rate_two" value="{{isset($assets->balance_rate_two) ? $assets->balance_rate_two :old('balance_rate_two')}}" id="balance_rate_two" placeholder="{{__('Balance Rate %')}}" readonly>
                            </div>
                        </div>
                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label>{{__('Installment Count')}} </label>
                                <input type="number" step="any" class="form-control" name="installment_count_two" value="{{isset($assets->installment_count_two) ? $assets->installment_count_two :old('installment_count_two')}}" id="installment_count_two" placeholder="{{__('Installment Count')}}">
                            </div>
                        </div>
                        <?php $balance_rate_period_two = isset($assets->balance_rate_period_two) ? $assets->balance_rate_period_two :old('balance_rate_period_two');?>
                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label>{{__('Installment Interval')}} </label>
                                <select name="balance_rate_period_two" id="balance_rate_period_two" class="form-control">
                                    <option selected disabled>{{__('Select')}}</option>
                                    <option value="monthly"  {{ $balance_rate_period_two  =='monthly' ? 'selected' : ''}}>{{__('Monthly')}}</option>
                                    <option value="quarterly" {{ $balance_rate_period_two  =='quarterly' ? 'selected' : ''}}>{{__('Quarterly')}}</option>

                                </select>
                            </div>
                        </div>
                        <div class="offset-1 col-10">
                            <div class="form-group">
                                <label>{{__('Interest Rate %')}} </label>
                                <input type="number" step="any" class="form-control" name="interest_rate_two" value="{{isset($assets->interest_rate_two) ? $assets->interest_rate_two :old('interest_rate_two')}}" id="interest_rate_two" placeholder="{{__('Interest Rate %')}}" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                <button type="submit" class="btn btn-rev float-right"  name="submit_button" value="next">{{__('Next')}}</button>
                <button type="submit" class="btn btn-rev float-right main-page-button" name="submit_button" value="save">{{__('Save & Go To Main Page')}}</button>

            </form>
        </Div>
    </div>
    <div class="clearfix"></div>
@endsection

@section('js')
    <script>
        $('#fixed_assets_value,#life_duration_months').on('change', function () {
            var fixed_assets_value =  $('#fixed_assets_value').val();
            var life_duration_months = $('#life_duration_months').val();
            result = 0;
            if (fixed_assets_value != '' && life_duration_months !=0 && life_duration_months != '') {
                var result = fixed_assets_value/life_duration_months;
            }
            $('#monthly_deprication').val(result.toFixed(0));
        });

        $('#down_payment').on('change', function () {
            var down_payment =  $('#down_payment').val();
            var result = 100-down_payment;
            $('#balance_rate').val(result);
        });
        $('#fixed_assets_value_two,#life_duration_months_two').on('change', function () {
            var fixed_assets_value_two =  $('#fixed_assets_value_two').val();
            var life_duration_months_two = $('#life_duration_months_two').val();
            result = 0;
            if (fixed_assets_value_two != '' && life_duration_months_two !=0 && life_duration_months_two != '') {
                var result = fixed_assets_value_two/life_duration_months_two;
            }
            $('#monthly_deprication_two').val(result.toFixed(0));
        });

        $('#down_payment_two').on('change', function () {
            var down_payment_two =  $('#down_payment_two').val();
            var result = 100-down_payment_two;
            $('#balance_rate_two').val(result);
        });

    </script>
@endsection
