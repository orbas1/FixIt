import 'dart:convert';
import 'dart:developer';

import 'package:fixit_user/config.dart';
import 'package:fixit_user/screens/app_setting_screen/layouts/currency_bottomsheet.dart';
import 'package:fixit_user/screens/app_setting_screen/layouts/theme_layout.dart';
import 'package:fixit_user/widgets/alert_message_common.dart';
import 'package:fluttertoast/fluttertoast.dart';

class AppSettingProvider with ChangeNotifier {
  int selectIndex = 0;
  final SharedPreferences sharedPreferences;

  AppSettingProvider(this.sharedPreferences);

  heightMQ(context) {
    double height = MediaQuery.of(context).size.height;
    return height;
  }

  widthMQ(context) {
    double width = MediaQuery.of(context).size.width;
    return width;
  }

  showLayout(context) async {
    showDialog(
      context: context,
      builder: (context1) {
        return const AlertDialog(
          insetPadding: EdgeInsets.symmetric(horizontal: Insets.i20),
          shape: SmoothRectangleBorder(
              borderRadius: SmoothBorderRadius.all(SmoothRadius(
                  cornerRadius: AppRadius.r14, cornerSmoothing: 1))),
          content: ThemeSelect(),
        );
      },
    );
  }

  onTapData(context, index) {
    log("dsf");
    if (index == 0) {
      showLayout(context);
      // showDialog(context: context, builder: (context) => AlertDialog(content: ,),);
    } else if (index == 1) {
      //   currencyBottomSheet(context);
      // } else if (index == 2) {
      route.pushNamed(context, routeName.changeLanguage);
    } else if (index == 2) {
      route.pushNamed(context, routeName.changePass);
    } else if (index == 3) {
      route.pushNamed(context, routeName.escrowCenter);
    } else if (index == 4) {
      route.pushNamed(context, routeName.disputeCenter);
    } else if (index == 5) {
      route.pushNamed(context, routeName.affiliate);
    } else if (index == 6) {
      route.pushNamed(context, routeName.storefronts);
    }
  }

  onChangeButton(index) async {
    selectIndex = index;
    notifyListeners();
  }

  bool isCurrencyUpdating = false;
  onUpdate(BuildContext context, CurrencyModel data) async {
    isCurrencyUpdating = true;
    notifyListeners();

    try {
      SharedPreferences pref = await SharedPreferences.getInstance();
      currency(context).priceSymbol = data.symbol.toString();
      final currencyData =
          Provider.of<CurrencyProvider>(context, listen: false);
      currencyData.currency = data;
      currencyData.currencyVal = data.exchangeRate ?? 0.0;
      log("currencyData.currencyVal::${currencyData.currencyVal}");

      currencyData.notifyListeners();

      await pref.setString(session.priceSymbol, currency(context).priceSymbol);
      Map<String, dynamic> cc = await currencyData.currency!.toJson();
      await pref.setString(session.currency, jsonEncode(cc));
      await pref.setDouble(session.currencyVal, currencyData.currencyVal);
      isCurrencyUpdating = false;
      Fluttertoast.showToast(
        msg: "Currency is Updated",
        backgroundColor: appColor(context).primary,
      );
      route.pop(context);
    } catch (e) {
      log("Currency update failed: $e");
      Fluttertoast.showToast(msg: "Currency update failed");
    } finally {
      isCurrencyUpdating = false;
      notifyListeners();
    }
  }

/*  onUpdate(context, CurrencyModel data) async {
    SharedPreferences pref = await SharedPreferences.getInstance();
    currency(context).priceSymbol = data.symbol.toString();
    final currencyData = Provider.of<CurrencyProvider>(context, listen: false);
    currencyData.currency = data;
    currencyData.currencyVal = (data.exchangeRate!);
    log("currencyData.currencyVal::${currencyData.currencyVal}");

    currencyData.notifyListeners();

    await pref.setString(session.priceSymbol, currency(context).priceSymbol);
    Map<String, dynamic> cc = await currencyData.currency!.toJson();
    await pref.setString(session.currency, jsonEncode(cc));
    await pref.setDouble(session.currencyVal, currencyData.currencyVal);
    notifyListeners();
    snackBarMessengers(
      isDuration: true,
      context,
      message: "Currency is Updated",
      color: appColor(context).primary,
    );
    route.pop(context);
  }*/

  currencyBottomSheet(context) {
    final dash = Provider.of<DashboardProvider>(context, listen: false);
    log("VURE :${dash.currencyList}");
    if (currency(context).currency != null) {
      selectIndex = dash.currencyList.indexWhere(
          (element) => element.symbol == currency(context).currency!.symbol);
      showModalBottomSheet(
        context: context,
        backgroundColor: Colors.transparent,
        isScrollControlled: true,
        builder: (context2) {
          return const CurrencyBottomSheet();
        },
      );
    } else {
      selectIndex = 0;
      showModalBottomSheet(
        context: context,
        backgroundColor: Colors.transparent,
        isScrollControlled: true,
        builder: (context2) {
          return const CurrencyBottomSheet();
        },
      );
    }
  }
}
