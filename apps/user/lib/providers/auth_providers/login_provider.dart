import 'dart:convert';
import 'dart:developer';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/cupertino.dart';
import 'package:fixit_user/config.dart';
import 'package:fixit_user/widgets/alert_message_common.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:firebase_auth/firebase_auth.dart';

import '../../screens/app_pages_screens/server_error_screen/server_error.dart';

class LoginProvider with ChangeNotifier {
  TextEditingController emailController = TextEditingController();
  TextEditingController passwordController = TextEditingController();
  GlobalKey<FormState> formKey = GlobalKey<FormState>();
  SharedPreferences? pref;
  final FocusNode emailFocus = FocusNode();
  final FocusNode passwordFocus = FocusNode();
  bool isPassword = true;

  Future<void> finalizeAuthenticatedSession(BuildContext context,
      {String? successMessage}) async {
    pref ??= await SharedPreferences.getInstance();
    isGuest = false;
    notifyListeners();
    pref!.setBool(session.isContinueAsGuest, false);

    final commonApi = Provider.of<CommonApiProvider>(context, listen: false);
    await commonApi.selfApi(context);

    final userRole = userModel?.role;
    log("USER:::$userRole");
    if (userRole != null && userRole != "user") {
      hideLoading(context);
      log("Unauthorized role detected: $userRole");
      Fluttertoast.showToast(
        msg: "This action is unauthorized for non-user roles.",
        backgroundColor: appColor(context).red,
      );
      return;
    }

    final dashCtrl = Provider.of<DashboardProvider>(context, listen: false);
    final review = Provider.of<MyReviewProvider>(context, listen: false);
    final notifyCtrl = Provider.of<NotificationProvider>(context, listen: false);

    dashCtrl.getBookingHistory(context);
    review.getMyReview(context);
    notifyCtrl.getNotificationList(context);

    final token = pref?.getString(session.accessToken);
    log("TOKEN :%sss$token");
    await commonApi.selfApi(context);
    commonApi.getDashboardHome(context);
    commonApi.getDashboardHome2(context);

    hideLoading(context);
    emailController.text = '';
    passwordController.text = '';

    if (pref!.getString(session.booking) != null) {
      final int? lastServiceId = pref!.getInt("lastOpenedServiceId");
      route.pushReplacementNamed(
        context,
        routeName.servicesDetailsScreen,
        args: {'serviceId': lastServiceId},
      );
    } else {
      route.pushReplacementNamed(context, routeName.dashboard);
    }

    final userData = pref!.getString(session.user);
    if (userData != null) {
      final locationCtrl = Provider.of<LocationProvider>(context, listen: false);
      await locationCtrl.getLocationList(context);
      await locationCtrl.getCountryState();

      if (context.mounted) {
        final cartCtrl = Provider.of<CartProvider>(context, listen: false);
        cartCtrl.onReady(context);
      }
      pref!.remove(session.isContinueAsGuest);
    }

    Fluttertoast.showToast(
      msg: successMessage ?? "Login Successfully",
      backgroundColor: const Color(0xff5465FF),
    );

    if (!context.mounted) {
      hideLoading(context);
    }

    notifyListeners();
  }

  onLogin(context) {
    FocusManager.instance.primaryFocus?.unfocus();
    /*  route.pushReplacementNamed(context, routeName.dashboard);*/
    // if (formKey.currentState!.validate()) {
    login(context);
    // }
  }

  demoCreds() {
    emailController.text = "user@example.com";
    passwordController.text = "123456789";
    notifyListeners();
  }

  Future<void> _handleMfaRequired(
    BuildContext context,
    Map<String, dynamic> payload,
    String? message,
  ) async {
    final mfaProvider =
        Provider.of<MfaChallengeProvider>(context, listen: false);

    mfaProvider.initializeChallenge(
      payload: payload,
      email: emailController.text.trim(),
      message: message,
    );

    hideLoading(context);

    if (context.mounted) {
      route.pushNamed(context, routeName.mfaVerification, args: payload);
    }
  }

  // password see tap
  passwordSeenTap() {
    isPassword = !isPassword;
    notifyListeners();
  }

  // SignIn With Google Method
  Future signInWithGoogle(context) async {
    try {
      showLoading(context);
      final FirebaseAuth auth = FirebaseAuth.instance;
      final GoogleSignIn googleSignIn = GoogleSignIn();

      final GoogleSignInAccount? googleSignInAccount =
          await googleSignIn.signIn();
      final GoogleSignInAuthentication googleSignInAuthentication =
          await googleSignInAccount!.authentication;
      final AuthCredential credential = GoogleAuthProvider.credential(
        accessToken: googleSignInAuthentication.accessToken,
        idToken: googleSignInAuthentication.idToken,
      );
      User? user = (await auth.signInWithCredential(credential)).user;
      socialLogin(context, user!);
      notifyListeners();
    } catch (e) {
      log("kbjhfjuht $e");
      hideLoading(context);
      notifyListeners();
    } finally {
      hideLoading(context);
      notifyListeners();
    }
  }

