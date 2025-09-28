// import 'dart:convert';
// import 'dart:developer';

// import 'package:collection/collection.dart';
// import 'package:dio/dio.dart';
// import 'package:fixit_user/models/step2model.dart';
// import 'package:flutter/cupertino.dart';

// import 'package:fluttertoast/fluttertoast.dart';
// import 'package:intl/intl.dart';
// import 'package:table_calendar/table_calendar.dart';
// import '../../config.dart';
// import '../../models/time_slot_model.dart';
// import '../../screens/app_pages_screens/slot_booking_screen/layouts/provider_time_slot_layout.dart';
// import '../../screens/app_pages_screens/slot_booking_screen/layouts/year_dialog.dart';
// import '../../utils/date_time_picker.dart';

// int getHashCode(DateTime key) {
//   return key.day * 1000000 + key.month * 10000 + key.year;
// }

// class SlotBookingProvider with ChangeNotifier {
//   Services? servicesCart;
//   int selectIndex = 0;
//   bool isStep2 = false, isPackage = false, isBottom = true;
//   PrimaryAddress? address;
//   int selectProviderIndex = 0;
//   List timeSlot = [];
//   List newTimeSlot = [];
//   int demoInt = 0;
//   dynamic chosenValue;
//   DateTime? selectedDay;
//   DateTime selectedYear = DateTime.now();
//   ScrollController scrollController = ScrollController();

//   dynamic slotChosenValue, slotTime;
//   DateTime? slotSelectedDay;
//   DateTime slotSelectedYear = DateTime.now();

//   int selectedIndex = 0;
//   int scrollDayIndex = 0;
//   int scrollMinIndex = 0;
//   int scrollHourIndex = 0;
//   String showYear = 'Select Year';
//   TimeSlotModel? timeSlotModel;
//   PageController pageController = PageController();
//   CalendarFormat calendarFormat = CalendarFormat.week;
//   CalendarFormat calendarFormatMonth = CalendarFormat.month;
//   CarouselSliderController carouselController = CarouselSliderController();
//   CarouselSliderController carouselController1 = CarouselSliderController();
//   CarouselSliderController carouselController2 = CarouselSliderController();
//   final ValueNotifier<DateTime> focusedDay = ValueNotifier(DateTime.now());
//   final FocusNode noteFocus = FocusNode();
//   bool visible = true;
//   int val = 1;
//   double loginWidth = 100.0;
//   TextEditingController txtNote = TextEditingController();
//   int? amIndex;

//   int? timeIndex;
//   bool isVisible = false;

//   bool isPositionedRight = false;
//   bool isAnimateOver = false;
//   AnimationController? controller;
//   Animation<Offset>? offsetAnimation;

//   @override
//   void dispose() {
//     scrollController.dispose();
//     pageController.dispose();
//     noteFocus.dispose();
//     txtNote.clear();
//     controller?.dispose();
//     super.dispose();
//   }

//   addNewLoc(context) {
//     final loc = Provider.of<LocationProvider>(context, listen: false);
//     route.pushNamed(context, routeName.currentLocation).then((e) async {
//       await loc.getLocationList(context);
//       if (loc.addressList.length == 1) {
//         address = loc.addressList[0];
//         notifyListeners();
//       }
//     });
//   }

//   Future<void> fetchTimeSlots() async {
//     try {
//       log("servicesCart!.user!.id::${servicesCart!.user!.id}");
//       // isLoading = true;
//       notifyListeners();
//       final response = await apiServices.getApi(
//         "${api.providerTimeSlot}/3",
//         [],
//         isToken: true,
//       );
//       log("GET API Response: ${response.toString()}");

//       if (response.isSuccess!) {
//         final timeSlots = response.data['time_slots'] as List<dynamic>;
//         for (var slot in timeSlots) {
//           final day = slot['day'].toString().toUpperCase();
//           final slots = List<String>.from(slot['slots'] ?? []);
//           final isActive = slot['is_active'] == 1 ? 1 : 0;

//           /*  final dayIndex = appArray.timeSlotList.indexWhere(
//                 (d) => d.day?.toUpperCase() == day,
//           );
//           if (dayIndex != -1) {
//             appArray.timeSlotList[dayIndex].slots = slots;
//             appArray.timeSlotList[dayIndex].status = isActive;

//             if (selectedSlots.isNotEmpty && timeList.isNotEmpty) {
//               selectedSlots[dayIndex] = List.generate(
//                 timeList.length,
//                     (i) => slots.contains(to24HourFormat(timeList[i])),
//               );
//             }
//           }*/
//         }
//       } else {
//         log("GET API failed: ${response.message}");
//       }
//     } catch (e, s) {
//       log("Fetch time slots error: $e\n$s");
//     } finally {
//       // isLoading = false;
//       notifyListeners();
//     }
//   }

//   deleteJobRequestConfirmation(context, sync, index) {
//     animateDesign(sync);
//     showDialog(
//         context: context,
//         builder: (context1) {
//           return StatefulBuilder(builder: (context2, setState) {
//             return Consumer<SlotBookingProvider>(
//                 builder: (context3, value, child) {
//               return AlertDialog(
//                   contentPadding: EdgeInsets.zero,
//                   insetPadding:
//                       const EdgeInsets.symmetric(horizontal: Insets.i20),
//                   shape: const SmoothRectangleBorder(
//                       borderRadius: SmoothBorderRadius.all(SmoothRadius(
//                           cornerRadius: AppRadius.r14, cornerSmoothing: 1))),
//                   backgroundColor: appColor(context).whiteBg,
//                   content: Stack(alignment: Alignment.topRight, children: [
//                     Column(mainAxisSize: MainAxisSize.min, children: [
//                       // Gif
//                       Stack(alignment: Alignment.topCenter, children: [
//                         Stack(alignment: Alignment.topCenter, children: [
//                           SizedBox(
//                                   width: MediaQuery.of(context).size.width,
//                                   child: Stack(
//                                       alignment: Alignment.bottomCenter,
//                                       children: [
//                                         SizedBox(
//                                             height: Sizes.s208,
//                                             width: Sizes.s150,
//                                             child: AnimatedContainer(
//                                                 duration: const Duration(
//                                                     milliseconds: 200),
//                                                 curve: isPositionedRight
//                                                     ? Curves.bounceIn
//                                                     : Curves.bounceOut,
//                                                 alignment: isPositionedRight
//                                                     ? Alignment.center
//                                                     : Alignment.topCenter,
//                                                 child: AnimatedContainer(
//                                                     duration: const Duration(
//                                                         milliseconds: 200),
//                                                     height: 40,
//                                                     child: Image.asset(
//                                                         eImageAssets
//                                                             .removeAddOn)))),
//                                         Image.asset(eImageAssets.dustbin,
//                                                 height: Sizes.s88,
//                                                 width: Sizes.s88)
//                                             .paddingOnly(bottom: Insets.i24)
//                                       ]))
//                               .decorated(
//                                   color: appColor(context).fieldCardBg,
//                                   borderRadius:
//                                       BorderRadius.circular(AppRadius.r10)),
//                         ]),
//                         if (offsetAnimation != null)
//                           SlideTransition(
//                               position: offsetAnimation!,
//                               child: (offsetAnimation != null &&
//                                       isAnimateOver == true)
//                                   ? Image.asset(eImageAssets.dustbinCover,
//                                       height: 38)
//                                   : const SizedBox())
//                       ]),
//                       // Sub text
//                       const VSpace(Sizes.s15),
//                       Text(language(context, translations!.removeAddOnsDes),
//                               textAlign: TextAlign.center,
//                               style: appCss.dmDenseRegular14
//                                   .textColor(appColor(context).lightText)
//                                   .textHeight(1.6))
//                           .paddingSymmetric(horizontal: Sizes.s56),
//                       const VSpace(Sizes.s25),
//                       Row(children: [
//                         Expanded(
//                             child: ButtonCommon(
//                                 onTap: () => route.pop(context),
//                                 title: translations?.no ??
//                                     language(context, appFonts.no),
//                                 borderColor: appColor(context).primary,
//                                 color: appColor(context).whiteBg,
//                                 style: appCss.dmDenseSemiBold16
//                                     .textColor(appColor(context).primary))),
//                         const HSpace(Sizes.s15),
//                         Expanded(
//                             child: ButtonCommon(
//                                 color: appColor(context).primary,
//                                 onTap: () {
//                                   servicesCart!.selectedAdditionalServices!
//                                       .removeAt(index);
//                                   notifyListeners();
//                                   route.pop(context);
//                                 },
//                                 style: appCss.dmDenseSemiBold16
//                                     .textColor(appColor(context).whiteColor),
//                                 title: translations?.yes ??
//                                     language(context, appFonts.yes)))
//                       ])
//                     ]).padding(
//                         horizontal: Insets.i20,
//                         top: Insets.i60,
//                         bottom: Insets.i20),
//                     Row(
//                         mainAxisAlignment: MainAxisAlignment.spaceBetween,
//                         children: [
//                           // Title
//                           Text(language(context, translations!.removeAddOns),
//                               style: appCss.dmDenseExtraBold18
//                                   .textColor(appColor(context).darkText)),
//                           Icon(CupertinoIcons.multiply,
//                                   size: Sizes.s20,
//                                   color: appColor(context).darkText)
//                               .inkWell(onTap: () => route.pop(context))
//                         ]).paddingAll(Insets.i20)
//                   ]));
//             });
//           });
//         }).then((value) {
//       isPositionedRight = false;
//       isAnimateOver = false;
//       notifyListeners();
//     });
//   }

//   animateDesign(TickerProvider sync) {
//     Future.delayed(DurationClass.s1).then((value) {
//       isPositionedRight = true;
//       notifyListeners();
//     }).then((value) {
//       Future.delayed(DurationClass.ms150).then((value) {
//         isAnimateOver = true;
//         notifyListeners();
//       }).then((value) {
//         controller = AnimationController(
//             vsync: sync, duration: const Duration(seconds: 2))
//           ..forward();
//         offsetAnimation = Tween<Offset>(
//                 begin: const Offset(0, 0.5), end: const Offset(0, 1.6))
//             .animate(
//                 CurvedAnimation(parent: controller!, curve: Curves.elasticOut));
//         notifyListeners();
//       });
//     });

//     notifyListeners();
//   }

//   bool isNextLoading = false;
//   List? selectedAddons;
//   Future<void> onTapNext(BuildContext context) async {
//     isNextLoading = true;
//     notifyListeners();

//     servicesCart!.selectedServiceMan = null;

//     if (isPackage) {
//       log("selectProviderIndex :$selectProviderIndex");
//       servicePackageList[selectProviderIndex] = servicesCart!;
//       servicePackageList[selectProviderIndex].selectedServiceNote =
//           txtNote.text;

//       if (servicePackageList[selectProviderIndex].serviceDate != null) {
//         servicesCart!.serviceDate = focusedDay.value;
//         servicesCart!.selectedDateTimeFormat =
//             DateFormat("aa").format(focusedDay.value);
//         servicePackageList[selectProviderIndex].serviceDate = focusedDay.value;
//         servicePackageList[selectProviderIndex].selectServiceManType =
//             "app_choose";
//         log("packageCtrl.servicePackageList[selectProviderIndex].selectServiceManType : ${servicesCart!.media}");

//         isStep2 = false;
//         isNextLoading = false;
//         notifyListeners();
//         route.pop(context);
//       } else {
//         isNextLoading = false;
//         notifyListeners();
//         Fluttertoast.showToast(
//           msg: "Please Select the Date & Time Slot",
//           backgroundColor: Colors.red,
//         );
//       }
//     } else {
//       callBookingApi(servicesCart?.id, servicesCart?.requiredServicemen,
//           servicesCart?.selectedAdditionalServices?.map((e) => e.id).toList());

//       notifyListeners();
//       log("message -=-=-=-=-=-=-=-=-= ${servicesCart?.id}-=-=-=-=-=${servicesCart?.requiredServicemen}======== ${servicesCart?.selectedAdditionalServices?.map((e) => e.id).join(',')}");
//       servicesCart!.selectedServiceNote = txtNote.text;
//       servicesCart!.selectServiceManType = "app_choose";

//       if (servicesCart!.serviceDate != null) {
//         if (address != null) {
//           log("servicesCart!.selectServiceManType : ${servicesCart!.id}");
//           isStep2 = true;
//           isNextLoading = false;
//           notifyListeners();
//         } else {
//           txtNote.text = "";
//           isNextLoading = false;
//           notifyListeners();
//           Fluttertoast.showToast(
//             msg: language(context, translations!.selectAddress),
//             backgroundColor: Colors.red,
//           );
//         }
//       } else {
//         isNextLoading = false;
//         notifyListeners();
//         Fluttertoast.showToast(
//           msg: 'Please Select the Date & Time Slot',
//           backgroundColor: Colors.red,
//         );
//       }
//     }
//   }

//   onChangeSlot(index) {
//     timeIndex = index;
//     notifyListeners();
//     checkSlotAvailable();
//   }

//   void onAmPmChange(BuildContext context, int index) {
//     scrollDayIndex = index;
//     amIndex = index;
//     notifyListeners();
//     filterSlotByAmPm(context);
//   }

//   Future<void> filterSlotByAmPm(BuildContext context) async {
//     showLoading(context);
//     // timeSlot.clear();
//     notifyListeners();

//     if (timeSlotModel?.timeSlots == null) {
//       hideLoading(context);
//       return;
//     }

//     String day = DateFormat('EEEE').format(focusedDay.value).toUpperCase();
//     List<TimeSlots> dayWeek = timeSlotModel!.timeSlots;
//     int index = dayWeek.indexWhere(
//         (element) => element.day.toLowerCase() == day.toLowerCase());

//     if (index >= 0 && dayWeek[index].isActive == "1") {
//       List<String> newTimeSlot = dayWeek[index].slots;
//       bool isToday = isSameDay(focusedDay.value, DateTime.now());
//       for (String slot in newTimeSlot) {
//         int hour = int.parse(slot.split(":")[0]);
//         // Filter for AM/PM
//         if (scrollDayIndex == 0 && hour < 12) {
//           // AM: Include slots before 12:00 PM
//           timeSlot.add(slot);
//           log("timeSlot:::$timeSlot");
//         } else if (scrollDayIndex == 1 && hour >= 12) {
//           // PM: Include slots from 12:00 PM onwards
//           timeSlot.add(slot);
//         }
//         // Restrict past times for today
//         if (isToday) {
//           int slotHour = hour;
//           int slotMinute = int.parse(slot.split(":")[1]);
//           DateTime now = DateTime.now();
//           if (scrollDayIndex == 0 && now.hour >= 12) {
//             timeSlot.remove(slot); // Remove AM slots if past noon
//           } else if (slotHour < now.hour ||
//               (slotHour == now.hour && slotMinute < now.minute)) {
//             timeSlot.remove(slot); // Remove past times
//           }
//         }
//       }
//     }

//     timeIndex = null;
//     hideLoading(context);
//     notifyListeners();
//   }

//   onChangeLocation(context, PrimaryAddress primaryAddress) async {
//     final loc = Provider.of<LocationProvider>(context, listen: false);
//     await loc.getLocationList(context);
//     address = primaryAddress;
//     if (isPackage) {
//       final packageCtrl =
//           Provider.of<SelectServicemanProvider>(context, listen: false);
//       servicePackageList[selectProviderIndex].primaryAddress = address;
//       packageCtrl.notifyListeners();
//     } else {
//       servicesCart!.primaryAddress = address;
//     }

//     notifyListeners();
//   }

//   //
//   // void onDaySelected(DateTime selectDay, DateTime fDay, BuildContext context) {
//   //   if (!isSameDay(selectedDay, selectDay)) {
//   //     selectedDay = selectDay;
//   //     focusedDay.value = fDay;
//   //     scrollDayIndex =
//   //         isSameDay(selectDay, DateTime.now()) && DateTime.now().hour >= 12
//   //             ? 1
//   //             : 0;
//   //     amIndex = scrollDayIndex;
//   //     timeIndex = null;
//   //     // carouselController2.jumpToPage(scrollDayIndex);
//   //     updateTimeSlotsForSelectedDay(context: context);
//   //   }
//   // }
//   void onDaySelected(DateTime selectDay, DateTime fDay, BuildContext context) {
//     if (!isSameDay(selectedDay, selectDay)) {
//       selectedDay = selectDay;
//       focusedDay.value = fDay;
//       scrollDayIndex =
//           isSameDay(selectDay, DateTime.now()) && DateTime.now().hour >= 12
//               ? 1
//               : 0;
//       amIndex = scrollDayIndex;
//       timeIndex = null;
//       log(">>> onDaySelected: $selectDay");
//       log(">>> Weekday: ${DateFormat('EEEE').format(selectDay)}");

//       // Refresh time slot list (if applicable)
//       updateTimeSlotsForSelectedDay(context: context);

//       // 🔥 Add this line
//       notifyListeners();
//     }
//   }

//   void onProviderDaySelected(
//       DateTime selectDay, DateTime fDay, BuildContext context) {
//     if (!isSameDay(selectedDay, selectDay)) {
//       selectedDay = selectDay;
//       focusedDay.value = fDay;
//       scrollDayIndex =
//           isSameDay(selectDay, DateTime.now()) && DateTime.now().hour >= 12
//               ? 1
//               : 0;
//       amIndex = scrollDayIndex;
//       timeIndex = null;
//       // carouselController2.jumpToPage(scrollDayIndex);
//       updateTimeSlotsForSelectedDay(context: context);
//     }
//   }
//   /* void onDaySelected(DateTime selectDay, DateTime fDay, BuildContext context) {
//     if (!isSameDay(selectedDay, selectDay)) {
//       selectedDay = selectDay;
//       focusedDay.value = fDay;
//       amIndex = null; // Reset AM/PM selection
//       timeIndex = null; // Reset time slot selection
//       updateTimeSlotsForSelectedDay(context: context);
//     }
//   }*/

