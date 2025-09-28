import '../../../../config.dart';

class PendingApprovalBillSummary extends StatelessWidget {
  final BookingModel? bookingModel;

  const PendingApprovalBillSummary({super.key, this.bookingModel});

  @override
  Widget build(BuildContext context) {
    /* final price = bookingModel?.service?.price ?? 0.0;
    final requiredServicemen = bookingModel?.service?.requiredServicemen ?? 1;
    final selectedRequiredServiceMan =
        bookingModel?.service?.selectedRequiredServiceMan ?? 1;
    final serviceRate = bookingModel?.service?.serviceRate ?? 0.0;

    final currencyVal = currency(context).currencyVal;

    double totalPrice = (currencyVal *
        ((price / requiredServicemen) * selectedRequiredServiceMan));
    double baseRate =
        (currencyVal * (serviceRate * selectedRequiredServiceMan));
    double difference = totalPrice - baseRate;
    String formattedDifference = symbolPosition
        ? "-${getSymbol(context)}${difference.toStringAsFixed(2)}"
        : "-${difference.toStringAsFixed(2)}${getSymbol(context)}"; */
    return Container(
        decoration: BoxDecoration(
            image: DecorationImage(
                image: AssetImage(appColor(context).appTheme.isDark
                    ? eImageAssets.bookingDetailBg
                    : eImageAssets.pendingApproval),
                fit: BoxFit.fill)),
        child: Column(children: [
          BillRowCommon(
                  title: translations!.servicePrice ??
                      appFonts
                          .servicePrice /* translations!.perServiceCharge */,
                  price: symbolPosition
                      ? "${getSymbol(context)}${((currency(context).currencyVal * (bookingModel?.service?.price ?? 0)).toStringAsFixed(2))}"
                      : "${((currency(context).currencyVal * (bookingModel?.service?.price ?? 0)).toStringAsFixed(2))}${getSymbol(context)}")
              .marginOnly(bottom: Insets.i20),
          if (bookingModel!.service?.discount != null)
            BillRowCommon(
                    color: appColor(context).appTheme.red,
                    title:
                        "${translations!.appliedDiscount ?? appFonts.appliedDiscount} (${bookingModel!.service!.discount}%)",
                    price: symbolPosition
                        ? "-${getSymbol(context)}${bookingModel?.service?.discountAmount}"
                        : "-${bookingModel?.service?.discountAmount}${getSymbol(context)}")
                .marginOnly(bottom: Insets.i20),
          if (bookingModel!.couponId != null)
            BillRowCommon(
                title: "Coupon discount ",
                price: symbolPosition
                    ? "-${getSymbol(context)}${bookingModel!.couponTotalDiscount!}"
                    : "-${bookingModel!.couponTotalDiscount!}${getSymbol(context)}",
                style: appCss.dmDenseBold14
                    .textColor(appColor(context).appTheme.red)),
          BillRowCommon(
                  title: symbolPosition
                      ? "${(bookingModel!.requiredServicemen != null ? bookingModel!.requiredServicemen! : 0) + (bookingModel!.totalExtraServicemen != null ? bookingModel!.totalExtraServicemen! : 0)} ${language(context, translations!.serviceman)} (${getSymbol(context)}${bookingModel?.perServicemanCharge} × ${(bookingModel!.requiredServicemen != null ? bookingModel!.requiredServicemen! : 0) + (bookingModel!.totalExtraServicemen != null ? bookingModel!.totalExtraServicemen! : 0)})"
                      : "${(bookingModel!.requiredServicemen != null ? bookingModel!.requiredServicemen! : 0) + (bookingModel!.totalExtraServicemen != null ? bookingModel!.totalExtraServicemen! : 0)} ${language(context, translations!.serviceman)} (${bookingModel?.perServicemanCharge} × ${(bookingModel!.requiredServicemen != null ? bookingModel!.requiredServicemen! : 0) + (bookingModel!.totalExtraServicemen != null ? bookingModel!.totalExtraServicemen! : 0)})",
                  price: symbolPosition
                      ? "${getSymbol(context)}${bookingModel?.totalExtraServicemenCharge.toStringAsFixed(2)}"
                      : "${getSymbol(context)}${bookingModel?.totalExtraServicemenCharge.toStringAsFixed(2)}${getSymbol(context)}",
                  style: appCss.dmDenseBold14
                      .textColor(appColor(context).appTheme.darkText))
              .padding(bottom: Insets.i20),
          if (bookingModel!.couponId != null) const VSpace(Sizes.s20),
          if (bookingModel!.additionalServices != null)
            ...bookingModel!.additionalServices!.map((charge) {
              return BillRowCommon(
                title: charge.title,
                color: appColor(context).appTheme.green,
                price: symbolPosition
                    ? "+${getSymbol(context)}${charge.price?.toStringAsFixed(2)}"
                    : "+${charge.price?.toStringAsFixed(2)}${getSymbol(context)}",
              ).padding(bottom: Insets.i20);
            }),
          /*   BillRowCommon(
              title: translations!.tax,
              price:
                  "+${getSymbol(context)}${(currency(context).currencyVal * bookingModel!.tax!)}",
              color: appColor(context).appTheme.online), */
          /*   const VSpace(Sizes.s20), */

          BillRowCommon(
              title: translations!.platformFees,
              price: symbolPosition
                  ? "+${getSymbol(context)}${(currency(context).currencyVal * (bookingModel!.platformFees ?? 0.0)).toStringAsFixed(2)}"
                  : "+${(currency(context).currencyVal * (bookingModel!.platformFees ?? 0.0)).toStringAsFixed(2)}${getSymbol(context)}",
              color: appColor(context).appTheme.online),
          const VSpace(Sizes.s20),
          if (bookingModel!.taxes != null && bookingModel!.taxes!.isNotEmpty)
            ...bookingModel!.taxes!.map((tax) {
              double rate = tax.rate ?? 0;

              return BillRowCommon(
                title:
                    "${translations!.tax} (${tax.name} ${rate.toStringAsFixed(0)}%)",
                price: symbolPosition
                    ? "+${getSymbol(context)}${(tax.amount).toStringAsFixed(2)}"
                    : "+${(tax.amount).toStringAsFixed(2)}${getSymbol(context)}",
                color: appColor(context).appTheme.online,
              ).paddingOnly(bottom: Insets.i20);
            }),
          if (bookingModel!.extraCharges != null &&
              bookingModel!.extraCharges!.isNotEmpty)
            ...bookingModel!.extraCharges!.asMap().entries.map((e) => BillRowCommon(
                    title:
                        "Extra service charge(${e.value.perServiceAmount} × ${e.value.noServiceDone})",
                    price: symbolPosition
                        ? "+${getSymbol(context)}${((e.value.noServiceDone ?? 1) * (currency(context).currencyVal * e.value.perServiceAmount!)).toStringAsFixed(2)}"
                        : "+${((e.value.noServiceDone ?? 1) * (currency(context).currencyVal * e.value.perServiceAmount!)).toStringAsFixed(2)}${getSymbol(context)}",
                    style: appCss.dmDenseBold14
                        .textColor(appColor(context).appTheme.green))
                .paddingOnly(bottom: Insets.i20)),
          // if (bookingModel!.extraCharges != null &&
          //     bookingModel!.extraCharges!.isNotEmpty)
          //   BillRowCommon(
          //       title: translations!.platformFees,
          //       price: symbolPosition
          //           ? "+${getSymbol(context)}${(currency(context).currencyVal * (bookingModel!.extraChargesTotal?.platformFees ?? 0.0)).toStringAsFixed(2)}"
          //           : "+${(currency(context).currencyVal * (bookingModel!.platformFees ?? 0.0)).toStringAsFixed(2)}${getSymbol(context)}",
          //       color: appColor(context).appTheme.online),
          if (bookingModel!.extraCharges != null &&
              bookingModel!.extraCharges!.isNotEmpty)
            const VSpace(Sizes.s20),
          BillRowCommon(
            title: "${translations!.tax}",
            price: symbolPosition
                ? "+${getSymbol(context)}${(bookingModel!.extraChargesTotal?.taxAmount!.toStringAsFixed(2))}"
                : "+${(bookingModel!.extraChargesTotal?.taxAmount!.toStringAsFixed(2))}${getSymbol(context)}",
            color: appColor(context).appTheme.online,
          ).paddingOnly(bottom: Insets.i20),
          Divider(
                  color: appColor(context).appTheme.stroke,
                  thickness: 1,
                  height: 1,
                  indent: 6,
                  endIndent: 6)
              .paddingOnly(bottom: Insets.i27),
          /* if (bookingModel!.extraCharges != null &&
              bookingModel!.extraCharges!.isNotEmpty)
            BillRowCommon(
                title: translations!.totalAmount,
                price: symbolPosition
                    ? "${getSymbol(context)}${(currency(context).currencyVal * (totalServicesCharges(bookingModel!) + double.tryParse(bookingModel!.total.toString())!).roundToDouble())}"
                    : "${(currency(context).currencyVal * (totalServicesCharges(bookingModel!) + double.tryParse(bookingModel!.total.toString())!).roundToDouble())}${getSymbol(context)}",

                /* "${getSymbol(context)}${(currency(context).currencyVal * (totalServicesCharges(bookingModel!) + bookingModel!.total).roundToDouble() /* double.parse(bookingModel!.total.toString()) */)}", */
                /*  "${getSymbol(context)}${(currency(context).currencyVal * bookingModel!.total)}", */
                styleTitle: appCss.dmDenseMedium14
                    .textColor(appColor(context).appTheme.darkText),
                style: appCss.dmDenseBold16
                    .textColor(appColor(context).appTheme.primary)), */
          /*   if (bookingModel!.extraCharges == null &&
              bookingModel!.extraCharges!.isEmpty) */
          if (bookingModel!.extraCharges != null &&
              bookingModel!.extraCharges!.isNotEmpty)
            BillRowCommon(
                title: translations!.totalAmount,
                price: symbolPosition
                    ? "${getSymbol(context)}${(bookingModel?.grandTotalWithExtras.toStringAsFixed(2))}"
                    : "${(bookingModel?.grandTotalWithExtras.toStringAsFixed(2))}${getSymbol(context)}",
                styleTitle: appCss.dmDenseMedium14
                    .textColor(appColor(context).appTheme.darkText),
                style: appCss.dmDenseBold16
                    .textColor(appColor(context).appTheme.primary)),
          if (bookingModel!.extraCharges == null &&
              bookingModel!.extraCharges!.isEmpty)
            BillRowCommon(
                title: translations!.totalAmount,
                price: symbolPosition
                    ? "${getSymbol(context)}${(bookingModel?.total)}"
                    : "${(bookingModel?.total)}${getSymbol(context)}",
                styleTitle: appCss.dmDenseMedium14
                    .textColor(appColor(context).appTheme.darkText),
                style: appCss.dmDenseBold16
                    .textColor(appColor(context).appTheme.primary))
        ]).paddingSymmetric(vertical: Insets.i20));
  }
}
