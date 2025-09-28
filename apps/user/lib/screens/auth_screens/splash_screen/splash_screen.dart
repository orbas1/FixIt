import 'dart:async';

import '../../../config.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with TickerProviderStateMixin {
  @override
  void initState() {
    super.initState();

    WidgetsBinding.instance.addPostFrameCallback((_) {
      final splash = Provider.of<SplashProvider>(context, listen: false);
      splash.onReady(context);
    });
    // final commonApi = Provider.of<CommonApiProvider>(context, listen: false);
    //
    // commonApi.selfApi(context);
  }

  @override
  void dispose() async {
    super.dispose();
    // final splash = Provider.of<SplashProvider>(context, listen: false);
    // bool isAvailable = await isNetworkConnection();
    // if (!isAvailable) {
    //   splash.controller2!.dispose();
    //   splash.controller3!.dispose();
    //   splash.controller!.dispose();
    //   splash.controllerSlide!.dispose();
    // }
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<SplashProvider>(builder: (context, splash, child) {
      return Scaffold(
          body: Center(
              child: Column(children: [
        Stack(alignment: Alignment.center, children: [
          Container(
              color: appColor(context).primary.withOpacity(0.7),
              width: MediaQuery.of(context).size.width,
              height: MediaQuery.of(context).size.height,
              child: Opacity(
                  opacity: 0.15,
                  child:
                      Image.asset(eImageAssets.splashBg, fit: BoxFit.cover))),
          Column(mainAxisAlignment: MainAxisAlignment.center, children: [
            const VSpace(Sizes.s15),
            // appSettingModel?.general?.splashScreenLogo != null
            //     ? CachedNetworkImage(
            //         imageUrl: "${appSettingModel?.general?.splashScreenLogo}",
            //         height: Sizes.s45,
            //         width: Sizes.s45)
            Image.asset(eImageAssets.appLogo,
                height: Sizes.s45, width: Sizes.s45),
            Text(appFonts.fixit,
                style: appCss.outfitSemiBold45
                    .textColor(appColor(context).whiteColor))
          ])
        ])
      ])));
    });
  }
}
