import 'dart:developer';

import 'package:fixit_user/screens/bottom_screens/booking_screen/booking_shimmer/booking_detail_shimmer.dart';

import '../../../common_tap.dart';
import '../../../config.dart';

class PendingBookingScreen extends StatelessWidget {
  const PendingBookingScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<PendingBookingProvider>(builder: (context1, value, child) {
      /*    final price = value.booking?.service?.price ?? 0.0;
        final requiredServicemen =
            value.booking?.service?.requiredServicemen ?? 1;
        final selectedRequiredServiceMan =
            value.booking?.service?.selectedRequiredServiceMan ?? 1;
        final serviceRate = value.booking?.service?.serviceRate ?? 0.0;

        final currencyVal = currency(context).currencyVal;

        double totalPrice = (currencyVal *
            ((price / requiredServicemen) * selectedRequiredServiceMan));
        double baseRate =
            (currencyVal * (serviceRate * selectedRequiredServiceMan));
        double difference = totalPrice - baseRate;

        String formattedDifference = symbolPosition
            ? "-${getSymbol(context)}${difference.toStringAsFixed(2)}"
            : "-${difference.toStringAsFixed(2)}${getSymbol(context)}"; */

      final reviews = value.booking?.service?.reviews;
      return PopScope(
          canPop: true,
          onPopInvoked: (didPop) {
            value.onBack(context, false);
            if (didPop) return;
          },
          child: StatefulWrapper(
              onInit: () => value.onReady(context),
              child: Scaffold(
                  appBar: AppBarCommon(
                    title: translations!.pendingBooking,
                    onTap: () => value.onBack(context, true),
                  ),
                  body: SafeArea(
                      child: value.isLoading || value.booking == null
                          ? const BookingDetailShimmer()
                          : ListView(children: [
                              RefreshIndicator(
                                  onRefresh: () => value.onRefresh(context),
                                  child: SingleChildScrollView(
                                      child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                        StatusDetailLayout(
                                          data: value.booking!,
                                          onTapStatus: () => showBookingStatus(
                                              context, value.booking),
                                        ),

                                        // if (reviews != null && reviews.isNotEmpty)
                                        //   ...reviews
                                        //       .asMap()
                                        //       .entries
                                        //       .map((e) => ServiceReviewLayout(
                                        //             data: e.value,
                                        //             index: e.key,
                                        //             list: appArray.reviewList,
                                        //           )),
                                        // if (value.booking!.service != null && value.booking!.service!.reviews!.isNotEmpty)
                                        //   ...value.booking!.service!.reviews!
                                        //       .asMap()
                                        //       .entries
                                        //       .map((e) => ServiceReviewLayout(
                                        //     data: e.value,
                                        //     index: e.key,
                                        //     list: appArray.reviewList,
                                        //   ))
                                        //       .toList(),
                                        Text(
                                          language(context,
                                              translations!.billSummary),
                                          style: appCss.dmDenseSemiBold14
                                              .textColor(
                                                  appColor(context).darkText),
                                        ).paddingOnly(
                                            top: Insets.i15,
                                            bottom: Insets.i10),
                                        Container(
                                            decoration: BoxDecoration(
                                                image: DecorationImage(
                                                    image: AssetImage(isDark(
                                                            context)
                                                        ? eImageAssets
                                                            .pendingBillBgDark
                                                        : eImageAssets
                                                            .pendingBillBg),
                                                    fit: BoxFit.fill)),
                                            child: Column(children: [
                                              BillRowCommon(
                                                      title:
                                                          "${translations?.service}",
                                                      price: symbolPosition
                                                          ? "${getSymbol(context)}${value.booking?.service?.price!.toStringAsFixed(2)}"
                                                          : "${value.booking?.service?.price!.toStringAsFixed(2)}${getSymbol(context)}",
                                                      style: appCss
                                                          .dmDenseBold14
                                                          .textColor(
                                                              appColor(context)
                                                                  .darkText))
                                                  .padding(bottom: Insets.i10),
                                              if (value.booking!.service
                                                      ?.discount !=
                                                  null)
                                                BillRowCommon(
                                                        color: appColor(context)
                                                            .red,
                                                        title:
                                                            "${translations!.appliedDiscount} (${value.booking!.service!.discount}%)",
                                                        price: symbolPosition
                                                            ? "-${getSymbol(context)}${value.booking?.service?.discountAmount}"
                                                            : "-${value.booking?.service?.discountAmount}${getSymbol(context)}")
                                                    .marginOnly(
                                                        bottom: Insets.i10),

                                              if (value.booking!
                                                          .couponTotalDiscount !=
                                                      null &&
                                                  value.booking!
                                                          .couponTotalDiscount !=
                                                      0.0)
                                                BillRowCommon(
                                                        color: appColor(context)
                                                            .red,
                                                        title:
                                                            "${translations!.couponDiscount} ",
                                                        price: symbolPosition
                                                            ? "-${getSymbol(context)}${value.booking!.couponTotalDiscount.toString()}"
                                                            : "-${value.booking!.couponTotalDiscount.toString()}${getSymbol(context)}")
                                                    .marginOnly(
                                                        bottom: Insets.i10),
                                              BillRowCommon(
                                                      title: symbolPosition
                                                          ? "${(value.booking!.requiredServicemen != null ? value.booking!.requiredServicemen! : 0) + (value.booking!.totalExtraServicemen != null ? value.booking!.totalExtraServicemen! : 0)} ${language(context, translations!.serviceman)} (${getSymbol(context)}${value.booking?.perServicemanCharge} × ${(value.booking!.requiredServicemen != null ? value.booking!.requiredServicemen! : 0) + (value.booking!.totalExtraServicemen != null ? value.booking!.totalExtraServicemen! : 0)})"
                                                          : "${(value.booking!.requiredServicemen != null ? value.booking!.requiredServicemen! : 0) + (value.booking!.totalExtraServicemen != null ? value.booking!.totalExtraServicemen! : 0)} ${language(context, translations!.serviceman)} (${value.booking?.perServicemanCharge}${getSymbol(context)} × ${(value.booking!.requiredServicemen != null ? value.booking!.requiredServicemen! : 0) + (value.booking!.totalExtraServicemen != null ? value.booking!.totalExtraServicemen! : 0)})",
                                                      price: symbolPosition
                                                          ? "${getSymbol(context)}${value.booking?.totalExtraServicemenCharge!.toStringAsFixed(2)}"
                                                          : "${value.booking?.totalExtraServicemenCharge!.toStringAsFixed(2)}${getSymbol(context)}",
                                                      style: appCss
                                                          .dmDenseBold14
                                                          .textColor(
                                                              appColor(context)
                                                                  .darkText))
                                                  .padding(bottom: Insets.i10),
                                              if (value.booking!
                                                      .additionalServices !=
                                                  null)
                                                ...value.booking!
                                                    .additionalServices!
                                                    .map((charge) {
                                                  return BillRowCommon(
                                                          title: charge.title,
                                                          color:
                                                              appColor(context)
                                                                  .green,
                                                          price: symbolPosition
                                                              ? "+${getSymbol(context)}${charge.price!.toStringAsFixed(2)}"
                                                              : "+${charge.price!.toStringAsFixed(2)}${getSymbol(context)}")
                                                      .padding(
                                                          bottom: Insets.i10);
                                                }),
                                              /*  BillRowCommon(
                                            title:
                                                "${(value.booking!.requiredServicemen ?? 1) + (value.booking!.totalExtraServicemen ?? 0)} ${language(context, translations!.serviceman)}",
                                            price:
                                                "${getSymbol(context)}${(currency(context).currencyVal * (value.booking!.service?.price / (value.booking!.requiredServicemen ?? 1) + (value.booking!.totalExtraServicemen ?? 0))).toStringAsFixed(2)}",
                                            style: appCss.dmDenseBold14
                                                .textColor(
                                                    appColor(context).darkText),
                                          ).paddingSymmetric(
                                              vertical: Insets.i15), */

                                              BillRowCommon(
                                                title:
                                                    translations!.platformFees,
                                                price: symbolPosition
                                                    ? "+${getSymbol(context)}${(currency(context).currencyVal * (value.booking!.platformFees ?? 0.0)).toStringAsFixed(2)}"
                                                    : "+${(currency(context).currencyVal * (value.booking!.platformFees ?? 0.0)).toStringAsFixed(2)}${getSymbol(context)}",
                                                color: appColor(context).online,
                                              ).padding(bottom: Insets.i10),

                                              if (value.booking!.taxes !=
                                                      null &&
                                                  value.booking!.taxes!
                                                      .isNotEmpty)
                                                ...value.booking!.taxes!
                                                    .map((tax) {
                                                  double rate = tax.rate ?? 0;

                                                  return BillRowCommon(
                                                          title:
                                                              "${translations!.tax} (${tax.name} ${rate.toStringAsFixed(0)}%)",
                                                          price: symbolPosition
                                                              ? "+${getSymbol(context)}${tax.amount}"
                                                              : "+${tax.amount}${getSymbol(context)}",
                                                          color:
                                                              appColor(context)
                                                                  .online)
                                                      .paddingOnly(
                                                          bottom: Insets.i10);
                                                }),

                                              if (value.booking!.taxes !=
                                                      null &&
                                                  value.booking!.taxes!
                                                      .isNotEmpty)
                                                VSpace(Sizes.s20),

                                              // Divider(
                                              //   color: appColor(context).stroke,
                                              //   thickness: 1,
                                              //   height: 1,
                                              //   indent: 6,
                                              //   endIndent: 6,
                                              // ).paddingOnly(bottom: 18),
                                              BillRowCommon(
                                                  title:
                                                      translations!.totalAmount,
                                                  price: symbolPosition
                                                      ? "${getSymbol(context)}${(currency(context).currencyVal * (value.booking!.total ?? 0.0)).toStringAsFixed(2)}"
                                                      : "${(currency(context).currencyVal * (value.booking!.total ?? 0.0)).toStringAsFixed(2)}${getSymbol(context)}",
                                                  styleTitle: appCss
                                                      .dmDenseMedium14
                                                      .textColor(
                                                          appColor(context)
                                                              .darkText),
                                                  style: appCss.dmDenseBold16
                                                      .textColor(
                                                          appColor(context)
                                                              .primary))
                                            ]).paddingSymmetric(
                                                vertical: Insets.i20)),
                                        if (value.booking!.bookingStatus !=
                                                null &&
                                            value.booking!.bookingStatus!
                                                    .slug !=
                                                translations!.cancel)
                                          if (value.booking!.dateTime != null &&
                                              value.checkForCancelButtonShow())
                                            ButtonCommon(
                                                    title: translations!
                                                        .cancelBooking!,
                                                    onTap: value.isCancel
                                                        ? () {}
                                                        : () => value
                                                            .onCancelBooking(
                                                                context))
                                                .paddingOnly(
                                                    top: Insets.i35,
                                                    bottom: Insets.i30),
                                        if (value.booking!.dateTime != null &&
                                            !value.checkForCancelButtonShow())
                                          SizedBox(
                                                  width: MediaQuery.of(context)
                                                      .size
                                                      .width,
                                                  child: Row(
                                                      mainAxisSize:
                                                          MainAxisSize.min,
                                                      crossAxisAlignment:
                                                          CrossAxisAlignment
                                                              .start,
                                                      children: [
                                                        Text(
                                                            language(context,
                                                                "${translations!.status}:"),
                                                            style: appCss
                                                                .dmDenseMedium14
                                                                .textColor(appColor(
                                                                        context)
                                                                    .red)),
                                                        const HSpace(Sizes.s10),
                                                        Expanded(
                                                            child: Text(
                                                                language(
                                                                    context,
                                                                    "You can’t cancel this booking short time before it starts."),
                                                                overflow:
                                                                    TextOverflow
                                                                        .fade,
                                                                style: appCss
                                                                    .dmDenseRegular14
                                                                    .textColor(
                                                                        appColor(context)
                                                                            .red)))
                                                      ]).paddingAll(Insets.i15))
                                              .boxShapeExtension(
                                                  color: appColor(context)
                                                      .red
                                                      .withOpacity(0.1))
                                              .paddingDirectional(
                                                  vertical: Sizes.s20)
                                      ]).paddingOnly(
                                          left: Insets.i20, right: Insets.i20)))
                            ])))));
    });
  }
}