//   /*void onDaySelected(DateTime selectDay, DateTime fDay, context) async {
//     log("SSSS :$selectDay // $fDay");
//     notifyListeners();
//     */ /*focusedDay.value = selectDay;*/ /*
//     if (!isSameDay(selectedDay, selectDay)) {
//       // Call `setState()` when updating the selected day
//       selectedDay = selectedDay;
//       focusedDay.value = fDay;

//       log("SEKE : $slotTime");
//     }
//     notifyListeners();
//     String day = DateFormat('EEEE').format(focusedDay.value);

//     if (slotTime != null) {
//       int ind = timeSlotModel!.timeSlots!.indexWhere(
//           (element) => element.day!.toLowerCase() == day.toLowerCase());

//       if (ind >= 0) {
//         log("ind :${timeSlotModel!.timeSlots![ind].status}");
//         if (timeSlotModel!.timeSlots![ind].status == "1") {
//           String gap = timeSlotModel!.timeUnit == "hours"
//               ? "${timeSlotModel!.gap}:00"
//               : "00:${timeSlotModel!.gap}";

//           timeSlot = await slots(
//                   timeSlotModel!.timeSlots![ind].startTime!.split(" ")[0],
//                   timeSlotModel!.timeSlots![ind].endTime,
//                   gap) ??
//               [];

//           if (timeSlot.isNotEmpty) {
//             focusedDay.value = DateTime.utc(
//                 focusedDay.value.year,
//                 focusedDay.value.month,
//                 focusedDay.value.day,
//                 DateTime.now().hour,
//                 DateTime.now().minute);
//             checkSlotAvailable();
//           }
//         } else {
//           timeSlot = [];
//           notifyListeners();
//         }
//       } else {
//         timeSlot = [];
//         notifyListeners();
//       }
//     } else {
//       fetchSlotTime(context);
//     }
//   }*/

//   int count = 0;
//   void onInit(
//     BuildContext context, {
//     bool? isPackage,
//     int? index,
//     Services? service,
//     bool isProviderTimeSlot = false,
//   }) async
//   {
//     this.isPackage = isPackage ?? false;
//     selectProviderIndex = index ?? 0;
//     servicesCart = service;

//     // Initialize date and time
//     DateTime now = DateTime.now();
//     DateTime twoHoursLater = now.add(const Duration(hours: 2, minutes: 2));

//     if (servicesCart?.serviceDate != null) {
//       // Use service date if provided
//       focusedDay.value = servicesCart!.serviceDate!;
//       selectedDay = servicesCart!.serviceDate;

//       // Convert hour to 12-hour format
//       int hour = servicesCart!.serviceDate!.hour;
//       int displayHour = hour % 12 == 0 ? 12 : hour % 12;
//       scrollHourIndex = appArray.hourList
//           .indexWhere((element) => element == displayHour.toString());

//       // Set minute
//       scrollMinIndex = appArray.minList.indexWhere((element) =>
//           element ==
//           servicesCart!.serviceDate!.minute.toString().padLeft(2, '0'));

//       // Set AM/PM
//       amIndex = servicesCart!.selectedDateTimeFormat == "AM" ? 0 : 1;
//     } else {
//       // Set to current time + 2 hours
//       focusedDay.value = twoHoursLater;
//       selectedDay = twoHoursLater;
//       // Convert hour to 12-hour format
//       int hour = twoHoursLater.hour;
//       int displayHour = hour % 12 == 0 ? 12 : hour % 12;
//       scrollHourIndex = appArray.hourList
//           .indexWhere((element) => element == displayHour.toString());

//       // Set minute
//       scrollMinIndex = appArray.minList.indexWhere((element) =>
//           element == twoHoursLater.minute.toString().padLeft(2, '0'));
//       scrollDayIndex = twoHoursLater.hour >= 12 ? 0 : 1;
//       // Set AM/PM
//       amIndex = twoHoursLater.hour >= 12 ? 0 : 1;
//     }
//     // Jump to initial positions
//     // Handle case where hour or minute not found in lists
//     if (scrollHourIndex == -1 || scrollHourIndex == null) scrollHourIndex = 0;
//     if (scrollMinIndex == -1 || scrollMinIndex == null) scrollMinIndex = 0;
//     if (scrollDayIndex == null) scrollDayIndex = 0;

//     carouselController.jumpToPage(scrollHourIndex);
//     carouselController1.jumpToPage(scrollMinIndex);
//     carouselController2.jumpToPage(scrollDayIndex);

//     // Initialize month dropdown
//     int monthIndex = appArray.monthList
//         .indexWhere((element) => element['index'] == focusedDay.value.month);
//     chosenValue = appArray.monthList[monthIndex];

//     notifyListeners();
//   }
// // // Updated onInit method
// //   void onInit(
// //     BuildContext context, {
// //     bool? isPackage,
// //     int? index,
// //     Services? service,
// //     bool isProviderTimeSlot = false,
// //   }) async
// //   {
// //     await fetchSlotTime(context);
// //     this.isPackage = isPackage ?? false;
// //     selectProviderIndex = index ?? 0;
// //     servicesCart = service;
// //
// //     txtNote.text = "";
// //     // Initialize date and time
// //     DateTime now = DateTime.now();
// //     DateTime twoHoursLater = now.add(const Duration(hours: 2));
// //
// //     if (servicesCart?.serviceDate != null) {
// //       await fetchSlotTime(context);
// //       // Use service date if provided
// //       focusedDay.value = servicesCart!.serviceDate!;
// //       selectedDay = servicesCart!.serviceDate;
// //
// //       // Convert hour to 12-hour format
// //       int hour = servicesCart!.serviceDate!.hour;
// //       int displayHour = hour % 12 == 0 ? 12 : hour % 12;
// //       scrollHourIndex = appArray.hourList
// //           .indexWhere((element) => element == displayHour.toString());
// //
// //       // Set minute
// //       scrollMinIndex = appArray.minList.indexWhere((element) =>
// //           element ==
// //           servicesCart!.serviceDate!.minute.toString().padLeft(2, '0'));
// //
// //       // Set AM/PM
// //       amIndex = servicesCart!.selectedDateTimeFormat == "AM" ? 0 : 1;
// //     } else {
// //       // Set to current time + 2 hours
// //       focusedDay.value = twoHoursLater;
// //       selectedDay = twoHoursLater;
// //
// //       // Convert hour to 12-hour format
// //       int hour = twoHoursLater.hour;
// //       int displayHour = hour % 12 == 0 ? 12 : hour % 12;
// //       scrollHourIndex = appArray.hourList
// //           .indexWhere((element) => element == displayHour.toString());
// //
// //       // Set minute
// //       scrollMinIndex = appArray.minList.indexWhere((element) =>
// //           element == twoHoursLater.minute.toString().padLeft(2, '0'));
// //
// //       // Set AM/PM
// //       amIndex = twoHoursLater.hour >= 12 ? 0 : 1;
// //     }
// //     // if (isProviderTimeSlot == false) {
// //     scrollHourIndex = appArray.hourList.indexWhere((element) {
// //       log("EE : $element");
// //       log("EE : ${focusedDay.value.hour}");
// //       return element == focusedDay.value.hour.toString();
// //     });
// //     scrollMinIndex = appArray.minList
// //         .indexWhere((element) => element == focusedDay.value.minute.toString());
// //     log("index : ${focusedDay.value.hour}");
// //     log("index : $scrollHourIndex");
// //     notifyListeners();
// //     await Future.delayed(DurationClass.s3);
// //
// //     carouselController.jumpToPage(scrollHourIndex);
// //
// //     carouselController1.jumpToPage(scrollMinIndex);
// //     amIndex = servicesCart!.selectedDateTimeFormat == "AM" ? 0 : 1;
// //     carouselController2.jumpToPage(amIndex!);
// //
// //     notifyListeners();
// // /*    } else {
// //       // Handle case where hour or minute not found in lists
// //       if (scrollHourIndex == -1) scrollHourIndex = 0;
// //       if (scrollMinIndex == -1) scrollMinIndex = 0;
// //
// //       // Jump to initial positions
// //       carouselController.jumpToPage(scrollHourIndex);
// //       carouselController1.jumpToPage(scrollMinIndex);
// //       carouselController2.jumpToPage(amIndex ?? 0);
// //       notifyListeners();
// //       // Initialize month dropdown
// //       int monthIndex = appArray.monthList
// //           .indexWhere((element) => element['index'] == focusedDay.value.month);
// //       chosenValue = appArray.monthList[monthIndex];
// //
// //       // await fetchSlotTime(context);
// //       notifyListeners();
// //     }*/
// //   }
// /*
//   onInit(context,
//       {isPackage = false,
//       index,
//       isEdit = false,
//       service,
//       sync,
//       isProviderTimeSlot = false}) async
//   {
//     log("isPackage :$isPackage");
//     fetchTimeSlots();
//     if (isEdit) {
//       servicesCart = service;
//       focusedDay.value = DateTime.utc(focusedDay.value.year,
//           focusedDay.value.month, focusedDay.value.day + 0);
//       onDaySelected(focusedDay.value, focusedDay.value, context);
//       DateTime dateTime = DateTime.now();
//       int index = appArray.monthList
//           .indexWhere((element) => element['index'] == dateTime.month);
//       chosenValue = appArray.monthList[index];
//       notifyListeners();
//     } else {
//       fetchSlotTime(context);
//       log("dfjghjkdlkbhlfih");
//       focusedDay.value = DateTime.now();
//       if (isPackage) {
//         final packageCtrl =
//             Provider.of<SelectServicemanProvider>(context, listen: false);
//         servicesCart = packageCtrl.servicePackageModel!.services![index];
//         servicesCart = servicesCart;
//       } else {
//         servicesCart = service;
//       }
//       notifyListeners();

//       if (servicesCart!.serviceDate != null) {
//         if (timeSlotModel != null) {
//           String gap = timeSlotModel!.timeUnit == "hour"
//               ? "${timeSlotModel!.gap}:00"
//               : "00:${timeSlotModel!.gap}";
//           focusedDay.value = servicesCart!.serviceDate!;
//           String day = DateFormat('EEEE').format(focusedDay.value);
//           int listIndex = timeSlotModel!.timeSlots!
//               .indexWhere((element) => element.day!.toLowerCase() == day);
//           if (listIndex >= 0) {
//             if (timeSlotModel!.timeSlots![listIndex].status == "1") {
//               timeSlot = await slots(slotTime["timeSlot"]["start_time"],
//                   slotTime["timeSlot"]["end_time"], gap);
//             } else {
//               timeSlot = [];
//             }
//           }
//         }
//         if (isProviderTimeSlot == false) {
//           scrollHourIndex = appArray.hourList.indexWhere((element) {
//             log("EE : $element");
//             log("EE : ${focusedDay.value.hour}");
//             return element == focusedDay.value.hour.toString();
//           });
//           scrollMinIndex = appArray.minList.indexWhere(
//               (element) => element == focusedDay.value.minute.toString());
//           log("index : ${focusedDay.value.hour}");
//           log("index : $scrollHourIndex");
//           notifyListeners();
//           await Future.delayed(DurationClass.s3);

//           carouselController.jumpToPage(scrollHourIndex);

//           carouselController1.jumpToPage(scrollMinIndex);
//           amIndex = servicesCart!.selectedDateTimeFormat == "AM" ? 0 : 1;
//           carouselController2.jumpToPage(amIndex!);

//           notifyListeners();
//         } else {
//           focusedDay.value = DateTime.utc(focusedDay.value.year,
//               focusedDay.value.month, focusedDay.value.day + 0);
//           onDaySelected(focusedDay.value, focusedDay.value, context);
//           DateTime dateTime = DateTime.now();
//           int index = appArray.monthList
//               .indexWhere((element) => element['index'] == dateTime.month);
//           chosenValue = appArray.monthList[index];
//           log("index : $dateTime");
//           scrollHourIndex = appArray.hourList.indexWhere((element) {
//             return element == dateTime.hour.toString();
//           });
//           scrollMinIndex = appArray.minList
//               .indexWhere((element) => element == dateTime.minute.toString());
//           log("scrollHourIndex :$scrollHourIndex");
//           carouselController.jumpToPage(scrollHourIndex);

//           carouselController1.jumpToPage(scrollMinIndex);
//           amIndex = DateFormat("aa").format(dateTime) == "AM" ? 0 : 1;
//           carouselController2.jumpToPage(amIndex!);
//         }
//       } else {
//         if (isProviderTimeSlot == false) {
//           focusedDay.value = DateTime.utc(focusedDay.value.year,
//               focusedDay.value.month, focusedDay.value.day + 0);
//           onDaySelected(focusedDay.value, focusedDay.value, context);
//           DateTime dateTime = DateTime.now();
//           int index = appArray.monthList
//               .indexWhere((element) => element['index'] == dateTime.month);
//           chosenValue = appArray.monthList[index];
//           log("index : $dateTime");
//           scrollHourIndex = appArray.hourList.indexWhere((element) {
//             return element == dateTime.hour.toString();
//           });
//           scrollMinIndex = appArray.minList
//               .indexWhere((element) => element == dateTime.minute.toString());
//           log("scrollHourIndex :$scrollHourIndex");
//           carouselController.jumpToPage(scrollHourIndex);

//           carouselController1.jumpToPage(scrollMinIndex);
//           amIndex = DateFormat("aa").format(dateTime) == "AM" ? 0 : 1;
//           carouselController2.jumpToPage(amIndex!);
//         }
//       }
//     }
//     notifyListeners();
//     fetchSlotTime(context);
//   }*/

//   Future<void> fetchSlotTime(BuildContext context) async {
//     // timeSlot = [];
//     showLoading(context);
//     notifyListeners();
//     try {
//       log("providerId::${servicesCart!.userId}");
//       final response = await apiServices.getApi(
//         "${api.providerTimeSlot}/${servicesCart?.userId}",
//         [],
//         isData: true,
//         isToken: true,
//       );
//       log("CALLA :${response.data}");
//       if (response.isSuccess == true) {
//         timeSlotModel = TimeSlotModel.fromJson(response.data);
//         updateTimeSlotsForSelectedDay();

//         showModalBottomSheet(
//             isScrollControlled: true,
//             context: context,
//             builder: (BuildContext context3) {
//               return Consumer<SlotBookingProvider>(
//                   builder: (context1, value, child) {
//                 return StatefulBuilder(builder: (context2, setState) {
//                   return ProviderTimeSlotLayout(
//                       isService: isPackage,
//                       selectProviderIndex: selectProviderIndex,
//                       service: servicesCart);
//                 });
//               });
//             }).then((value) {
//           log("VVVS : $value");
//           if (value == null) {
//             focusedDay.value = DateTime.now();
//             notifyListeners();
//           }
//           amIndex = null;
//           timeSlot = [];
//           notifyListeners();
//           if (isPackage) {
//             final packageCtrl =
//                 Provider.of<SelectServicemanProvider>(context, listen: false);
//             servicePackageList[selectProviderIndex] = servicesCart!;
//             servicePackageList[selectProviderIndex].serviceDate =
//                 servicesCart!.serviceDate;
//             servicePackageList[selectProviderIndex].selectDateTimeOption =
//                 selectIndex == 0 ? "custom" : "timeSlot";
//             servicePackageList[selectProviderIndex].selectedDateTimeFormat =
//                 servicesCart!.selectedDateTimeFormat;
//             notifyListeners();
//             packageCtrl.notifyListeners();
//             log("packageCtrl.servicePackageList[selectProviderIndex].serviceDate :${servicePackageList[selectProviderIndex].serviceDate}");
//           }
//         });
//       } else {
//         log("GET API failed: ${response.message}");
//         amIndex = null;
//         timeSlot = [];
//         Fluttertoast.showToast(
//             msg: response.message, backgroundColor: Colors.red);
//       }
//     } catch (e) {
//       log("EEEE fetchSlotTime: $e");
//       Fluttertoast.showToast(
//           msg: "Failed to fetch time slots", backgroundColor: Colors.red);
//     } finally {
//       hideLoading(context);
//       notifyListeners();
//     }
//   }

//   void updateTimeSlotsForSelectedDay({BuildContext? context}) {
//     if (timeSlotModel?.timeSlots == null || focusedDay.value == null) {
//       timeSlot = [];
//       timeIndex = null;
//       notifyListeners();
//       return;
//     }
//     final day = DateFormat('EEEE').format(focusedDay.value).toUpperCase();
//     final slot = timeSlotModel!.timeSlots.firstWhereOrNull(
//       (element) => element.day.toUpperCase() == day,
//     );
//     timeSlot = (slot != null && slot.isActive == 1) ? slot.slots : [];
//     timeIndex = null; // Reset selected slot
//     notifyListeners();
//     // Trigger AM/PM filtering if amIndex is set and context is provided
//     if (amIndex != null && context != null) {
//       filterSlotByAmPm(context);
//     }
//   }

