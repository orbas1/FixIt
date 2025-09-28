import 'dart:developer';
import 'dart:io';

import 'package:fixit_user/widgets/alert_message_common.dart';
import 'package:flutter/services.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:url_launcher/url_launcher.dart';

import 'config.dart';

//
const mail = 'mailto:';
const call = 'tel:';
const googleMapLink = 'https://www.google.com/maps/search/?api=1&query=';
const wpLink = 'whatsapp://send?phone=';
bool isOpen = false;

onBook(context, service,
    {GestureTapCallback? addTap,
    minusTap,
    ProviderModel? provider,
    providerId,
    isPackage = false,
    packageServiceId}) async {
  SharedPreferences preferences = await SharedPreferences.getInstance();
  bool isGuest = preferences.getBool(session.isContinueAsGuest) ?? false;
  final parentContext = context;
  showModalBottomSheet(
      isScrollControlled: true,
      context: parentContext,
      builder: (context1) {
        return StatefulBuilder(builder: (context2, setState) {
          isOpen = true;
          setState;
          // log("ISOPEN 1:${provider!.media![0].originalUrl}");
          return Consumer6<
                  ServicesDetailsProvider,
                  DashboardProvider,
                  CategoriesDetailsProvider,
                  CartProvider,
                  ProviderDetailsProvider,
                  SelectServicemanProvider>(
              builder: (context3, value, dash, category, cart, providerDetail,
                  selectServiceMan, child) {
            return BookYourServiceLayout(
                price: service.serviceRate?.toDouble() ?? 0.0,
                style: appCss.dmDenseSemiBold18
                    .textColor(appColor(context).primary),
                providerModel: provider,
                requiredServiceMan: (service.requiredServicemen ?? 1),
                addTap: addTap,
                minusTap: minusTap,
                packageServiceId: packageServiceId,
                isPackage: isPackage,
                services: service);
          });
        });
      }).then((value) {
    isOpen = false;
    log("IHS L$isOpen");
  });
}

mailTap(context, String url) {
  if (url.isNotEmpty) {
    commonUrlTap(context, '$mail$url',
        launchMode: LaunchMode.externalApplication);
  }
}

commonUrlTap(context, String address,
    {LaunchMode launchMode = LaunchMode.inAppWebView}) async {
  try {
    await launchUrl(Uri.parse(address), mode: launchMode);
  } catch (e) {
    String errorMessage = 'Unknown error occurred';

    if (e is PlatformException) {
      errorMessage = e.message ?? 'Platform error occurred';
    } else if (e is Exception) {
      errorMessage = e.toString();
    }

    // Show the error message as a toast without returning anything
    Fluttertoast.showToast(msg: errorMessage);
  }
}

launchCall(context, String? url) {
  if (url != null) {
    if (Platform.isIOS) {
      commonUrlTap(context, '$call//$url',
          launchMode: LaunchMode.externalApplication);
    } else {
      commonUrlTap(context, '$call$url',
          launchMode: LaunchMode.externalApplication);
    }
  }
}

launchMap(context, String? url) {
  commonUrlTap(context, googleMapLink + url!,
      launchMode: LaunchMode.externalApplication);
}

wpTap(BuildContext context, String? phoneNumber) {
  if (phoneNumber == null || phoneNumber.isEmpty) {
    log('Error: phone number is null or empty');
    return;
  }
  final url = 'whatsapp://send?phone=$phoneNumber';
  log("url:::$url");
  commonUrlTap(context, url, launchMode: LaunchMode.externalApplication);
}

showBookingStatus(context, BookingModel? bookingModel) {
  showModalBottomSheet(
      isScrollControlled: true,
      context: context,
      builder: (context) {
        return BookingStatusDialog(
          bookingModel: bookingModel,
        );
      });
}
