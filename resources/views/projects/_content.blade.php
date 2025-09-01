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
