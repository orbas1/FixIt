import 'dart:developer';

import '../../../../config.dart';

class CustomerLayout extends StatelessWidget {
  final UserModel? data;
  final String? title;
  final bool isDetailShow;
  const CustomerLayout(
      {super.key, this.data, this.title, this.isDetailShow = true});

  @override
  Widget build(BuildContext context) {
    log("message====${data}");
    return SizedBox(
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Text(language(context, title!),
              style: appCss.dmDenseMedium12
                  .textColor(appColor(context).appTheme.lightText))
          .padding(horizontal: Insets.i15, top: Insets.i15),
      Divider(height: 1, color: appColor(context).appTheme.stroke)
          .paddingSymmetric(vertical: Insets.i15),
      Row(children: [
        Row(children: [
          data!.media != null && data!.media!.isNotEmpty
              ? CommonImageLayout(
                  image: data!.media![0].originalUrl!,
                  assetImage: eImageAssets.noImageFound3,
                  height: Sizes.s40,
                  width: Sizes.s40,
                  isCircle: true)
              : CommonCachedImage(
                  image: eImageAssets.noImageFound3,
                  height: Sizes.s40,
                  width: Sizes.s40,
                  isCircle: true),
          const HSpace(Sizes.s12),
          Text(data!.name!,
              style: appCss.dmDenseMedium14
                  .textColor(appColor(context).appTheme.darkText))
        ])
      ]).padding(horizontal: Insets.i15),
      const VSpace(Sizes.s15),
      if (isDetailShow)
        Column(
          children: [
            if (data!.email != null)
              ContactDetailRowCommon(
                  image: eSvgAssets.email, title: data!.email),
            if (data!.phone != null)
              ContactDetailRowCommon(
                      code: "+${data?.code ?? ''}",
                      image: eSvgAssets.phone,
                      title: data!.phone != null
                          ? data!.phone.toString().replaceRange(
                              5, data!.phone.toString().length, "*****")
                          : "")
                  .paddingSymmetric(vertical: Insets.i15),
            if (data!.primaryAddress != null)
              ContactDetailRowCommon(
                  image: eSvgAssets.locationOut,
                  title:
                      "${data!.primaryAddress!.area != null ? "${data!.primaryAddress!.area}, " : ""}${data!.primaryAddress!.address}, ${data!.primaryAddress!.country!.name}, ${data!.primaryAddress!.state!.name}, ${data!.primaryAddress!.postalCode}"),
          ],
        )
            .paddingAll(Insets.i15)
            .boxShapeExtension(color: appColor(context).appTheme.whiteBg)
            .padding(horizontal: Insets.i15, bottom: Insets.i15)
    ])).boxShapeExtension(color: appColor(context).appTheme.fieldCardBg);
  }
}
