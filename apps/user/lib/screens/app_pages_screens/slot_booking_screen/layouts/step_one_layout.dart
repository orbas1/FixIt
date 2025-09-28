// import 'dart:async';
// import 'dart:developer';

// import 'package:intl/intl.dart';

// import '../../../../config.dart';
// import '../../services_details_screen/layouts/add_on_service_card.dart';

// class StepOneLayout extends StatefulWidget {
//   const StepOneLayout({super.key});

//   @override
//   State<StepOneLayout> createState() => _StepOneLayoutState();
// }

// class _StepOneLayoutState extends State<StepOneLayout>
//     with SingleTickerProviderStateMixin {
//   @override
//   Widget build(BuildContext context) {
//     return Consumer<SlotBookingProvider>(builder: (context1, value, child) {
//       return Consumer<LocationProvider>(builder: (context2, loc, child) {
//         // log("value.servicesCart${value.servicesCart!.media}");
//         return SafeArea(
//             child: Stack(alignment: Alignment.bottomCenter, children: [
//           value.servicesCart == null
//               ? Container()
//               : ListView(controller: value.scrollController, children: [
//                   Text(
//                           language(context, translations!.selectDateTime)
//                               .toUpperCase(),
//                           style: appCss.dmDenseSemiBold16
//                               .textColor(appColor(context).primary))
//                       .paddingSymmetric(horizontal: Insets.i20),
//                   const VSpace(Sizes.s15),
//                   loc.addressList.isEmpty && value.address == null
//                       ? Column(
//                           crossAxisAlignment: CrossAxisAlignment.start,
//                           children: [
//                               Text(language(context, translations!.location),
//                                   overflow: TextOverflow.ellipsis,
//                                   style: appCss.dmDenseMedium14
//                                       .textColor(appColor(context).darkText)),
//                               const VSpace(Sizes.s5),
//                               ButtonCommon(
//                                   title: language(
//                                       context, translations!.addLocation),
//                                   color: appColor(context).whiteBg,
//                                   onTap: () => value.addNewLoc(context),
//                                   fontColor: appColor(context).primary,
//                                   borderColor: appColor(context).primary)
//                             ]).marginOnly(
//                           bottom: Insets.i10,
//                           right: Insets.i20,
//                           left: Insets.i20)
//                       : LocationChangeRowCommon(
//                           title: language(
//                               context,
//                               value.address == null
//                                   ? translations!.addLocation
//                                   : translations!.change),
//                           onTap: () => route
//                                   .pushNamed(context, routeName.myLocation,
//                                       arg: true)
//                                   .then((e) {
//                                 log("EE :$e");
//                                 if (e != null) {
//                                   value.onChangeLocation(context, e);
//                                 }
//                               })),
//                   const VSpace(Sizes.s10),
//                   if (value.address != null)
//                     LocationLayout(
//                       data: value.address,
//                       isPrimaryAnTapLayout: false,
//                     ),
//                   if (value.servicesCart!.selectedAdditionalServices != null &&
//                       value
//                           .servicesCart!.selectedAdditionalServices!.isNotEmpty)
//                     Column(
//                       crossAxisAlignment: CrossAxisAlignment.start,
//                       children: [
//                         Text(language(context, translations!.addOns),
//                             style: appCss.dmDenseMedium14
//                                 .textColor(appColor(context).darkText)),
//                         const VSpace(Sizes.s10),
//                         /*    Text(
//                             "data${value.servicesCart!.selectedAdditionalServices}"), */
//                         ...value.servicesCart!.selectedAdditionalServices!
//                             .asMap()
//                             .entries
//                             .map((e) => AddOnServiceCard(
//                                   deleteTap: () =>
//                                       value.deleteJobRequestConfirmation(
//                                           context, this, e.key),
//                                   index: e.key,
//                                   isDelete: true,
//                                   additionalServices: e.value,
//                                   additionalServicesLength: value.servicesCart!
//                                           .selectedAdditionalServices!.length -
//                                       1,
//                                 ))
//                       ],
//                     ).padding(horizontal: Insets.i20, bottom: Sizes.s20),
//                   value.servicesCart != null &&
//                           value.servicesCart!.serviceDate != null
//                       ? Column(
//                           crossAxisAlignment: CrossAxisAlignment.start,
//                           children: [
//                             Text(
//                                 language(context, translations!.bookedDateTime),
//                                 style: appCss.dmDenseMedium14
//                                     .textColor(appColor(context).darkText)),
//                             const VSpace(Sizes.s10),
//                             BookedDateTimeLayout(
//                                 onTap: () {
//                                   value.servicesCart!.serviceDate = null;
//                                   value.servicesCart!.selectedDateTimeFormat =
//                                       null;
//                                   value.notifyListeners();
//                                 },
//                                 date: DateFormat('dd MMMM, yyyy')
//                                     .format(value.servicesCart!.serviceDate!),
//                                 time:
//                                     "At ${DateFormat('hh:mm').format(value.servicesCart!.serviceDate!)} ${value.servicesCart!.selectedDateTimeFormat ?? DateFormat('aa').format(value.servicesCart!.serviceDate!)}"),
//                           ],
//                         ).paddingSymmetric(horizontal: Insets.i20)
//                       : Column(
//                           crossAxisAlignment: CrossAxisAlignment.start,
//                           children: [
//                             Text(
//                               language(context, translations!.dateTime),
//                               style: appCss.dmDenseMedium14
//                                   .textColor(appColor(context).darkText),
//                             ).paddingSymmetric(horizontal: Insets.i20),
//                             Text(
//                               "${language(context, translations!.thisServiceWill)} ${value.servicesCart!.duration} ${value.servicesCart!.durationUnit ?? "hours"}",
//                               style: appCss.dmDenseMedium12
//                                   .textColor(appColor(context).lightText),
//                             ).paddingSymmetric(horizontal: Insets.i20),
//                             const VSpace(Sizes.s10),
//                             Column(
//                               children: [
//                                 Row(
//                                   mainAxisAlignment:
//                                       MainAxisAlignment.spaceBetween,
//                                   children: [
//                                     Text(
//                                       language(context,
//                                           translations!.customDateTime),
//                                       style: appCss.dmDenseRegular14.textColor(
//                                         value.selectIndex == 0
//                                             ? appColor(context).primary
//                                             : appColor(context).darkText,
//                                       ),
//                                     ),
//                                     CommonRadio(
//                                       onTap: () =>
//                                           value.onDateTimeSelect(context, 0),
//                                       index: 0,
//                                       selectedIndex: value.selectIndex,
//                                     ),
//                                   ],
//                                 ).inkWell(
//                                     onTap: () =>
//                                         value.onDateTimeSelect(context, 0)),
//                                 if (value.selectIndex == 0)
//                                   const VSpace(Sizes.s20),
//                                 if (value.selectIndex == 0)
//                                   TimeSlotLayout(
//                                     title: translations!.dateTime,
//                                     onTap: () =>
//                                         value.onProviderDateTimeSelect(context),
//                                   ),
//                                 Divider(
//                                   height: 1,
//                                   thickness: 1,
//                                   color: appColor(context).stroke,
//                                 ).paddingSymmetric(vertical: Insets.i20),
//                                 Row(
//                                   mainAxisAlignment:
//                                       MainAxisAlignment.spaceBetween,
//                                   children: [
//                                     Text(
//                                       language(
//                                           context, translations!.asPerProvider),
//                                       style: appCss.dmDenseRegular14.textColor(
//                                         value.selectIndex == 1
//                                             ? appColor(context).primary
//                                             : appColor(context).darkText,
//                                       ),
//                                     ),
//                                     CommonRadio(
//                                       onTap: () =>
//                                           value.onDateTimeSelect(context, 1),
//                                       index: 1,
//                                       selectedIndex: value.selectIndex,
//                                     ),
//                                   ],
//                                 ).inkWell(
//                                     onTap: () =>
//                                         value.onDateTimeSelect(context, 1)),
//                                 if (value.selectIndex == 1)
//                                   const VSpace(Sizes.s20),
//                                 // Conditionally show TimeSlotLayout when "As Per Provider" is selected
//                                 if (value.selectIndex == 1)
//                                   TimeSlotLayout(
//                                     title: translations!.dateTime,
//                                     onTap: () =>
//                                         value.onProviderDateTimeSelect(context),
//                                   ),
//                               ],
//                             )
//                                 .paddingSymmetric(
//                                     vertical: Insets.i20,
//                                     horizontal: Insets.i15)
//                                 .decorated(
//                                   color: appColor(context).whiteBg,
//                                   borderRadius:
//                                       BorderRadius.circular(AppRadius.r8),
//                                   boxShadow: [
//                                     BoxShadow(
//                                       color: appColor(context)
//                                           .darkText
//                                           .withOpacity(0.06),
//                                       blurRadius: 12,
//                                       spreadRadius: 0,
//                                       offset: const Offset(0, 2),
//                                     ),
//                                   ],
//                                   border: Border.all(
//                                       color: appColor(context).stroke),
//                                 )
//                                 .paddingSymmetric(horizontal: Insets.i20),
//                           ],
//                         ),
//                   const VSpace(Sizes.s15),
//                   const ServicemanQuantityLayout(),
//                   if ((value.servicesCart!.selectedRequiredServiceMan!) >
//                       (value.servicesCart!.requiredServicemen ?? 1))
//                     Text(language(context, translations!.youWillOnly),
//                             style: appCss.dmDenseMedium12
//                                 .textColor(appColor(context).red))
//                         .paddingOnly(bottom: Insets.i25)
//                         .paddingSymmetric(horizontal: Insets.i20),
//                   CustomMessageLayout(
//                       onTap: () {
//                         Timer(const Duration(milliseconds: 500), () {
//                           log("value.scrollController.position.maxScrollExtent:${value.scrollController.position.maxScrollExtent}");
//                           value.scrollController.jumpTo(
//                               value.scrollController.position.maxScrollExtent);
//                           value.notifyListeners();
//                         });

