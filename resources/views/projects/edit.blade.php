@extends('layouts.app')
@section('content')
<style>

</style>
<div class="container-main-width">
    <h1 class="d-flex justify-content-between steps-span">
        <span><a href="{{ url()->previous() }}" style="color: white">
                < {{__("Back")}}</a></span>
        <span>{{ __('Step ') . $step_data['place_num'] .'/'. $step_data['count'] }}</span>
    </h1>
    <h1 class="bread-crumbs">
        {{ __("ZAVERO Manufacturing") }} > {{$project->name}} > {{ __($step_data['route_name'])}}
    </h1>
    <Div class="ProjectList">
        <form method="POST" action="{{ route('projects.update' , $project) }}">



            @csrf
            @method('put')

            <div class="div-title">
                {{ __('Study Info') }}
            </div>

            <div class="formItem">
                <div class="row ml-1">
                    <div class="col-5">
                        <div class="form-group">
                            <label>{{__('Study Name')}} </label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror  " required name="name" value="{{isset($project->name) ? $project->name  :old('name') }}" placeholder="{{__('Second Service')}}">
                            <span>{{ @$errors->first('name') }}</span>
                        </div>
                    </div>

                    <div class="col-5">
                        <label>{{ __('Company Type') }}</label>
                        <div class="form-group">

                            <?php $new_company = isset($project->new_company) ? $project->new_company : old('new_company') ;?>
                            <div class="form-check form-check-inline col-5">
                                <input class="form-check-input" name="new_company" type="radio" name="inlineRadioOptions" id="inlineRadio1" {{$new_company == 1 ? "checked" : "" }} value="1">
                                <label class="form-check-label" for="inlineRadio1">{{__("New Company")}}</label>
                            </div>
                            <div class="form-check form-check-inline col-5">
                                <input class="form-check-input" name="new_company" type="radio" name="inlineRadioOptions" id="inlineRadio2" {{$new_company == 0 ? "checked" : "" }} value="0">
                                <label class="form-check-label" for="inlineRadio2">{{__("Existing Company")}}</label>
                            </div>

                        </div>
                        <div class="msg-alert  {{$new_company == 1 ? '' : 'hidden'}} ">
                            <span class="red">{{"* ".__('Backlog & Opening Balances Will Be Deleted Upon Saving Or Clicking Next')}}</span>
                        </div>
                    </div>
                </div>

            </div>



            <div class="div-title">
                {{$project->name ." [ ".__("General Info") . ' ]'}}
            </div>
            <div class="formItem">
                <div class="row ml-1 mr-1">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="month">{{__("Study Start Date")}}
                                <span class="red">*</span></label>
                            </label>
                            @include('components.calendar-month-year',[
                            'name'=>'start_date',
                            'value'=>$project->getStudyStartDateYearAndMonth()
                            ])
                        </div>
                    </div>

                    <div class="col-md-3">

                        @php
                        $duration_data = isset($project->duration) ? $project->duration :old('duration');
                        $start_date = isset($project->start_date) ? $project->start_date :old('start_date');
                        $max_length = 7;
                        @endphp
                        <div class="form-group">
                            <label>{{__('Duration In Years')}}</label><span class="red"> *</span>
                            <select name="duration" id="duration" class="form-control @error('duration') is-invalid @enderror">
                                <option value="">{{__('Select')}}</option>
                                @for ($value =1 ; $value <= $max_length ; $value++) <option value="{{$value}}" {{$duration_data == $value ?  'selected' : ''}}>{{$value}}</option>
                                    @endfor
                            </select>
                            @error('duration')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{$message}}</strong>
                            </span>
                            @enderror
                        </div>

                    </div>


                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{__('Study End Date')}}</label>
                            <input type="Text" placeholder="mm/yyyy" readonly class="form-control" id="end_date">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="month">{{__("Operation Start Date")}}
                                <span class="red">*</span></label>
                            </label>
                            @include('components.calendar-month-year',[
                            'name'=>'operation_start_date',
                            'value'=>$project->getOperationStartDateYearAndMonth()
                            ])
                        </div>
                    </div>



                    <input type="hidden" name="end_date" id="full_end_date" value="{{isset($project->end_date) ? $project->end_date :old('end_date')}}" />
                    <input type="hidden" id="start_date" value="{{$start_date }}">
                    <input type="hidden" id="selling_start_date" value="{{isset($project->selling_start_date) ? $project->selling_start_date :old('selling_start_date')}}">

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{__('Business Sector')}}</label>
                            <select name="business_sector_id" id="business_sector_id" readonly class="form-control @error('business_sector_id') is-invalid @enderror  ">
                                {{-- <option value="">{{__('Select')}}</option> --}}
                                <?php $business_sector_id = isset($project->business_sector_id) ? $project->business_sector_id :old('business_sector_id') ?>
                                @foreach($sectors as $key => $sector)
                                <?php $name =  "name_".app()->getLocale();?>
                                <option value="{{ $sector->id }}" selected>{{ $sector->$name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{__('Corporate Tax Rate %')}} <span class="red"> *</span></label>
                            <input type="number" step="any" min="0" class="form-control @error('tax_rate') is-invalid @enderror  " name="tax_rate" value="{{isset($project->tax_rate) ? $project->tax_rate  :old('tax_rate') }}" placeholder="{{__('Corporate Tax Rate %')}}">
                            @error('tax_rate')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{$message}}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label> {{__('Required Investment Return %')}} </label>
                            <input type="number" step="any" min="0" class="form-control @error('return_rate') is-invalid @enderror  " name="return_rate" value="{{isset($project->return_rate) ? $project->return_rate  :old('return_rate') }}" placeholder="{{__('Required Investment Return %')}}" id="return_rate">
                        </div>
                    </div>
                    <div class="col-md-3">

                        <div class="form-group">
                            <label>{{__('Perpetual Growth Rate %')}} </label>
                            <?php $perpetual_growth_rate = isset($project->perpetual_growth_rate) ? $project->perpetual_growth_rate  :old('perpetual_growth_rate')  ?>
                            <input type="number" title="{{__('Recommended Between 2.5% & 5%')}}" max="10" step="any" min="0" id="perpetual_growth_rate" class="form-control @error('perpetual_growth_rate') is-invalid @enderror  " name="perpetual_growth_rate" value="{{$perpetual_growth_rate}}" placeholder="{{__('Perpetual Growth Rate %')}}" {{isset($duration_data) && $duration_data < 3 ? 'readonly': ''}}>
                        </div>

                    </div>
                </div>





            </div>

            <!--  Start Products -->


            <div class="row ">
                <div class="col-12 ">
                    <div class="div-title">
                        {{ __('Product Names') }}
                        [
                        <span class="color-green font-14">{{__('You must fill at least one field')}}</span>
                        ]
                    </div>
                </div>
                <div class="col-12 ">

                    @error('project_product_validation')
                    <div class="alert alert-danger" role="alert">
                        {{$message}}
                    </div>
                    @enderror
                </div>
            </div>
            @php
            $currentRepeaterId = 'products';
            @endphp

            <div class="formItem repeater_products">
                <div data-repeater-list="{{ $currentRepeaterId }}">
                    @foreach(count($products) ? $products : [null] as $product)
                    <div data-repeater-item>
                        <input type="hidden" name="id" value="{{ $product ? $product->id : 0 }}">
                        <div class="row ml-1 mr-1">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label> {{ __('Name') }} </label>
                                    @php
                                    $name = "name";
                                    $nameToOld = generateOldNameFromFieldName($name) ;
                                    @endphp
                                    <input type="text" class="form-control @error($nameToOld) is-invalid @enderror  " name="{{ $name }}" value="{{ old($nameToOld) ?: (isset($product) ? $product->getName() : '' ) }}">
                                    <span>{{ @$errors->first($nameToOld) }}</span>
                                </div>
                            </div>


                            <div class="col-md-2">
                                <div class="form-group">
                                    @php
                                    $currentFieldName = "measurement_unit";
                                    $nameToOld = generateOldNameFromFieldName($currentFieldName) ;
                                    $currentFieldId = 'measurement-unit-id';
                                    @endphp
                                    <label for="{{ $currentFieldId }}">{{__("Measurement Units")}}
                                        @include('required')
                                    </label>

                                    <select name="{{ $currentFieldName }}" id="{{ $currentFieldId }}" class="form-control @error($nameToOld) is-invalid @enderror">
                                        <option value="">{{__('Select')}}</option>
                                        @foreach(getMeasurementUnits() as $unitId => $unitName)
                                        @php
                                        $currentValue = old($nameToOld) ?: (isset($product) ? $product->getMeasurementUnit():1);
                                        @endphp
                                        <option value="{{ $unitId }}" {{ $unitId  ==$currentValue ? 'selected' : ''}}>{{__($unitName)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="month">{{__("Selling Start Date")}}
                                        @include('required')
                                    </label>
                                    @include('components.calendar-month-year',[
                                    'name'=>'selling_start_date',
                                    'value'=>$product ? $product->getSellingStartDateYearAndMonth() : now()->format('Y-m')
                                    ])
                                </div>
                            </div>
							
							
							 <div class="col-md-2">
                                <div class="form-group">
                                    <label> {{ __('Vat Rate %') }} </label>
                                    @php
                                    $name = "vat_rate";
                                    $nameToOld = generateOldNameFromFieldName($name) ;
                                    @endphp
                                    <input type="text" class="form-control @error($nameToOld) is-invalid @enderror  " name="{{ $name }}" value="{{ old($nameToOld) ?: (isset($product) ? $product->getVatRate() : 0 ) }}">
                                    <span>{{ @$errors->first($nameToOld) }}</span>
                                </div>
                            </div>
							
							<div class="col-md-2">
                                <div class="form-group">
                                    <label> {{ __('Withhold Rate %') }} </label>
                                    @php
                                    $name = "withhold_tax_rate";
                                    $nameToOld = generateOldNameFromFieldName($name) ;
                                    @endphp
                                    <input type="text" class="form-control @error($nameToOld) is-invalid @enderror  " name="{{ $name }}" value="{{ old($nameToOld) ?: (isset($product) ? $product->getWithholdTaxRate() : 0 ) }}">
                                    <span>{{ @$errors->first($nameToOld) }}</span>
                                </div>
                            </div>
							
							




                            <div class="col-md-1">
                                <div class="form-group">
                                    <div class="d-flex flex-column">
                                        <label class="visibility-hidden "> Delete </label>
                                        <input data-repeater-delete type="button" class="btn btn-danger btn-md ml-2" value="{{ __('Delete') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>
                <div class="ml-4">
                    <input data-repeater-create type="button" class="btn btn-success btn-sm " value="{{ __('Add Product') }}">
                </div>

            </div>

            <!--  Start Products -->



            <!--  Start Raw Materials -->


            <div class="row ">
                <div class="col-12 ">
                    <div class="div-title">
                        {{ __('Raw Materials Names') }}
                        [
                        <span class="color-green font-14">{{__('You must fill at least one field')}}</span>
                        ]
                    </div>
                </div>
                <div class="col-12 ">

                    @error('project_product_validation')
                    <div class="alert alert-danger" role="alert">
                        {{$message}}
                    </div>
                    @enderror
                </div>
            </div>
            @php
            $currentRepeaterId = 'rawMaterials';
            @endphp
            <div class="formItem repeater_rawMaterials">
                <div data-repeater-list="{{ $currentRepeaterId }}">
                    @foreach(count($rawMaterials) ? $rawMaterials : [null] as $rawMaterial)
                    <div data-repeater-item>
                        <input type="hidden" name="id" value="{{ $rawMaterial ? $rawMaterial->id : 0 }}">
                        <div class="row ml-1 mr-1">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label> {{ __('Name') }} </label>
                                    @php
                                    $name = "name";
                                    $nameToOld = generateOldNameFromFieldName($name) ;
                                    @endphp
                                    <input type="text" class="form-control @error($nameToOld) is-invalid @enderror  " name="{{ $name }}" value="{{ old($nameToOld) ?: (isset($rawMaterial) ? $rawMaterial->getName() : '' ) }}">
                                    <span>{{ @$errors->first($nameToOld) }}</span>
                                </div>
                            </div>




                            @php
                            $name = "rm_inventory_coverage_days";
                            $nameToOld = generateOldNameFromFieldName($name) ;
                            @endphp

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>{{__('Inventory Coverage Days')}} @include('required') </label>
                                    <select class="form-control  @error($nameToOld) is-invalid @enderror" name="{{ $name }}" id="{{ $nameToOld }}">
                                        <option selected value="">{{__('Select')}}</option>
                                        @foreach(getRmInventoryCoverageDays() as $id => $title)
                                        <option value="{{ $id }}" {{isset($rawMaterial) && $rawMaterial->getRmInventoryCoverageDays() ==$id    ?'selected' : '' }}>{{ $title }}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>


                            <div class="col-md-2">
                                <div class="form-group">
                                    @php
                                    $currentFieldName = "measurement_unit";
                                    $nameToOld = generateOldNameFromFieldName($currentFieldName) ;
                                    $currentFieldId = 'measurement-unit-id';
                                    @endphp
                                    <label for="{{ $currentFieldId }}">{{__("Measurement Units")}}
                                        @include('required')
                                    </label>

                                    <select name="{{ $currentFieldName }}" id="{{ $currentFieldId }}" class="form-control @error($nameToOld) is-invalid @enderror">
                                        <option value="">{{__('Select')}}</option>
                                        @foreach(getMeasurementUnits() as $unitId => $unitName)
                                        @php
                                        $currentValue = old($nameToOld) ?: (isset($rawMaterial) ? $rawMaterial->getMeasurementUnit():1);
                                        @endphp
                                        <option value="{{ $unitId }}" {{ $unitId  ==$currentValue ? 'selected' : ''}}>{{__($unitName)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
							
								 <div class="col-md-2">
                                <div class="form-group">
                                    <label> {{ __('Vat Rate %') }} </label>
                                    @php
                                    $name = "vat_rate";
                                    $nameToOld = generateOldNameFromFieldName($name) ;
                                    @endphp
                                    <input type="text" class="form-control @error($nameToOld) is-invalid @enderror  " name="{{ $name }}" value="{{ old($nameToOld) ?: (isset($rawMaterial) ? $rawMaterial->getVatRate() : 0 ) }}">
                                    <span>{{ @$errors->first($nameToOld) }}</span>
                                </div>
                            </div>
							
							<div class="col-md-2">
                                <div class="form-group">
                                    <label> {{ __('Withhold Rate %') }} </label>
                                    @php
                                    $name = "withhold_tax_rate";
                                    $nameToOld = generateOldNameFromFieldName($name) ;
                                    @endphp
                                    <input type="text" class="form-control @error($nameToOld) is-invalid @enderror  " name="{{ $name }}" value="{{ old($nameToOld) ?: (isset($rawMaterial) ? $rawMaterial->getWithholdTaxRate() : 0 ) }}">
                                    <span>{{ @$errors->first($nameToOld) }}</span>
                                </div>
                            </div>
							





                            <div class="col-md-1">
                                <div class="form-group">
                                    <div class="d-flex flex-column">
                                        <label class="visibility-hidden "> Delete </label>
                                        <input data-repeater-delete type="button" class="btn btn-danger btn-md ml-2" value="{{ __('Delete') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>
                <div class="ml-4">
                    <input data-repeater-create type="button" class="btn btn-success btn-sm " value="{{ __('Add Raw Material') }}">
                </div>

            </div>







    </div>

    <button type="submit" class="btn btn-rev float-right" name="submit_button" value="next">{{__('Next')}}</button>
    <button type="submit" class="btn btn-rev float-right main-page-button" name="submit_button" value="save">{{__('Save & Go To Main Page')}}</button>

    </form>
</Div>
</div>

@endsection
@section('js')
<script>
    // old end date
    if (("{{$start_date}}" != "" && "{{$duration_data}}" != "")) {
        var duration = $('#duration').val();

        var monthYear = $('#month-year').val();
        var month = monthYear.split('-')[1];
        var year = monthYear.split('-')[0];

        if (duration == 1) {
            $('#return_rate').attr('disabled', 'true');
            $('#return_rate').val('');
        } else {
            $('#return_rate').removeAttr('disabled');
        }
        if (month != '' && year != '') {
            getdate(duration, month, year, 'start_date', 'end_date');
        }
    }
    //  if the duration or start date Changed : CALL The Function [ end_date ] To Auto Calculate The End date From the Duration + The Start Date
    $('#duration,#month-year').on('keyup change', function() {

        var duration = $('#duration').val();
        var monthYear = $('#month-year').val();
        var month = monthYear.split('-')[1];
        var year = monthYear.split('-')[0];


        if (duration == 1) {
            $('#return_rate').attr('disabled', 'true');
            $('#return_rate').val('');
        } else {
            $('#return_rate').removeAttr('disabled');
        }
        if (month != '' && year != '') {
            getdate(duration, month, year, 'start_date', 'end_date');
        }
    });
    $('#selling_start_month,#selling_start_year').on('keyup change', function() {
        var month = $('#selling_start_month').val();
        var year = $('#selling_start_year').val();
        if (month != '' && year != '') {
            getdate(0, month, year, 'selling_start_date');
        }
    });


    $('#duration').on('change', function() {
        var duration = $('#duration').val();
        if (duration != "" && duration < 3) {
            $('#perpetual_growth_rate').val('');
            $('#perpetual_growth_rate').attr('readonly', 'true');
        } else if (duration >= 3) {
            $('#perpetual_growth_rate').removeAttr("readonly");
        }
    });
    //date
    function getdate(duration, month, year, hidden_input, view_input = null) {
        var data;

        $.ajax({
            type: 'GET'
            , data: {
                'duration': duration
                , 'month': month
                , 'year': year
            }
            , url: "{{ route('get.date') }}"
            , dataType: 'json'
            , accepts: 'application/json'
        }).done(function(data) {
            console.log(data);
            if (view_input != null) {
                $('#' + view_input).val(data.view_date);
                $('#full_end_date').val(data.end_date);
            }
            $('#' + hidden_input).val(data.full_date);
        });

    }
    //Alert
    $(document).on('change', '.form-check-inline', function() {
        val = $('input[name="new_company"]:checked').val();
        if (val == 1) {
            $('.msg-alert').removeClass('hidden');
        } else {
            $('.msg-alert').addClass('hidden');
        }
    });

</script>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });



    var translations = {
        deleteConfirm: @json(__('Are you sure you want to delete this position?'))
    };

    $(document).ready(function() {
        var selector = ".repeater_products";
        $(selector).repeater({
            initEmpty: false
            , defaultValues: {
                'position': ''
                , 'avg_salary': '0'
                , 'existing_count': '0'
            }
            , show: function() {
                $(this).slideDown();

            }
            , ready: function(setIndexes) {

            }
            , hide: function(deleteElement) {
                if (confirm(translations.deleteConfirm)) {
                    $(this).slideUp(deleteElement);
                }

            }
            , isFirstItemUndeletable: true
        });
    });



    $(document).ready(function() {
        var selector = ".repeater_rawMaterials";
        $(selector).repeater({
            initEmpty: false
            , defaultValues: {
                'position': ''
                , 'avg_salary': '0'
                , 'existing_count': '0'
            }
            , show: function() {
                $(this).slideDown();

            }
            , ready: function(setIndexes) {

            }
            , hide: function(deleteElement) {
                if (confirm(translations.deleteConfirm)) {
                    $(this).slideUp(deleteElement);
                }

            }
            , isFirstItemUndeletable: true
        });
    });

</script>
@endsection
