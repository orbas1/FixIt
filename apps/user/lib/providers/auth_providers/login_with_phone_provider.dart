import 'dart:developer';
import 'package:firebase_auth/firebase_auth.dart';
import '../../config.dart';

class LoginWithPhoneProvider with ChangeNotifier {
  TextEditingController numberController = TextEditingController();
  GlobalKey<FormState> globalKey = GlobalKey<FormState>();
  bool isCodeSent = false;
  String dialCode = "+1";
  final FirebaseAuth firebaseAuth = FirebaseAuth.instance;
  final FocusNode phoneFocus = FocusNode();
  String? verificationCode;

  onTapOtp(context) async {
    FocusManager.instance.primaryFocus?.unfocus();
    if (globalKey.currentState!.validate()) {
      showLoading(context);
      notifyListeners();

      sendOtp(context);
    }
  }

  String? uid;
  final FirebaseAuth auth = FirebaseAuth.instance;

  Future<void> verifyPhoneNumber(String phoneNumber) async {
    await auth.verifyPhoneNumber(
      phoneNumber: phoneNumber,
      verificationCompleted: (PhoneAuthCredential credential) async {
        // Auto-retrieve verification code
        await auth.signInWithCredential(credential);
      },
      verificationFailed: (FirebaseAuthException e) {
        // Verification failed
      },
      codeSent: (String verificationId, int? resendToken) async {
        // Save the verification ID for future use
        String smsCode = 'xxxxxx'; // Code input by the user
        verificationCode = verificationId;
        notifyListeners();
        PhoneAuthCredential credential = PhoneAuthProvider.credential(
          verificationId: verificationId,
          smsCode: smsCode,
        );
        // Sign the user in with the credential
        await auth.signInWithCredential(credential);
      },
      codeAutoRetrievalTimeout: (String verificationId) {},
      timeout: Duration(seconds: 60),
    );
  }

  // Future<void> verifyPhoneNumber() async {
  //   log("DDDD:$dialCode${numberController.text.toString()}");
  //   await auth.verifyPhoneNumber(
  //     phoneNumber: '$dialCode${numberController.text.toString()}',
  //     verificationCompleted: (PhoneAuthCredential credential) {
  //       debugPrint("log 4 $credential");
  //       notifyListeners();
  //     },
  //     timeout: const Duration(seconds: 60),
  //     verificationFailed: (FirebaseAuthException e) {
  //       debugPrint("log 5 $e");
  //
  //       notifyListeners();
  //     },
  //     codeSent: (String verificationId, int? resendToken) async {
  //       // resendCodeID = verificationId;
  //       verificationCode = verificationId;
  //       log("log 2 $verificationId");
  //
  //       PhoneAuthCredential credential = PhoneAuthProvider.credential(
  //         verificationId: verificationId,
  //         smsCode: verificationCode!,
  //       );
  //       log("credential::${credential}");
  //
  //       try {
  //         UserCredential userCredential =
  //             await auth.signInWithCredential(credential);
  //
  //         // Get the UID
  //         uid = userCredential.user?.uid;
  //         if (uid != null) {
  //           print("User signed in with UID: $uid");
  //           // Use the UID as needed
  //         } else {
  //           print("No user found after manual verification");
  //         }
  //       } catch (e) {
  //         print("Error signing in with credential: $e");
  //       }
  //       // Sign the user in with the credential
  //       await auth.signInWithCredential(credential);
  //     },
  //     codeAutoRetrievalTimeout: (String verificationId) {
  //       notifyListeners();
  //       log("SOMETHING");
  //     },
  //   );
  //   // await auth.verifyPhoneNumber(
  //   //   phoneNumber: '$dialCode ${numberController.text}',
  //   //   verificationCompleted: (PhoneAuthCredential credential) async {
  //   //     log("dialCode::${dialCode}");
  //   //     UserCredential userCredential =
  //   //         await auth.signInWithCredential(credential);
  //   //     uid = userCredential.user?.uid;
  //   //     if (uid != null) {
  //   //       log("User signed in with UID: $uid");
  //   //       // Use the UID as needed (e.g., save to database, navigate to next screen)
  //   //     } else {
  //   //       log("No user found after auto-verification");
  //   //     }
  //   //     await auth.signInWithCredential(credential);
  //   //   },
  //   //   verificationFailed: (FirebaseAuthException e) {
  //   //     // Verification failed
  //   //   },
  //   //   codeSent: (String verificationId, int? resendToken) async {
  //   //     // Save the verification ID for future use
  //   //     String smsCode = verificationId; // Code input by the user
  //   //     log("verificationId::${verificationId}");
  //   //     PhoneAuthCredential credential = PhoneAuthProvider.credential(
  //   //       verificationId: verificationId,
  //   //       smsCode: smsCode,
  //   //     );
  //   //     try {
  //   //       UserCredential userCredential =
  //   //           await auth.signInWithCredential(credential);
  //   //       // Get the UID
  //   //       uid = userCredential.user?.uid;
  //   //       if (uid != null) {
  //   //         print("User signed in with UID: $uid");
  //   //         // Use the UID as needed
  //   //       } else {
  //   //         print("No user found after manual verification");
  //   //       }
  //   //     } catch (e) {
  //   //       print("Error signing in with credential: $e");
  //   //     }
  //   //     // Sign the user in with the credential
  //   //     await auth.signInWithCredential(credential);
  //   //   },
  //   //   codeAutoRetrievalTimeout: (String verificationId) {},
  //   //   timeout: Duration(seconds: 60),
  //   // );
  // }

  //send Otp api
  sendOtp(context) async {
    showLoading(context);
    log("uid::$uid");
    notifyListeners();
    // if (appSettingModel!.general!.defaultSmsGateway == "firebase") {
    verifyPhoneNumber("$dialCode${numberController.text.toString()}");
    route.pushNamed(context, routeName.verifyOtp, arg: {
      "phone": numberController.text,
      "dialCode": dialCode,
      "uid": uid
    });
    hideLoading(context);

    log("DDDDDSENT Firebase");
    // } else {
    //   try {
    //     dynamic mimeTypeData;
    //
    //     var body = {
    //       "dial_code": dialCode.replaceAll("+", ""),
    //       "phone": numberController.text
    //     };
    //     dio.FormData formData = dio.FormData.fromMap(body);
    //
    //     log("BODU :$body");
    //
    //     await apiServices
    //         .postApi(api.sendOtp, formData, isToken: true)
    //         .then((value) async {
    //       hideLoading(context);
    //       notifyListeners();
    //       if (value.isSuccess!) {
    //         route.pushNamed(context, routeName.verifyOtp, arg: {
    //           "phone": numberController.text,
    //           "dialCode": dialCode,
    //           "verificationCode": verificationCode
    //         });
    //
    //         notifyListeners();
    //       } else {
    //         Fluttertoast.showToast(msg: value.message);
    //       }
    //     });
    //   } catch (e) {
    //     hideLoading(context);
    //     notifyListeners();
    //
    //     log("EEEE sendOTP : $e");
    //   }
    // }
  }

  changeDialCode(CountryCodeCustom country) {
    dialCode = country.dialCode!;
    notifyListeners();
  }
}