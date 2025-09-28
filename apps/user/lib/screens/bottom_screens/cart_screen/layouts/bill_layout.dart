/* import '../../../../config.dart';

class BillLayout extends StatelessWidget {
  const BillLayout({super.key});

  @override
  Widget build(BuildContext context) {
    final value = Provider.of<CartProvider>(context, listen: true);

    return value.checkoutModel != null
        ? Container(
            padding: const EdgeInsets.symmetric(vertical: Insets.i20),
            width: MediaQuery.of(context).size.width,
            decoration: BoxDecoration(
                image: DecorationImage(
                    image: AssetImage(isDark(context)
                        ? eImageAssets.pendingBillBgDark
                        : eImageAssets.pendingBillBg),
                    fit: BoxFit.fill)),
            child: Column(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(children: [
                    /* Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              Row(children: [
                Text(language(context, "Ac service"),
                    style: appCss.dmDenseMedium14
                        .textColor(appColor(context).lightText)),
                const HSpace(Sizes.s5),
                SvgPicture.asset(eSvgAssets.about,
                        fit: BoxFit.scaleDown,
                        colorFilter: ColorFilter.mode(
                            appColor(context).primary,
                            BlendMode.srcIn))
                    .inkWell(onTap: () => value.onServiceDetail(context))
              ]),
              Text("\$22.00",
                  style: appCss.dmDenseMedium14
                      .textColor(appColor(context).darkText))
            ]),
            const VSpace(Sizes.s20),
            BillRowCommon(title: translations!.perServiceCharge, price: "\$12.00"),
            const BillRowCommon(title: "Furnishing", price: "\$12.00")
                .paddingSymmetric(vertical: Insets.i20),
            const BillRowCommon(title: "Cleaning package", price: "\$15.23"),
            const VSpace(Sizes.s20),
            const BillRowCommon(title: "Subtotal", price: "+\$52.46"),
            BillRowCommon(
                    color: appColor(context).red,
                    title: "Coupon Discount (24% off)",
                    price: "-\$2.00")
                .paddingSymmetric(vertical: Insets.i20),
            BillRowCommon(
                title: translations!.tax,
                color: appColor(context).online,
                price: "+\$2.46"),*/
                    /*  if (value.checkoutModel!.services != null)
                      Column(
                        children: [
                          ...value.checkoutModel!.services!
                              .asMap()
                              .entries
                              .map((e) {
                            int total = getTotalRequiredServiceMan(
                                value.cartList, e.value.serviceId, false);
                            return e.value.total!.totalServicemen! > 1
                                ? Row(
                                    mainAxisAlignment:
                                        MainAxisAlignment.spaceBetween,
                                    children: [
                                        Row(children: [
                                          Text(
                                                  getName(value.cartList,
                                                      e.value.serviceId, false),
                                                  style: appCss.dmDenseMedium14
                                                      .textColor(
                                                          appColor(context)
                                                              .lightText))
                                              .width(Sizes.s200),
                                          const HSpace(Sizes.s5),
                                          SvgPicture.asset(eSvgAssets.about,
                                                  fit: BoxFit.scaleDown,
                                                  colorFilter: ColorFilter.mode(
                                                      appColor(context).primary,
                                                      BlendMode.srcIn))
                                              .inkWell(
                                                  onTap: () => value
                                                      .onServiceDetail(context,
                                                          data: e.value,
                                                          totalServiceman:
                                                              total))
                                        ]),
                                        Text(
                                            "${getSymbol(context)}${(currency(context).currencyVal * e.value.price!).toStringAsFixed(2)}",
                                            style: appCss.dmDenseMedium14
                                                .textColor(
                                                    appColor(context).darkText))
                                      ]).paddingOnly(
                                    bottom: Insets.i10,
                                    right: Insets.i15,
                                    left: Insets.i15)
                                : BillRowCommon(
                                        title: getName(value.cartList,
                                            e.value.serviceId, false),
                                        color: appColor(context).darkText,
                                        price:
                                            "${getSymbol(context)}${(currency(context).currencyVal * e.value.price! /*  total!.subtotal! */).toStringAsFixed(2)}")
                                    .paddingOnly(bottom: Insets.i10);
                          })
                        ],
                      ), */
                    if (value.checkoutModel!.servicesPackage != null)
                      Column(
                        children: [
                          ...value.checkoutModel!.servicesPackage!
                              .asMap()
                              .entries
                              .map((e) => Column(
                                    children: e.value.services!
                                        .asMap()
                                        .entries
                                        .map((ser) {
                                      int total = getTotalRequiredServiceMan(
                                          value.cartList,
                                          ser.value.serviceId,
                                          true);
                                      return ser.value.total!.totalServicemen! >
                                              1
                                          ? Row(
                                              mainAxisAlignment:
                                                  MainAxisAlignment
                                                      .spaceBetween,
                                              children: [
                                                  Row(children: [
                                                    Text(
                                                        getName(
                                                            value.cartList,
                                                            ser.value.serviceId,
                                                            true),
                                                        style: appCss
                                                            .dmDenseMedium14
                                                            .textColor(appColor(
                                                                    context)
                                                                .lightText)),
                                                    const HSpace(Sizes.s5),
                                                    SvgPicture.asset(
                                                            eSvgAssets.about,
                                                            fit: BoxFit
                                                                .scaleDown,
                                                            colorFilter:
                                                                ColorFilter.mode(
                                                                    appColor(
                                                                            context)
                                                                        .primary,
                                                                    BlendMode
                                                                        .srcIn))
                                                        .inkWell(
                                                            onTap: () => value
                                                                .onServiceDetail(
                                                                    context,
                                                                    packageServices:
                                                                        ser
                                                                            .value,
                                                                    totalServiceman:
                                                                        total))
                                                  ]),
                                                  Text(
                                                      "${getSymbol(context)}${(currency(context).currencyVal * ser.value.total!.subtotal!).toStringAsFixed(2)}",
                                                      style: appCss
                                                          .dmDenseMedium14
                                                          .textColor(
                                                              appColor(context)
                                                                  .darkText))
                                                ]).paddingOnly(
                                              bottom: Insets.i10,
                                              right: Insets.i15,
                                              left: Insets.i15)
                                          : BillRowCommon(
                                                  title: getName(
                                                      value.cartList,
                                                      ser.value.serviceId,
                                                      true),
                                                  color: appColor(context)
                                                      .darkText,
                                                  price: symbolPosition
                                                      ? "${getSymbol(context)}${(currency(context).currencyVal * ser.value.total!.subtotal!).toStringAsFixed(2)}"
                                                      : "${(currency(context).currencyVal * ser.value.total!.subtotal!).toStringAsFixed(2)}${getSymbol(context)}")
                                              .paddingOnly(bottom: Insets.i10);
                                    }).toList(),
                                  ))
                        ],
                      ),
                    if (value.cartList.isNotEmpty &&
                        value.checkoutModel!.services!.isNotEmpty)
                      Column(
                        children: value.cartList.map((item) {
                          final service = item.serviceList;
                          final price = service.price ?? 0;
                          final discount = service.discount ?? 0;
                          final title = service.title ?? "Service";

                          final discountedAmount =
                              discount > 0 ? (price * discount / 100) : 0;

                          return Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    title,
                                    style: appCss.dmDenseMedium14
                                        .textColor(appColor(context).lightText),
                                  ).width(Sizes.s200),
                                  Text(
                                    symbolPosition
                                        ? "${getSymbol(context)}${price.toStringAsFixed(2)}"
                                        : "${price.toStringAsFixed(2)}${getSymbol(context)}",
                                    style: appCss.dmDenseMedium14
                                        .textColor(appColor(context).darkText),
                                  ),
                                ],
                              ).paddingOnly(
                                  bottom: Insets.i10,
                                  left: Insets.i15,
                                  right: Insets.i15),

                              if (discount > 0)
                                BillRowCommon(
                                  title:
                                      "Discount (${discount.toStringAsFixed(0)}%)",
                                  color: appColor(context).red,
                                  price: symbolPosition
                                      ? "-${getSymbol(context)}${discountedAmount.toStringAsFixed(2)}"
                                      : "-${discountedAmount.toStringAsFixed(2)}${getSymbol(context)}",
                                ).paddingOnly(bottom: Insets.i10),

                              // ✅ Add-On Display
                              if (service.selectedAdditionalServices != null &&
                                  service
                                      .selectedAdditionalServices!.isNotEmpty)
                                ...service.selectedAdditionalServices!
                                    .map((addon) {
                                  final addonTitle = addon.title ?? "Add-On";
                                  final addonPrice = addon.price ?? 0;

                                  return BillRowCommon(
                                    title: addonTitle,
                                    color: appColor(context).green,
                                    price: symbolPosition
                                        ? "+${getSymbol(context)}${addonPrice.toStringAsFixed(2)}"
                                        : "+${addonPrice.toStringAsFixed(2)}${getSymbol(context)}",
                                  ).paddingOnly(bottom: Insets.i10);
                                }).toList(),
                              // Step 2: Loop and render each tax
                              if (value.checkoutModel!.services![0].taxes !=
                                      null &&
                                  value.checkoutModel!.services![0].taxes!
                                      .isNotEmpty)
                                ...value.checkoutModel!.services![0].taxes!
                                    .asMap()
                                    .entries
                                    .map((entry) {
                                  int index = entry.key;
                                  var tax = entry.value;
                                  double rate = tax.rate ?? 0;

                                  return BillRowCommon(
                                          title:
                                              "${translations!.tax} (${rate.toStringAsFixed(0)}%)",
                                          color: appColor(context).online,
                                          price: symbolPosition
                                              ? "+${getSymbol(context)}${(entry.value.amount)}"
                                              : "+${(entry.value.amount)}${getSymbol(context)}")
                                      .paddingOnly(bottom: Insets.i10);
                                }),
                              // BillRowCommon(
                              //         title:
                              //             "${translations!.tax}(${value.checkoutModel!.services![0].taxes?[0].rate}%)",
                              //         color: appColor(context).online,
                              //         price:
                              //             "+${getSymbol(context)}${(currency(context).currencyVal * value.checkoutModel!.total!.tax!).toStringAsFixed(2)}")
                              //     .paddingOnly(bottom: Insets.i10),
                            ],
                          );
                        }).toList(),
                      ),
                    BillRowCommon(
                            title: translations!.platformFees,
                            color: appColor(context).online,
                            price: symbolPosition
                                ? "+${getSymbol(context)}${(currency(context).currencyVal * value.checkoutModel!.total!.platformFees!).toStringAsFixed(2)}"
                                : "+${(currency(context).currencyVal * value.checkoutModel!.total!.platformFees!).toStringAsFixed(2)}${getSymbol(context)}")
                        .paddingOnly(bottom: Insets.i10),
                    if (value.checkoutModel!.total!.couponTotalDiscount !=
                            null &&
                        value.checkoutModel!.total!.couponTotalDiscount! > 0)
                      // Display coupon discount only if it's greater than 0
                      BillRowCommon(
                              title: translations!.coupons,
                              color: appColor(context).red,
                              price: symbolPosition
                                  ? "-${getSymbol(context)}${(currency(context).currencyVal * value.checkoutModel!.total!.couponTotalDiscount!).toStringAsFixed(2)}"
                                  : "-${(currency(context).currencyVal * value.checkoutModel!.total!.couponTotalDiscount!).toStringAsFixed(2)}${getSymbol(context)}")
                          .paddingOnly(bottom: Insets.i10),
                    // BillRowCommon(
                    //         title: translations!.subtotal,
                    //         price:
                    //             "${getSymbol(context)}${(currency(context).currencyVal * value.checkoutModel!.total!.subtotal!).toStringAsFixed(2)}")
                    //     .paddingOnly(bottom: Insets.i25),

                    /* ...value.checkoutModel!.servicesPackage!.asMap().entries.map((e) {
              return BillRowCommon(
                  title:" e.value.title",
                  color: appColor(context).online,
                  price: "${getSymbol(context)}${(currency(context).currencyVal*int.parse(e.value.subtotal.toString()).toStringAsFixed(2)())}").paddingSymmetric(vertical: Insets.i20);
            }).toList()*/
                  ]),
                  Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                        Text(language(context, translations!.totalAmount),
                            style: appCss.dmDenseMedium14
                                .textColor(appColor(context).darkText)),
                        Text(
                            symbolPosition
                                ? "${getSymbol(context)}${(currency(context).currencyVal * value.checkoutModel!.total!.total!).toStringAsFixed(2)}"
                                : "${(currency(context).currencyVal * value.checkoutModel!.total!.total!).toStringAsFixed(2)}${getSymbol(context)}",
                            style: appCss.dmDenseBold16
                                .textColor(appColor(context).primary))
                      ])
                      .paddingSymmetric(horizontal: Insets.i15)
                      .paddingOnly(bottom: Insets.i5, top: Insets.i10)
                ]))
        : Container();
  }
}
 */