/*
class PendingBookingScreen extends StatelessWidget {
  const PendingBookingScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<PendingBookingProvider>(builder: (context1, value, child) {
      return PopScope(
        canPop: true,
        onPopInvoked: (didPop) {
          value.onBack(context, false);
          if (didPop) return;
        },
        child: StatefulWrapper(
          onInit: () => Future.delayed(
              const Duration(milliseconds: 150), () => value.onReady(context)),
          child: value.booking == null
              ? const BookingDetailShimmer()
              : Scaffold(
                  appBar: AppBarCommon(
                      title: translations!.pendingBooking,
                      onTap: () => value.onBack(context, true)),
                  body: RefreshIndicator(
                    onRefresh: () async {
                      value.onRefresh(context);
                    },
                    child: SingleChildScrollView(
                        child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                          StatusDetailLayout(
                              data: value.booking,
                              onTapStatus: () =>
                                  showBookingStatus(context, value.booking)),
                              if(value.booking!.service!=null&&value.booking!.service?.reviews!=null)
                          ...value.booking!.service!.reviews!
                              .asMap()
                              .entries
                              .map((e) => ServiceReviewLayout(
                                  data: e.value,
                                  index: e.key,
                                  list: appArray.reviewList)),
                          Text(language(context, translations!.billSummary),
                                  style: appCss.dmDenseSemiBold14
                                      .textColor(appColor(context).darkText))
                              .paddingOnly(top: Insets.i15, bottom: Insets.i10),
                          Container(
                              decoration: BoxDecoration(
                                  image: DecorationImage(
                                      image: AssetImage(isDark(context)
                                          ? eImageAssets.pendingBillBgDark
                                          : eImageAssets.pendingBillBg),
                                      fit: BoxFit.fill)),
                              child: Column(children: [
                                if(value.booking!.perServicemanCharge!=null)
                                BillRowCommon(
                                    title: translations!.perServiceCharge,
                                    price:
                                        "${getSymbol(context)}${(currency(context).currencyVal * value.booking!.perServicemanCharge!).toStringAsFixed(2)}"),
                                BillRowCommon(
                                        title:
                                            "${((value.booking!.requiredServicemen ?? 1) + (value.booking!.totalExtraServicemen != null ? (value.booking!.totalExtraServicemen ?? 1) : 0))} ${language(context, translations!.serviceman)}",
                                        price:
                                            "${getSymbol(context)}${(currency(context).currencyVal * (value.booking!.subtotal??0.0)).toStringAsFixed(2)}",
                                        style: appCss.dmDenseBold14.textColor(
                                            appColor(context).darkText))
                                    .paddingSymmetric(vertical: Insets.i15),

                                BillRowCommon(
                                    title: translations!.tax,
                                    price:
                                        "+${getSymbol(context)}${(currency(context).currencyVal * (value.booking!.tax??0.0)).toStringAsFixed(2)}",
                                    color: appColor(context).online),
                                BillRowCommon(
                                        title: translations!.platformFees,
                                        price:
                                            "+${getSymbol(context)}${(currency(context).currencyVal * (value.booking!.platformFees ?? 0.0)).toStringAsFixed(2)}",
                                        color: appColor(context).online)
                                    .paddingSymmetric(vertical: Insets.i15),
                                Divider(
                                        color: appColor(context).stroke,
                                        thickness: 1,
                                        height: 1,
                                        indent: 6,
                                        endIndent: 6)
                                    .paddingOnly(bottom: 18),
                                BillRowCommon(
                                    title: translations!.totalAmount,
                                    price:
                                        "${getSymbol(context)}${(currency(context).currencyVal * (value.booking!.total??0.0)).toStringAsFixed(2)}",
                                    styleTitle: appCss.dmDenseMedium14
                                        .textColor(appColor(context).darkText),
                                    style: appCss.dmDenseBold16
                                        .textColor(appColor(context).primary)),
                              ]).paddingSymmetric(vertical: Insets.i20)),
                          if (value.booking!.bookingStatus!=null&&value.booking!.bookingStatus!.slug !=
                              translations!.cancel)
                            if (value.booking!=null&&value.booking!.dateTime!=null&&value.checkForCancelButtonShow())
                              ButtonCommon(
                                      title: translations!.cancelBooking,
                                      onTap: value.isCancel
                                          ? () {
                                              print(
                                                  "object=-=-=-=-=-=-=-=-=-=-=-=-");
                                            }
                                          : () =>
                                              value.onCancelBooking(context))
                                  .paddingOnly(
                                      top: Insets.i35, bottom: Insets.i30),
                          if (value.booking!=null&&value.booking!.dateTime!=null&&value.checkForCancelButtonShow() == false)
                            SizedBox(
                              width: MediaQuery.of(context).size.width,
                              child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                        language(context,
                                            "${translations!.status}:"),
                                        style: appCss.dmDenseMedium14
                                            .textColor(appColor(context).red)),
                                    const HSpace(Sizes.s10),
                                    Expanded(
                                        child: Text(
                                            language(context,
                                                "You can’t cancel this booking short time before it starts." ),
                                            overflow: TextOverflow.fade,
                                            style: appCss.dmDenseRegular14
                                                .textColor(
                                                    appColor(context).red)))
                                  ]).paddingAll(Insets.i15),
                            ).boxShapeExtension(
                                color: appColor(context).red.withOpacity(0.1)).paddingDirectional(vertical: Sizes.s20)
                        ]).paddingOnly(left: Insets.i20, right: Insets.i20)),
                  )),
        ),
      );
    });
  }
}
*/
