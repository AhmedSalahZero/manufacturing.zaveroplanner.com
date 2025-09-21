 <div class="div-title">
     {{ __('Expense As Percentage Of Revenues') }}
 </div>

 <div class="formItem">

     <div class="col-12">


         @php
         $repeaterId = 'expense_as_percentage';
         @endphp
         <div id="{{ $repeaterId }}_repeater" class="rooms-repeater">
             <div class="form-group  m-form__group row">
                 <div data-repeater-list="{{ $repeaterId }}" class="col-lg-12">
                     @include('expenses._percentage_repeater',['repeaterId'=>$repeaterId,'expenseType'=>$repeaterId,'expenses'=>$expenses->where('relation_name',$repeaterId)])
                 </div>
             </div>
             <input data-repeater-create type="button" class="btn btn-success btn-sm " value="{{ __('Add Expense') }}">
         </div>

     </div>





 </div>

 <div class="div-title">
     {{ __('Cost Per Unit') }}
 </div>

 <div class="formItem">

     <div class="col-12">


         @php
         $repeaterId = 'cost_per_unit';
         @endphp
         <div id="{{ $repeaterId }}_repeater" class="rooms-repeater">
             <div class="form-group  m-form__group row">
                 <div data-repeater-list="{{ $repeaterId }}" class="col-lg-12">
                     @include('expenses._cost_per_unit_repeater',['repeaterId'=>$repeaterId,'expenseType'=>$repeaterId,'expenses'=>$expenses->where('relation_name',$repeaterId)])
                 </div>
             </div>
             <input data-repeater-create type="button" class="btn btn-success btn-sm " value="{{ __('Add Expense') }}">
         </div>

     </div>





 </div>
 
 <div class="div-title">
     {{ __('Monthly Fixed Expenses') }}
 </div>


 <div class="formItem">

     <div class="col-12">
         @php
         $repeaterId = 'fixed_monthly_repeating_amount';
         @endphp
         <div id="{{ $repeaterId }}_repeater" class="rooms-repeater">
             <div class="form-group  m-form__group row">
                 <div data-repeater-list="{{ $repeaterId }}" class="col-lg-12">
                     @include('expenses._fixed_monthly_repeater',['repeaterId'=>$repeaterId,'expenseType'=>$repeaterId,'expenses'=>$expenses->where('relation_name',$repeaterId)])
                 </div>
             </div>
             <input data-repeater-create type="button" class="btn btn-success btn-sm " value="{{ __('Add Expense') }}">
         </div>

     </div>





 </div>

 <div class="div-title">
     {{ __('One Time Expense') }}
 </div>


 <div class="formItem">

     <div class="col-12">
         @php
         $repeaterId = 'one_time_expense';
         @endphp
         <div id="{{ $repeaterId }}_repeater" class="rooms-repeater">
             <div class="form-group  m-form__group row">
                 <div data-repeater-list="{{ $repeaterId }}" class="col-lg-12">

                     @include('expenses._one_time_repeater',['repeaterId'=>$repeaterId,'expenseType'=>$repeaterId,'expenses'=>$expenses->where('relation_name',$repeaterId)])


                 </div>
             </div>
             <input data-repeater-create type="button" class="btn btn-success btn-sm " value="{{ __('Add Expense') }}">
         </div>

     </div>





 </div>
