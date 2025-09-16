@push('css')
<link href="/css/dashboard.css" rel="stylesheet">
@endpush

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
        @php
        $index = 0 ;
        @endphp
        @foreach($twoLineChartWithGrowthRates as $chartId => $currentChartData )

        <div class="col-md-6">
            <div class="div-title">
                {{ $chartTitleMapping[$chartId] }}
            </div>
            <div class="chartdiv_two_lines {{ $index % 2 == 1 ? 'margin__left' : '' }}" id="{{ $chartId }}two-line-with-growth-rate-chart"></div>
            <input type="hidden" class="three-line-chart-data-class" data-chart-name="{{ $chartId }}" data-chart-data="{{ json_encode($currentChartData) }}">
        </div>
        @php
        $index++;
        @endphp
        @endforeach

        @foreach($oneLineChart as $chartId => $currentChartData )

        <div class="col-md-6">
            <div class="div-title">
                {{ $chartTitleMapping[$chartId] }}
            </div>
            <div class="chartdiv_two_lines {{ $index % 2 == 1 ? 'margin__left' : '' }}" id="{{ $chartId }}one-line-chart"></div>
            <input type="hidden" class="three-line-chart-data-class" data-chart-name="{{ $chartId }}" data-chart-data="{{ json_encode($currentChartData) }}">
        </div>

        @php
        $index++;
        @endphp

        @endforeach

    </div>
</div>



 <div class="div-title toggle-show-hide position-relative" data-query=".comment-for-sales-class">

        {{__('Insert Comment')}}

    </div>
    <div id="myCard" class="formItem comment-for-sales-class">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="form-group ">
                    <button title="{{ __('Full Screen') }}" type="button" id="toggleBtn" class="fullscreen-btn">⛶</button>
                    <textarea data-is-ck-editor name="dashboard_comment_1">{!! $project->dashboard_comment_1 !!}</textarea>
                </div>
            </div>

        </div>

    </div>


<div class="div-title">
    {{ __('Cost & Expenses Summary Figs\'000' ) }}
</div>

<div class="formItem">

    <div class="row pl-4 pr-4">
@php
	$expensePercentages = [];
@endphp

        @include('dashboard._expenses',['formattedExpenses'=>$formattedExpenses])
        @include('dashboard._expenses-percentage-of',['formattedExpenses'=>$formattedExpenses,'expensePercentages'=>&$expensePercentages])

    </div>


</div>


<div class="div-title">
    {{ __('Cost & Expenses Revenue %' ) }}
</div>
<div class="formItem">
    <div class="row pl-4 pr-4">
        <div class="col-md-12">
            <div id="bar-chart-id" class="chartdashboard"></div>
        </div>
    </div>
</div>

        @foreach($twoLineChartWithPercentageOfSales as $chartId => $currentChartData )
	@if($index % 2 == 0)
<div class="formItem pl-4 pr-4">
    <div class="row">
	@endif

        <div class="col-md-6">
            <div class="div-title">
                {{ $chartTitleMapping[$chartId] }}
            </div>
            <div class="chartdiv_two_lines {{ $index % 2 == 1 ? 'margin__left' : '' }}" id="{{ $chartId }}two-line-with-percentage-of-sales-chart"></div>
            <input type="hidden" class="three-line-chart-data-class" data-chart-name="{{ $chartId }}" data-chart-data="{{ json_encode($currentChartData) }}">
        </div>

        @php
        $index++;
        @endphp
	@if($index % 2 == 0)
    </div>
</div>
	@endif
        @endforeach



<div class="div-title">
    {{ __('Liquidity Ratios Analysis' ) }}
</div>

<div class="formItem">

    <div class="row pl-4 pr-4">
        @include('dashboard._liquidity_analysis',['formattedDcfMethod'=>$liquidityRatio])
    </div>


</div>



<div class="div-title">
    {{ __('Activity Ratios Analysis' ) }}
</div>
<div class="formItem">

    <div class="row pl-4 pr-4">
               @include('dashboard._liquidity_analysis',['formattedDcfMethod'=>$activityRatio])

    </div>


</div>


<div class="div-title">
    {{ __('Discount Cashflow Valuation (DCF) Figs\'000' ) }}
</div>

<div class="formItem">

    <div class="row pl-4 pr-4">

        @include('dashboard._irr',['formattedDcfMethod'=>$formattedDcfMethod])

    </div>


</div>




 <div class="div-title toggle-show-hide position-relative" data-query=".comment-for-sales-class2">

        {{__('Insert Comment')}}

    </div>
    <div id="myCard2" class="formItem comment-for-sales-class2">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="form-group ">
                    <button title="{{ __('Full Screen') }}" type="button" id="toggleBtn2" class="fullscreen-btn">⛶</button>
                    <textarea data-is-ck-editor name="dashboard_comment_2">{!! $project->dashboard_comment_2 !!}</textarea>
                </div>
            </div>

        </div>

    </div>