//                         value.notifyListeners();
//                       },
//                       controller: value.txtNote,
//                       focusNode: value.noteFocus),
//                   const VSpace(Sizes.s100)
//                 ]),
//           ButtonCommon(
//                   isLoading: value.isNextLoading,
//                   title: value.buttonName(context),
//                   margin: Insets.i20,
//                   onTap: () => value.onTapNext(context))
//               .marginOnly(bottom: Insets.i20)
//               .backgroundColor(appColor(context).whiteBg)
//         ]));
//       });
//     });
//   }
// }

import 'dart:async';
import 'dart:developer';

import 'package:intl/intl.dart';

import '../../../../config.dart';
import '../../services_details_screen/layouts/add_on_service_card.dart';

class StepOneLayout extends StatefulWidget {
  const StepOneLayout({super.key});

  @override
  State<StepOneLayout> createState() => _StepOneLayoutState();
}

class _StepOneLayoutState extends State<StepOneLayout>
    with SingleTickerProviderStateMixin {
  @override
  Widget build(BuildContext context) {
    return Consumer2<SlotBookingProvider, ServicesDetailsProvider>(
        builder: (context1, value, serviceCtrl, child) {
      return Consumer<LocationProvider>(builder: (context2, loc, child) {
        // log("value.servicesCart${value.servicesCart!.media}");
        return SafeArea(
            child: Stack(alignment: Alignment.bottomCenter, children: [
          value.servicesCart == null
              ? Container()
              : ListView(controller: value.scrollController, children: [
                  Text(
                          language(context, translations!.selectDateTime)
                              .toUpperCase(),
                          style: appCss.dmDenseSemiBold16
                              .textColor(appColor(context).primary))
                      .paddingSymmetric(horizontal: Insets.i20),
                  const VSpace(Sizes.s15),
                  loc.addressList.isEmpty && value.address == null
                      ? Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                              Text(language(context, translations!.location),
                                  overflow: TextOverflow.ellipsis,
                                  style: appCss.dmDenseMedium14
                                      .textColor(appColor(context).darkText)),
                              const VSpace(Sizes.s5),
                              ButtonCommon(
                                  title: language(
                                      context, translations!.addLocation),
                                  color: appColor(context).whiteBg,
                                  onTap: () => value.addNewLoc(context),
                                  fontColor: appColor(context).primary,
                                  borderColor: appColor(context).primary)
                            ]).marginOnly(
                          bottom: Insets.i10,
                          right: Insets.i20,
                          left: Insets.i20)
                      : LocationChangeRowCommon(
                          title: language(
                              context,
                              value.address == null
                                  ? translations!.addLocation
                                  : translations!.change),
                          onTap: () => route
                                  .pushNamed(context, routeName.myLocation,
                                      arg: true)
                                  .then((e) {
                                log("EE :$e");
                                if (e != null) {
                                  value.onChangeLocation(context, e);
                                }
                              })),
                  const VSpace(Sizes.s10),
                  if (value.address != null)
                    LocationLayout(
                      data: value.address,
                      isPrimaryAnTapLayout: false,
                    ),
                  if (value.servicesCart!.selectedAdditionalServices != null &&
                      value
                          .servicesCart!.selectedAdditionalServices!.isNotEmpty)
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(language(context, translations!.addOns),
                            style: appCss.dmDenseMedium14
                                .textColor(appColor(context).darkText)),
                        const VSpace(Sizes.s10),
                        /*    Text(
                            "data${value.servicesCart!.selectedAdditionalServices}"), */
                        ...value.servicesCart!.selectedAdditionalServices!
                            .asMap()
                            .entries
                            .map((e) => AddOnServiceCard(
                                  deleteTap: () =>
                                      value.deleteJobRequestConfirmation(
                                          context, this, e.key),
                                  index: e.key,
                                  isDelete: true,
                                  incrementTap: () =>
                                      serviceCtrl.increment(e.key),
                                  decrementTap: () =>
                                      serviceCtrl.decrement(e.key),
                                  additionalServices: e.value,
                                  additionalServicesLength: value.servicesCart!
                                          .selectedAdditionalServices!.length -
                                      1,
                                ))
                      ],
                    ).padding(horizontal: Insets.i20, bottom: Sizes.s20),
                  value.servicesCart != null &&
                          value.servicesCart!.serviceDate != null
                      ? Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                                language(context, translations!.bookedDateTime),
                                style: appCss.dmDenseMedium14
                                    .textColor(appColor(context).darkText)),
                            const VSpace(Sizes.s10),
                            BookedDateTimeLayout(
                                onTap: () {
                                  value.servicesCart!.serviceDate = null;
                                  value.servicesCart!.selectedDateTimeFormat =
                                      null;
                                  value.notifyListeners();
                                },
                                date: DateFormat('dd MMMM, yyyy')
                                    .format(value.servicesCart!.serviceDate!),
                                time:
                                    "At ${DateFormat('hh:mm').format(value.servicesCart!.serviceDate!)} ${value.servicesCart!.selectedDateTimeFormat ?? DateFormat('aa').format(value.servicesCart!.serviceDate!)}"),
                          ],
                        ).paddingSymmetric(horizontal: Insets.i20)
                      : Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              language(context, translations!.dateTime),
                              style: appCss.dmDenseMedium14
                                  .textColor(appColor(context).darkText),
                            ).paddingSymmetric(horizontal: Insets.i20),
                            Text(
                              "${language(context, translations!.thisServiceWill)} ${value.servicesCart!.duration} ${value.servicesCart!.durationUnit ?? "hours"}",
                              style: appCss.dmDenseMedium12
                                  .textColor(appColor(context).lightText),
                            ).paddingSymmetric(horizontal: Insets.i20),
                            const VSpace(Sizes.s10),
                            Column(
                              children: [
                                Row(
                                  mainAxisAlignment:
                                      MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      language(context,
                                          translations!.customDateTime),
                                      style: appCss.dmDenseRegular14.textColor(
                                        value.selectIndex == 0
                                            ? appColor(context).primary
                                            : appColor(context).darkText,
                                      ),
                                    ),
                                    CommonRadio(
                                      onTap: () =>
                                          value.onDateTimeSelect(context, 0),
                                      index: 0,
                                      selectedIndex: value.selectIndex,
                                    ),
                                  ],
                                ).inkWell(
                                    onTap: () =>
                                        value.onDateTimeSelect(context, 0)),
                                if (value.selectIndex == 0)
                                  const VSpace(Sizes.s20),
                                if (value.selectIndex == 0)
                                  TimeSlotLayout(
                                    title: translations!.dateTime,
                                    onTap: () =>
                                        value.onProviderDateTimeSelect(context),
                                  ),
                                Divider(
                                  height: 1,
                                  thickness: 1,
                                  color: appColor(context).stroke,
                                ).paddingSymmetric(vertical: Insets.i20),
                                Row(
                                  mainAxisAlignment:
                                      MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      language(
                                          context, translations!.asPerProvider),
                                      style: appCss.dmDenseRegular14.textColor(
                                        value.selectIndex == 1
                                            ? appColor(context).primary
                                            : appColor(context).darkText,
                                      ),
                                    ),
                                    CommonRadio(
                                      onTap: () =>
                                          value.onDateTimeSelect(context, 1),
                                      index: 1,
                                      selectedIndex: value.selectIndex,
                                    ),
                                  ],
                                ).inkWell(
                                    onTap: () =>
                                        value.onDateTimeSelect(context, 1)),
                                if (value.selectIndex == 1)
                                  const VSpace(Sizes.s20),
                                // Conditionally show TimeSlotLayout when "As Per Provider" is selected
                                if (value.selectIndex == 1)
                                  TimeSlotLayout(
                                    title: translations!.dateTime,
                                    onTap: () =>
                                        value.onProviderDateTimeSelect(context),
                                  ),
                              ],
                            )
                                .paddingSymmetric(
                                    vertical: Insets.i20,
                                    horizontal: Insets.i15)
                                .decorated(
                                  color: appColor(context).whiteBg,
                                  borderRadius:
                                      BorderRadius.circular(AppRadius.r8),
                                  boxShadow: [
                                    BoxShadow(
                                      color: appColor(context)
                                          .darkText
                                          .withOpacity(0.06),
                                      blurRadius: 12,
                                      spreadRadius: 0,
                                      offset: const Offset(0, 2),
                                    ),
                                  ],
                                  border: Border.all(
                                      color: appColor(context).stroke),
                                )
                                .paddingSymmetric(horizontal: Insets.i20),
                          ],
                        ),
                  const VSpace(Sizes.s15),
                  const ServicemanQuantityLayout(),
                  if ((value.servicesCart!.selectedRequiredServiceMan!) >
                      (value.servicesCart!.requiredServicemen ?? 1))
                    Text(language(context, translations!.youWillOnly),
                            style: appCss.dmDenseMedium12
                                .textColor(appColor(context).red))
                        .paddingOnly(bottom: Insets.i25)
                        .paddingSymmetric(horizontal: Insets.i20),
                  CustomMessageLayout(
                      onTap: () {
                        Timer(const Duration(milliseconds: 500), () {
                          log("value.scrollController.position.maxScrollExtent:${value.scrollController.position.maxScrollExtent}");
                          value.scrollController.jumpTo(
                              value.scrollController.position.maxScrollExtent);
                          value.notifyListeners();
                        });

                        value.notifyListeners();
                      },
                      controller: value.txtNote,
                      focusNode: value.noteFocus),
                  const VSpace(Sizes.s100)
                ]),
          ButtonCommon(
                  isLoading: value.isNextLoading,
                  title: value.buttonName(context),
                  margin: Insets.i20,
                  onTap: () => value.onTapNext(context))
              .marginOnly(bottom: Insets.i20)
              .backgroundColor(appColor(context).whiteBg)
        ]));
      });
    });
  }
}
