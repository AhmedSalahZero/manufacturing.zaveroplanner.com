  <div class="div-title">

            {{ __('Profitability Summary Figs\'000') }}
        </div>

        <div class="formItem pl-4 pr-4">


            <div class="row">
                @include('dashboard._income-statement')
                @if($withSensitivity)
                @include('dashboard._income-statement',['formattedResult'=>$sensitivityFormattedResult])
                @endif
                @include('dashboard._income-statement-percentage-of',['formattedResult'=>$formattedResult])

                @if($withSensitivity)
                @include('dashboard._income-statement-percentage-of',['formattedResult'=>$sensitivityFormattedResult])
                @endif
            </div>

        </div>


   <div class="div-title">
            {{ __('Cost & Expenses Summary Figs\'000' ) }}
        </div>

        <div class="formItem">

            <div class="row pl-4 pr-4">


                @include('dashboard._expenses',['formattedExpenses'=>$formattedExpenses])
                @include('dashboard._expenses-percentage-of',['formattedExpenses'=>$formattedExpenses])


            </div>


        </div>
		
		
		  <div class="div-title">
            {{ __('Discount Cashflow Valuation (DCF)' ) }}
        </div>

        <div class="formItem">

            <div class="row pl-4 pr-4">




                @include('dashboard._irr',['formattedDcfMethod'=>$formattedDcfMethod])

            </div>


        </div>
		