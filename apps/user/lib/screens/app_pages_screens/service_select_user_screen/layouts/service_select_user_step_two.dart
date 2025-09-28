import 'dart:developer';

import 'package:intl/intl.dart';

import '../../../../config.dart';

class ServiceSelectUserStepTwo extends StatelessWidget {
  const ServiceSelectUserStepTwo({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer2<ServiceSelectProvider, SlotBookingProvider>(
        builder: (context1, value, slot, child) {
      // log("value.servicesCart?.discount ${value.servicesCart?.serviceRate}");
      final currencyValue = currency(context).currencyVal;

      final price = value.servicesCart?.price ?? 0;
      final requiredServicemen = value.servicesCart?.requiredServicemen ?? 1;
      final selectedServicemen =
          value.servicesCart?.selectedRequiredServiceMan ?? requiredServicemen;
      final serviceRate = value.servicesCart?.serviceRate ?? 0;

      double totalPrice =
          currencyValue * ((price / requiredServicemen) * selectedServicemen);
      dynamic baseRate = value.servicesCart?.serviceRate;
      double difference = totalPrice - baseRate;

      String formattedDifference = symbolPosition
          ? "${getSymbol(context)}${difference.toStringAsFixed(2)}"
          : "${difference.toStringAsFixed(2)}${getSymbol(context)}";
      return Consumer<LocationProvider>(builder: (context2, loc, child) {
        return Stack(
          children: [
            SingleChildScrollView(
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                        language(context, translations!.billDetails)
                            .toUpperCase(),
                        style: appCss.dmDenseSemiBold16
                            .textColor(appColor(context).primary)),
                    const VSpace(Sizes.s15),
                    Text(language(context, translations!.selectedServicemen),
                        style: appCss.dmDenseMedium14
                            .textColor(appColor(context).darkText)),
                    loc.addressList.isEmpty
                        ? Row(
                            mainAxisAlignment: MainAxisAlignment.start,
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                                SvgPicture.asset(eSvgAssets.about,
                                    fit: BoxFit.scaleDown),
                                const HSpace(Sizes.s10),
                                Expanded(
                                    child: Column(children: [
                                  Text(
                                      language(context,
                                          translations!.asYouPreviously),
                                      overflow: TextOverflow.fade,
                                      style: appCss.dmDenseRegular14.textColor(
                                          appColor(context).darkText))
                                ]))
                              ]).paddingAll(Insets.i15).boxShapeExtension(
                            color: appColor(context).fieldCardBg)
                        : Column(
                            children: value.servicesCart!.selectedServiceMan!
                                .asMap()
                                .entries
                                .map<Widget>((e) => ServicemanLayout(
                                      isEdit: false,
                                      image: e.value.media != null &&
                                              e.value.media!.isNotEmpty
                                          ? e.value.media![0].originalUrl!
                                          : null,
                                      title: e.value.name,
                                      rate: e.value.reviewRatings != null
                                          ? e.value.reviewRatings.toString()
                                          : "0",
                                      exp: e.value.experienceDuration != null
                                          ? e.value.experienceDuration
                                              .toString()
                                          : "0",
                                      expYear:
                                          e.value.experienceInterval ?? "Year",
                                      editTap: () => route.pushNamed(context,
                                          routeName.servicemanListScreen,
                                          arg: {
                                            "providerId":
                                                value.servicesCart!.userId,
                                            "requiredServiceman": value
                                                .servicesCart!
                                                .selectedRequiredServiceMan,
                                            "selectedServiceMan": value
                                                .servicesCart!
                                                .selectedServiceMan,
                                          }).then((val) {
                                        if (val != null) {
                                          value.servicesCart!
                                              .selectedServiceMan = val;
                                          value.notifyListeners();
                                        } else {
                                          value.servicesCart!
                                              .selectedServiceMan = null;
                                        }
                                      }),
                                      tileTap: () => route.pushNamed(context,
                                          routeName.servicemanDetailScreen,
                                          arg: e.value.id),
                                    ).paddingOnly(top: Insets.i10))
                                .toList(),
                          ),
                    const VSpace(Sizes.s25),
                    Text(language(context, translations!.bookedDateTime),
                        style: appCss.dmDenseMedium14
                            .textColor(appColor(context).darkText)),
                    const VSpace(Sizes.s10),
                    BookedDateTimeLayout(
                        onTap: () => value.onTapDate(context),
                        date: DateFormat('dd MMMM, yyyy')
                            .format(value.servicesCart!.serviceDate!),
                        time:
                            "${language(context, translations!.at)} ${DateFormat('hh:mm').format(value.servicesCart!.serviceDate!)} ${value.servicesCart!.selectedDateTimeFormat ?? DateFormat('aa').format(value.servicesCart!.serviceDate!)}"),
                    const VSpace(Sizes.s25),
                    BillSummaryLayout(
                        balance: symbolPosition
                            ? "${getSymbol(context)}${(currency(context).currencyVal * (userModel!.wallet != null ? userModel!.wallet!.balance! : 0.0)).toStringAsFixed(2)}"
                            : "${(currency(context).currencyVal * (userModel!.wallet != null ? userModel!.wallet!.balance! : 0.0)).toStringAsFixed(2)}${getSymbol(context)}"),
                    const VSpace(Sizes.s10),
                    Container(
                        width: MediaQuery.of(context).size.width,
                        decoration: BoxDecoration(
                            image: DecorationImage(
                                image: AssetImage(isDark(context)
                                    ? eImageAssets.pendingBillBgDark
                                    : eImageAssets.pendingBillBg),
                                fit: BoxFit.fill)),
                        child: Column(children: [
                          Column(children: [
                            BillRowCommon(
                                title: translations!.perServiceCharge,
                                price: symbolPosition
                                    ? "${getSymbol(context)}${slot.step2Data?.perServicemanCharge}"
                                    : "${slot.step2Data?.perServicemanCharge}${getSymbol(context)}"),
                            BillRowCommon(
                                    title: symbolPosition
                                        ? "${slot.step2Data?.requiredServicemen} ${language(context, translations!.serviceman)} "
                                            "(${getSymbol(context)}${slot.step2Data?.perServicemanCharge} × "
                                            "${value.servicesCart?.requiredServicemen ?? 0})"
                                        : "${slot.step2Data?.requiredServicemen} ${language(context, translations!.serviceman)} "
                                            "(${slot.step2Data?.perServicemanCharge}${getSymbol(context)} × "
                                            "${value.servicesCart?.requiredServicemen ?? 0})",
                                    price: symbolPosition
                                        ? "${getSymbol(context)}${slot.step2Data?.totalServicemenCharge}"
                                        : "${slot.step2Data?.totalServicemenCharge}${getSymbol(context)}")
                                .marginOnly(top: Insets.i20),
                            if (value.servicesCart!.discount != null &&
                                value.servicesCart!.discount != 0)
                              BillRowCommon(
                                      color: appColor(context).red,
                                      title:
                                          "${translations!.appliedDiscount} (${slot.step2Data?.discountPercent}%)",
                                      price: (value.servicesCart?.discount == 0)
                                          ? "0"
                                          : "-${slot.step2Data?.discountAmount}")
                                  .marginOnly(top: Insets.i20),
                            if (value.servicesCart!
                                        .selectedAdditionalServices !=
                                    null &&
                                value.servicesCart!.selectedAdditionalServices
                                    .isNotEmpty)
                              const VSpace(Sizes.s20),
                            if (value.servicesCart!
                                        .selectedAdditionalServices !=
                                    null &&
                                value.servicesCart!.selectedAdditionalServices
                                    .isNotEmpty)
                              ...value.servicesCart!.selectedAdditionalServices!
                                  .map((charge) {
                                return BillRowCommon(
                                  title: charge.title,
                                  color: appColor(context).green,
                                  price: symbolPosition
                                      ? "+ ${getSymbol(context)}${(currency(context).currencyVal * charge.price)}"
                                      : "+ ${(currency(context).currencyVal * charge.price)}${getSymbol(context)}",
                                ).padding(bottom: Insets.i10);
                              }),
                            // BillRowCommon(
                            //     title: translations!.tax,
                            //     price: language(
                            //         context, translations!.costAtCheckout))
                          ]).paddingSymmetric(
                            vertical: Insets.i20,
                          ),
                          // VSpace((value.servicesCart!
                          //             .selectedRequiredServiceMan!) >
                          //         1
                          //     ? Sizes.s10
                          //     : Sizes.s8),
                          Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                    language(
                                        context, translations!.totalAmount),
                                    style: appCss.dmDenseMedium14
                                        .textColor(appColor(context).darkText)),
                                if (value.servicesCart!
                                            .selectedAdditionalServices !=
                                        null &&
                                    value.servicesCart!
                                        .selectedAdditionalServices.isNotEmpty)
                                  Text(
                                      symbolPosition
                                          ? "${getSymbol(context)}${slot.step2Data?.totalAmount}"
                                          : "${slot.step2Data?.totalAmount}${getSymbol(context)}",
                                      style: appCss.dmDenseBold16.textColor(
                                          appColor(context).primary)),
                                if (value.servicesCart!
                                            .selectedAdditionalServices ==
                                        null /*||
                                value.servicesCart!.selectedAdditionalServices
                                    .isNotEmpty*/
                                    )
                                  Text(
                                      value.servicesCart != null
                                          ? symbolPosition
                                              ? "${getSymbol(context)}${(currency(context).currencyVal * (value.servicesCart!.serviceRate!)).toStringAsFixed(2)}"
                                              : "${(currency(context).currencyVal * (value.servicesCart!.serviceRate!)).toStringAsFixed(2)}${getSymbol(context)}"
                                          : "",
                                      style: appCss.dmDenseBold16.textColor(
                                          appColor(context).primary)),
                              ]).paddingSymmetric(
                              vertical: Insets.i20, horizontal: Insets.i15)
                        ])),
                    const DottedLines().paddingSymmetric(vertical: Insets.i20),
                    const CancellationPolicyLayout(),
                    const DisclaimerLayout(),
                    const VSpace(Sizes.s100),
                  ]).paddingSymmetric(horizontal: Insets.i20),
            ),
            ButtonCommon(
              title: translations!.confirmBooking!,
              onTap: () => value.addToCart(context),
              margin: Insets.i20,
            )
                .marginOnly(bottom: Insets.i20)
                .decorated(color: appColor(context).whiteBg)
                .alignment(Alignment.bottomCenter)
          ],
        );
      });
    });
  }
}
