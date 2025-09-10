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



  <div class="formItem pl-4 pr-4">
      <div class="row">
          @foreach($twoLineChartWithGrowthRates as $chartId => $currentChartData )

          <div class="col-md-6">
              <div class="div-title">
                  {{ $chartTitleMapping[$chartId] }}
              </div>
              <div class="chartdiv_two_lines" id="{{ $chartId }}two-line-with-growth-rate-chart"></div>
              <input type="hidden" class="three-line-chart-data-class" data-chart-name="{{ $chartId }}" data-chart-data="{{ json_encode($currentChartData) }}">
          </div>
          @endforeach 
		  
		  @foreach($oneLineChart as $chartId => $currentChartData )

          <div class="col-md-6">
              <div class="div-title">
                  {{ $chartTitleMapping[$chartId] }}
              </div>
              <div class="chartdiv_two_lines" id="{{ $chartId }}one-line-chart"></div>
              <input type="hidden" class="three-line-chart-data-class" data-chart-name="{{ $chartId }}" data-chart-data="{{ json_encode($currentChartData) }}">
          </div>
          @endforeach @foreach($twoLineChartWithPercentageOfSales as $chartId => $currentChartData )

          <div class="col-md-6">
              <div class="div-title">
                  {{ $chartTitleMapping[$chartId] }}
              </div>
              <div class="chartdiv_two_lines" id="{{ $chartId }}two-line-with-percentage-of-sales-chart"></div>
              <input type="hidden" class="three-line-chart-data-class" data-chart-name="{{ $chartId }}" data-chart-data="{{ json_encode($currentChartData) }}">
          </div>
          @endforeach
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