@push('js_end')
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>


<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>

@foreach($twoLineChartWithGrowthRates as $id => $data)
<script>
    am4core.ready(function() {


        // Themes begin
        am4core.useTheme(am4themes_animated);
        // Themes end

        // Create chart instance
        var chart = am4core.create("{{ $id }}two-line-with-growth-rate-chart", am4charts.XYChart);
        // Increase contrast by taking evey second color
        chart.colors.step = 2;

        // Add data
        chart.data = @json($data);

        // Create axes
        var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
        dateAxis.renderer.minGridDistance = 50;
        dateAxis.dateFormats.setKey("year", "yyyy");
        dateAxis.periodChangeDateFormats.setKey("year", "yyyy");
        dateAxis.tooltipDateFormat = "yyyy";
        // Create series
        function createAxisAndSeries(field, name, opposite, bullet) {
            var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
            if (chart.yAxes.indexOf(valueAxis) != 0) {
                valueAxis.syncWithAxis = chart.yAxes.getIndex(0);
            }

            var series = chart.series.push(new am4charts.LineSeries());
            series.dataFields.valueY = field;
            series.dataFields.dateX = "date";
            series.strokeWidth = 2;
            series.yAxis = valueAxis;
            series.name = name;
            series.tooltipText = "{name}: [bold]{valueY}[/]";
            series.tensionX = 0.8;
            series.showOnInit = true;

            var interfaceColors = new am4core.InterfaceColorSet();

            switch (bullet) {
                case "triangle":
                    var bullet = series.bullets.push(new am4charts.Bullet());
                    bullet.width = 12;
                    bullet.height = 12;
                    bullet.horizontalCenter = "middle";
                    bullet.verticalCenter = "middle";

                    var triangle = bullet.createChild(am4core.Triangle);
                    triangle.stroke = interfaceColors.getFor("background");
                    triangle.strokeWidth = 2;
                    triangle.direction = "top";
                    triangle.width = 12;
                    triangle.height = 12;
                    break;
                case "rectangle":
                    var bullet = series.bullets.push(new am4charts.Bullet());
                    bullet.width = 10;
                    bullet.height = 10;
                    bullet.horizontalCenter = "middle";
                    bullet.verticalCenter = "middle";

                    var rectangle = bullet.createChild(am4core.Rectangle);
                    rectangle.stroke = interfaceColors.getFor("background");
                    rectangle.strokeWidth = 2;
                    rectangle.width = 10;
                    rectangle.height = 10;
                    break;
                default:
                    var bullet = series.bullets.push(new am4charts.CircleBullet());
                    bullet.circle.stroke = interfaceColors.getFor("background");
                    bullet.circle.strokeWidth = 2;
                    break;
            }

            valueAxis.renderer.line.strokeOpacity = 1;
            valueAxis.renderer.line.strokeWidth = 2;
            valueAxis.renderer.line.stroke = series.stroke;
            valueAxis.renderer.labels.template.fill = series.stroke;
            valueAxis.renderer.opposite = opposite;
        }

        createAxisAndSeries("revenue_value", "{{ __('Revenues Value ') }}", false, "circle");
        createAxisAndSeries("growth_rate", "{{ __('Growth Rate %') }}", true, "triangle");
        // createAxisAndSeries("revenue_percentage", "{{ __('Revenue %') }}", true, "rectangle");

        // Add legend
        chart.legend = new am4charts.Legend();

        // Add cursor
        chart.cursor = new am4charts.XYCursor();



    });

</script>

@endforeach



