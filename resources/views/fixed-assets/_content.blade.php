   <div class="div-title">
                {{ __('Fixed Assets') }}
            </div>


            <div class="formItem">

                <div class="col-12">
                    @php
                    $repeaterId = 'fixedAssets';
                    @endphp
                    <div id="{{ $repeaterId }}_repeater" class="rooms-repeater">
                        <div class="form-group  m-form__group row">
                            <div data-repeater-list="{{ $repeaterId }}" class="col-lg-12">

                                @include('fixed-assets._repeater',['repeaterId'=>$repeaterId,'expenseType'=>$repeaterId,'fixedAssets'=>$fixedAssets])


                            </div>
                        </div>
                        <input data-repeater-create type="button" class="btn btn-success btn-sm " value="{{ __('Add Fixed Asset') }}">
                    </div>

                </div>





            </div>
