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

		

     	<div class="mt-3">
            <a href="{{ route('main.project.page',['project'=>$project->id]) }}" type="submit" class="btn btn-rev float-right main-page-button" name="submit_button" value="save">{{ __('Go To Main Page') }}</a>
		</div>

    </div>






    <div class="clearfix"></div>


</div>

@endsection

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

@endpush