  socialLogin(context, User user) async {
    showLoading(context);
    notifyListeners();
    String token = await getFcmToken();
    var body = {
      "login_type": "google",
      "user": {"email": user.email, "name": user.displayName},
      "fcm_token": token
    };

    try {
      await apiServices
          .postApi(api.socialLogin, jsonEncode(body))
          .then((value) async {
        notifyListeners();
        if (value.isSuccess!) {
          pref = await SharedPreferences.getInstance();
          pref!.setBool(session.isContinueAsGuest, false);
          String? token = pref?.getString(session.accessToken);
          log("TOKEN :%sss$token");
          final appDetails =
              Provider.of<AppDetailsProvider>(context, listen: false);
          appDetails.getAppPages();
          final commonApi =
              Provider.of<CommonApiProvider>(context, listen: false);
          await commonApi.selfApi(context);
          commonApi.getDashboardHome(context);
          commonApi.getDashboardHome2(context);
          await Future.delayed(DurationClass.ms150);
          hideLoading(context);
          final locationCtrl =
              Provider.of<LocationProvider>(context, listen: false);
          locationCtrl.getUserCurrentLocation(context);
          locationCtrl.getLocationList(context);
          locationCtrl.getCountryState();
          pref!.remove(session.isContinueAsGuest);
          // final dashCtrl =
          //     Provider.of<DashboardProvider>(context, listen: false);
          // dashCtrl.getJobRequest();

          final cartCtrl = Provider.of<CartProvider>(context, listen: false);
          cartCtrl.onReady(context);
          final notifyCtrl =
              Provider.of<NotificationProvider>(context, listen: false);
          notifyCtrl.getNotificationList(context);
          /*Navigator.popUntil(
              context,
              route.pushReplacementNamed(
                  context, routeName.servicesDetailsScreen));*/
          route.pushReplacementNamed(context, routeName.dashboard);
        } else {
          hideLoading(context);
          notifyListeners();
          Fluttertoast.showToast(
              msg: value.message, backgroundColor: appColor(context).red);
        }
      });
    } catch (e) {
      hideLoading(context);
      notifyListeners();
      log("CATCH ff: $e");
    }
  }

  //login
  login(context) async {
    try {
      pref = await SharedPreferences.getInstance();
      String? token = await getFcmToken();

      log("TOKEN FOR FCM : $token");

      showLoading(context);

      var body = {
        "email": emailController.text,
        "password": passwordController.text,
        "fcm_token": token
      };

      log("body : $body");

      await apiServices
          .postApi(api.login, jsonEncode(body))
          .then((value) async {
        if (value.isSuccess == true) {
          await finalizeAuthenticatedSession(context,
              successMessage: value.message);
        } else if (value.data is Map) {
          final payload = Map<String, dynamic>.from(value.data as Map);
          final requiresMfa =
              payload['requires_mfa'] == true || payload['requiresMfa'] == true;
          if (requiresMfa) {
            await _handleMfaRequired(context, payload, value.message);
            return;
          }
          hideLoading(context);
          Fluttertoast.showToast(
            msg: value.message.isNotEmpty
                ? value.message
                : "Unable to process your request.",
            backgroundColor: appColor(context).red,
          );
        } else {
          hideLoading(context);
          Fluttertoast.showToast(
            msg: value.message.isNotEmpty
                ? value.message
                : "Unable to process your request.",
            backgroundColor: appColor(context).red,
          );
        }
      });
    } catch (e, s) {
      hideLoading(context);
      notifyListeners();
      log("CATCH login: $e====> $s");
    }
  }

  continueAsGuestTap(context) async {
    pref = await SharedPreferences.getInstance();

    pref!.setBool(session.isContinueAsGuest, true);
    // log("vbvbvb ${pref!.setBool(session.isContinueAsGuest, true)}");
    log("CCC");

    route.pushReplacementNamed(context, routeName.dashboard);
  }