//   /*checkSlotAvailable({isEdit = false, context, isService = false}) async {
//     focusedDay.value = DateTime.utc(
//         focusedDay.value.year,
//         focusedDay.value.month,
//         focusedDay.value.day + 0,
//         int.parse(timeSlot[timeIndex ?? 0].toString().split(":")[0]),
//         int.parse(timeSlot[timeIndex ?? 0].toString().split(":")[1]));
//     try {
//       log("SSS : ${servicesCart!.user!.id}");
//       log("SSS : ${"${DateFormat("dd-MMM-yyy,hh:mm").format(focusedDay.value)} ${amIndex != null ? appArray.amPmList[amIndex ?? 0].toLowerCase() : DateFormat("aa").format(focusedDay.value).toLowerCase()}"}");
//       var data = {
//         "provider_id": servicesCart!.user!.id,
//         "dateTime":
//             "${DateFormat("dd-MMM-yyy,hh:mm").format(focusedDay.value)} ${amIndex != null ? appArray.amPmList[amIndex ?? 0].toLowerCase() : DateFormat("aa").format(focusedDay.value).toLowerCase()}"
//       };

//       // log("data : $data");
//       await apiServices
//           .getApi(api.isValidTimeSlot, data, isData: true, isToken: true)
//           .then((value) async {
//         if (value.isSuccess!) {
//           log("DDAA 1:${value.data}");
//           if (value.data['isValidTimeSlot'] == true) {
//             String day = DateFormat('EEEE').format(focusedDay.value);

//             List<TimeSlots> dayWeek = timeSlotModel!.timeSlots!;
//             int listIndex = dayWeek.indexWhere(
//                 (element) => element.day!.toLowerCase() == day.toLowerCase());

//             if (listIndex >= 0) {
//               if (timeSlotModel!.timeSlots![listIndex].isActive == 1) {
//               */
//   /*  timeSlot = await slots(
//                         dayWeek[listIndex].startTime!.split(" ")[0],
//                         dayWeek[listIndex].endTime,
//                         gap) ??
//                     [];*/
//   /*
//               } else {
//                 timeIndex = null;
//                 timeSlot = [];
//                 notifyListeners();
//               }
//             } else {
//               timeIndex = null;
//               timeSlot = [];
//               notifyListeners();
//             }
//           } else {
//             timeIndex = null;
//             timeSlot = [];
//             notifyListeners();
//             Fluttertoast.showToast(msg: value.message);
//           }

//           notifyListeners();
//         }
//       });
//     } catch (e) {
//       notifyListeners();
//     }
//   }*/

//   Future<void> checkSlotAvailable(
//       {bool isEdit = false, BuildContext? context}) async {
//     if (context == null ||
//         servicesCart?.user?.id == null ||
//         timeIndex == null ||
//         timeSlot.isEmpty) {
//       timeIndex = null;
//       if (context != null) {
//         Fluttertoast.showToast(
//             msg: "Please select a time slot", backgroundColor: Colors.red);
//       }
//       notifyListeners();
//       return;
//     }
//     try {
//       showLoading(context);
//       final selectedTime = timeSlot[timeIndex!];
//       focusedDay.value = DateTime.utc(
//         focusedDay.value.year,
//         focusedDay.value.month,
//         focusedDay.value.day,
//         int.parse(selectedTime.split(":")[0]),
//         int.parse(selectedTime.split(":")[1]),
//       );
//       final data = {
//         "provider_id": servicesCart!.user!.id,
//         "dateTime": DateFormat("dd-MMM-yyyy,hh:mm aa").format(focusedDay.value),
//       };
//       final response = await apiServices.getApi(
//         api.isValidTimeSlot,
//         data,
//         isData: true,
//         isToken: true,
//       );
//       if (response.isSuccess == true &&
//           response.data['isValidTimeSlot'] == true) {
//         // Slot is valid
//       } else {
//         timeIndex = null;
//         Fluttertoast.showToast(
//             msg: response.message.isNotEmpty
//                 ? response.message
//                 : "Slot not available",
//             backgroundColor: Colors.red);
//       }
//     } catch (e) {
//       log("Error checking slot: $e");
//       timeIndex = null;
//       Fluttertoast.showToast(
//           msg: "Error validating slot", backgroundColor: Colors.red);
//     } finally {
//       hideLoading(context);
//       notifyListeners();
//     }
//   }

//   bool isLoading = false;

//   Future<void> checkSlotAvailableForAppChoose({
//     required BuildContext context,
//     bool isEdit = false,
//     bool isService = false,
//   }) async {
//     isLoading = true;
//     showLoading(context);
//     notifyListeners();

//     try {
//       // Ensure amIndex is valid
//       if (amIndex == null && amIndex! < 0 && amIndex! > 1) {
//         isLoading = false;
//         hideLoading(context);
//         Fluttertoast.showToast(
//             msg: "Invalid AM/PM selection. Please try again.",
//             backgroundColor: Colors.red);
//         notifyListeners();
//         return;
//       }

//       // Adjust hour for 12-hour format with AM/PM
//       int hour = int.parse(appArray.hourList[scrollHourIndex]);
//       String amPm = appArray.amPmList[amIndex!]; // "AM" or "PM"
//       if (amPm == "PM" && hour != 12) {
//         hour += 12; // Convert PM hours to 24-hour format (e.g., 1 PM -> 13)
//       } else if (amPm == "AM" && hour == 12) {
//         hour = 0; // Convert 12 AM to midnight
//       } else if (amPm == "PM" && hour == 12) {
//         hour = 12; // Convert 12 PM to noon
//       }

// // Construct selected DateTime in local time
//       final selectedDateTime = DateTime(
//         focusedDay.value.year,
//         focusedDay.value.month,
//         focusedDay.value.day,
//         hour,
//         int.parse(appArray.minList[scrollMinIndex]),
//       );

// // Debugging log
//       log("Selected DateTime: $selectedDateTime, AM/PM: $amPm, Hour: $hour, Minute: ${appArray.minList[scrollMinIndex]}");

// // Get current time for validation (local time)
//       final now = DateTime.now();
//       final minAllowedTime = now
//           .add(const Duration(hours: 2, minutes: 1)); // Minimum 1 hour from now

// // Debugging log for validation
//       log("Current Time: $now, Min Allowed Time: $minAllowedTime");

// // Validation 1: Check for current date
//       final today = DateTime(now.year, now.month, now.day);
//       final selectedDate = DateTime(
//           selectedDateTime.year, selectedDateTime.month, selectedDateTime.day);
//       if (selectedDate.isAtSameMomentAs(today)) {
//         // Validation 2: Prevent past, current, or within 1-hour time on current date
//         if (selectedDateTime.isBefore(minAllowedTime) ||
//             selectedDateTime.isAtSameMomentAs(now)) {
//           isLoading = false;
//           hideLoading(context);
//           Fluttertoast.showToast(
//               backgroundColor: Colors.red,
//               msg:
//                   "Please select a time at least 2 hour from now on the current date.");
//           notifyListeners();
//           return;
//         }
//       }

// // Update focusedDay.value with validated time
//       focusedDay.value = selectedDateTime;

// // Prepare API data
//       var data = {
//         "provider_id": servicesCart!.userId,
//         "dateTime":
//             "${DateFormat("dd-MMM-yyy,hh:mm").format(focusedDay.value)} ${amPm.toLowerCase()}",
//       };

// // Debugging log
//       log("API Data: $data");

// // Make API call to check time slot availability
//       await apiServices
//           .getApi(api.isValidTimeSlot, data, isData: true, isToken: true)
//           .then((value) async {
//         hideLoading(context);
//         if (value.isSuccess!) {
//           if (value.data['isValidTimeSlot'] == true) {
//             dateTimeSelect(context, isService, isEdit: isEdit);
//           } else {
//             timeIndex = null;
//             timeSlot = [];
//             Fluttertoast.showToast(
//                 msg: value.message, backgroundColor: Colors.red);
//           }
//         }
//       });
//     } catch (e) {
//       log("Error: $e");
//       hideLoading(context);
//       Fluttertoast.showToast(
//           msg: "An error occurred. Please try again.",
//           backgroundColor: Colors.red);
//     }

//     isLoading = false;
//     notifyListeners();
//   }

//   onMinDecrement() {
//     if (scrollMinIndex > 0) {
//       scrollMinIndex--;
//     }
//     carouselController1.jumpToPage(scrollMinIndex);
//     notifyListeners();
//   }

//   onMinIncrement() {
//     if (scrollMinIndex < appArray.minList.length - 1) {
//       scrollMinIndex++;
//     }
//     notifyListeners();
//     carouselController1.jumpToPage(scrollMinIndex);
//     notifyListeners();
//   }

//   onDayDecrement() {
//     if (scrollDayIndex > 0) {
//       scrollDayIndex--;
//     }
//     notifyListeners();
//     carouselController2.jumpToPage(scrollDayIndex);
//     notifyListeners();
//   }

//   onDayIncrement() {
//     if (scrollDayIndex < appArray.dayList.length) {
//       scrollDayIndex++;
//     }
//     notifyListeners();
//     carouselController2.jumpToPage(scrollDayIndex);
//     notifyListeners();
//   }

//   onDropDownChange(choseVal, context) async {
//     notifyListeners();

//     int index = choseVal['index'];
//     log("chosenValue : $index");

//     DateTime now =
//         DateTime.utc(focusedDay.value.year, index, focusedDay.value.day);
//     log("HHHHHHH :$now ${focusedDay.value}");
//     log("HHHHHHH :$now ${now.isAfter(focusedDay.value) || DateFormat('MMMM-yyyy').format(now) == DateFormat('MMMM-yyyy').format(focusedDay.value)}");
//     if (now.isAfter(DateTime.now()) ||
//         DateFormat('MMMM-yyyy').format(now) ==
//             DateFormat('MMMM-yyyy').format(focusedDay.value)) {
//       chosenValue = choseVal;

//       notifyListeners();
//       focusedDay.value =
//           DateTime.utc(focusedDay.value.year, index, focusedDay.value.day + 0);
//       onDaySelected(focusedDay.value, focusedDay.value, context);
//       log("choseVal : $choseVal");
//       String day = DateFormat('EEEE').format(focusedDay.value);

//       if (timeSlotModel != null) {
//         List<TimeSlots> dayWeek = timeSlotModel!.timeSlots;
//         int listIndex = dayWeek.indexWhere(
//             (element) => element.day.toLowerCase() == day.toLowerCase());

//         if (listIndex >= 0) {
//           /*String gap = timeSlotModel!.timeUnit == "hours"
//               ? "${timeSlotModel!.gap}:00"
//               : "00:${timeSlotModel!.gap}";*/
//           if (dayWeek[listIndex].isActive == 1) {
//             /* timeSlot = await slots(dayWeek[listIndex].startTime!.split(" ")[0],
//                     dayWeek[listIndex].endTime, gap) ??*/
//             [];
//           } else {
//             timeSlot = [];
//             notifyListeners();
//           }
//         } else {
//           timeSlot = [];
//           notifyListeners();
//         }
//       }
//     } else {
//       if (DateFormat('MMMM-yyyy').format(now) ==
//           DateFormat('MMMM-yyyy').format(DateTime.now())) {
//         focusedDay.value = DateTime.utc(
//             focusedDay.value.year, index, focusedDay.value.day + 0);
//         onDaySelected(focusedDay.value, focusedDay.value, context);
//         log("choseVal : $choseVal");
//         String day = DateFormat('EEEE').format(focusedDay.value);
//         if (timeSlotModel != null) {
//           List<TimeSlots> dayWeek = timeSlotModel!.timeSlots!;
//           int listIndex = dayWeek.indexWhere(
//               (element) => element.day!.toLowerCase() == day.toLowerCase());

//           if (listIndex >= 0) {
//             /* String gap = timeSlotModel!.timeUnit == "hours"
//                 ? "${timeSlotModel!.gap}:00"
//                 : "00:${timeSlotModel!.gap}";*/
//             if (dayWeek[listIndex].isActive == 1) {
//               /*  timeSlot = await slots(
//                       dayWeek[listIndex].startTime!.split(" ")[0],
//                       dayWeek[listIndex].endTime,
//                       gap) ??
//                   []*/
//               ;
//             } else {
//               timeSlot = [];
//               notifyListeners();
//             }
//           } else {
//             timeSlot = [];
//             notifyListeners();
//           }
//         }
//       } else {
//         log("ERROR");
//         isVisible = true;
//         notifyListeners();
//         await Future.delayed(DurationClass.s3);

//         isVisible = false;
//         notifyListeners();
//       }
//     }
//   }

//   onPageCtrl(dayFocused) {
//     focusedDay.value = dayFocused;
//     demoInt = dayFocused.year;
//     log("dayFocused :: $demoInt");
//     notifyListeners();
//   }

//   onHourScroll(index) {
//     scrollHourIndex = index;
//     notifyListeners();
//   }

// // Updated onHourTap method
//   void onHourTap(BuildContext context) async {
//     final TimeOfDay? time = await showTimePicker(
//       context: context,
//       initialTime: TimeOfDay.now(),
//       initialEntryMode: TimePickerEntryMode.dial,
//       builder: (context, child) {
//         return Theme(
//           data: Theme.of(context).copyWith(
//             primaryColor: appColor(context).primary,
//             timePickerTheme: TimePickerThemeData(
//               backgroundColor: appColor(context).whiteBg,
//               hourMinuteColor: appColor(context).stroke,
//               dialTextStyle: TextStyle(color: appColor(context).primary),
//               dayPeriodColor: appColor(context).primary.withOpacity(.6),
//               hourMinuteTextColor: appColor(context).primary,
//               dayPeriodTextColor: appColor(context).primary,
//               dayPeriodBorderSide: BorderSide(color: appColor(context).primary),
//               dialHandColor: appColor(context).primary,
//               dialTextColor: appColor(context).darkText,
//               dialBackgroundColor: appColor(context).fieldCardBg,
//               entryModeIconColor: appColor(context).primary,
//               helpTextStyle: TextStyle(color: appColor(context).whiteBg),
//               cancelButtonStyle: ButtonStyle(
//                 backgroundColor:
//                     MaterialStateProperty.all<Color>(appColor(context).primary),
//                 foregroundColor:
//                     MaterialStateProperty.all<Color>(appColor(context).whiteBg),
//               ),
//               confirmButtonStyle: ButtonStyle(
//                 backgroundColor:
//                     MaterialStateProperty.all<Color>(appColor(context).primary),
//                 foregroundColor:
//                     MaterialStateProperty.all<Color>(appColor(context).whiteBg),
//               ),
//               hourMinuteTextStyle: const TextStyle(fontSize: 30),
//             ),
//           ),
//           child: child!,
//         );
//       },
//     );

//     if (time != null) {
//       // Convert to 12-hour format
//       int hour = time.hourOfPeriod;
//       if (hour == 0) hour = 12; // Handle midnight/noon

//       // Set scrollHourIndex
//       scrollHourIndex =
//           appArray.hourList.indexWhere((element) => element == hour.toString());
//       if (scrollHourIndex == -1) scrollHourIndex = 0; // Fallback

//       // Set minute
//       String paddedMinute = time.minute.toString().padLeft(2, '0');
//       scrollMinIndex =
//           appArray.minList.indexWhere((element) => element == paddedMinute);
//       if (scrollMinIndex == -1) scrollMinIndex = 0; // Fallback

//       // Set AM/PM
//       amIndex = time.period == DayPeriod.am ? 1 : 0;

//       // Animate to selected positions
//       carouselController.animateToPage(scrollHourIndex);
//       carouselController1.animateToPage(scrollMinIndex);
//       carouselController2.animateToPage(amIndex!);

//       // Update focusedDay with selected time
//       focusedDay.value = DateTime(
//         focusedDay.value.year,
//         focusedDay.value.month,
//         focusedDay.value.day,
//         time.hour, // Use 24-hour format for internal storage
//         time.minute,
//       );

//       notifyListeners();
//     }
//   }

//   onMinScroll(index) {
//     scrollMinIndex = index;
//     notifyListeners();
//   }

//   onDayScroll(index) {
//     scrollDayIndex = index;
//     notifyListeners();
//   }

//   onCalendarCreate(controller) {
//     // log("controller : $controller");
//     pageController = controller;
//   }

//   selectYear(context) async {
//     showDialog(
//         context: context,
//         builder: (BuildContext context3) {
//           return const YearAlertDialog();
//         });
//   }

//   onLeftArrow() async {
//     DateTime now = DateTime.now();
//     if (DateFormat('MM-yyyy').format(focusedDay.value) !=
//         DateFormat('MM-yyyy').format(now)) {
//       pageController.previousPage(
//           duration: const Duration(microseconds: 200), curve: Curves.bounceIn);
//       final newMonth = focusedDay.value.subtract(const Duration(days: 30));
//       focusedDay.value = newMonth;
//       int index = appArray.monthList
//           .indexWhere((element) => element['index'] == focusedDay.value.month);
//       chosenValue = appArray.monthList[index];
//       selectedYear = DateTime.utc(focusedDay.value.year, focusedDay.value.month,
//           focusedDay.value.day + 0);
//       notifyListeners();
//     } else {
//       isVisible = true;
//       notifyListeners();
//       await Future.delayed(DurationClass.s3);

//       isVisible = false;
//       notifyListeners();
//     }
//     log("FFF : $focusedDay");
//   }