import '../../../../config.dart';

class BillLayout extends StatelessWidget {
  const BillLayout({super.key});

  @override
  Widget build(BuildContext context) {
    final value = Provider.of<CartProvider>(context, listen: true);

    return value.checkoutModel != null
        ? Container(
            padding: const EdgeInsets.symmetric(vertical: Insets.i20),
            width: MediaQuery.of(context).size.width,
            decoration: BoxDecoration(
                image: DecorationImage(
                    image: AssetImage(isDark(context)
                        ? eImageAssets.pendingBillBgDark
                        : eImageAssets.pendingBillBg),
                    fit: BoxFit.fill)),
            child: Column(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(children: [
                    /* Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              Row(children: [
                Text(language(context, "Ac service"),
                    style: appCss.dmDenseMedium14
                        .textColor(appColor(context).lightText)),
                const HSpace(Sizes.s5),
                SvgPicture.asset(eSvgAssets.about,
                        fit: BoxFit.scaleDown,
                        colorFilter: ColorFilter.mode(
                            appColor(context).primary,
                            BlendMode.srcIn))
                    .inkWell(onTap: () => value.onServiceDetail(context))
              ]),
              Text("\$22.00",
                  style: appCss.dmDenseMedium14
                      .textColor(appColor(context).darkText))
            ]),
            const VSpace(Sizes.s20),
            BillRowCommon(title: translations!.perServiceCharge, price: "\$12.00"),
            const BillRowCommon(title: "Furnishing", price: "\$12.00")
                .paddingSymmetric(vertical: Insets.i20),
            const BillRowCommon(title: "Cleaning package", price: "\$15.23"),
            const VSpace(Sizes.s20),
            const BillRowCommon(title: "Subtotal", price: "+\$52.46"),
            BillRowCommon(
                    color: appColor(context).red,
                    title: "Coupon Discount (24% off)",
                    price: "-\$2.00")
                .paddingSymmetric(vertical: Insets.i20),
            BillRowCommon(
                title: translations!.tax,
                color: appColor(context).online,
                price: "+\$2.46"),*/
                    /*  if (value.checkoutModel!.services != null)
                      Column(
                        children: [
                          ...value.checkoutModel!.services!
                              .asMap()
                              .entries
                              .map((e) {
                            int total = getTotalRequiredServiceMan(
                                value.cartList, e.value.serviceId, false);
                            return e.value.total!.totalServicemen! > 1
                                ? Row(
                                    mainAxisAlignment:
                                        MainAxisAlignment.spaceBetween,
                                    children: [
                                        Row(children: [
                                          Text(
                                                  getName(value.cartList,
                                                      e.value.serviceId, false),
                                                  style: appCss.dmDenseMedium14
                                                      .textColor(
                                                          appColor(context)
                                                              .lightText))
                                              .width(Sizes.s200),
                                          const HSpace(Sizes.s5),
                                          SvgPicture.asset(eSvgAssets.about,
                                                  fit: BoxFit.scaleDown,
                                                  colorFilter: ColorFilter.mode(
                                                      appColor(context).primary,
                                                      BlendMode.srcIn))
                                              .inkWell(
                                                  onTap: () => value
                                                      .onServiceDetail(context,
                                                          data: e.value,
                                                          totalServiceman:
                                                              total))
                                        ]),
                                        Text(
                                            "${getSymbol(context)}${(currency(context).currencyVal * e.value.price!).toStringAsFixed(2)}",
                                            style: appCss.dmDenseMedium14
                                                .textColor(
                                                    appColor(context).darkText))
                                      ]).paddingOnly(
                                    bottom: Insets.i10,
                                    right: Insets.i15,
                                    left: Insets.i15)
                                : BillRowCommon(
                                        title: getName(value.cartList,
                                            e.value.serviceId, false),
                                        color: appColor(context).darkText,
                                        price:
                                            "${getSymbol(context)}${(currency(context).currencyVal * e.value.price! /*  total!.subtotal! */).toStringAsFixed(2)}")
                                    .paddingOnly(bottom: Insets.i10);
                          })
                        ],
                      ), */
                    if (value.checkoutModel!.servicesPackage != null)
                      Column(
                        children: [
                          ...value.checkoutModel!.servicesPackage!
                              .asMap()
                              .entries
                              .map((e) => Column(
                                    children: e.value.services!
                                        .asMap()
                                        .entries
                                        .map((ser) {
                                      int total = getTotalRequiredServiceMan(
                                          value.cartList,
                                          ser.value.serviceId,
                                          true);
                                      return ser.value.total!.totalServicemen! >
                                              1
                                          ? Row(
                                              mainAxisAlignment:
                                                  MainAxisAlignment
                                                      .spaceBetween,
                                              children: [
                                                  Row(children: [
                                                    Text(
                                                        getName(
                                                            value.cartList,
                                                            ser.value.serviceId,
                                                            true),
                                                        style: appCss
                                                            .dmDenseMedium14
                                                            .textColor(appColor(
                                                                    context)
                                                                .lightText)),
                                                    const HSpace(Sizes.s5),
                                                    SvgPicture.asset(
                                                            eSvgAssets.about,
                                                            fit: BoxFit
                                                                .scaleDown,
                                                            colorFilter:
                                                                ColorFilter.mode(
                                                                    appColor(
                                                                            context)
                                                                        .primary,
                                                                    BlendMode
                                                                        .srcIn))
                                                        .inkWell(
                                                            onTap: () => value
                                                                .onServiceDetail(
                                                                    context,
                                                                    packageServices:
                                                                        ser
                                                                            .value,
                                                                    totalServiceman:
                                                                        total))
                                                  ]),
                                                  Text(
                                                      "${getSymbol(context)}${(currency(context).currencyVal * ser.value.total!.subtotal!).toStringAsFixed(2)}",
                                                      style: appCss
                                                          .dmDenseMedium14
                                                          .textColor(
                                                              appColor(context)
                                                                  .darkText))
                                                ]).paddingOnly(
                                              bottom: Insets.i10,
                                              right: Insets.i15,
                                              left: Insets.i15)
                                          : BillRowCommon(
                                                  title: getName(
                                                      value.cartList,
                                                      ser.value.serviceId,
                                                      true),
                                                  color: appColor(context)
                                                      .darkText,
                                                  price: symbolPosition
                                                      ? "${getSymbol(context)}${(currency(context).currencyVal * ser.value.total!.subtotal!).toStringAsFixed(2)}"
                                                      : "${(currency(context).currencyVal * ser.value.total!.subtotal!).toStringAsFixed(2)}${getSymbol(context)}")
                                              .paddingOnly(bottom: Insets.i10);
                                    }).toList(),
                                  ))
                        ],
                      ),
                    if (value.cartList.isNotEmpty &&
                        value.checkoutModel!.services!.isNotEmpty)
                      Column(
                        children: value.cartList.map((item) {
                          final service = item.serviceList;
                          final price = service.price ?? 0;
                          final discount = service.discount ?? 0;
                          final title = service.title ?? "Service";

                          final discountedAmount =
                              discount > 0 ? (price * discount / 100) : 0;

                          return Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    title,
                                    style: appCss.dmDenseMedium14
                                        .textColor(appColor(context).lightText),
                                  ).width(Sizes.s200),
                                  Text(
                                    symbolPosition
                                        ? "${getSymbol(context)}${price.toStringAsFixed(2)}"
                                        : "${price.toStringAsFixed(2)}${getSymbol(context)}",
                                    style: appCss.dmDenseMedium14
                                        .textColor(appColor(context).darkText),
                                  ),
                                ],
                              ).paddingOnly(
                                  bottom: Insets.i10,
                                  left: Insets.i15,
                                  right: Insets.i15),

                              if (discount > 0)
                                BillRowCommon(
                                  title:
                                      "Discount (${discount.toStringAsFixed(0)}%)",
                                  color: appColor(context).red,
                                  price: symbolPosition
                                      ? "-${getSymbol(context)}${discountedAmount.toStringAsFixed(2)}"
                                      : "-${discountedAmount.toStringAsFixed(2)}${getSymbol(context)}",
                                ).paddingOnly(bottom: Insets.i10),

                              // ✅ Add-On Display
                              if (service.selectedAdditionalServices != null &&
                                  service
                                      .selectedAdditionalServices!.isNotEmpty)
                                ...service.selectedAdditionalServices!
                                    .map((addon) {
                                  final addonTitle = addon.title ?? "Add-On";
                                  final addonPrice = addon.price ?? 0;
                                  final totalPrice = addonPrice * addon.qty;
                                  /*  final toatalPrice = addon.totalPrice ?? 0;
 */
                                  return BillRowCommon(
                                    title:
                                        "$addonTitle (\$${addonPrice} × ${addon.qty})",
                                    color: appColor(context).green,
                                    price: symbolPosition
                                        ? "+${getSymbol(context)}${totalPrice.toStringAsFixed(2)}"
                                        : "+${totalPrice.toStringAsFixed(2)}${getSymbol(context)}",
                                  ).paddingOnly(bottom: Insets.i10);
                                }).toList(),
                              // Step 2: Loop and render each tax
                              // if (value.checkoutModel!.services![0].taxes !=
                              //         null &&
                              //     value.checkoutModel!.services![0].taxes!
                              //         .isNotEmpty)
                              //   ...value.checkoutModel!.services![0].taxes!
                              //       .asMap()
                              //       .entries
                              //       .map((entry) {
                              //     int index = entry.key;
                              //     var tax = entry.value;
                              //     double rate = tax.rate ?? 0;
                              //
                              //     return BillRowCommon(
                              //             title:
                              //                 "${translations!.tax} (${rate.toStringAsFixed(0)}%)",
                              //             color: appColor(context).online,
                              //             price: symbolPosition
                              //                 ? "+${getSymbol(context)}${(entry.value.amount)}"
                              //                 : "+${(entry.value.amount)}${getSymbol(context)}")
                              //         .paddingOnly(bottom: Insets.i10);
                              //   }),
                              // BillRowCommon(
                              //         title:
                              //             "${translations!.tax}(${value.checkoutModel!.services![0].taxes?[0].rate}%)",
                              //         color: appColor(context).online,
                              //         price:
                              //             "+${getSymbol(context)}${(currency(context).currencyVal * value.checkoutModel!.total!.tax!).toStringAsFixed(2)}")
                              //     .paddingOnly(bottom: Insets.i10),
                            ],
                          );
                        }).toList(),
                      ),
                    BillRowCommon(
                            title: translations!.platformFees,
                            color: appColor(context).online,
                            price: symbolPosition
                                ? "+${getSymbol(context)}${value.checkoutModel!.total!.platformFees != null ? (currency(context).currencyVal * value.checkoutModel!.total!.platformFees!).toStringAsFixed(2) : "0.00"}"
                                : "+${value.checkoutModel!.total!.platformFees != null ? (currency(context).currencyVal * value.checkoutModel!.total!.platformFees!).toStringAsFixed(2) : "0.00"}${getSymbol(context)}")
                        .paddingOnly(bottom: Insets.i10),
                    if (value.checkoutModel!.total!.couponTotalDiscount !=
                            null &&
                        value.checkoutModel!.total!.couponTotalDiscount! > 0)
                      // Display coupon discount only if it's greater than 0
                      BillRowCommon(
                              title: translations!.coupons,
                              color: appColor(context).red,
                              price: symbolPosition
                                  ? "-${getSymbol(context)}${(currency(context).currencyVal * value.checkoutModel!.total!.couponTotalDiscount!).toStringAsFixed(2)}"
                                  : "-${(currency(context).currencyVal * value.checkoutModel!.total!.couponTotalDiscount!).toStringAsFixed(2)}${getSymbol(context)}")
                          .paddingOnly(bottom: Insets.i10),
                    // BillRowCommon(
                    //         title: translations!.subtotal,
                    //         price:
                    //             "${getSymbol(context)}${(currency(context).currencyVal * value.checkoutModel!.total!.subtotal!).toStringAsFixed(2)}")
                    //     .paddingOnly(bottom: Insets.i25),

                    /* ...value.checkoutModel!.servicesPackage!.asMap().entries.map((e) {
              return BillRowCommon(
                  title:" e.value.title",
                  color: appColor(context).online,
                  price: "${getSymbol(context)}${(currency(context).currencyVal*int.parse(e.value.subtotal.toString()).toStringAsFixed(2)())}").paddingSymmetric(vertical: Insets.i20);
            }).toList()*/
                  ]),
                  Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                        Text(language(context, translations!.totalAmount),
                            style: appCss.dmDenseMedium14
                                .textColor(appColor(context).darkText)),
                        Text(
                            symbolPosition
                                ? "${getSymbol(context)}${(currency(context).currencyVal * value.checkoutModel!.total!.total!).toStringAsFixed(2)}"
                                : "${(currency(context).currencyVal * value.checkoutModel!.total!.total!).toStringAsFixed(2)}${getSymbol(context)}",
                            style: appCss.dmDenseBold16
                                .textColor(appColor(context).primary))
                      ])
                      .paddingSymmetric(horizontal: Insets.i15)
                      .paddingOnly(bottom: Insets.i5, top: Insets.i10)
                ]))
        : Container();
  }
}