@foreach($oneLineChart as $id => $data)
<script>
    am4core.ready(function() {


        // Themes begin
        am4core.useTheme(am4themes_animated);
        // Themes end

        // Create chart instance
        var chart = am4core.create("{{ $id }}one-line-chart", am4charts.XYChart);
        // Increase contrast by taking evey second color
        chart.colors.step = 2;

        // Add data
        chart.data = @json($data);

        // Create axes
        var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
        dateAxis.renderer.minGridDistance = 50;
        dateAxis.dateFormats.setKey("year", "yyyy");
        dateAxis.periodChangeDateFormats.setKey("year", "yyyy");
        dateAxis.tooltipDateFormat = "yyyy";
        // Create series
        function createAxisAndSeries(field, name, opposite, bullet) {
            var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
            if (chart.yAxes.indexOf(valueAxis) != 0) {
                valueAxis.syncWithAxis = chart.yAxes.getIndex(0);
            }

            var series = chart.series.push(new am4charts.LineSeries());
            series.dataFields.valueY = field;
            series.dataFields.dateX = "date";
            series.strokeWidth = 2;
            series.yAxis = valueAxis;
            series.name = name;
            series.tooltipText = "{name}: [bold]{valueY}[/]";
            series.tensionX = 0.8;
            series.showOnInit = true;

            var interfaceColors = new am4core.InterfaceColorSet();

            switch (bullet) {
                case "triangle":
                    var bullet = series.bullets.push(new am4charts.Bullet());
                    bullet.width = 12;
                    bullet.height = 12;
                    bullet.horizontalCenter = "middle";
                    bullet.verticalCenter = "middle";

                    var triangle = bullet.createChild(am4core.Triangle);
                    triangle.stroke = interfaceColors.getFor("background");
                    triangle.strokeWidth = 2;
                    triangle.direction = "top";
                    triangle.width = 12;
                    triangle.height = 12;
                    break;
                case "rectangle":
                    var bullet = series.bullets.push(new am4charts.Bullet());
                    bullet.width = 10;
                    bullet.height = 10;
                    bullet.horizontalCenter = "middle";
                    bullet.verticalCenter = "middle";

                    var rectangle = bullet.createChild(am4core.Rectangle);
                    rectangle.stroke = interfaceColors.getFor("background");
                    rectangle.strokeWidth = 2;
                    rectangle.width = 10;
                    rectangle.height = 10;
                    break;
                default:
                    var bullet = series.bullets.push(new am4charts.CircleBullet());
                    bullet.circle.stroke = interfaceColors.getFor("background");
                    bullet.circle.strokeWidth = 2;
                    break;
            }

            valueAxis.renderer.line.strokeOpacity = 1;
            valueAxis.renderer.line.strokeWidth = 2;
            valueAxis.renderer.line.stroke = series.stroke;
            valueAxis.renderer.labels.template.fill = series.stroke;
            valueAxis.renderer.opposite = opposite;
        }

        createAxisAndSeries("revenue_value", "{{ __('Revenues Value ') }}", false, "circle");
        //	createAxisAndSeries("growth_rate", "{{ __('Growth Rate %') }}", true, "triangle");
        // createAxisAndSeries("revenue_percentage", "{{ __('Revenue %') }}", true, "rectangle");

        // Add legend
        chart.legend = new am4charts.Legend();

        // Add cursor
        chart.cursor = new am4charts.XYCursor();



    });

</script>

@endforeach


@foreach($twoLineChartWithPercentageOfSales as $id => $data)
<script>
    am4core.ready(function() {


        // Themes begin
        am4core.useTheme(am4themes_animated);
        // Themes end

        // Create chart instance
        var chart = am4core.create("{{ $id }}two-line-with-percentage-of-sales-chart", am4charts.XYChart);
        // Increase contrast by taking evey second color
        chart.colors.step = 2;

        // Add data
        chart.data = @json($data);

        // Create axes
        var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
        dateAxis.renderer.minGridDistance = 50;
        dateAxis.dateFormats.setKey("year", "yyyy");
        dateAxis.periodChangeDateFormats.setKey("year", "yyyy");
        dateAxis.tooltipDateFormat = "yyyy";
        // Create series
        function createAxisAndSeries(field, name, opposite, bullet) {
            var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
            if (chart.yAxes.indexOf(valueAxis) != 0) {
                valueAxis.syncWithAxis = chart.yAxes.getIndex(0);
            }

            var series = chart.series.push(new am4charts.LineSeries());
            series.dataFields.valueY = field;
            series.dataFields.dateX = "date";
            series.strokeWidth = 2;
            series.yAxis = valueAxis;
            series.name = name;
            series.tooltipText = "{name}: [bold]{valueY}[/]";
            series.tensionX = 0.8;
            series.showOnInit = true;

            var interfaceColors = new am4core.InterfaceColorSet();

            switch (bullet) {
                case "triangle":
                    var bullet = series.bullets.push(new am4charts.Bullet());
                    bullet.width = 12;
                    bullet.height = 12;
                    bullet.horizontalCenter = "middle";
                    bullet.verticalCenter = "middle";

                    var triangle = bullet.createChild(am4core.Triangle);
                    triangle.stroke = interfaceColors.getFor("background");
                    triangle.strokeWidth = 2;
                    triangle.direction = "top";
                    triangle.width = 12;
                    triangle.height = 12;
                    break;
                case "rectangle":
                    var bullet = series.bullets.push(new am4charts.Bullet());
                    bullet.width = 10;
                    bullet.height = 10;
                    bullet.horizontalCenter = "middle";
                    bullet.verticalCenter = "middle";

                    var rectangle = bullet.createChild(am4core.Rectangle);
                    rectangle.stroke = interfaceColors.getFor("background");
                    rectangle.strokeWidth = 2;
                    rectangle.width = 10;
                    rectangle.height = 10;
                    break;
                default:
                    var bullet = series.bullets.push(new am4charts.CircleBullet());
                    bullet.circle.stroke = interfaceColors.getFor("background");
                    bullet.circle.strokeWidth = 2;
                    break;
            }

            valueAxis.renderer.line.strokeOpacity = 1;
            valueAxis.renderer.line.strokeWidth = 2;
            valueAxis.renderer.line.stroke = series.stroke;
            valueAxis.renderer.labels.template.fill = series.stroke;
            valueAxis.renderer.opposite = opposite;
        }

        createAxisAndSeries("value", "{{ __('Value') }}", false, "circle");
        createAxisAndSeries("percentage", "{{ __('% Of Sales') }}", true, "triangle");
        // createAxisAndSeries("revenue_percentage", "{{ __('Revenue %') }}", true, "rectangle");

        // Add legend
        chart.legend = new am4charts.Legend();

        // Add cursor
        chart.cursor = new am4charts.XYCursor();



    });