//   onRightArrow() {
//     pageController.nextPage(
//         duration: const Duration(microseconds: 200), curve: Curves.bounceIn);
//     final newMonth = focusedDay.value.add(const Duration(days: 30));
//     focusedDay.value = newMonth;
//     int index = appArray.monthList
//         .indexWhere((element) => element['index'] == focusedDay.value.month);
//     chosenValue = appArray.monthList[index];
//     selectedYear = DateTime.utc(focusedDay.value.year, focusedDay.value.month,
//         focusedDay.value.day + 0);
//     notifyListeners();
//     log("hbfbfdbf::::::$newMonth");
//   }

//   void listen() {
//     if (scrollController.position.pixels >= 200) {
//       hide();
//     } else {
//       show();
//     }
//     notifyListeners();
//   }

//   void show() {
//     if (!isBottom) {
//       isBottom = true;
//       notifyListeners();
//     }
//   }

//   void hide() {
//     if (isBottom) {
//       isBottom = false;
//       notifyListeners();
//     }
//   }

//   onReady(context) async {
//     dynamic data = ModalRoute.of(context)!.settings.arguments;

//     scrollController.addListener(listen);
//     log("ARG: ${data['selectServicesCart']}");
//     servicesCart = data['selectServicesCart'];
//     servicesCart!.selectedRequiredServiceMan =
//         servicesCart!.selectedRequiredServiceMan ?? 1;
//     isPackage = data['isPackage'] ?? false;
//     selectProviderIndex = data['selectProviderIndex'] ?? 0;
//     notifyListeners();
//     final locationCtrl = Provider.of<LocationProvider>(context, listen: false);
//     if (locationCtrl.addressList.isNotEmpty) {
//       int index = locationCtrl.addressList
//           .indexWhere((element) => element.isPrimary == 1);
//       if (index >= 0) {
//         address = locationCtrl.addressList[index];
//       } else {
//         address = locationCtrl.addressList[0];
//       }
//     }
//     if (isPackage) {
//       final packageCtrl =
//           Provider.of<SelectServicemanProvider>(context, listen: false);
//       servicePackageList[selectProviderIndex].primaryAddress = address;
//       packageCtrl.notifyListeners();
//     } else {
//       servicesCart!.primaryAddress = address;
//     }
//     DateTime dateTime = DateTime.now();
//     int index = appArray.monthList
//         .indexWhere((element) => element['index'] == dateTime.month);
//     chosenValue = appArray.monthList[index];
//     notifyListeners();
//   }

//   setAddress(context) {
//     if (isPackage) {
//       final packageCtrl =
//           Provider.of<SelectServicemanProvider>(context, listen: false);
//       servicePackageList[selectProviderIndex].primaryAddress = address;
//       packageCtrl.notifyListeners();
//       notifyListeners();
//     }
//   }

//   onRemoveService(context) {
//     if ((servicesCart!.selectedRequiredServiceMan!) == 1) {
//       route.pop(context);
//     } else {
//       servicesCart!.selectedRequiredServiceMan =
//           ((servicesCart!.selectedRequiredServiceMan!) - 1);
//     }
//     notifyListeners();
//   }

// /*  void onAdd() {
//     int required = servicesCart?.requiredServicemen ?? 1;
//     int current = servicesCart?.selectedRequiredServiceMan ?? required;

//     current++; // Always increment from required
//     servicesCart!.selectedRequiredServiceMan = current;

//     log("Selected Required Service Man Updated: $current");
//     notifyListeners();
//   }*/

//   onAdd() {
//     int count = (servicesCart!.selectedRequiredServiceMan!);
//     count++;
//     log("count::${count}");
//     servicesCart!.selectedRequiredServiceMan = count;

//     notifyListeners();
//   }

//   onDateTimeSelect(context, index) {
//     selectIndex = index;
//     notifyListeners();
//   }

//   onProviderDateTimeSelect(context) async {
//     log("SSS : $selectProviderIndex ");

//     if (selectIndex == 0) {
//       showModalBottomSheet(
//           isScrollControlled: true,
//           context: context,
//           builder: (BuildContext context3) {
//             return DateTimePicker(
//               isWeek: false,
//               isService: isPackage,
//               selectProviderIndex: selectProviderIndex,
//               service: servicesCart,
//             );
//           }).then((value) {
//         log("VVVS :#$value");
//         if (isPackage) {
//           final packageCtrl =
//               Provider.of<SelectServicemanProvider>(context, listen: false);
//           servicePackageList[selectProviderIndex].serviceDate =
//               servicesCart!.serviceDate;
//           servicePackageList[selectProviderIndex].selectDateTimeOption =
//               selectIndex == 0 ? "custom" : "timeSlot";
//           servicePackageList[selectProviderIndex].selectedDateTimeFormat =
//               servicesCart!.selectedDateTimeFormat;
//           notifyListeners();
//           packageCtrl.notifyListeners();
//         }
//       });
//       notifyListeners();
//     } else {
//       await fetchSlotTime(context);
//       // showModalBottomSheet(
//       //     isScrollControlled: true,
//       //     context: context,
//       //     builder: (BuildContext context3) {
//       //       return Consumer<SlotBookingProvider>(
//       //           builder: (context1, value, child) {
//       //             return StatefulBuilder(builder: (context2, setState) {
//       //               return ProviderTimeSlotLayout(
//       //                   isService: isPackage,
//       //                   selectProviderIndex: selectProviderIndex,
//       //                   service: servicesCart);
//       //             });
//       //           });
//       //     }).then((value) {
//       //   log("VVVS : $value");
//       //   if (value == null) {
//       //     focusedDay.value = DateTime.now();
//       //     notifyListeners();
//       //   }
//       //   amIndex = null;
//       //   timeSlot = [];
//       //   notifyListeners();
//       //   if (isPackage) {
//       //     final packageCtrl =
//       //     Provider.of<SelectServicemanProvider>(context, listen: false);
//       //     servicePackageList[selectProviderIndex] = servicesCart!;
//       //     servicePackageList[selectProviderIndex].serviceDate =
//       //         servicesCart!.serviceDate;
//       //     servicePackageList[selectProviderIndex].selectDateTimeOption =
//       //     selectIndex == 0 ? "custom" : "timeSlot";
//       //     servicePackageList[selectProviderIndex].selectedDateTimeFormat =
//       //         servicesCart!.selectedDateTimeFormat;
//       //     notifyListeners();
//       //     packageCtrl.notifyListeners();
//       //     log("packageCtrl.servicePackageList[selectProviderIndex].serviceDate :${servicePackageList[selectProviderIndex].serviceDate}");
//       //   }
//       // });
//     }
//     // notifyListeners();
//   }

//   String getWeekday(String rawDate) {
//     try {
//       final parsedDate =
//           DateTime.parse(rawDate); // Make sure date is "yyyy-MM-dd"
//       return DateFormat('E').format(parsedDate); // Output: Mon, Tue, etc.
//     } catch (e) {
//       return rawDate; // fallback if format fails
//     }
//   }

//   dateTimeSelect(context, isService, {isEdit = false}) {
//     log("isService: $servicesCart");
//     focusedDay.value = DateTime.utc(
//       focusedDay.value.year,
//       focusedDay.value.month,
//       focusedDay.value.day,
//       int.parse(appArray.hourList[scrollHourIndex]),
//       int.parse(appArray.minList[scrollMinIndex]),
//     );
//     notifyListeners();

//     if (isEdit) {
//       route.pop(context, arg: {
//         "date": focusedDay.value,
//         "time": appArray.amPmList[scrollDayIndex]
//       });
//     } else {
//       servicesCart!.serviceDate = focusedDay.value;
//       servicesCart!.selectedDateTimeFormat = appArray.amPmList[scrollDayIndex];
//       notifyListeners();
//       if (isService) {
//         final packageCtrl =
//             Provider.of<SelectServicemanProvider>(context, listen: false);
//         packageCtrl.notifyListeners();
//       }
//       log("isService: ${servicesCart!.selectedDateTimeFormat}");
//       route.pop(context, arg: isService ? servicesCart : focusedDay.value);
//     }
//   }

//   void provideTimeSlotSelect(BuildContext context) async {
//     log("timeIndex : $timeIndex");

//     if (timeIndex != null) {
//       final selectedDate = selectedDay ?? focusedDay.value;
//       final selectedWeekday =
//           DateFormat('EEEE').format(selectedDate).toUpperCase();

//       // Match the day slot
//       final matchedDaySlot = timeSlotModel!.timeSlots.firstWhere(
//         (e) => e.day == selectedWeekday && e.isActive == true,
//         orElse: () =>
//             TimeSlots(day: selectedWeekday, slots: [], isActive: false),
//       );

//       // Get the selected slot time
//       final selectedSlot = matchedDaySlot.slots[timeIndex!];

//       log("Selected slot: $selectedSlot");

//       // Now parse the selected slot time
//       final hour = int.parse(selectedSlot.split(":")[0]);
//       final minute = int.parse(selectedSlot.split(":")[1]);

//       // Construct final DateTime
//       focusedDay.value = DateTime(
//         selectedDate.year,
//         selectedDate.month,
//         selectedDate.day,
//         hour,
//         minute,
//       );

//       servicesCart!.serviceDate = focusedDay.value;
//       servicesCart!.selectedDateTimeFormat =
//           DateFormat("aa").format(focusedDay.value);

//       notifyListeners();

//       log("DOC: $isPackage ///${servicesCart!.serviceDate}");

//       if (isPackage) {
//         final packageCtrl =
//             Provider.of<SelectServicemanProvider>(context, listen: false);

//         servicePackageList[selectProviderIndex] = servicesCart!;
//         servicePackageList[selectProviderIndex].serviceDate =
//             servicesCart!.serviceDate;
//         servicePackageList[selectProviderIndex].selectDateTimeOption =
//             selectIndex == 0 ? "custom" : "timeSlot";
//         servicePackageList[selectProviderIndex].selectedDateTimeFormat =
//             servicesCart!.selectedDateTimeFormat;

//         packageCtrl.notifyListeners();
//         log("DOC:sss $isPackage ///${servicesCart!.serviceDate}");
//       }

//       route.pop(context, arg: isPackage ? servicesCart : focusedDay.value);
//     } else {
//       Fluttertoast.showToast(
//           msg: "Please select time slot", backgroundColor: Colors.red);
//     }
//   }

//   // void provideTimeSlotSelect(BuildContext context) async {
//   //   log("timeIndex : $timeIndex");
//   //   if (timeIndex != null) {
//   //     focusedDay.value = DateTime.utc(
//   //       focusedDay.value.year,
//   //       focusedDay.value.month,
//   //       focusedDay.value.day,
//   //       int.parse(timeSlotModel!.timeSlots[timeIndex!].slots[timeIndex!]
//   //           .toString()
//   //           .split(":")[0]),
//   //       int.parse(timeSlotModel!.timeSlots[timeIndex!].slots[timeIndex!]
//   //           .toString()
//   //           .split(":")[1]),
//   //     );
//   //
//   //     servicesCart!.serviceDate = focusedDay.value;
//   //     servicesCart!.selectedDateTimeFormat =
//   //         DateFormat("aa").format(focusedDay.value);
//   //     notifyListeners();
//   //     log("DOC: $isPackage ///${servicesCart!.serviceDate}");
//   //     if (isPackage) {
//   //       final packageCtrl =
//   //           Provider.of<SelectServicemanProvider>(context, listen: false);
//   //       servicePackageList[selectProviderIndex] = servicesCart!;
//   //       servicePackageList[selectProviderIndex].serviceDate =
//   //           servicesCart!.serviceDate;
//   //       notifyListeners();
//   //       servicePackageList[selectProviderIndex].selectDateTimeOption =
//   //           selectIndex == 0 ? "custom" : "timeSlot";
//   //       servicePackageList[selectProviderIndex].selectedDateTimeFormat =
//   //           servicesCart!.selectedDateTimeFormat;
//   //       packageCtrl.notifyListeners();
//   //       log("DOC:sss $isPackage ///${servicesCart!.serviceDate}");
//   //     }
//   //     route.pop(context, arg: isPackage ? servicesCart : focusedDay.value);
//   //   } else {
//   //     Fluttertoast.showToast(
//   //         msg: "Please select time slot", backgroundColor: Colors.red);
//   //   }
//   // }

//   String buttonName(context) {
//     String name = translations?.next ?? language(context, appFonts.next);
//     log("isPackage ::$isPackage");
//     if (isPackage) {
//       final packageCtrl =
//           Provider.of<SelectServicemanProvider>(context, listen: false);
//       if (servicePackageList.length == 1) {
//         name = translations?.submit ?? language(context, appFonts.submit);
//         return name;
//       } else {
//         log("IMDD:${selectProviderIndex + 1} //$selectProviderIndex");
//         if (selectProviderIndex + 1 < servicePackageList.length) {
//           name = translations?.submit ?? language(context, appFonts.submit);
//         } else {
//           name = translations?.next ?? language(context, appFonts.next);
//         }

//         return name;
//       }
//     } else {
//       return translations?.next ?? language(context, appFonts.next);
//     }
//   }

//   onBack(context) {
//     log("WOEK ");
//     if (isStep2) {
//       isStep2 = false;
//     } else {
//       isStep2 = false;
//       route.pop(context);
//       txtNote.text = "";
//     }
//     if (servicesCart != null) {
//       servicesCart!.serviceDate = null;
//       servicesCart!.selectDateTimeOption = null;
//       if (isPackage) {
//         final packageCtrl =
//             Provider.of<SelectServicemanProvider>(context, listen: false);
//         servicePackageList[selectProviderIndex].serviceDate = null;
//         servicePackageList[selectProviderIndex].selectDateTimeOption = null;
//       }
//       amIndex = null;
//     }
//     notifyListeners();
//   }

//   addToCart(context) async {
//     servicesCart!.primaryAddress = address;
//     notifyListeners();
//     Provider.of<CommonApiProvider>(context, listen: false).selfApi(context);
//     final cartCtrl = Provider.of<CartProvider>(context, listen: false);
//     log("SERVI :${servicesCart!.selectedAdditionalServices}");
//     int index = cartCtrl.cartList.indexWhere((element) =>
//         element.isPackage == false &&
//         element.serviceList != null &&
//         element.serviceList!.id == servicesCart!.id);
//     log("ADDD :${servicesCart!.primaryAddress}");

//     if (index >= 0) {
//       log("message is true");
//       cartCtrl.cartList[index].serviceList = servicesCart;

//       cartCtrl.notifyListeners();
//     } else {
//       log("message is false");
//       CartModel cartModel =
//           CartModel(isPackage: false, serviceList: servicesCart);
//       cartCtrl.cartList.add(cartModel);
//       cartCtrl.notifyListeners();
//     }

//     SharedPreferences preferences = await SharedPreferences.getInstance();
//     preferences.remove(session.cart);

//     List<String> personsEncoded =
//         cartCtrl.cartList.map((person) => jsonEncode(person.toJson())).toList();
//     await preferences.setString(session.cart, json.encode(personsEncoded));

//     cartCtrl.notifyListeners();
//     cartCtrl.checkout(context);
//     isStep2 = false;
//     selectIndex = 0;
//     txtNote.text = "";
//     servicesCart = null;
//     final selectOption =
//         Provider.of<SelectServicemanProvider>(context, listen: false);
//     final providerDetail =
//         Provider.of<ProviderDetailsProvider>(context, listen: false);
//     selectOption.servicePackageModel = null;
//     providerDetail.selectProviderIndex = 0;
//     providerDetail.selectIndex = 0;
//     focusedDay.value = DateTime.now();
//     notifyListeners();

//     route.pushNamed(context, routeName.cartScreen);
//   }

//   Step2Model? step2Data;

//   /* callBookingApi(serviceiId, requiredServicemen, addons) async {
//     final dio = Dio();

//     log("message -=-=-= ${[
//       servicesCart?.selectedAdditionalServices?.map((e) => e.id).join(',')
//     ]}");

//     /*  var addons =
//         servicesCart?.selectedAdditionalServices?.map((e) => e.id).join(',');

//     log(" popopoppoppo ${[addons]}"); */

//     SharedPreferences pref = await SharedPreferences.getInstance();
//     String? token = pref.getString(session.accessToken);
//     try {
//       final response = await dio.get(
//         api.step2Booking,
//         queryParameters: {
//           'service_id': servicesCart?.id,
//           'required_servicemen': servicesCart?.requiredServicemen,
//           /* if (servicesCart?.selectedAdditionalServices != null) */
//           "additional_services": addons
//         },
//         options: Options(
//           headers: {
//             'Authorization': 'Bearer $token',
//             'Accept': 'application/json',
//           },
//         ),
//       );

//       log("uhyusdfhyudfsi ${response.requestOptions.data}");
//       log("uhyusdfhyudfsi ${response.headers}");
//       print('Response: ${response.data}');

//       if (response.statusCode == 200) {
//         step2Data = Step2Model.fromJson(response.data);
//       }
//     } catch (e) {
//       print('Error: $e');
//     }
//   } */

//   /* callBookingApi(serviceId, requiredServicemen, additionalServices) async {
//     final dio = Dio();

//     SharedPreferences pref = await SharedPreferences.getInstance();
//     String? token = pref.getString(session.accessToken);

//     // Simulate JSON body in query params
//     final queryParams = {
//       "service_id": serviceId,
//       "required_servicemen": requiredServicemen,
//       "additional_services": additionalServices,
//     };

//     log("Query Params: $queryParams");

//     try {
//       final response = await dio.get(
//         api.step2Booking,
//         queryParameters: queryParams,
//         options: Options(
//           headers: {
//             "Authorization": "Bearer $token",
//             "Accept": "application/json",
//           },
//         ),
//       );

