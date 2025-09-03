@extends('layouts.app')
@push('css')
<link href="/css/dashboard.css" rel="stylesheet">
@endpush
@section('content')
<style>
    table,
    table * {
        font-size: 10px !important;
    }

    .max-column-th-class {
        width: 30% !important;
        min-width: 30% !important;
        max-width: 30% !important;
    }

    .expandable-percentage-input {
        max-width: 50px !important;
        min-width: 50px !important;
        text-align: center !important;
    }

    .three-dots-parent {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    .b-bottom {
        border-bottom: 1px solid green !important;
    }

    .expandable-amount-input {
        max-width: 60px !important;
        min-width: 60px !important;
        width: 60px !important;
    }

    table:not(.table-condensed) thead th,
    table:not(.table-condensed) tbody td {
        padding-top: 6px !important;
        padding-bottom: 6px !important;
    }

    input {
        padding-top: 6px !important;
        padding-bottom: 6px !important;
    }

    .chartdiv_two_lines {
        width: 100%;
        height: 500px;
    }

    .chartDiv {
        max-height: 500px !important;
    }

    .margin__left {
        border-left: 2px solid #366cf3;
    }

    .sky-border {
        border-bottom: 1.5px solid #CCE2FD !important;
    }

    .kt-widget24__title {
        color: black !important;
    }

</style>

<style>
    input.form-control[type="text"][readonly] {
        background-color: white !important;
        color: black !important;
        font-weight: 400 !important;
    }

</style>

<div class="col-12">

    <h1 class="bread-crumbs">
        {{ __('ZAVERO Manufacturing') }} > {{ $project->name }} >
    </h1>
    <div class="ProjectList">
        @include('dashboard._content',$project->getDashboardViewVars())



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

    </div>






    <div class="clearfix"></div>


</div>

@endsection

@section('js_end')

{{--

<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>


<script>
    am4core.ready(function() {

        // Themes begin
        am4core.useTheme(am4themes_animated);
        // Themes end

        // Create chart instance
        var chart = am4core.create("three-line-chart-id-chart", am4charts.XYChart);
        var data = [];
        //
        // Increase contrast by taking evey second color
        chart.colors.step = 2;

        // Add data
        chart.data = data;

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



am5.ready(function() {


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

makeSeries("Leasing", "leasing");
makeSeries("Direct Factoring", "direct-factoring");
makeSeries("Reverse Factoring", "reverse-factoring");
makeSeries("Portfolio Mortgage", "portfolio-mortgage");
makeSeries("Microfinance", "microfinance");



chart.appear(1000, 100);

});




</script>

<script>
    $(function() {
        $(document).on('change', 'select[js-refresh-three-line-chart]', function(e) {
            let chartId = $(this).val();
            var chartDataArr = $('.three-line-chart-data-class[data-chart-name="' + chartId + '"]').attr('data-chart-data');
            if (chartDataArr) {
                chartDataArr = JSON.parse(chartDataArr);
            } else {
                chartDataArr = {};
            }
            let currentChartId = 'three-line-chart-id-chart';
            am4core.registry.baseSprites.find(c => c.htmlContainer.id === currentChartId).data = chartDataArr
        })

    })

</script>
<script>
    $(function() {
        $('select[js-refresh-three-line-chart]').trigger('change')
    })

</script> --}}



@endsection
