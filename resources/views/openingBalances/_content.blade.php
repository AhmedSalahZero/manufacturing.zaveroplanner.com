

            <div class="div-title">
                {{ __('Fixed Assets') }}
            </div>


            <div class="formItem">

                <div class="col-12">
                    @php
                    $repeaterId = 'fixedAssetOpeningBalances';
                    $repeaterIds[] =$repeaterId ;
                    @endphp
                    <div id="{{ $repeaterId }}_repeater" class="rooms-repeater">
                        <div class="form-group  m-form__group row">
                            <div data-repeater-list="{{ $repeaterId }}" class="col-lg-12">
                                @include('openingBalances._fixed-asset',['repeaterId'=>$repeaterId,'rows'=>$fixedAssetOpeningBalances])
                            </div>
                        </div>
                        <input data-repeater-create type="button" class="btn btn-success btn-sm " value="{{ __('Add Fixed Asset') }}">
                    </div>

                </div>





            </div>









            <div class="div-title">
                {{ __('Cash And Banks') }}
            </div>
            @php
            $repeaterId = 'cashAndBankOpeningBalances';
            $hiringPopModels[] = 'cashAndBankOpeningBalances';
            @endphp
            <div class="formItem repeater{{ $repeaterId }}">

                <div data-repeater-list="{{ $repeaterId }}">
                    @foreach(count($cashAndBankOpeningBalances) ? $cashAndBankOpeningBalances : [null] as $currentRowIndex=>$cashAndBank)
                    <div data-repeater-item class="container parent-for-salary-amount">
                        <input type="hidden" name="id" value="{{ $cashAndBank ? $cashAndBank->id : 0 }}">
                        <div class="row closest-parent pb-2  col-12">
                            <div class="col-3">
                                <label>{{ __('Cash & Bank Amount') }}</label>
                                <input type="text" name="cash_and_bank_amount" class="form-control" value="{{ $cashAndBank ? $cashAndBank->getCashAndBankAmount() : 0 }}">
                            </div>

                            <div class="col-2">
                                <label>{{ __('Customer Receivables') }}</label>
                                <input type="text" name="customer_receivable_amount" class="form-control  only-greater-than-or-equal-zero-allowed" value="{{ $cashAndBank ? $cashAndBank->getCustomerReceivableAmount() : 0 }}">
                            </div>

                            <div class="col-1 common-parent">
                                <label class="visible-hidden">{{ __('Settlements') }}</label>
                                <div>
                                    <button class="btn btn-primary btn-md text-nowrap " type="button" data-toggle="modal" data-target="#modal-{{ $repeaterId }}-{{ $currentRowIndex }}">{{ __('Settlements') }}</button>
                                </div>
                            </div>


                            <div class="col-2">
                                <label>{{ __('Inventory') }}</label>
                                <input type="text" name="inventory_amount" class="form-control only-greater-than-or-equal-zero-allowed" value="{{ $cashAndBank ? $cashAndBank->getInventoryAmount() : 0 }}">
                            </div>


                        </div>

                        <!-- Modal for Settlements -->
                        <div class="modal fade" id="modal-{{ $repeaterId }}-{{ $currentRowIndex }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel-{{ $repeaterId }}-{{ $currentRowIndex }}" aria-hidden="true">
                            <div class="modal-dialog modal-full" role="document">
                                <div class="modal-content">
                                    <div class="modal-header header-border">
                                        <h5 class="modal-title font-size-1rem text-blue" id="modalLabel-{{ $repeaterId }}">{{ __('Settlements') }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <table>
                                            <tbody>
                                                @php

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
                                                                <input class="form-control input-border" data-main-category="{{ $repeaterId }}" data-sub-category="payload" data-last-index="{{ $dateAsIndex }}" name="[payload]" multiple value="{{ $cashAndBank ? $cashAndBank->getPayloadAtDateIndex($dateAsIndex):0 }}">
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
                                        <button type="button" class="btn btn-primary save-modal" data-dismiss="modal">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>



            <div class="div-title">
                {{ __('Other Debtors') }}
            </div>
            @php
            $repeaterId = 'otherDebtorsOpeningBalances';
            $hiringPopModels[] =$repeaterId;
            @endphp
            <div class="formItem repeater{{ $repeaterId }}">

                <div data-repeater-list="{{ $repeaterId }}">
                    @foreach(count($otherDebtorsOpeningBalances) ? $otherDebtorsOpeningBalances : [null] as $currentRowIndex=>$model)
                    <div data-repeater-item class="container parent-for-salary-amount">
                        <input type="hidden" name="id" value="{{ $model ? $model->id : 0 }}">
                        <div class="row closest-parent pb-2  col-12">
                            <div class="col-3">
                                <label>{{ __('Name') }}</label>
                                <input type="text" name="name" class="form-control" value="{{ $model ? $model->getName() : 0 }}">
                            </div>

                            <div class="col-2">
                                <label>{{ __('Amount') }}</label>
                                <input type="text" name="amount" class="form-control" value="{{ $model ? $model->getAmount() : 0 }}">
                            </div>

                            <div class="col-3 common-parent">
                                <label class="visible-hidden">{{ __('Settlements') }}</label>
                                <div>
                                    <button class="btn btn-primary btn-md text-nowrap " type="button" data-toggle="modal" data-target="#modal-{{ $repeaterId }}-{{ $currentRowIndex }}">{{ __('Settlements') }}</button>
                                    <input data-repeater-delete type="button" class="btn btn-danger btn-md ml-2" value="{{ __('Delete') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Modal for Settlements -->
                        <div class="modal fade" id="modal-{{ $repeaterId }}-{{ $currentRowIndex }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel-{{ $repeaterId }}-{{ $currentRowIndex }}" aria-hidden="true">
                            <div class="modal-dialog modal-full" role="document">
                                <div class="modal-content">
                                    <div class="modal-header header-border">
                                        <h5 class="modal-title font-size-1rem text-blue" id="modalLabel-{{ $repeaterId }}">{{ __('Settlements') }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <table>
                                            <tbody>
                                                @php
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
                                                                <input class="form-control input-border" data-main-category="{{ $repeaterId }}" data-sub-category="payload" data-last-index="{{ $dateAsIndex }}" name="[payload]" multiple value="{{ $model ? $model->getPayloadAtDateIndex($dateAsIndex):0 }}">
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
                                        <button type="button" class="btn btn-primary save-modal" data-dismiss="modal">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="ml-5 mt-4 d-flex justify-content-between" style="width:94%">
                    <input data-repeater-create type="button" class="btn btn-success btn-sm " value="{{ __('Add Other Debtor') }}">
                </div>

            </div>


            <div class="div-title">
                {{ __('Supplier Payable ') }}
            </div>
            @php
            $repeaterId = 'supplierPayableOpeningBalances';
            $hiringPopModels[] = $repeaterId;
            @endphp
            <div class="formItem repeater{{ $repeaterId }}">

                <div data-repeater-list="{{ $repeaterId }}">
                    @foreach(count($supplierPayableOpeningBalances) ? $supplierPayableOpeningBalances : [null] as $currentRowIndex=>$model)
                    <div data-repeater-item class="container parent-for-salary-amount">
                        <input type="hidden" name="id" value="{{ $model ? $model->id : 0 }}">
                        <div class="row closest-parent pb-2  col-12">
                            <div class="col-3">
                                <label>{{ __('Amount') }}</label>
                                <input type="text" name="amount" class="form-control" value="{{ $model ? $model->getAmount() : 0 }}">
                            </div>


                            <div class="col-3 common-parent">
                                <label class="visible-hidden">{{ __('Settlements') }}</label>
                                <div>
                                    <button class="btn btn-primary btn-md text-nowrap " type="button" data-toggle="modal" data-target="#modal-{{ $repeaterId }}-{{ $currentRowIndex }}">{{ __('Settlements') }}</button>
                                    {{-- <input data-repeater-delete type="button" class="btn btn-danger btn-md ml-2" value="{{ __('Delete') }}"> --}}
                                </div>
                            </div>
                        </div>

                        <!-- Modal for Settlements -->
                        <div class="modal fade" id="modal-{{ $repeaterId }}-{{ $currentRowIndex }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel-{{ $repeaterId }}-{{ $currentRowIndex }}" aria-hidden="true">
                            <div class="modal-dialog modal-full" role="document">
                                <div class="modal-content">
                                    <div class="modal-header header-border">
                                        <h5 class="modal-title font-size-1rem text-blue" id="modalLabel-{{ $repeaterId }}">{{ __('Settlements') }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <table>
                                            <tbody>
                                                @php

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
                                                                <input class="form-control input-border" data-main-category="{{ $repeaterId }}" data-sub-category="payload" data-last-index="{{ $dateAsIndex }}" name="[payload]" multiple value="{{ $model ? $model->getPayloadAtDateIndex($dateAsIndex):0 }}">
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
                                        <button type="button" class="btn save-modal btn-primary" data-dismiss="modal">{{ __('Save') }}</button>
                                        {{-- <button type="button" class="btn btn-primary">{{ __('Save changes') }}</button> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>






            <div class="div-title">
                {{ __('Other Creditors') }}
            </div>
            @php
            $repeaterId = 'otherCreditorsOpeningBalances';
            $hiringPopModels[] =$repeaterId;
            @endphp
            <div class="formItem repeater{{ $repeaterId }}">

                <div data-repeater-list="{{ $repeaterId }}">
                    @foreach(count($otherCreditorsOpeningBalances) ? $otherCreditorsOpeningBalances : [null] as $currentRowIndex=>$model)
                    <div data-repeater-item class="container parent-for-salary-amount">
                        <input type="hidden" name="id" value="{{ $model ? $model->id : 0 }}">
                        <div class="row closest-parent pb-2  col-12">
                            <div class="col-3">
                                <label>{{ __('Name') }}</label>
                                <input type="text" name="name" class="form-control" value="{{ $model ? $model->getName() : 0 }}">
                            </div>

                            <div class="col-2">
                                <label>{{ __('Amount') }}</label>
                                <input type="text" name="amount" class="form-control" value="{{ $model ? $model->getAmount() : 0 }}">
                            </div>

                            <div class="col-3 common-parent">
                                <label class="visible-hidden">{{ __('Settlements') }}</label>
                                <div>
                                    <button class="btn btn-primary btn-md text-nowrap " type="button" data-toggle="modal" data-target="#modal-{{ $repeaterId }}-{{ $currentRowIndex }}">{{ __('Settlements') }}</button>
                                    <input data-repeater-delete type="button" class="btn btn-danger btn-md ml-2" value="{{ __('Delete') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Modal for Settlements -->
                        <div class="modal fade" id="modal-{{ $repeaterId }}-{{ $currentRowIndex }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel-{{ $repeaterId }}-{{ $currentRowIndex }}" aria-hidden="true">
                            <div class="modal-dialog modal-full" role="document">
                                <div class="modal-content">
                                    <div class="modal-header header-border">
                                        <h5 class="modal-title font-size-1rem text-blue" id="modalLabel-{{ $repeaterId }}">{{ __('Settlements') }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <table>
                                            <tbody>
                                                @php
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
                                                                <input class="form-control input-border" data-main-category="{{ $repeaterId }}" data-sub-category="payload" data-last-index="{{ $dateAsIndex }}" name="[payload]" multiple value="{{ $model ? $model->getPayloadAtDateIndex($dateAsIndex):0 }}">
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
                                        <button type="button" class="btn save-modal btn-primary" data-dismiss="modal">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="ml-5 mt-4 d-flex justify-content-between" style="width:94%">
                    <input data-repeater-create type="button" class="btn btn-success btn-sm " value="{{ __('Add Other Creditor') }}">
                </div>

            </div>



 <div class="div-title">
                {{ __('Vat & Credit Withhold Taxes') }}
            </div>
            @php
            $repeaterId = 'vatAndCreditWithholdTaxesOpeningBalances';
            $hiringPopModels[] = $repeaterId;
            @endphp
            <div class="formItem repeater{{ $repeaterId }}">

                <div data-repeater-list="{{ $repeaterId }}">
                    @foreach(count($vatAndCreditWithholdTaxesOpeningBalances) ? $vatAndCreditWithholdTaxesOpeningBalances : [null] as $currentRowIndex=>$model)
                    <div data-repeater-item class="container">
                        <input type="hidden" name="id" value="{{ $model ? $model->id : 0 }}">
                        <div class="row closest-parent pb-2  col-12">
                            <div class="col-2">
                                <label>{{ __('VAT Amount') }}</label>
                                <input type="text" name="vat_amount" class="form-control " value="{{ $model ? $model->getVatAmount() : 0 }}">
                            </div>
                            <div class="col-2">
                                <label>{{ __('Credit Withhold Taxes') }}</label>
                                <input type="text" name="credit_withhold_taxes" class="form-control " value="{{ $model ? $model->getCreditWithholdTaxes() : 0 }}">
                            </div>

                            

                        </div>

                    </div>
                    <!-- Modal for Settlements -->


                    @endforeach
                </div>
            </div>
			



            <div class="div-title">
                {{ __('Long Term Loan') }}
            </div>
            @php
            $repeaterId = 'longTermLoanOpeningBalances';
            $hiringPopModels[] =$repeaterId;
            @endphp
            <div class="formItem repeater{{ $repeaterId }}">

                <div data-repeater-list="{{ $repeaterId }}">
                    @foreach(count($longTermLoanOpeningBalances) ? $longTermLoanOpeningBalances : [null] as $currentRowIndex=>$model)
                    <div data-repeater-item class="container parent-for-salary-amount">
                        <input type="hidden" name="id" value="{{ $model ? $model->id : 0 }}">
                        <div class="row closest-parent pb-2  col-12">
                            <div class="col-3">
                                <label>{{ __('Amount') }}</label>
                                <input type="text" name="amount" class="form-control" value="{{ $model ? $model->getAmount() : 0 }}">
                            </div>
                            <div class="col-1 common-parent">
                                <label class="visible-hidden">{{ __('Installments') }}</label>
                                <div>
                                    <button class="btn btn-primary btn-md text-nowrap " type="button" data-toggle="modal" data-target="#modal-installments-{{ $repeaterId }}-{{ $currentRowIndex }}">{{ __('Installments') }}</button>
                                </div>
                            </div>
                            <div class="col-2 common-parent">
                                <label class="visible-hidden">{{ __('Interests') }}</label>
                                <div>
                                    <button class="btn btn-primary btn-md text-nowrap " type="button" data-toggle="modal" data-target="#modal-interests-{{ $repeaterId }}-{{ $currentRowIndex }}">{{ __('Interests') }}</button>
                                    <input data-repeater-delete type="button" class="btn btn-danger btn-md ml-2" value="{{ __('Delete') }}">
                                </div>
                            </div>


                        </div>

                        <!-- Modal for Settlements -->
                        <div class="modal fade" id="modal-interests-{{ $repeaterId }}-{{ $currentRowIndex }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel-{{ $repeaterId }}-{{ $currentRowIndex }}" aria-hidden="true">
                            <div class="modal-dialog modal-full" role="document">
                                <div class="modal-content">
                                    <div class="modal-header header-border">
                                        <h5 class="modal-title font-size-1rem text-blue" id="modalLabel-{{ $repeaterId }}">{{ __('Interests') }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <table>
                                            <tbody>
                                                @php
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
                                                                <input class="form-control input-border" data-main-category="{{ $repeaterId }}" data-sub-category="interests" data-last-index="{{ $dateAsIndex }}" name="[interests]" multiple value="{{ $model ? $model->getInterestAtDateIndex($dateAsIndex):0 }}">
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
                                        <button type="button" class="btn save-modal btn-primary" data-dismiss="modal">{{ __('Save') }}</button>
                                        {{-- <button type="button" class="btn btn-primary">{{ __('Save changes') }}</button> --}}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="modal-installments-{{ $repeaterId }}-{{ $currentRowIndex }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel-{{ $repeaterId }}-{{ $currentRowIndex }}" aria-hidden="true">
                            <div class="modal-dialog modal-full" role="document">
                                <div class="modal-content">
                                    <div class="modal-header header-border">
                                        <h5 class="modal-title font-size-1rem text-blue" id="modalLabel-{{ $repeaterId }}">{{ __('Installments') }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <table>
                                            <tbody>
                                                @php
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
                                                                <input class="form-control input-border" data-main-category="{{ $repeaterId }}" data-sub-category="installments" data-last-index="{{ $dateAsIndex }}" name="[installments]" multiple value="{{ $model ? $model->getInstallmentAtDateIndex($dateAsIndex):0 }}">
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
                                        <button type="button" class="btn save-modal btn-primary" data-dismiss="modal">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    @endforeach
                </div>
                <div class="ml-5 mt-4 d-flex justify-content-between" style="width:94%">
                    <input data-repeater-create type="button" class="btn btn-success btn-sm " value="{{ __('Add Long Term Loan') }}">
                </div>

            </div>



            <div class="div-title">
                {{ __('Other Long Term Liabilities') }}
            </div>
            @php
            $repeaterId = 'otherLongTermLiabilitiesOpeningBalances';
            $hiringPopModels[] =$repeaterId;
            @endphp
            <div class="formItem repeater{{ $repeaterId }}">

                <div data-repeater-list="{{ $repeaterId }}">
                    @foreach(count($otherLongTermLiabilitiesOpeningBalances) ? $otherLongTermLiabilitiesOpeningBalances : [null] as $currentRowIndex=>$model)
                    <div data-repeater-item class="container parent-for-salary-amount">
                        <input type="hidden" name="id" value="{{ $model ? $model->id : 0 }}">
                        <div class="row closest-parent pb-2  col-12">
                            <div class="col-3">
                                <label>{{ __('Amount') }}</label>
                                <input type="text" name="amount" class="form-control" value="{{ $model ? $model->getAmount() : 0 }}">
                            </div>

                            <div class="col-3 common-parent">
                                <label class="visible-hidden">{{ __('Settlements') }}</label>
                                <div>
                                    <button class="btn btn-primary btn-md text-nowrap " type="button" data-toggle="modal" data-target="#modal-{{ $repeaterId }}-{{ $currentRowIndex }}">{{ __('Settlements') }}</button>
                                    <input data-repeater-delete type="button" class="btn btn-danger btn-md ml-2" value="{{ __('Delete') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Modal for Settlements -->
                        <div class="modal fade" id="modal-{{ $repeaterId }}-{{ $currentRowIndex }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel-{{ $repeaterId }}-{{ $currentRowIndex }}" aria-hidden="true">
                            <div class="modal-dialog modal-full" role="document">
                                <div class="modal-content">
                                    <div class="modal-header header-border">
                                        <h5 class="modal-title font-size-1rem text-blue" id="modalLabel-{{ $repeaterId }}">{{ __('Settlements') }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <table>
                                            <tbody>
                                                @php
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
                                                                <input class="form-control input-border" data-main-category="{{ $repeaterId }}" data-sub-category="payload" data-last-index="{{ $dateAsIndex }}" name="[payload]" multiple value="{{ $model ? $model->getPayloadAtDateIndex($dateAsIndex):0 }}">
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
                                        <button type="button" class="btn  save-modal btn-primary" data-dismiss="modal">{{ __('Save') }}</button>
                                        {{-- <button type="button" class="btn btn-primary">{{ __('Save changes') }}</button> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="ml-5 mt-4 d-flex justify-content-between" style="width:94%">
                    <input data-repeater-create type="button" class="btn btn-success btn-sm " value="{{ __('Add Other Long Term') }}">
                </div>

            </div>










            <div class="div-title">
                {{ __('Equity') }}
            </div>
            @php
            $repeaterId = 'equityOpeningBalances';
            $hiringPopModels[] = $repeaterId;
            @endphp
            <div class="formItem repeater{{ $repeaterId }}">

                <div data-repeater-list="{{ $repeaterId }}">
                    @foreach(count($equityOpeningBalances) ? $equityOpeningBalances : [null] as $currentRowIndex=>$model)
                    <div data-repeater-item class="container parent-for-salary-amount">
                        <input type="hidden" name="id" value="{{ $model ? $model->id : 0 }}">
                        <div class="row closest-parent pb-2  col-12">
                            <div class="col-2">
                                <label>{{ __('Paid Up Capital') }}</label>
                                <input type="text" name="paid_up_capital_amount" class="form-control sum-num1" value="{{ $model ? $model->getPaidUpCapitalAmount() : 0 }}">
                            </div>
                            <div class="col-2">
                                <label>{{ __('Legal Reserve') }}</label>
                                <input type="text" name="legal_reserve" class="form-control sum-num2" value="{{ $model ? $model->getLegalReserveAmount() : 0 }}">
                            </div>

                            <div class="col-2">
                                <label>{{ __('Retained Earning') }}</label>
                                <input type="text" name="retained_earnings" class="form-control sum-num3" value="{{ $model ? $model->getRetainedEarningAmount() : 0 }}">
                            </div>
							 <div class="col-2">
                                <label>{{ __('Total Shareholders Equity') }}</label>
                                <input readonly type="text"  data-number-format="0" class="form-control readonly sum-three-column-result" value="{{ $model ? $model->getRetainedEarningAmount() : 0 }}">
                            </div>

                        </div>

                    </div>
                    <!-- Modal for Settlements -->


                    @endforeach
                </div>
            </div>


@section('js')
	<script src="https://cdn.jsdelivr.net/npm/jquery.repeater@1.2.1/jquery.repeater.min.js"></script>
<script>
    var translations = {
        deleteConfirm: @json(__('Are you sure you want to delete this position?'))
    };

</script>
	@foreach($repeaterIds as $repeaterId)
<script>
    $(document).ready(function() {
        var selector = "#{{ $repeaterId.'_repeater' }}";
        $(selector).repeater({
            initEmpty: false
            , defaultValues: {
                'category_id': 'manufacturing-expenses'
                , 'payment_terms': 'cash'
            , }
            , show: function() {
                $(this).slideDown();
                $('.js-select2-with-one-selection').select2({});
                recalculateAllocations(this);

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
@endforeach


@foreach($hiringPopModels as $repeaterId )
<script>
    $(document).ready(function() {
        var selector = ".repeater{{ $repeaterId }}";
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
