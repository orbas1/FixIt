import 'package:fixit_user/models/app_setting_model.dart';

import '../../../../config.dart';

class PaymentMethodLayout extends StatelessWidget {
  final PaymentMethods? data;

  //final dynamic? data;
  final int? index, selectIndex;
  final GestureTapCallback? onTap;

  const PaymentMethodLayout(
      {super.key, this.data, this.onTap, this.index, this.selectIndex});

  @override
  Widget build(BuildContext context) {
    final value = Provider.of<PaymentProvider>(context, listen: true);

    return Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
      Row(children: [
        if (data!.slug == "cash")
          SvgPicture.asset(eSvgAssets.cash,
                  colorFilter: ColorFilter.mode(
                      appColor(context).primary, BlendMode.srcIn))
              .paddingAll(Sizes.s10)
              .decorated(
                  shape: BoxShape.circle,
                  color: appColor(context).primary.withOpacity(0.15)),
        if (data!.slug != "cash")
          CommonImageLayout(
              height: Sizes.s45,
              boxFit: BoxFit.contain,
              width: Sizes.s70,
              image: data!.image,
              assetImage: eImageAssets.noImageFound1),
        const HSpace(Sizes.s12),
        Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(language(context, data!.name!).capitalizeFirst(),
              style: appCss.dmDenseSemiBold16.textColor(selectIndex == index
                  ? appColor(context).primary
                  : appColor(context).darkText)),
          if (data!.slug != "cash")
            Column(
              children: [
                SizedBox(
                  width: 184,
                  child: Text(
                      softWrap: true,
                      symbolPosition
                          ? "+ ${getSymbol(context)}${language(context, data!.processingFee.toString())} gateway fees"
                              .capitalizeFirst()
                          : "+ ${language(context, data!.processingFee.toString())}${getSymbol(context)} gateway fees"
                              .capitalizeFirst(),
                      style: appCss.dmDenseMedium14.textColor(
                          /* selectIndex == index
                              ? appColor(context).primary
                              : */
                          appColor(context).darkText)),
                ),
              ],
            ),
        ])
      ]),
      CommonRadio(index: index, selectedIndex: selectIndex, onTap: onTap)
    ])
        .paddingSymmetric(vertical: Insets.i12, horizontal: Insets.i15)
        .boxBorderExtension(context,
            bColor: selectIndex == index
                ? appColor(context).stroke
                : appColor(context).fieldCardBg,
            isShadow: selectIndex == index ? false : true)
        .paddingSymmetric(vertical: Insets.i10, horizontal: Sizes.s20)
        .inkWell(onTap: onTap);
  }
}
