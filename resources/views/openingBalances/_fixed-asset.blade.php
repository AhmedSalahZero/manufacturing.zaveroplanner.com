@foreach(count($rows) ? $rows : [null] as $model)
<div data-repeater-item class="form-group  row align-items-center 
 m-form__group
closest-parent
 repeater_item
common-parent
 ">

    <input type="hidden" name="id" value="{{ isset($model) ? $model->id:0 }}">
    <div class="col-md-2 pr-2 pl-4">
        <label class="form-label font-weight-bold">{{ __('Name') }} <br> <span class="visible-hidden">Name</span> </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="text" class="form-control " name="name" value="{{ isset($model) ? $model->getName() : old('name') }}">
            </div>
        </div>
    </div>

    <div class="col-md-1 pr-2 pl-2">
        <label class="form-label font-weight-bold">{!! __('Gross <br> Amount') !!} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="number" class="form-control number_minus_field_1 only-greater-than-or-equal-zero-allowed " name="gross_amount" value="{{ isset($model) ? $model->getGrossAmount() : old('gross_amount') }}" step="0.5">
            </div>
        </div>
    </div>
    <div class="col-md-1 pr-2 pl-2">
        <label class="form-label font-weight-bold">{{ __('Accumulated Depreciation') }} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="number" class="form-control  number_minus_field_2 only-greater-than-or-equal-zero-allowed " name="accumulated_depreciation" value="{{ isset($model) ? $model->getAccumulatedDepreciation() : old('accumulated_depreciation') }}" step="0.5">
            </div>
        </div>
    </div>

    <div class="col-md-1 pr-2 pl-2">
        <label class="form-label font-weight-bold"> {!! __('Net <br> Amount') !!} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input readonly type="number" class="form-control number_minus_number_result only-greater-than-or-equal-zero-allowed " value="{{ isset($model) ? $model->getNetAmount() : old('net_amount') }}" step="0.5">
            </div>
        </div>
    </div>

    <div class="col-md-1 pr-2 pl-2">
        <label class="form-label font-weight-bold">{!! __('Monthly <br> Depreciation') !!} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="number" class="form-control  only-greater-than-or-equal-zero-allowed " name="monthly_depreciation" value="{{ isset($model) ? $model->getMonthlyDepreciation() : old('monthly_depreciation') }}" step="0.5">
            </div>
        </div>
    </div>


    <div class="col-md-1 pr-2 pl-2">
        <label class="form-label font-weight-bold">{!! __('Monthly <br> Count') !!}</label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="number" class="form-control   only-greater-than-or-equal-zero-allowed " name="monthly_counts" value="{{ isset($model) ? $model->getMonthlyCounts() : old('monthly_counts') }}" step="0.5">
            </div>
        </div>
    </div>



    <div class="col-md-1 pr-2 pl-2 ">
        <label class="form-label font-weight-bold"> {!! __('Administration <br> Depreciation %') !!} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="number" class="form-control only-greater-than-or-equal-zero-allowed " name="admin_depreciation_percentage" value="{{ isset($model) ? $model->getAdminDepreciationPercentage() : old('admin_depreciation_percentage',0) }}" step="0.5">
            </div>
        </div>
    </div>

    <div class="max-w-10 pr-2 pl-2 ">
        <label class="form-label font-weight-bold">{!! __('Manufacturing <br> Depreciation %') !!} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="number" class="form-control  only-greater-than-or-equal-zero-allowed " name="manufacturing_depreciation_percentage" value="{{ isset($model) ? $model->getManufacturingDepreciationPercentage() : old('manufacturing_depreciation_percentage',0) }}" step="0.5">
            </div>
        </div>
    </div>


    <div class="col-md-1 pr-2 pl-2 allocate-parent">
        <label class="form-label  font-weight-bold">{{ __('Allocate') }} <br> <span class="visible-hidden">Allocate</span> </label>
        <div class="kt-input-icon ">
            <div class="input-group ">
                <button class="btn btn-primary btn-md allocate-parent-trigger text-nowrap w-full" type="button" data-toggle="modal" data-target="#modal-allocate-{{ $repeaterId }}">{{ __('Allocate') }}</button>
            </div>
        </div>


        <div class="modal fade allocate-parent-modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel-{{ $repeaterId }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header header-border">
                        <h5 class="modal-title font-size-1rem">{{ __('Allocate') }} </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <table class="table w-full closest-parent">
                            <tbody>

                                @foreach($products as $product)
                                @php
                                $percentage = $model ? $model->getProductAllocationPercentageForTypeAndProduct($product->id) : null;
                                $percentage = is_null($percentage) ? 1/count($products)*100 : $percentage;
                                @endphp
                                <tr>

                                    <td>
                                        <div class="form-group d-flex  dep-parent text-center">

                                            <div class="col-9 text-left">
                                                <label>{{ __('Product') }}</label>
                                                <input readonly class="form-control" type="text" value="{{ $product->getName() }}">
                                                <input multiple class="form-control product-id-class" data-product-id="{{ $product->id }}" name="product_id" type="hidden" value="{{ $product->id }}">

                                            </div>
                                            <div class="col-3 text-left">
                                                <label>{{ __('Perc.%') }}</label>
                                                <input multiple class="form-control percentage-depreciation total_input input-border" name="percentage" value="{{ $percentage }}">
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

    </div>





    <div style="max-width:40px;margin-bottom:-20px" class=" ">
        <div class="d-flex flex-column">
            <label for="" class="visibility-hidden">delete</label>
            <input data-repeater-delete type="button" class="btn btn-danger btn-md ml-2" value="{{ __('X') }}">
        </div>
    </div>

</div>
@endforeach
