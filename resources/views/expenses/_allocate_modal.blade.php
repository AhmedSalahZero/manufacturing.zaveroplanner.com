<div class="modal fade allocate-parent-modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel-{{ $repeaterId }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header header-border">
                <h5 class="modal-title font-size-1rem">{{ __('Allocate') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <table class="table w-full closest-parent">
                    <tbody>
                        <tr class="closest-parent">
                            <td>

                                <div class="d-flex  align-content-center">
                                    <span class="mr-3">
                                        <b>{{ __('Allocate based on Revenues Percentages') }}</b>
                                    </span>
                                    <div class="kt-radio-inline mt--5">

                                        <label class="kt-radio kt-radio--success text-black font-size-18px">
                                            @php
                                            $isAsRevenuePercentage = $subModel ? $subModel->isAsRevenuePercentage() : true ;
                                            @endphp
                                            <input type="checkbox" class="allocate-checkbox" value="1" name="is_as_revenue_percentages" @if( $isAsRevenuePercentage) checked @endisset>
                                            <span></span>
                                        </label>

                                    </div>
                                </div>

                            </td>


                        </tr>
                        @foreach($products as $product)
                        @php
                        $percentage = $subModel ? $subModel->getProductAllocationPercentageForTypeAndProduct($product->id) : null;

                        $percentage = is_null($percentage) ? 1/count($products)*100 : $percentage;
                        @endphp
                        <tr>

                            <td>
                                <div class="form-group d-flex   text-center">

                                    <div class="col-9 text-left">
                                        <label>{{ __('Product') }}</label>
                                        <input readonly class="form-control" type="text" value="{{ $product->getName() }}">
                                        <input multiple class="form-control product-id-class" data-product-id="{{ $product->id }}" name="product_id" type="hidden" value="{{ $product->id }}">
                                    </div>
                                    <div class="col-3 text-left">
                                        <label>{{ __('Perc.%') }}</label>
                                        <input multiple class="form-control   percentage-allocation total_input input-border" name="percentage" data-old-value="{{ $percentage }}" value="{{ $percentage }}">
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
                                        <input readonly class="form-control {{ $repeaterId }} must-not-exceed-100 total_row_result input-border" value="{{ 0 }}">
                                    </div>


                                </div>
                            </td>

                        </tr>


                    </tbody>
                </table>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn save-modal btn-primary" data-dismiss="modal">{{ __('Save') }}</button>
            </div>

        </div>
    </div>
</div>