//       log("Response:sssss ${jsonEncode(response.data)}");

//       if (response.statusCode == 200) {
//         step2Data = Step2Model.fromJson(response.data);

//         log("step2Data ${step2Data?.addonsTotalAmount}");
//       }
//     } catch (e, s) {
//       print("Error: $e======$s");
//     }
//   } */

//   Future<void> callBookingApi(
//       int? serviceId, int? requiredServicemen, additionalServices) async {
//     log("Query Params: $additionalServices");
//     final queryParams = {
//       "service_id": serviceId,
//       "required_servicemen": requiredServicemen,
//       "additional_services": additionalServices,
//     };

//     notifyListeners();
//     log("Query Params: $queryParams");
//     // log("Full URL: ${Uri.parse(api.step2Booking).replace(queryParameters: queryParams)}");

//     try {
//       await apiServices
//           .getApi(api.step2Booking, queryParams, isToken: true, isData: true)
//           .then(
//         (value) {
//           if (value.isSuccess!) {
//             step2Data = Step2Model.fromJson(value.data);
//             log("message-=-=-=-=-=-=-=-=-=-=-=-=-=${value.data}");
//           }

//           notifyListeners();
//         },
//       );
//     } catch (e, s) {
//       print("API Error: $e\nStackTrace: $s");
//     }
//   }

//   // callBookingApi(serviceId, requiredServicemen, addons) async {
//   //   final dio = Dio();

//   //   SharedPreferences pref = await SharedPreferences.getInstance();
//   //   String? token = pref.getString(session.accessToken);

//   //   final queryParams = {
//   //     'service_id': serviceId,
//   //     'required_servicemen': requiredServicemen,
//   //     'additional_services': addons,
//   //   };

//   //   log("Query Parameters => $queryParams");

//   //   try {
//   //     final response = await dio.get(
//   //       api.step2Booking,
//   //       queryParameters: queryParams,
//   //       options: Options(
//   //         headers: {
//   //           'Authorization': 'Bearer $token',
//   //           'Accept': 'application/json',
//   //         },
//   //       ),
//   //     );

//   //     log("Request URL => ${response.requestOptions.uri}");
//   //     print('Response: ${response.data}');

//   //     if (response.statusCode == 200) {
//   //       step2Data = Step2Model.fromJson(response.data);
//   //     }
//   //   } catch (e) {
//   //     print('Error: $e');
//   //   }
//   // }
// }

import 'dart:convert';
import 'dart:developer';

import 'package:collection/collection.dart';
import 'package:dio/dio.dart';
import 'package:fixit_user/models/step2model.dart';
import 'package:flutter/cupertino.dart';

import 'package:fluttertoast/fluttertoast.dart';
import 'package:intl/intl.dart';
import 'package:table_calendar/table_calendar.dart';
import '../../config.dart';
import '../../models/time_slot_model.dart';
import '../../screens/app_pages_screens/slot_booking_screen/layouts/provider_time_slot_layout.dart';
import '../../screens/app_pages_screens/slot_booking_screen/layouts/year_dialog.dart';
import '../../utils/date_time_picker.dart';

int getHashCode(DateTime key) {
  return key.day * 1000000 + key.month * 10000 + key.year;
}

class SlotBookingProvider with ChangeNotifier {
  Services? servicesCart;
  int selectIndex = 0;
  bool isStep2 = false, isPackage = false, isBottom = true;
  PrimaryAddress? address;
  int selectProviderIndex = 0;
  List timeSlot = [];
  List newTimeSlot = [];
  int demoInt = 0;
  dynamic chosenValue;
  DateTime? selectedDay;
  DateTime selectedYear = DateTime.now();
  ScrollController scrollController = ScrollController();

  dynamic slotChosenValue, slotTime;
  DateTime? slotSelectedDay;
  DateTime slotSelectedYear = DateTime.now();

  int selectedIndex = 0;
  int scrollDayIndex = 0;
  int scrollMinIndex = 0;
  int scrollHourIndex = 0;
  String showYear = 'Select Year';
  TimeSlotModel? timeSlotModel;
  PageController pageController = PageController();
  CalendarFormat calendarFormat = CalendarFormat.week;
  CalendarFormat calendarFormatMonth = CalendarFormat.month;
  CarouselSliderController carouselController = CarouselSliderController();
  CarouselSliderController carouselController1 = CarouselSliderController();
  CarouselSliderController carouselController2 = CarouselSliderController();
  final ValueNotifier<DateTime> focusedDay = ValueNotifier(DateTime.now());
  final FocusNode noteFocus = FocusNode();
  bool visible = true;
  int val = 1;
  double loginWidth = 100.0;
  TextEditingController txtNote = TextEditingController();
  int? amIndex;

  int? timeIndex;
  bool isVisible = false;

  bool isPositionedRight = false;
  bool isAnimateOver = false;
  AnimationController? controller;
  Animation<Offset>? offsetAnimation;

  @override
  void dispose() {
    scrollController.dispose();
    pageController.dispose();
    noteFocus.dispose();
    txtNote.clear();
    controller?.dispose();
    super.dispose();
  }

  addNewLoc(context) {
    final loc = Provider.of<LocationProvider>(context, listen: false);
    route.pushNamed(context, routeName.currentLocation).then((e) async {
      await loc.getLocationList(context);
      if (loc.addressList.length == 1) {
        address = loc.addressList[0];
        notifyListeners();
      }
    });
  }

  Future<void> fetchTimeSlots() async {
    try {
      log("servicesCart!.user!.id::${servicesCart!.user!.id}");
      // isLoading = true;
      notifyListeners();
      final response = await apiServices.getApi(
        "${api.providerTimeSlot}/3",
        [],
        isToken: true,
      );
      log("GET API Response: ${response.toString()}");

      if (response.isSuccess!) {
        final timeSlots = response.data['time_slots'] as List<dynamic>;
        for (var slot in timeSlots) {
          final day = slot['day'].toString().toUpperCase();
          final slots = List<String>.from(slot['slots'] ?? []);
          final isActive = slot['is_active'] == 1 ? 1 : 0;

          /*  final dayIndex = appArray.timeSlotList.indexWhere(
                (d) => d.day?.toUpperCase() == day,
          );
          if (dayIndex != -1) {
            appArray.timeSlotList[dayIndex].slots = slots;
            appArray.timeSlotList[dayIndex].status = isActive;

            if (selectedSlots.isNotEmpty && timeList.isNotEmpty) {
              selectedSlots[dayIndex] = List.generate(
                timeList.length,
                    (i) => slots.contains(to24HourFormat(timeList[i])),
              );
            }
          }*/
        }
      } else {
        log("GET API failed: ${response.message}");
      }
    } catch (e, s) {
      log("Fetch time slots error: $e\n$s");
    } finally {
      // isLoading = false;
      notifyListeners();
    }
  }

  deleteJobRequestConfirmation(context, sync, index) {
    animateDesign(sync);
    showDialog(
        context: context,
        builder: (context1) {
          return StatefulBuilder(builder: (context2, setState) {
            return Consumer<SlotBookingProvider>(
                builder: (context3, value, child) {
              return AlertDialog(
                  contentPadding: EdgeInsets.zero,
                  insetPadding:
                      const EdgeInsets.symmetric(horizontal: Insets.i20),
                  shape: const SmoothRectangleBorder(
                      borderRadius: SmoothBorderRadius.all(SmoothRadius(
                          cornerRadius: AppRadius.r14, cornerSmoothing: 1))),
                  backgroundColor: appColor(context).whiteBg,
                  content: Stack(alignment: Alignment.topRight, children: [
                    Column(mainAxisSize: MainAxisSize.min, children: [
                      // Gif
                      Stack(alignment: Alignment.topCenter, children: [
                        Stack(alignment: Alignment.topCenter, children: [
                          SizedBox(
                                  width: MediaQuery.of(context).size.width,
                                  child: Stack(
                                      alignment: Alignment.bottomCenter,
                                      children: [
                                        SizedBox(
                                            height: Sizes.s208,
                                            width: Sizes.s150,
                                            child: AnimatedContainer(
                                                duration: const Duration(
                                                    milliseconds: 200),
                                                curve: isPositionedRight
                                                    ? Curves.bounceIn
                                                    : Curves.bounceOut,
                                                alignment: isPositionedRight
                                                    ? Alignment.center
                                                    : Alignment.topCenter,
                                                child: AnimatedContainer(
                                                    duration: const Duration(
                                                        milliseconds: 200),
                                                    height: 40,
                                                    child: Image.asset(
                                                        eImageAssets
                                                            .removeAddOn)))),
                                        Image.asset(eImageAssets.dustbin,
                                                height: Sizes.s88,
                                                width: Sizes.s88)
                                            .paddingOnly(bottom: Insets.i24)
                                      ]))
                              .decorated(
                                  color: appColor(context).fieldCardBg,
                                  borderRadius:
                                      BorderRadius.circular(AppRadius.r10)),
                        ]),
                        if (offsetAnimation != null)
                          SlideTransition(
                              position: offsetAnimation!,
                              child: (offsetAnimation != null &&
                                      isAnimateOver == true)
                                  ? Image.asset(eImageAssets.dustbinCover,
                                      height: 38)
                                  : const SizedBox())
                      ]),
                      // Sub text
                      const VSpace(Sizes.s15),
                      Text(language(context, translations!.removeAddOnsDes),
                              textAlign: TextAlign.center,
                              style: appCss.dmDenseRegular14
                                  .textColor(appColor(context).lightText)
                                  .textHeight(1.6))
                          .paddingSymmetric(horizontal: Sizes.s56),
                      const VSpace(Sizes.s25),
                      Row(children: [
                        Expanded(
                            child: ButtonCommon(
                                onTap: () => route.pop(context),
                                title: translations?.no ??
                                    language(context, appFonts.no),
                                borderColor: appColor(context).primary,
                                color: appColor(context).whiteBg,
                                style: appCss.dmDenseSemiBold16
                                    .textColor(appColor(context).primary))),
                        const HSpace(Sizes.s15),
                        Expanded(
                            child: ButtonCommon(
                                color: appColor(context).primary,
                                onTap: () {
                                  servicesCart!.selectedAdditionalServices!
                                      .removeAt(index);
                                  notifyListeners();
                                  route.pop(context);
                                },
                                style: appCss.dmDenseSemiBold16
                                    .textColor(appColor(context).whiteColor),
                                title: translations?.yes ??
                                    language(context, appFonts.yes)))
                      ])
                    ]).padding(
                        horizontal: Insets.i20,
                        top: Insets.i60,
                        bottom: Insets.i20),
                    Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          // Title
                          Text(language(context, translations!.removeAddOns),
                              style: appCss.dmDenseExtraBold18
                                  .textColor(appColor(context).darkText)),
                          Icon(CupertinoIcons.multiply,
                                  size: Sizes.s20,
                                  color: appColor(context).darkText)
                              .inkWell(onTap: () => route.pop(context))
                        ]).paddingAll(Insets.i20)
                  ]));
            });
          });
        }).then((value) {
      isPositionedRight = false;
      isAnimateOver = false;
      notifyListeners();
    });
  }

  animateDesign(TickerProvider sync) {
    Future.delayed(DurationClass.s1).then((value) {
      isPositionedRight = true;
      notifyListeners();
    }).then((value) {
      Future.delayed(DurationClass.ms150).then((value) {
        isAnimateOver = true;
        notifyListeners();
      }).then((value) {
        controller = AnimationController(
            vsync: sync, duration: const Duration(seconds: 2))
          ..forward();
        offsetAnimation = Tween<Offset>(
                begin: const Offset(0, 0.5), end: const Offset(0, 1.6))
            .animate(
                CurvedAnimation(parent: controller!, curve: Curves.elasticOut));
        notifyListeners();
      });
    });

    notifyListeners();
  }

  bool isNextLoading = false;
  List? selectedAddons;
  Future<void> onTapNext(BuildContext context) async {
    isNextLoading = true;
    notifyListeners();

    servicesCart!.selectedServiceMan = null;

    if (isPackage) {
      log("selectProviderIndex :$selectProviderIndex");
      servicePackageList[selectProviderIndex] = servicesCart!;
      servicePackageList[selectProviderIndex].selectedServiceNote =
          txtNote.text;

      if (servicePackageList[selectProviderIndex].serviceDate != null) {
        servicesCart!.serviceDate = focusedDay.value;
        servicesCart!.selectedDateTimeFormat =
            DateFormat("aa").format(focusedDay.value);
        servicePackageList[selectProviderIndex].serviceDate = focusedDay.value;
        servicePackageList[selectProviderIndex].selectServiceManType =
            "app_choose";
        log("packageCtrl.servicePackageList[selectProviderIndex].selectServiceManType : ${servicesCart!.media}");

        isStep2 = false;
        isNextLoading = false;
        notifyListeners();
        route.pop(context);
      } else {
        isNextLoading = false;
        notifyListeners();
        Fluttertoast.showToast(
          msg: "Please Select the Date & Time Slot",
          backgroundColor: Colors.red,
        );
      }
    } else {
      callBookingApi(
          servicesCart?.id,
          servicesCart?.requiredServicemen,
          servicesCart?.selectedAdditionalServices
              ?.map((service) => {
                    "id": service.id,
                    "qty": service.qty,
                  })
              .toList() /* servicesCart?.selectedAdditionalServices?.map((e) => e.id).toList() */);

      notifyListeners();
      log("message -=-=-=-=-=-=-=-=-= ${servicesCart?.id}-=-=-=-=-=${servicesCart?.requiredServicemen}======== ${servicesCart?.selectedAdditionalServices?.map((e) => e.id).join(',')}");
      servicesCart!.selectedServiceNote = txtNote.text;
      servicesCart!.selectServiceManType = "app_choose";

      if (servicesCart!.serviceDate != null) {
        if (address != null) {
          log("servicesCart!.selectServiceManType : ${servicesCart!.id}");
          isStep2 = true;
          isNextLoading = false;
          notifyListeners();
        } else {
          txtNote.text = "";
          isNextLoading = false;
          notifyListeners();
          Fluttertoast.showToast(
            msg: language(context, translations!.selectAddress),
            backgroundColor: Colors.red,
          );
        }
      } else {
        isNextLoading = false;
        notifyListeners();
        Fluttertoast.showToast(
          msg: 'Please Select the Date & Time Slot',
          backgroundColor: Colors.red,
        );
      }
    }
  }

  onChangeSlot(index) {
    timeIndex = index;
    notifyListeners();
    checkSlotAvailable();
  }

  void onAmPmChange(BuildContext context, int index) {
    scrollDayIndex = index;
    amIndex = index;
    notifyListeners();
    filterSlotByAmPm(context);
  }

  Future<void> filterSlotByAmPm(BuildContext context) async {
    showLoading(context);
    // timeSlot.clear();
    notifyListeners();

    if (timeSlotModel?.timeSlots == null) {
      hideLoading(context);
      return;
    }

    String day = DateFormat('EEEE').format(focusedDay.value).toUpperCase();
    List<TimeSlots> dayWeek = timeSlotModel!.timeSlots;
    int index = dayWeek.indexWhere(
        (element) => element.day.toLowerCase() == day.toLowerCase());

    if (index >= 0 && dayWeek[index].isActive == "1") {
      List<String> newTimeSlot = dayWeek[index].slots;
      bool isToday = isSameDay(focusedDay.value, DateTime.now());
      for (String slot in newTimeSlot) {
        int hour = int.parse(slot.split(":")[0]);
        // Filter for AM/PM
        if (scrollDayIndex == 0 && hour < 12) {
          // AM: Include slots before 12:00 PM
          timeSlot.add(slot);
          log("timeSlot:::$timeSlot");
        } else if (scrollDayIndex == 1 && hour >= 12) {
          // PM: Include slots from 12:00 PM onwards
          timeSlot.add(slot);
        }
        // Restrict past times for today
        if (isToday) {
          int slotHour = hour;
          int slotMinute = int.parse(slot.split(":")[1]);
          DateTime now = DateTime.now();
          if (scrollDayIndex == 0 && now.hour >= 12) {
            timeSlot.remove(slot); // Remove AM slots if past noon
          } else if (slotHour < now.hour ||
              (slotHour == now.hour && slotMinute < now.minute)) {
            timeSlot.remove(slot); // Remove past times
          }
        }
      }
    }

    timeIndex = null;
    hideLoading(context);
    notifyListeners();
  }

  onChangeLocation(context, PrimaryAddress primaryAddress) async {
    final loc = Provider.of<LocationProvider>(context, listen: false);
    await loc.getLocationList(context);
    address = primaryAddress;
    if (isPackage) {
      final packageCtrl =
          Provider.of<SelectServicemanProvider>(context, listen: false);
      servicePackageList[selectProviderIndex].primaryAddress = address;
      packageCtrl.notifyListeners();
    } else {
      servicesCart!.primaryAddress = address;
    }

    notifyListeners();
  }

  //
  // void onDaySelected(DateTime selectDay, DateTime fDay, BuildContext context) {
  //   if (!isSameDay(selectedDay, selectDay)) {
  //     selectedDay = selectDay;
  //     focusedDay.value = fDay;
  //     scrollDayIndex =
  //         isSameDay(selectDay, DateTime.now()) && DateTime.now().hour >= 12
  //             ? 1
  //             : 0;
  //     amIndex = scrollDayIndex;
  //     timeIndex = null;
  //     // carouselController2.jumpToPage(scrollDayIndex);
  //     updateTimeSlotsForSelectedDay(context: context);
  //   }
  // }
  void onDaySelected(DateTime selectDay, DateTime fDay, BuildContext context) {
    if (!isSameDay(selectedDay, selectDay)) {
      selectedDay = selectDay;
      focusedDay.value = fDay;
      scrollDayIndex =
          isSameDay(selectDay, DateTime.now()) && DateTime.now().hour >= 12
              ? 1
              : 0;
      amIndex = scrollDayIndex;
      timeIndex = null;
      log(">>> onDaySelected: $selectDay");
      log(">>> Weekday: ${DateFormat('EEEE').format(selectDay)}");

      // Refresh time slot list (if applicable)
      updateTimeSlotsForSelectedDay(context: context);

      // 🔥 Add this line
      notifyListeners();
    }
  }

  void onProviderDaySelected(
      DateTime selectDay, DateTime fDay, BuildContext context) {
    if (!isSameDay(selectedDay, selectDay)) {
      selectedDay = selectDay;
      focusedDay.value = fDay;
      scrollDayIndex =
          isSameDay(selectDay, DateTime.now()) && DateTime.now().hour >= 12
              ? 1
              : 0;
      amIndex = scrollDayIndex;
      timeIndex = null;
      // carouselController2.jumpToPage(scrollDayIndex);
      updateTimeSlotsForSelectedDay(context: context);
    }
  }
  /* void onDaySelected(DateTime selectDay, DateTime fDay, BuildContext context) {
    if (!isSameDay(selectedDay, selectDay)) {
      selectedDay = selectDay;
      focusedDay.value = fDay;
      amIndex = null; // Reset AM/PM selection
      timeIndex = null; // Reset time slot selection
      updateTimeSlotsForSelectedDay(context: context);
    }
  }*/

  /*void onDaySelected(DateTime selectDay, DateTime fDay, context) async {
    log("SSSS :$selectDay // $fDay");
    notifyListeners();
    */ /*focusedDay.value = selectDay;*/ /*
    if (!isSameDay(selectedDay, selectDay)) {
      // Call `setState()` when updating the selected day
      selectedDay = selectedDay;
      focusedDay.value = fDay;

      log("SEKE : $slotTime");
    }
    notifyListeners();
    String day = DateFormat('EEEE').format(focusedDay.value);

    if (slotTime != null) {
      int ind = timeSlotModel!.timeSlots!.indexWhere(
          (element) => element.day!.toLowerCase() == day.toLowerCase());

      if (ind >= 0) {
        log("ind :${timeSlotModel!.timeSlots![ind].status}");
        if (timeSlotModel!.timeSlots![ind].status == "1") {
          String gap = timeSlotModel!.timeUnit == "hours"
              ? "${timeSlotModel!.gap}:00"
              : "00:${timeSlotModel!.gap}";

          timeSlot = await slots(
                  timeSlotModel!.timeSlots![ind].startTime!.split(" ")[0],
                  timeSlotModel!.timeSlots![ind].endTime,
                  gap) ??
              [];

          if (timeSlot.isNotEmpty) {
            focusedDay.value = DateTime.utc(
                focusedDay.value.year,
                focusedDay.value.month,
                focusedDay.value.day,
                DateTime.now().hour,
                DateTime.now().minute);
            checkSlotAvailable();
          }
        } else {
          timeSlot = [];
          notifyListeners();
        }
      } else {
        timeSlot = [];
        notifyListeners();
      }
    } else {
      fetchSlotTime(context);
    }
  }*/

  int count = 0;
  void onInit(
    BuildContext context, {
    bool? isPackage,
    int? index,
    Services? service,
    bool isProviderTimeSlot = false,
  }) async {
    this.isPackage = isPackage ?? false;
    selectProviderIndex = index ?? 0;
    servicesCart = service;

    // Initialize date and time
    DateTime now = DateTime.now();
    DateTime twoHoursLater = now.add(const Duration(hours: 2, minutes: 2));

    if (servicesCart?.serviceDate != null) {
      // Use service date if provided
      focusedDay.value = servicesCart!.serviceDate!;
      selectedDay = servicesCart!.serviceDate;

      // Convert hour to 12-hour format
      int hour = servicesCart!.serviceDate!.hour;
      int displayHour = hour % 12 == 0 ? 12 : hour % 12;
      scrollHourIndex = appArray.hourList
          .indexWhere((element) => element == displayHour.toString());

      // Set minute
      scrollMinIndex = appArray.minList.indexWhere((element) =>
          element ==
          servicesCart!.serviceDate!.minute.toString().padLeft(2, '0'));

      // Set AM/PM
      amIndex = servicesCart!.selectedDateTimeFormat == "AM" ? 0 : 1;
    } else {
      fetchSlotTime(context);
      // Set to current time + 2 hours
      focusedDay.value = twoHoursLater;
      selectedDay = twoHoursLater;
      // Convert hour to 12-hour format
      int hour = twoHoursLater.hour;
      int displayHour = hour % 12 == 0 ? 12 : hour % 12;
      scrollHourIndex = appArray.hourList
          .indexWhere((element) => element == displayHour.toString());

      // Set minute
      scrollMinIndex = appArray.minList.indexWhere((element) =>
          element == twoHoursLater.minute.toString().padLeft(2, '0'));
      scrollDayIndex = twoHoursLater.hour >= 12 ? 0 : 1;
      // Set AM/PM
      amIndex = twoHoursLater.hour >= 12 ? 0 : 1;
    }
    // Jump to initial positions
    carouselController.jumpToPage(scrollHourIndex);
    carouselController1.jumpToPage(scrollMinIndex);
    carouselController2.jumpToPage(scrollDayIndex ?? 0);
    // Handle case where hour or minute not found in lists
    if (scrollHourIndex == -1) scrollHourIndex = 0;
    if (scrollMinIndex == -1) scrollMinIndex = 0;

    // Initialize month dropdown
    int monthIndex = appArray.monthList
        .indexWhere((element) => element['index'] == focusedDay.value.month);
    chosenValue = appArray.monthList[monthIndex];

    await fetchSlotTime(context);
    notifyListeners();
  }
