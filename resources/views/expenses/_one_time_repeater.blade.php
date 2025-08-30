@foreach(count($expenses) ? $expenses : [null] as $expense)
<div data-repeater-item class="form-group  row align-items-center 
 m-form__group

 repeater_item
common-parent
 ">
    <div class="col-md-2 pr-2 pl-4">
        <label class="form-label font-weight-bold">{{ __('Expense Category') }} </label>
        <select name="category_id" class="form-control expense-category-class">
            @foreach( $project->getExpenseCategories() as $id => $expenseCategoryOptionArr)
            @php
            $title = $expenseCategoryOptionArr['title'];
            $hasAllocation = $expenseCategoryOptionArr['has_allocation'];
            @endphp
            <option data-has-allocation="{{ $hasAllocation }}" value="{{ $id }}" {{ isset($expense) && $expense->getCategoryId() == $id ? 'selected':'' }}>{{ $title }}</option>
            @endforeach
        </select>
    </div>
    <input type="hidden" name="id" value="{{ isset($expense) ? $expense->id:0 }}">
    <div class="col-md-2 pr-2 pl-2">
        <label class="form-label font-weight-bold">{{ __('Expense Name') }} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="text" class="form-control " name="name" value="{{ isset($expense) ? $expense->getName() : old('name') }}">
            </div>
        </div>
    </div>

    <div class="col-md-1 pr-2 pl-2">
        <label class="form-label font-weight-bold">{{ __('Amount') }} </label>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="number" class="form-control only-greater-than-or-equal-zero-allowed " name="amount" value="{{ isset($expense) ? $expense->getAmount() : old('amount') }}" step="0.5">
            </div>
        </div>
    </div>

    <div class="max-w-15 pr-2 pl-2">
        <label class="form-label font-weight-bold">{{ __('Start Date') }} </label>

        @include('components.calendar-month-year',[
        'name'=>'start_date',
        'value'=>$expense ? $expense->getStartDateYearAndMonth() : now()->format('Y-m')
        ])

        {{-- <div class="kt-input-icon">
            <div class="input-group">
                <input type="date" class="form-control " name="start_date" value="{{ isset($expense) ? $expense->getStartDateAsString() : old('start_date') }}">
    </div>
</div> --}}
</div>

{{-- <div class="max-w-15 pr-2 pl-2">
    <label class="form-label font-weight-bold">{{ __('End Date') }} </label>
    @include('components.calendar-month-year',[
    'name'=>'end_date',
    'value'=>$expense ? $expense->getEndDateYearAndMonth() : now()->format('Y-m')
    ])

</div> --}}


<div class="max-w-15 pr-2 pl-2 closest-parent">
    <label class="form-label font-weight-bold">{{ __('Payment') }} </label>
    <x-form.select :selectedValue="isset($expense) ? $expense->getPaymentTerm() : 'cash'" :options="getPaymentTerms()" :add-new="false" class="select2-select repeater-select payment_terms " :all="false" name="payment_terms"></x-form.select>
    <x-modal.custom-collection :title="__('Custom Payment')" :subModel="isset($expense) ? $expense : null "></x-modal.custom-collection>

</div>

<div class="col-md-1 pr-2 pl-2 allocate-parent">
    <label class="form-label  font-weight-bold">{{ __('Allocate') }} </label>
    <div class="kt-input-icon ">
        <div class="input-group ">
            <button class="btn btn-primary btn-md allocate-parent-trigger text-nowrap w-full" type="button" data-toggle="modal" data-target="#modal-allocate-{{ $repeaterId }}">{{ __('Allocate') }}</button>
        </div>
    </div>


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

                            @foreach($products as $product)
                            @php
                            $percentage = $expense ? $expense->getProductAllocationPercentageForTypeAndProduct($product->id) : null;
                            $percentage = is_null($percentage) ? 1/count($products)*100 : $percentage;
                            @endphp
                            <tr>

                                <td>
                                    <div class="form-group d-flex   text-center">

                                        <div class="col-9 text-left">
                                            <label>{{ __('Product') }}</label>
                                            <input readonly class="form-control" type="text" value="{{ $product->getName() }}">
                                            <input multiple class="form-control" name="product_id" type="hidden" value="{{ $product->id }}">

                                        </div>
                                        <div class="col-3 text-left">
                                            <label>{{ __('Perc.%') }}</label>
                                            <input multiple class="form-control total_input input-border" name="percentage" value="{{ $percentage }}">
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





<div style="max-width:40px;" class=" ">
    <div class="d-flex flex-column">
        <label for="" class="visibility-hidden">delete</label>
        <input data-repeater-delete type="button" class="btn btn-danger btn-md ml-2" value="{{ __('X') }}">
    </div>
</div>
{{-- <div class="">
        <i data-repeater-delete="" class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill trash_icon fas fa-times-circle">

        </i>
    </div> --}}
</div>
@endforeach