</script>

@endforeach
<script>
    am5.ready(function() {

        // Create root element
        // https://www.amcharts.com/docs/v5/getting-started/#Root_element
        var root = am5.Root.new("bar-chart-id");
        root.numberFormatter.set("numberFormat", "#,###.##");

        // Set themes
        // https://www.amcharts.com/docs/v5/concepts/themes/
        root.setThemes([
            am5themes_Animated.new(root)
        ]);


        // Create chart
        // https://www.amcharts.com/docs/v5/charts/xy-chart/
        var chart = root.container.children.push(am5xy.XYChart.new(root, {
            panX: false
            , panY: false
            , wheelX: "panX"
            , wheelY: ""
            , layout: root.verticalLayout
        }));

        // Add scrollbar
        // https://www.amcharts.com/docs/v5/charts/xy-chart/scrollbars/
        chart.set("scrollbarX", am5.Scrollbar.new(root, {
            orientation: "horizontal"
        }));
        var chartData = @json($barChart);

        var data = chartData;
        console.log(chartData)




        // Create axes
        // https://www.amcharts.com/docs/v5/charts/xy-chart/axes/
        var xRenderer = am5xy.AxisRendererX.new(root, {});
        var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
            categoryField: "year"
            , renderer: xRenderer
            , tooltip: am5.Tooltip.new(root, {}),

        }));

        xRenderer.grid.template.setAll({
            location: 1
        })

        xAxis.data.setAll(data);

        var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
            min: 0
            , renderer: am5xy.AxisRendererY.new(root, {
                strokeOpacity: 0.1
            })
        }));


        // Add legend
        // https://www.amcharts.com/docs/v5/charts/xy-chart/legend-xy-series/
        var legend = chart.children.push(am5.Legend.new(root, {
            centerX: am5.p50
            , x: am5.p50
        }));


        // Add series
        // https://www.amcharts.com/docs/v5/charts/xy-chart/series/
        function makeSeries(name, fieldName) {
            var series = chart.series.push(am5xy.ColumnSeries.new(root, {
                name: name
                , stacked: true
                , xAxis: xAxis
                , yAxis: yAxis
                , valueYField: fieldName
                , categoryXField: "year"
            }));

            series.columns.template.setAll({
                tooltipText: "{name}, {categoryX}: {valueY}"
                , tooltipY: am5.percent(10)
            });
            series.data.setAll(data);

            // Make stuff animate on load
            // https://www.amcharts.com/docs/v5/concepts/animations/
            series.appear();

            series.bullets.push(function() {
                return am5.Bullet.new(root, {
                    sprite: am5.Label.new(root, {
                        text: "{valueY}"
                        , fill: root.interfaceColors.get("alternativeText")
                        , centerY: am5.p50
                        , centerX: am5.p50
                        , populateText: true
                    })
                });
            });

            legend.data.push(series);
        }

        makeSeries("Raw Material Cost", "raw-material-cost");
        makeSeries("Labor Cost", "labor-cost");
        makeSeries("Manufacturing Overheads", "manufacturing-overheads");
        makeSeries("Marketing Expenses", "marketing-expense");
        makeSeries("Sales Expense", "sales-expense");
        makeSeries("General Expense", "general-expense");


        // Make stuff animate on load
        // https://www.amcharts.com/docs/v5/concepts/animations/
        chart.appear(1000, 100);

    });

</script>
@endpush