// // Updated onInit method
//   void onInit(
//     BuildContext context, {
//     bool? isPackage,
//     int? index,
//     Services? service,
//     bool isProviderTimeSlot = false,
//   }) async
//   {
//     await fetchSlotTime(context);
//     this.isPackage = isPackage ?? false;
//     selectProviderIndex = index ?? 0;
//     servicesCart = service;
//
//     txtNote.text = "";
//     // Initialize date and time
//     DateTime now = DateTime.now();
//     DateTime twoHoursLater = now.add(const Duration(hours: 2));
//
//     if (servicesCart?.serviceDate != null) {
//       await fetchSlotTime(context);
//       // Use service date if provided
//       focusedDay.value = servicesCart!.serviceDate!;
//       selectedDay = servicesCart!.serviceDate;
//
//       // Convert hour to 12-hour format
//       int hour = servicesCart!.serviceDate!.hour;
//       int displayHour = hour % 12 == 0 ? 12 : hour % 12;
//       scrollHourIndex = appArray.hourList
//           .indexWhere((element) => element == displayHour.toString());
//
//       // Set minute
//       scrollMinIndex = appArray.minList.indexWhere((element) =>
//           element ==
//           servicesCart!.serviceDate!.minute.toString().padLeft(2, '0'));
//
//       // Set AM/PM
//       amIndex = servicesCart!.selectedDateTimeFormat == "AM" ? 0 : 1;
//     } else {
//       // Set to current time + 2 hours
//       focusedDay.value = twoHoursLater;
//       selectedDay = twoHoursLater;
//
//       // Convert hour to 12-hour format
//       int hour = twoHoursLater.hour;
//       int displayHour = hour % 12 == 0 ? 12 : hour % 12;
//       scrollHourIndex = appArray.hourList
//           .indexWhere((element) => element == displayHour.toString());
//
//       // Set minute
//       scrollMinIndex = appArray.minList.indexWhere((element) =>
//           element == twoHoursLater.minute.toString().padLeft(2, '0'));
//
//       // Set AM/PM
//       amIndex = twoHoursLater.hour >= 12 ? 0 : 1;
//     }
//     // if (isProviderTimeSlot == false) {
//     scrollHourIndex = appArray.hourList.indexWhere((element) {
//       log("EE : $element");
//       log("EE : ${focusedDay.value.hour}");
//       return element == focusedDay.value.hour.toString();
//     });
//     scrollMinIndex = appArray.minList
//         .indexWhere((element) => element == focusedDay.value.minute.toString());
//     log("index : ${focusedDay.value.hour}");
//     log("index : $scrollHourIndex");
//     notifyListeners();
//     await Future.delayed(DurationClass.s3);
//
//     carouselController.jumpToPage(scrollHourIndex);
//
//     carouselController1.jumpToPage(scrollMinIndex);
//     amIndex = servicesCart!.selectedDateTimeFormat == "AM" ? 0 : 1;
//     carouselController2.jumpToPage(amIndex!);
//
//     notifyListeners();
// /*    } else {
//       // Handle case where hour or minute not found in lists
//       if (scrollHourIndex == -1) scrollHourIndex = 0;
//       if (scrollMinIndex == -1) scrollMinIndex = 0;
//
//       // Jump to initial positions
//       carouselController.jumpToPage(scrollHourIndex);
//       carouselController1.jumpToPage(scrollMinIndex);
//       carouselController2.jumpToPage(amIndex ?? 0);
//       notifyListeners();
//       // Initialize month dropdown
//       int monthIndex = appArray.monthList
//           .indexWhere((element) => element['index'] == focusedDay.value.month);
//       chosenValue = appArray.monthList[monthIndex];
//
//       // await fetchSlotTime(context);
//       notifyListeners();
//     }*/
//   }
/*
  onInit(context,
      {isPackage = false,
      index,
      isEdit = false,
      service,
      sync,
      isProviderTimeSlot = false}) async
  {
    log("isPackage :$isPackage");
    fetchTimeSlots();
    if (isEdit) {
      servicesCart = service;
      focusedDay.value = DateTime.utc(focusedDay.value.year,
          focusedDay.value.month, focusedDay.value.day + 0);
      onDaySelected(focusedDay.value, focusedDay.value, context);
      DateTime dateTime = DateTime.now();
      int index = appArray.monthList
          .indexWhere((element) => element['index'] == dateTime.month);
      chosenValue = appArray.monthList[index];
      notifyListeners();
    } else {
      fetchSlotTime(context);
      log("dfjghjkdlkbhlfih");
      focusedDay.value = DateTime.now();
      if (isPackage) {
        final packageCtrl =
            Provider.of<SelectServicemanProvider>(context, listen: false);
        servicesCart = packageCtrl.servicePackageModel!.services![index];
        servicesCart = servicesCart;
      } else {
        servicesCart = service;
      }
      notifyListeners();

      if (servicesCart!.serviceDate != null) {
        if (timeSlotModel != null) {
          String gap = timeSlotModel!.timeUnit == "hour"
              ? "${timeSlotModel!.gap}:00"
              : "00:${timeSlotModel!.gap}";
          focusedDay.value = servicesCart!.serviceDate!;
          String day = DateFormat('EEEE').format(focusedDay.value);
          int listIndex = timeSlotModel!.timeSlots!
              .indexWhere((element) => element.day!.toLowerCase() == day);
          if (listIndex >= 0) {
            if (timeSlotModel!.timeSlots![listIndex].status == "1") {
              timeSlot = await slots(slotTime["timeSlot"]["start_time"],
                  slotTime["timeSlot"]["end_time"], gap);
            } else {
              timeSlot = [];
            }
          }
        }
        if (isProviderTimeSlot == false) {
          scrollHourIndex = appArray.hourList.indexWhere((element) {
            log("EE : $element");
            log("EE : ${focusedDay.value.hour}");
            return element == focusedDay.value.hour.toString();
          });
          scrollMinIndex = appArray.minList.indexWhere(
              (element) => element == focusedDay.value.minute.toString());
          log("index : ${focusedDay.value.hour}");
          log("index : $scrollHourIndex");
          notifyListeners();
          await Future.delayed(DurationClass.s3);

          carouselController.jumpToPage(scrollHourIndex);

          carouselController1.jumpToPage(scrollMinIndex);
          amIndex = servicesCart!.selectedDateTimeFormat == "AM" ? 0 : 1;
          carouselController2.jumpToPage(amIndex!);

          notifyListeners();
        } else {
          focusedDay.value = DateTime.utc(focusedDay.value.year,
              focusedDay.value.month, focusedDay.value.day + 0);
          onDaySelected(focusedDay.value, focusedDay.value, context);
          DateTime dateTime = DateTime.now();
          int index = appArray.monthList
              .indexWhere((element) => element['index'] == dateTime.month);
          chosenValue = appArray.monthList[index];
          log("index : $dateTime");
          scrollHourIndex = appArray.hourList.indexWhere((element) {
            return element == dateTime.hour.toString();
          });
          scrollMinIndex = appArray.minList
              .indexWhere((element) => element == dateTime.minute.toString());
          log("scrollHourIndex :$scrollHourIndex");
          carouselController.jumpToPage(scrollHourIndex);

          carouselController1.jumpToPage(scrollMinIndex);
          amIndex = DateFormat("aa").format(dateTime) == "AM" ? 0 : 1;
          carouselController2.jumpToPage(amIndex!);
        }
      } else {
        if (isProviderTimeSlot == false) {
          focusedDay.value = DateTime.utc(focusedDay.value.year,
              focusedDay.value.month, focusedDay.value.day + 0);
          onDaySelected(focusedDay.value, focusedDay.value, context);
          DateTime dateTime = DateTime.now();
          int index = appArray.monthList
              .indexWhere((element) => element['index'] == dateTime.month);
          chosenValue = appArray.monthList[index];
          log("index : $dateTime");
          scrollHourIndex = appArray.hourList.indexWhere((element) {
            return element == dateTime.hour.toString();
          });
          scrollMinIndex = appArray.minList
              .indexWhere((element) => element == dateTime.minute.toString());
          log("scrollHourIndex :$scrollHourIndex");
          carouselController.jumpToPage(scrollHourIndex);

          carouselController1.jumpToPage(scrollMinIndex);
          amIndex = DateFormat("aa").format(dateTime) == "AM" ? 0 : 1;
          carouselController2.jumpToPage(amIndex!);
        }
      }
    }
    notifyListeners();
    fetchSlotTime(context);
  }*/

  Future<void> fetchSlotTime(BuildContext context) async {
    // timeSlot = [];
    showLoading(context);
    notifyListeners();
    try {
      final response = await apiServices.getApi(
        "${api.providerTimeSlot}/3",
        [],
        isData: true,
        isToken: true,
      );
      log("CALLA :${response.data}");
      if (response.isSuccess == true) {
        timeSlotModel = TimeSlotModel.fromJson(response.data);
        updateTimeSlotsForSelectedDay();
        // showModalBottomSheet(
        //     isScrollControlled: true,
        //     context: context,
        //     builder: (BuildContext context3) {
        //       return Consumer<SlotBookingProvider>(
        //           builder: (context1, value, child) {
        //         return StatefulBuilder(builder: (context2, setState) {
        //           return ProviderTimeSlotLayout(
        //               isService: isPackage,
        //               selectProviderIndex: selectProviderIndex,
        //               service: servicesCart);
        //         });
        //       });
        //     }).then((value) {
        //   log("VVVS : $value");
        //   if (value == null) {
        //     focusedDay.value = DateTime.now();
        //     notifyListeners();
        //   }
        //   amIndex = null;
        //   timeSlot = [];
        //   notifyListeners();
        //   if (isPackage) {
        //     final packageCtrl =
        //         Provider.of<SelectServicemanProvider>(context, listen: false);
        //     servicePackageList[selectProviderIndex] = servicesCart!;
        //     servicePackageList[selectProviderIndex].serviceDate =
        //         servicesCart!.serviceDate;
        //     servicePackageList[selectProviderIndex].selectDateTimeOption =
        //         selectIndex == 0 ? "custom" : "timeSlot";
        //     servicePackageList[selectProviderIndex].selectedDateTimeFormat =
        //         servicesCart!.selectedDateTimeFormat;
        //     notifyListeners();
        //     packageCtrl.notifyListeners();
        //     log("packageCtrl.servicePackageList[selectProviderIndex].serviceDate :${servicePackageList[selectProviderIndex].serviceDate}");
        //   }
        // });
      } else {
        log("GET API failed: ${response.message}");
        Fluttertoast.showToast(
            msg: response.message, backgroundColor: Colors.red);
      }
    } catch (e) {
      log("EEEE fetchSlotTime: $e");
      Fluttertoast.showToast(
          msg: "Failed to fetch time slots", backgroundColor: Colors.red);
    } finally {
      hideLoading(context);
      notifyListeners();
    }
  }

  void updateTimeSlotsForSelectedDay({BuildContext? context}) {
    if (timeSlotModel?.timeSlots == null || focusedDay.value == null) {
      timeSlot = [];
      timeIndex = null;
      notifyListeners();
      return;
    }
    final day = DateFormat('EEEE').format(focusedDay.value).toUpperCase();
    final slot = timeSlotModel!.timeSlots.firstWhereOrNull(
      (element) => element.day.toUpperCase() == day,
    );
    timeSlot = (slot != null && slot.isActive == 1) ? slot.slots : [];
    timeIndex = null; // Reset selected slot
    notifyListeners();
    // Trigger AM/PM filtering if amIndex is set and context is provided
    if (amIndex != null && context != null) {
      filterSlotByAmPm(context);
    }
  }

  /*checkSlotAvailable({isEdit = false, context, isService = false}) async {
    focusedDay.value = DateTime.utc(
        focusedDay.value.year,
        focusedDay.value.month,
        focusedDay.value.day + 0,
        int.parse(timeSlot[timeIndex ?? 0].toString().split(":")[0]),
        int.parse(timeSlot[timeIndex ?? 0].toString().split(":")[1]));
    try {
      log("SSS : ${servicesCart!.user!.id}");
      log("SSS : ${"${DateFormat("dd-MMM-yyy,hh:mm").format(focusedDay.value)} ${amIndex != null ? appArray.amPmList[amIndex ?? 0].toLowerCase() : DateFormat("aa").format(focusedDay.value).toLowerCase()}"}");
      var data = {
        "provider_id": servicesCart!.user!.id,
        "dateTime":
            "${DateFormat("dd-MMM-yyy,hh:mm").format(focusedDay.value)} ${amIndex != null ? appArray.amPmList[amIndex ?? 0].toLowerCase() : DateFormat("aa").format(focusedDay.value).toLowerCase()}"
      };

      // log("data : $data");
      await apiServices
          .getApi(api.isValidTimeSlot, data, isData: true, isToken: true)
          .then((value) async {
        if (value.isSuccess!) {
          log("DDAA 1:${value.data}");
          if (value.data['isValidTimeSlot'] == true) {
            String day = DateFormat('EEEE').format(focusedDay.value);

            List<TimeSlots> dayWeek = timeSlotModel!.timeSlots!;
            int listIndex = dayWeek.indexWhere(
                (element) => element.day!.toLowerCase() == day.toLowerCase());


            if (listIndex >= 0) {
              if (timeSlotModel!.timeSlots![listIndex].isActive == 1) {
              */
  /*  timeSlot = await slots(
                        dayWeek[listIndex].startTime!.split(" ")[0],
                        dayWeek[listIndex].endTime,
                        gap) ??
                    [];*/
  /*
              } else {
                timeIndex = null;
                timeSlot = [];
                notifyListeners();
              }
            } else {
              timeIndex = null;
              timeSlot = [];
              notifyListeners();
            }
          } else {
            timeIndex = null;
            timeSlot = [];
            notifyListeners();
            Fluttertoast.showToast(msg: value.message);
          }

          notifyListeners();
        }
      });
    } catch (e) {
      notifyListeners();
    }
  }*/

  Future<void> checkSlotAvailable(
      {bool isEdit = false, BuildContext? context}) async {
    if (context == null ||
        servicesCart?.user?.id == null ||
        timeIndex == null ||
        timeSlot.isEmpty) {
      timeIndex = null;
      if (context != null) {
        Fluttertoast.showToast(
            msg: "Please select a time slot", backgroundColor: Colors.red);
      }
      notifyListeners();
      return;
    }
    try {
      showLoading(context);
      final selectedTime = timeSlot[timeIndex!];
      focusedDay.value = DateTime.utc(
        focusedDay.value.year,
        focusedDay.value.month,
        focusedDay.value.day,
        int.parse(selectedTime.split(":")[0]),
        int.parse(selectedTime.split(":")[1]),
      );
      final data = {
        "provider_id": servicesCart!.user!.id,
        "dateTime": DateFormat("dd-MMM-yyyy,hh:mm aa").format(focusedDay.value),
      };
      final response = await apiServices.getApi(
        api.isValidTimeSlot,
        data,
        isData: true,
        isToken: true,
      );
      if (response.isSuccess == true &&
          response.data['isValidTimeSlot'] == true) {
        // Slot is valid
      } else {
        timeIndex = null;
        Fluttertoast.showToast(
            msg: response.message.isNotEmpty
                ? response.message
                : "Slot not available",
            backgroundColor: Colors.red);
      }
    } catch (e) {
      log("Error checking slot: $e");
      timeIndex = null;
      Fluttertoast.showToast(
          msg: "Error validating slot", backgroundColor: Colors.red);
    } finally {
      hideLoading(context);
      notifyListeners();
    }
  }

  bool isLoading = false;

  Future<void> checkSlotAvailableForAppChoose({
    required BuildContext context,
    bool isEdit = false,
    bool isService = false,
  }) async {
    isLoading = true;
    showLoading(context);
    notifyListeners();

    try {
      // Ensure amIndex is valid
      if (amIndex == null && amIndex! < 0 && amIndex! > 1) {
        isLoading = false;
        hideLoading(context);
        Fluttertoast.showToast(
            msg: "Invalid AM/PM selection. Please try again.",
            backgroundColor: Colors.red);
        notifyListeners();
        return;
      }

      // Adjust hour for 12-hour format with AM/PM
      int hour = int.parse(appArray.hourList[scrollHourIndex]);
      String amPm = appArray.amPmList[amIndex!]; // "AM" or "PM"
      if (amPm == "PM" && hour != 12) {
        hour += 12; // Convert PM hours to 24-hour format (e.g., 1 PM -> 13)
      } else if (amPm == "AM" && hour == 12) {
        hour = 0; // Convert 12 AM to midnight
      } else if (amPm == "PM" && hour == 12) {
        hour = 12; // Convert 12 PM to noon
      }

// Construct selected DateTime in local time
      final selectedDateTime = DateTime(
        focusedDay.value.year,
        focusedDay.value.month,
        focusedDay.value.day,
        hour,
        int.parse(appArray.minList[scrollMinIndex]),
      );

// Debugging log
      log("Selected DateTime: $selectedDateTime, AM/PM: $amPm, Hour: $hour, Minute: ${appArray.minList[scrollMinIndex]}");

// Get current time for validation (local time)
      final now = DateTime.now();
      final minAllowedTime = now
          .add(const Duration(hours: 2, minutes: 1)); // Minimum 1 hour from now

// Debugging log for validation
      log("Current Time: $now, Min Allowed Time: $minAllowedTime");

// Validation 1: Check for current date
      final today = DateTime(now.year, now.month, now.day);
      final selectedDate = DateTime(
          selectedDateTime.year, selectedDateTime.month, selectedDateTime.day);
      if (selectedDate.isAtSameMomentAs(today)) {
        // Validation 2: Prevent past, current, or within 1-hour time on current date
        if (selectedDateTime.isBefore(minAllowedTime) ||
            selectedDateTime.isAtSameMomentAs(now)) {
          isLoading = false;
          hideLoading(context);
          Fluttertoast.showToast(
              backgroundColor: Colors.red,
              msg:
                  "Please select a time at least 2 hour from now on the current date.");
          notifyListeners();
          return;
        }
      }

// Update focusedDay.value with validated time
      focusedDay.value = selectedDateTime;

// Prepare API data
      var data = {
        "provider_id": servicesCart!.userId,
        "dateTime":
            "${DateFormat("dd-MMM-yyy,hh:mm").format(focusedDay.value)} ${amPm.toLowerCase()}",
      };

// Debugging log
      log("API Data: $data");

// Make API call to check time slot availability
      await apiServices
          .getApi(api.isValidTimeSlot, data, isData: true, isToken: true)
          .then((value) async {
        hideLoading(context);
        if (value.isSuccess!) {
          if (value.data['isValidTimeSlot'] == true) {
            dateTimeSelect(context, isService, isEdit: isEdit);
          } else {
            timeIndex = null;
            timeSlot = [];
            Fluttertoast.showToast(
                msg: value.message, backgroundColor: Colors.red);
          }
        }
      });
    } catch (e) {
      log("Error: $e");
      hideLoading(context);
      Fluttertoast.showToast(
          msg: "An error occurred. Please try again.",
          backgroundColor: Colors.red);
    }

    isLoading = false;
    notifyListeners();
  }

  onMinDecrement() {
    if (scrollMinIndex > 0) {
      scrollMinIndex--;
    }
    carouselController1.jumpToPage(scrollMinIndex);
    notifyListeners();
  }

  onMinIncrement() {
    if (scrollMinIndex < appArray.minList.length - 1) {
      scrollMinIndex++;
    }
    notifyListeners();
    carouselController1.jumpToPage(scrollMinIndex);
    notifyListeners();
  }

  onDayDecrement() {
    if (scrollDayIndex > 0) {
      scrollDayIndex--;
    }
    notifyListeners();
    carouselController2.jumpToPage(scrollDayIndex);
    notifyListeners();
  }

  onDayIncrement() {
    if (scrollDayIndex < appArray.dayList.length) {
      scrollDayIndex++;
    }
    notifyListeners();
    carouselController2.jumpToPage(scrollDayIndex);
    notifyListeners();
  }

  onDropDownChange(choseVal, context) async {
    notifyListeners();

    int index = choseVal['index'];
    log("chosenValue : $index");

    DateTime now =
        DateTime.utc(focusedDay.value.year, index, focusedDay.value.day);
    log("HHHHHHH :$now ${focusedDay.value}");
    log("HHHHHHH :$now ${now.isAfter(focusedDay.value) || DateFormat('MMMM-yyyy').format(now) == DateFormat('MMMM-yyyy').format(focusedDay.value)}");
    if (now.isAfter(DateTime.now()) ||
        DateFormat('MMMM-yyyy').format(now) ==
            DateFormat('MMMM-yyyy').format(focusedDay.value)) {
      chosenValue = choseVal;

      notifyListeners();
      focusedDay.value =
          DateTime.utc(focusedDay.value.year, index, focusedDay.value.day + 0);
      onDaySelected(focusedDay.value, focusedDay.value, context);
      log("choseVal : $choseVal");
      String day = DateFormat('EEEE').format(focusedDay.value);

      if (timeSlotModel != null) {
        List<TimeSlots> dayWeek = timeSlotModel!.timeSlots;
        int listIndex = dayWeek.indexWhere(
            (element) => element.day.toLowerCase() == day.toLowerCase());

        if (listIndex >= 0) {
          /*String gap = timeSlotModel!.timeUnit == "hours"
              ? "${timeSlotModel!.gap}:00"
              : "00:${timeSlotModel!.gap}";*/
          if (dayWeek[listIndex].isActive == 1) {
            /* timeSlot = await slots(dayWeek[listIndex].startTime!.split(" ")[0],
                    dayWeek[listIndex].endTime, gap) ??*/
            [];
          } else {
            timeSlot = [];
            notifyListeners();
          }
        } else {
          timeSlot = [];
          notifyListeners();
        }
      }
    } else {
      if (DateFormat('MMMM-yyyy').format(now) ==
          DateFormat('MMMM-yyyy').format(DateTime.now())) {
        focusedDay.value = DateTime.utc(
            focusedDay.value.year, index, focusedDay.value.day + 0);
        onDaySelected(focusedDay.value, focusedDay.value, context);
        log("choseVal : $choseVal");
        String day = DateFormat('EEEE').format(focusedDay.value);
        if (timeSlotModel != null) {
          List<TimeSlots> dayWeek = timeSlotModel!.timeSlots!;
          int listIndex = dayWeek.indexWhere(
              (element) => element.day!.toLowerCase() == day.toLowerCase());

          if (listIndex >= 0) {
            /* String gap = timeSlotModel!.timeUnit == "hours"
                ? "${timeSlotModel!.gap}:00"
                : "00:${timeSlotModel!.gap}";*/
            if (dayWeek[listIndex].isActive == 1) {
              /*  timeSlot = await slots(
                      dayWeek[listIndex].startTime!.split(" ")[0],
                      dayWeek[listIndex].endTime,
                      gap) ??
                  []*/
              ;
            } else {
              timeSlot = [];
              notifyListeners();
            }
          } else {
            timeSlot = [];
            notifyListeners();
          }
        }
      } else {
        log("ERROR");
        isVisible = true;
        notifyListeners();
        await Future.delayed(DurationClass.s3);

        isVisible = false;
        notifyListeners();
      }
    }
  }

  onPageCtrl(dayFocused) {
    focusedDay.value = dayFocused;
    demoInt = dayFocused.year;
    log("dayFocused :: $demoInt");
    notifyListeners();
  }

  onHourScroll(index) {
    scrollHourIndex = index;
    notifyListeners();
  }

