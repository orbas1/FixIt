import 'package:syncfusion_flutter_charts/charts.dart';

import '../../../../config.dart';

class CustomChartLayout extends StatelessWidget {
  const CustomChartLayout({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<HomeProvider>(builder: (context, value, child) {
      // log("value.totalW/eeklyRevenue:${value.totalWeeklyRevenue}");
      return Listener(
        onPointerMove: (event) {
          if (event.delta.dy != 0) {
            // Move the scroll position by the delta of the pointer movement
            Scrollable.of(context).position.moveTo(
                  Scrollable.of(context).position.pixels - event.delta.dy,
                );
          }
        },
        child: SfCartesianChart(
            plotAreaBorderWidth: 0,
            enableAxisAnimation: true,
            enableSideBySideSeriesPlacement: false,
            zoomPanBehavior: ZoomPanBehavior(enablePanning: true),
            primaryXAxis: ChartSeriesClass().xAxis(context),
            primaryYAxis: ChartSeriesClass().yAxis(
                context,
                value.selectedIndex,
                value.totalWeeklyRevenue.roundToDouble(),
                value.totalMonthlyRevenue.roundToDouble(),
                value.totalYearlyRevenue.roundToDouble()),
            tooltipBehavior: TooltipBehavior(
                enable: true,
                opacity: 0,
                tooltipPosition: TooltipPosition.auto,
                elevation: 0,
                // Templating the tooltip
                builder: (dynamic data, dynamic point, dynamic series,
                    int pointIndex, int seriesIndex) {
                  if (pointIndex == 0 || pointIndex == 5) {
                    return ChartToolTip(
                        data: data,
                        point: point,
                        pointIndex: pointIndex,
                        series: series,
                        seriesIndex: seriesIndex);
                  } else {
                    return ChartToolTip2(
                        data: data,
                        point: point,
                        pointIndex: pointIndex,
                        series: series,
                        seriesIndex: seriesIndex);
                  }
                }),
            series: <CartesianSeries<ChartData, String>>[
              ChartSeriesClass()
                  .chartSeries1(context, value.selectedIndex, value.isToolTip,
                      onPointTap: (pointInteractionDetails) {
                value.isToolTip = !value.isToolTip;
                value.notifyListeners();
              }),
              if (!value.isToolTip)
                ChartSeriesClass()
                    .chartSeries2(context, value.selectedIndex, value.isToolTip,
                        onPointTap: (pointInteractionDetails) {
                  value.isToolTip = !value.isToolTip;
                  value.notifyListeners();
                })
            ]),
      );
    });
  }
}