  locationConformation(
    context,
  ) {
    showDialog(
        context: context,
        builder: (context1) {
          return StatefulBuilder(builder: (context2, setState) {
            return Consumer<LocationProvider>(
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
                      /* Stack(alignment: Alignment.topCenter, children: [
                        Stack(alignment: Alignment.topCenter, children: [
                          SizedBox(
                                  width: MediaQuery.of(context).size.width,
                                  child: Image.asset(eImageAssets.failedBook,
                                          height: Sizes.s165, width: Sizes.s88)
                                      .paddingOnly(
                                          bottom: Insets.i15, top: Insets.i25))
                              .decorated(
                                  color: appColor(context).fieldCardBg,
                                  borderRadius:
                                      BorderRadius.circular(AppRadius.r10)),
                        ]),
                      ]), */
                      // Sub text
                      const VSpace(Sizes.s15),
                      Text(
                          /* language(context, translations!.logoutDesc) */
                          "We Need Your Location to Enhance Your Experience.",
                          textAlign: TextAlign.center,
                          style: appCss.dmDenseRegular14
                              .textColor(appColor(context).lightText)
                              .textHeight(1.3)),
                      const VSpace(Sizes.s20),
                      ButtonCommon(
                          onTap: () async {
                            final dashCtrl = Provider.of<DashboardProvider>(
                                context,
                                listen: false);
                            final locationCtrl = Provider.of<LocationProvider>(
                                context,
                                listen: false);

                            final review = Provider.of<MyReviewProvider>(
                                context,
                                listen: false);

                            final notifyCtrl =
                                Provider.of<NotificationProvider>(context,
                                    listen: false);
                            await locationCtrl.getZoneId(context);
                            dashCtrl.getBookingHistory(context);
                            // favCtrl.getFavourite();
                            review.getMyReview(context);

                            notifyCtrl.getNotificationList(context);
                            String? token =
                                pref?.getString(session.accessToken);
                            log("TOKEN :%sss$token");
                            final commonApi = Provider.of<CommonApiProvider>(
                                context,
                                listen: false);
                            await commonApi.selfApi(context);

                            commonApi.getDashboardHome(context);
                            commonApi.getDashboardHome2(context);

                            // if (pref!.getString(session.booking) != null) {
                            //
                            //   route.pushReplacementNamed(
                            //       context, routeName.servicesDetailsScreen);
                            //   /*  bookingCtrl.getBookingDetails(context); */
                            // } else {
                            //   route.pushReplacementNamed(
                            //       context, routeName.dashboard);
                            // }
                            /*    route.pushReplacementNamed(context, routeName.dashboard); */
                            dynamic userData = pref!.getString(session.user);

                            if (userData != null) {
                              log("message=============> $userData");
                              final locationCtrl =
                                  Provider.of<LocationProvider>(context,
                                      listen: false);
                              /*locationCtrl.getUserCurrentLocation(context);*/
                              await locationCtrl.getLocationList(context);
                              await locationCtrl.getCountryState();
                              // WidgetsBinding.instance.addPostFrameCallback((_) {
                              //   final dashCtrl =
                              //       Provider.of<DashboardProvider>(context, listen: false);
                              //   dashCtrl.getJobRequest();
                              // });
                              if (context.mounted) {
                                final cartCtrl = Provider.of<CartProvider>(
                                    context,
                                    listen: false);
                                cartCtrl.onReady(context);
                              }
                              pref!.remove(session.isContinueAsGuest);
                            }
                            /* Fluttertoast.showToast(
                              msg: value.message,
                              backgroundColor: appColor(context).primary,
                            ); */
                            if (!context.mounted) {
                              hideLoading(context);
                            }
                            emailController.text = "";
                            passwordController.text = "";
                            notifyListeners();
                          } /* => route.pop(context) */,
                          title: "Current Location",
                          borderColor: appColor(context).primary,
                          color: appColor(context).whiteBg,
                          style: appCss.dmDenseSemiBold16
                              .textColor(appColor(context).primary)),
                      VSpace(Sizes.s20),
                      ButtonCommon(
                        title: "Manualy",
                        color: appColor(context).primary,
                        onTap: () async {
                          route.pushNamed(
                            context,
                            routeName.currentLocation, /*  arg: true */
                          );
                          /*  route
                              .pushNamed(context, routeName.location)
                              .then((e) {
                            /* animationController!.reset(); */
                            notifyListeners();
                          }).then((e) {
                            final location = Provider.of<LocationProvider>(
                                context,
                                listen: false);
                            location.getLocationList(context);
                          }); */
                        },
                        style: appCss.dmDenseSemiBold16
                            .textColor(appColor(context).whiteColor),
                      )
                      /* Row(children: [
                        Expanded(
                            child: ButtonCommon(
                                onTap: () => route.pop(context),
                                title: "Current Location",
                                borderColor: appColor(context).primary,
                                color: appColor(context).whiteBg,
                                style: appCss.dmDenseSemiBold16
                                    .textColor(appColor(context).primary))),
                        const HSpace(Sizes.s15),
                        Expanded(
                            child: ButtonCommon(
                          title: "Manualy",
                          color: appColor(context).primary,
                          onTap: () async {},
                          style: appCss.dmDenseSemiBold16
                              .textColor(appColor(context).whiteColor),
                        ))
                      ]) */
                    ]).padding(
                        horizontal: Insets.i20,
                        top: Insets.i60,
                        bottom: Insets.i20),
                    Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          // Title
                          Text(
                              language(context, translations!.logOut)
                                  .replaceAll(" ", ""),
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
        });
  }
}