// Updated onHourTap method
  void onHourTap(BuildContext context) async {
    final TimeOfDay? time = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.now(),
      initialEntryMode: TimePickerEntryMode.dial,
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            primaryColor: appColor(context).primary,
            timePickerTheme: TimePickerThemeData(
              backgroundColor: appColor(context).whiteBg,
              hourMinuteColor: appColor(context).stroke,
              dialTextStyle: TextStyle(color: appColor(context).primary),
              dayPeriodColor: appColor(context).primary.withOpacity(.6),
              hourMinuteTextColor: appColor(context).primary,
              dayPeriodTextColor: appColor(context).primary,
              dayPeriodBorderSide: BorderSide(color: appColor(context).primary),
              dialHandColor: appColor(context).primary,
              dialTextColor: appColor(context).darkText,
              dialBackgroundColor: appColor(context).fieldCardBg,
              entryModeIconColor: appColor(context).primary,
              helpTextStyle: TextStyle(color: appColor(context).whiteBg),
              cancelButtonStyle: ButtonStyle(
                backgroundColor:
                    MaterialStateProperty.all<Color>(appColor(context).primary),
                foregroundColor:
                    MaterialStateProperty.all<Color>(appColor(context).whiteBg),
              ),
              confirmButtonStyle: ButtonStyle(
                backgroundColor:
                    MaterialStateProperty.all<Color>(appColor(context).primary),
                foregroundColor:
                    MaterialStateProperty.all<Color>(appColor(context).whiteBg),
              ),
              hourMinuteTextStyle: const TextStyle(fontSize: 30),
            ),
          ),
          child: child!,
        );
      },
    );

    if (time != null) {
      // Convert to 12-hour format
      int hour = time.hourOfPeriod;
      if (hour == 0) hour = 12; // Handle midnight/noon

      // Set scrollHourIndex
      scrollHourIndex =
          appArray.hourList.indexWhere((element) => element == hour.toString());
      if (scrollHourIndex == -1) scrollHourIndex = 0; // Fallback

      // Set minute
      String paddedMinute = time.minute.toString().padLeft(2, '0');
      scrollMinIndex =
          appArray.minList.indexWhere((element) => element == paddedMinute);
      if (scrollMinIndex == -1) scrollMinIndex = 0; // Fallback

      // Set AM/PM
      amIndex = time.period == DayPeriod.am ? 1 : 0;

      // Animate to selected positions
      carouselController.animateToPage(scrollHourIndex);
      carouselController1.animateToPage(scrollMinIndex);
      carouselController2.animateToPage(amIndex!);

      // Update focusedDay with selected time
      focusedDay.value = DateTime(
        focusedDay.value.year,
        focusedDay.value.month,
        focusedDay.value.day,
        time.hour, // Use 24-hour format for internal storage
        time.minute,
      );

      notifyListeners();
    }
  }

  onMinScroll(index) {
    scrollMinIndex = index;
    notifyListeners();
  }

  onDayScroll(index) {
    scrollDayIndex = index;
    notifyListeners();
  }

  onCalendarCreate(controller) {
    log("controller : $controller");
    pageController = controller;
  }

  selectYear(context) async {
    showDialog(
        context: context,
        builder: (BuildContext context3) {
          return const YearAlertDialog();
        });
  }

  onLeftArrow() async {
    DateTime now = DateTime.now();
    if (DateFormat('MM-yyyy').format(focusedDay.value) !=
        DateFormat('MM-yyyy').format(now)) {
      pageController.previousPage(
          duration: const Duration(microseconds: 200), curve: Curves.bounceIn);
      final newMonth = focusedDay.value.subtract(const Duration(days: 30));
      focusedDay.value = newMonth;
      int index = appArray.monthList
          .indexWhere((element) => element['index'] == focusedDay.value.month);
      chosenValue = appArray.monthList[index];
      selectedYear = DateTime.utc(focusedDay.value.year, focusedDay.value.month,
          focusedDay.value.day + 0);
      notifyListeners();
    } else {
      isVisible = true;
      notifyListeners();
      await Future.delayed(DurationClass.s3);

      isVisible = false;
      notifyListeners();
    }
    log("FFF : $focusedDay");
  }

  onRightArrow() {
    pageController.nextPage(
        duration: const Duration(microseconds: 200), curve: Curves.bounceIn);
    final newMonth = focusedDay.value.add(const Duration(days: 30));
    focusedDay.value = newMonth;
    int index = appArray.monthList
        .indexWhere((element) => element['index'] == focusedDay.value.month);
    chosenValue = appArray.monthList[index];
    selectedYear = DateTime.utc(focusedDay.value.year, focusedDay.value.month,
        focusedDay.value.day + 0);
    notifyListeners();
    log("hbfbfdbf::::::$newMonth");
  }

  void listen() {
    if (scrollController.position.pixels >= 200) {
      hide();
    } else {
      show();
    }
    notifyListeners();
  }

  void show() {
    if (!isBottom) {
      isBottom = true;
      notifyListeners();
    }
  }

  void hide() {
    if (isBottom) {
      isBottom = false;
      notifyListeners();
    }
  }

  onReady(context) async {
    dynamic data = ModalRoute.of(context)!.settings.arguments;

    scrollController.addListener(listen);
    log("ARG: ${data['selectServicesCart']}");
    servicesCart = data['selectServicesCart'];
    servicesCart!.selectedRequiredServiceMan =
        servicesCart!.selectedRequiredServiceMan ?? 1;
    isPackage = data['isPackage'] ?? false;
    selectProviderIndex = data['selectProviderIndex'] ?? 0;
    notifyListeners();
    final locationCtrl = Provider.of<LocationProvider>(context, listen: false);
    if (locationCtrl.addressList.isNotEmpty) {
      int index = locationCtrl.addressList
          .indexWhere((element) => element.isPrimary == 1);
      if (index >= 0) {
        address = locationCtrl.addressList[index];
      } else {
        address = locationCtrl.addressList[0];
      }
    }
    if (isPackage) {
      final packageCtrl =
          Provider.of<SelectServicemanProvider>(context, listen: false);
      servicePackageList[selectProviderIndex].primaryAddress = address;
      packageCtrl.notifyListeners();
    } else {
      servicesCart!.primaryAddress = address;
    }
    DateTime dateTime = DateTime.now();
    int index = appArray.monthList
        .indexWhere((element) => element['index'] == dateTime.month);
    chosenValue = appArray.monthList[index];
    notifyListeners();
  }

  setAddress(context) {
    if (isPackage) {
      final packageCtrl =
          Provider.of<SelectServicemanProvider>(context, listen: false);
      servicePackageList[selectProviderIndex].primaryAddress = address;
      packageCtrl.notifyListeners();
      notifyListeners();
    }
  }

  onRemoveService(context) {
    if ((servicesCart!.selectedRequiredServiceMan!) == 1) {
      route.pop(context);
    } else {
      servicesCart!.selectedRequiredServiceMan =
          ((servicesCart!.selectedRequiredServiceMan!) - 1);
    }
    notifyListeners();
  }

