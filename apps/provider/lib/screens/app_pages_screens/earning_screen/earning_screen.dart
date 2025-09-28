import 'dart:developer';

import 'package:syncfusion_flutter_charts/charts.dart';

import '../../../config.dart';

class EarningScreen extends StatelessWidget {
  const EarningScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<UserDataApiProvider>(builder: (context, value, child) {
      return Scaffold(
          appBar: AppBarCommon(title: translations!.earnings),
          body: value.isCommissionLoader == true
              ? Center(
                  child: Image.asset(eGifAssets.loaderGif, height: Sizes.s100))
              : commissionList == null
                  ? const CommonEmpty()
                  : SingleChildScrollView(
                      child: Column(children: [
                      Column(children: [
                        if (commissionList!.total != 0.0)
                          Container(
                              height: Sizes.s63,
                              width: MediaQuery.of(context).size.width,
                              decoration: BoxDecoration(
                                  image: DecorationImage(
                                      image: AssetImage(
                                          eImageAssets.balanceContainer),
                                      fit: BoxFit.fill)),
                              child: Row(
                                  mainAxisAlignment:
                                      MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                        language(context,
                                            "${language(context, translations!.totalEarning)} :"),
                                        style: appCss.dmDenseBold18.textColor(
                                            appColor(context)
                                                .appTheme
                                                .whiteBg)),
                                    Text(
                                        symbolPosition
                                            ? "${getSymbol(context)}${(currency(context).currencyVal * commissionList!.total!).toStringAsFixed(2)}"
                                            : "${(currency(context).currencyVal * commissionList!.total!).toStringAsFixed(2)}${getSymbol(context)}",
                                        style: appCss.dmDenseBold18.textColor(
                                            appColor(context)
                                                .appTheme
                                                .whiteColor))
                                  ]).paddingSymmetric(horizontal: Insets.i20)),
                        const VSpace(Sizes.s30),
                        if (commissionList!.histories!.isNotEmpty)
                          Column(children: [
                            Stack(alignment: Alignment.center, children: [
                              SfCircularChart(series: <CircularSeries>[
                                DoughnutSeries<ChartDataColor, String>(
                                    dataSource: appArray.earningChartData,
                                    xValueMapper: (ChartDataColor data, _) =>
                                        data.x,
                                    yValueMapper: (ChartDataColor data, _) =>
                                        data.y,
                                    cornerStyle: CornerStyle.bothCurve,
                                    pointColorMapper:
                                        (ChartDataColor data, _) => data.color,
                                    explodeAll: false,
                                    innerRadius: '85%',
                                    explode: true)
                              ]),
                              SizedBox(
                                  width: Sizes.s120,
                                  child: Text(
                                      language(
                                          context, translations!.topCategorys),
                                      textAlign: TextAlign.center,
                                      style: appCss.dmDenseMedium16.textColor(
                                          appColor(context).appTheme.darkText)))
                            ]),
                            const EarningPercentageLayout()
                          ]).paddingAll(Insets.i15).boxShapeExtension(
                              color: appColor(context).appTheme.fieldCardBg)
                      ]).paddingSymmetric(horizontal: Insets.i20),
                      const VSpace(Sizes.s25),
                      if (commissionList!.histories!.isEmpty)
                        const CommonEmpty(),
                      if (commissionList!.histories!.isNotEmpty)
                        const HistoryBody()
                    ])));
    });
  }
}