/*  void onAdd() {
    int required = servicesCart?.requiredServicemen ?? 1;
    int current = servicesCart?.selectedRequiredServiceMan ?? required;

    current++; // Always increment from required
    servicesCart!.selectedRequiredServiceMan = current;

    log("Selected Required Service Man Updated: $current");
    notifyListeners();
  }*/

  onAdd() {
    int count = (servicesCart!.selectedRequiredServiceMan!);
    count++;
    log("count::${count}");
    servicesCart!.selectedRequiredServiceMan = count;

    notifyListeners();
  }

  onDateTimeSelect(context, index) {
    selectIndex = index;
    notifyListeners();
  }

  onProviderDateTimeSelect(context) async {
    log("SSS : $selectProviderIndex ");

    if (selectIndex == 0) {
      showModalBottomSheet(
          isScrollControlled: true,
          context: context,
          builder: (BuildContext context3) {
            return DateTimePicker(
              isWeek: false,
              isService: isPackage,
              selectProviderIndex: selectProviderIndex,
              service: servicesCart,
            );
          }).then((value) {
        log("VVVS :#$value");
        if (isPackage) {
          final packageCtrl =
              Provider.of<SelectServicemanProvider>(context, listen: false);
          servicePackageList[selectProviderIndex].serviceDate =
              servicesCart!.serviceDate;
          servicePackageList[selectProviderIndex].selectDateTimeOption =
              selectIndex == 0 ? "custom" : "timeSlot";
          servicePackageList[selectProviderIndex].selectedDateTimeFormat =
              servicesCart!.selectedDateTimeFormat;
          notifyListeners();
          packageCtrl.notifyListeners();
        }
      });
      notifyListeners();
    } else {
      await fetchSlotTime(context);
      showModalBottomSheet(
          isScrollControlled: true,
          context: context,
          builder: (BuildContext context3) {
            return Consumer<SlotBookingProvider>(
                builder: (context1, value, child) {
              return StatefulBuilder(builder: (context2, setState) {
                return ProviderTimeSlotLayout(
                    isService: isPackage,
                    selectProviderIndex: selectProviderIndex,
                    service: servicesCart);
              });
            });
          }).then((value) {
        log("VVVS : $value");
        if (value == null) {
          focusedDay.value = DateTime.now();
          notifyListeners();
        }
        amIndex = null;
        timeSlot = [];
        notifyListeners();
        if (isPackage) {
          final packageCtrl =
              Provider.of<SelectServicemanProvider>(context, listen: false);
          servicePackageList[selectProviderIndex] = servicesCart!;
          servicePackageList[selectProviderIndex].serviceDate =
              servicesCart!.serviceDate;
          servicePackageList[selectProviderIndex].selectDateTimeOption =
              selectIndex == 0 ? "custom" : "timeSlot";
          servicePackageList[selectProviderIndex].selectedDateTimeFormat =
              servicesCart!.selectedDateTimeFormat;
          notifyListeners();
          packageCtrl.notifyListeners();
          log("packageCtrl.servicePackageList[selectProviderIndex].serviceDate :${servicePackageList[selectProviderIndex].serviceDate}");
        }
      });
    }
    notifyListeners();
  }

  String getWeekday(String rawDate) {
    try {
      final parsedDate =
          DateTime.parse(rawDate); // Make sure date is "yyyy-MM-dd"
      return DateFormat('E').format(parsedDate); // Output: Mon, Tue, etc.
    } catch (e) {
      return rawDate; // fallback if format fails
    }
  }

  dateTimeSelect(context, isService, {isEdit = false}) {
    log("isService: $servicesCart");
    focusedDay.value = DateTime.utc(
      focusedDay.value.year,
      focusedDay.value.month,
      focusedDay.value.day,
      int.parse(appArray.hourList[scrollHourIndex]),
      int.parse(appArray.minList[scrollMinIndex]),
    );
    notifyListeners();

    if (isEdit) {
      route.pop(context, arg: {
        "date": focusedDay.value,
        "time": appArray.amPmList[scrollDayIndex]
      });
    } else {
      servicesCart!.serviceDate = focusedDay.value;
      servicesCart!.selectedDateTimeFormat = appArray.amPmList[scrollDayIndex];
      notifyListeners();
      if (isService) {
        final packageCtrl =
            Provider.of<SelectServicemanProvider>(context, listen: false);
        packageCtrl.notifyListeners();
      }
      log("isService: ${servicesCart!.selectedDateTimeFormat}");
      route.pop(context, arg: isService ? servicesCart : focusedDay.value);
    }
  }

  void provideTimeSlotSelect(BuildContext context) async {
    log("timeIndex : $timeIndex");

    if (timeIndex != null) {
      final selectedDate = selectedDay ?? focusedDay.value;
      final selectedWeekday =
          DateFormat('EEEE').format(selectedDate).toUpperCase();

      // Match the day slot
      final matchedDaySlot = timeSlotModel!.timeSlots.firstWhere(
        (e) => e.day == selectedWeekday && e.isActive == true,
        orElse: () =>
            TimeSlots(day: selectedWeekday, slots: [], isActive: false),
      );

      // Get the selected slot time
      final selectedSlot = matchedDaySlot.slots[timeIndex!];

      log("Selected slot: $selectedSlot");

      // Now parse the selected slot time
      final hour = int.parse(selectedSlot.split(":")[0]);
      final minute = int.parse(selectedSlot.split(":")[1]);

      // Construct final DateTime
      focusedDay.value = DateTime(
        selectedDate.year,
        selectedDate.month,
        selectedDate.day,
        hour,
        minute,
      );

      servicesCart!.serviceDate = focusedDay.value;
      servicesCart!.selectedDateTimeFormat =
          DateFormat("aa").format(focusedDay.value);

      notifyListeners();

      log("DOC: $isPackage ///${servicesCart!.serviceDate}");

      if (isPackage) {
        final packageCtrl =
            Provider.of<SelectServicemanProvider>(context, listen: false);

        servicePackageList[selectProviderIndex] = servicesCart!;
        servicePackageList[selectProviderIndex].serviceDate =
            servicesCart!.serviceDate;
        servicePackageList[selectProviderIndex].selectDateTimeOption =
            selectIndex == 0 ? "custom" : "timeSlot";
        servicePackageList[selectProviderIndex].selectedDateTimeFormat =
            servicesCart!.selectedDateTimeFormat;

        packageCtrl.notifyListeners();
        log("DOC:sss $isPackage ///${servicesCart!.serviceDate}");
      }

      route.pop(context, arg: isPackage ? servicesCart : focusedDay.value);
    } else {
      Fluttertoast.showToast(
          msg: "Please select time slot", backgroundColor: Colors.red);
    }
  }

  // void provideTimeSlotSelect(BuildContext context) async {
  //   log("timeIndex : $timeIndex");
  //   if (timeIndex != null) {
  //     focusedDay.value = DateTime.utc(
  //       focusedDay.value.year,
  //       focusedDay.value.month,
  //       focusedDay.value.day,
  //       int.parse(timeSlotModel!.timeSlots[timeIndex!].slots[timeIndex!]
  //           .toString()
  //           .split(":")[0]),
  //       int.parse(timeSlotModel!.timeSlots[timeIndex!].slots[timeIndex!]
  //           .toString()
  //           .split(":")[1]),
  //     );
  //
  //     servicesCart!.serviceDate = focusedDay.value;
  //     servicesCart!.selectedDateTimeFormat =
  //         DateFormat("aa").format(focusedDay.value);
  //     notifyListeners();
  //     log("DOC: $isPackage ///${servicesCart!.serviceDate}");
  //     if (isPackage) {
  //       final packageCtrl =
  //           Provider.of<SelectServicemanProvider>(context, listen: false);
  //       servicePackageList[selectProviderIndex] = servicesCart!;
  //       servicePackageList[selectProviderIndex].serviceDate =
  //           servicesCart!.serviceDate;
  //       notifyListeners();
  //       servicePackageList[selectProviderIndex].selectDateTimeOption =
  //           selectIndex == 0 ? "custom" : "timeSlot";
  //       servicePackageList[selectProviderIndex].selectedDateTimeFormat =
  //           servicesCart!.selectedDateTimeFormat;
  //       packageCtrl.notifyListeners();
  //       log("DOC:sss $isPackage ///${servicesCart!.serviceDate}");
  //     }
  //     route.pop(context, arg: isPackage ? servicesCart : focusedDay.value);
  //   } else {
  //     Fluttertoast.showToast(
  //         msg: "Please select time slot", backgroundColor: Colors.red);
  //   }
  // }

  String buttonName(context) {
    String name = translations?.next ?? language(context, appFonts.next);
    log("isPackage ::$isPackage");
    if (isPackage) {
      final packageCtrl =
          Provider.of<SelectServicemanProvider>(context, listen: false);
      if (servicePackageList.length == 1) {
        name = translations?.submit ?? language(context, appFonts.submit);
        return name;
      } else {
        log("IMDD:${selectProviderIndex + 1} //$selectProviderIndex");
        if (selectProviderIndex + 1 < servicePackageList.length) {
          name = translations?.submit ?? language(context, appFonts.submit);
        } else {
          name = translations?.next ?? language(context, appFonts.next);
        }

        return name;
      }
    } else {
      return translations?.next ?? language(context, appFonts.next);
    }
  }

  onBack(context) {
    log("WOEK ");
    if (isStep2) {
      isStep2 = false;
    } else {
      isStep2 = false;
      route.pop(context);
      txtNote.text = "";
    }
    if (servicesCart != null) {
      servicesCart!.serviceDate = null;
      servicesCart!.selectDateTimeOption = null;
      if (isPackage) {
        final packageCtrl =
            Provider.of<SelectServicemanProvider>(context, listen: false);
        servicePackageList[selectProviderIndex].serviceDate = null;
        servicePackageList[selectProviderIndex].selectDateTimeOption = null;
      }
      amIndex = null;
    }
    notifyListeners();
  }

  addToCart(context) async {
    servicesCart!.primaryAddress = address;
    notifyListeners();
    Provider.of<CommonApiProvider>(context, listen: false).selfApi(context);
    final cartCtrl = Provider.of<CartProvider>(context, listen: false);
    log("SERVI :${servicesCart!.selectedAdditionalServices}");
    int index = cartCtrl.cartList.indexWhere((element) =>
        element.isPackage == false &&
        element.serviceList != null &&
        element.serviceList!.id == servicesCart!.id);
    log("ADDD :${servicesCart!.primaryAddress}");

    if (index >= 0) {
      log("message is true");
      cartCtrl.cartList[index].serviceList = servicesCart;

      cartCtrl.notifyListeners();
    } else {
      log("message is false");
      CartModel cartModel =
          CartModel(isPackage: false, serviceList: servicesCart);
      cartCtrl.cartList.add(cartModel);
      cartCtrl.notifyListeners();
    }

    SharedPreferences preferences = await SharedPreferences.getInstance();
    preferences.remove(session.cart);

    List<String> personsEncoded =
        cartCtrl.cartList.map((person) => jsonEncode(person.toJson())).toList();
    await preferences.setString(session.cart, json.encode(personsEncoded));

    cartCtrl.notifyListeners();
    cartCtrl.checkout(context);
    isStep2 = false;
    selectIndex = 0;
    txtNote.text = "";
    servicesCart = null;
    final selectOption =
        Provider.of<SelectServicemanProvider>(context, listen: false);
    final providerDetail =
        Provider.of<ProviderDetailsProvider>(context, listen: false);
    selectOption.servicePackageModel = null;
    providerDetail.selectProviderIndex = 0;
    providerDetail.selectIndex = 0;
    focusedDay.value = DateTime.now();
    notifyListeners();

    route.pushNamed(context, routeName.cartScreen);
  }

  Step2Model? step2Data;

  /* callBookingApi(serviceiId, requiredServicemen, addons) async {
    final dio = Dio();

    log("message -=-=-= ${[
      servicesCart?.selectedAdditionalServices?.map((e) => e.id).join(',')
    ]}");

    /*  var addons =
        servicesCart?.selectedAdditionalServices?.map((e) => e.id).join(',');

    log(" popopoppoppo ${[addons]}"); */

    SharedPreferences pref = await SharedPreferences.getInstance();
    String? token = pref.getString(session.accessToken);
    try {
      final response = await dio.get(
        api.step2Booking,
        queryParameters: {
          'service_id': servicesCart?.id,
          'required_servicemen': servicesCart?.requiredServicemen,
          /* if (servicesCart?.selectedAdditionalServices != null) */
          "additional_services": addons
        },
        options: Options(
          headers: {
            'Authorization': 'Bearer $token',
            'Accept': 'application/json',
          },
        ),
      );

      log("uhyusdfhyudfsi ${response.requestOptions.data}");
      log("uhyusdfhyudfsi ${response.headers}");
      print('Response: ${response.data}');

      if (response.statusCode == 200) {
        step2Data = Step2Model.fromJson(response.data);
      }
    } catch (e) {
      print('Error: $e');
    }
  } */

  /* callBookingApi(serviceId, requiredServicemen, additionalServices) async {
    final dio = Dio();

    SharedPreferences pref = await SharedPreferences.getInstance();
    String? token = pref.getString(session.accessToken);

    // Simulate JSON body in query params
    final queryParams = {
      "service_id": serviceId,
      "required_servicemen": requiredServicemen,
      "additional_services": additionalServices,
    };

    log("Query Params: $queryParams");

    try {
      final response = await dio.get(
        api.step2Booking,
        queryParameters: queryParams,
        options: Options(
          headers: {
            "Authorization": "Bearer $token",
            "Accept": "application/json",
          },
        ),
      );

      log("Response:sssss ${jsonEncode(response.data)}");

      if (response.statusCode == 200) {
        step2Data = Step2Model.fromJson(response.data);

        log("step2Data ${step2Data?.addonsTotalAmount}");
      }
    } catch (e, s) {
      print("Error: $e======$s");
    }
  } */

  Future<void> callBookingApi(
      int? serviceId, int? requiredServicemen, additionalServices) async {
    log("Query Params: ${additionalServices}");
    final queryParams = {
      "service_id": serviceId,
      "required_servicemen": requiredServicemen,
      "additional_services": additionalServices,
    };

    notifyListeners();
    log("Query Params: $queryParams");
    // log("Full URL: ${Uri.parse(api.step2Booking).replace(queryParameters: queryParams)}");

    try {
      await apiServices
          .getApi(api.step2Booking, queryParams, isToken: true, isData: true)
          .then(
        (value) {
          if (value.isSuccess!) {
            step2Data = Step2Model.fromJson(value.data);
            log("message-=-=-=-=-=-=-=-=-=-=-=-=-=${value.data}");
          } else {
            log("message-=-=-=-=-=-=-=-=-=-=-=-=-=${value.message}");
          }

          notifyListeners();
        },
      );
    } catch (e, s) {
      print("API Error: $e\nStackTrace: $s");
    }
  }

  // callBookingApi(serviceId, requiredServicemen, addons) async {
  //   final dio = Dio();

  //   SharedPreferences pref = await SharedPreferences.getInstance();
  //   String? token = pref.getString(session.accessToken);

  //   final queryParams = {
  //     'service_id': serviceId,
  //     'required_servicemen': requiredServicemen,
  //     'additional_services': addons,
  //   };

  //   log("Query Parameters => $queryParams");

  //   try {
  //     final response = await dio.get(
  //       api.step2Booking,
  //       queryParameters: queryParams,
  //       options: Options(
  //         headers: {
  //           'Authorization': 'Bearer $token',
  //           'Accept': 'application/json',
  //         },
  //       ),
  //     );

  //     log("Request URL => ${response.requestOptions.uri}");
  //     print('Response: ${response.data}');

  //     if (response.statusCode == 200) {
  //       step2Data = Step2Model.fromJson(response.data);
  //     }
  //   } catch (e) {
  //     print('Error: $e');
  //   }
  // }
}
