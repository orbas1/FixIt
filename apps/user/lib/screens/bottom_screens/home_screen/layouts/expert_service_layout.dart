import 'dart:developer';

import '../../../../config.dart';
import '../../../../models/dashboard_user_model_2.dart';

class ExpertServiceLayout extends StatelessWidget {
  final ProviderModel? data;
  final GestureTapCallback? onTap;
  final bool isHome;

  const ExpertServiceLayout(
      {super.key, this.data, this.onTap, this.isHome = false});

  @override
  Widget build(BuildContext context) {
    //log("data?.media::${data!.primaryAddress}");
    return Consumer<FavouriteListProvider>(builder: (context, favCtrl, child) {
      return Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
            Row(
              children: [
                (data != null && data!.media != null && data!.media!.isNotEmpty)
                    ? CommonImageLayout(
                        height: Sizes.s72,
                        width: Sizes.s72,
                        radius: 8,
                        image: data!.media![0].originalUrl ?? '',
                        assetImage: eImageAssets.noImageFound3,
                      ).boxShapeExtension()
                    : CommonCachedImage(
                        image: eImageAssets.noImageFound3,
                        assetImage: eImageAssets.noImageFound3,
                        height: Sizes.s72,
                        width: Sizes.s72,
                      ).boxShapeExtension(),
                const HSpace(Sizes.s10),
                Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(
                      capitalizeFirstLetter(
                          data != null ? data!.name! : "Provider"),
                      style: appCss.dmDenseSemiBold15
                          .textColor(appColor(context).darkText)),
                  SizedBox(
                      width: Sizes.s120,
                      child: Text(
                          /* data!.categories != null && data!.categories!.isNotEmpty
                                ? data!.categories![0].title!
                                : */
                          data!.expertise != null && data!.expertise!.isNotEmpty
                              ? data!.expertise![0].title!
                              : "Services",
                          overflow: TextOverflow.ellipsis,
                          style: appCss.dmDenseSemiBold12
                              .textColor(appColor(context).darkText))),
                  const VSpace(Sizes.s15),
                  Row(children: [
                    SvgPicture.asset(eSvgAssets.clock,
                        height: Sizes.s15,
                        colorFilter: ColorFilter.mode(
                            appColor(context).lightText, BlendMode.srcIn)),
                    const HSpace(Sizes.s5),
                    SizedBox(
                        width: Sizes.s140,
                        child: Text(
                            data!.experienceInterval != null
                                ? "${data!.experienceDuration!} ${data!.experienceInterval} ${translations!.of} ${language(context, translations!.experience).toLowerCase()}"
                                : "data!.experienceDuration",
                            overflow: TextOverflow.ellipsis,
                            style: appCss.dmDenseSemiBold12
                                .textColor(appColor(context).lightText)))
                  ])
                ]),
              ],
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(children: [
                  SvgPicture.asset(eSvgAssets.star),
                  const HSpace(Sizes.s3),
                  Text(
                      data!.reviewRatings != null
                          ? data!.reviewRatings!.toStringAsFixed(2)
                          : "0",
                      style: appCss.dmDenseSemiBold13
                          .textColor(appColor(context).darkText))
                ]),
                VSpace(Sizes.s20),
                if (data != null)
                  data!.isFavourite == 1
                      ? SvgPicture.asset(eSvgAssets.heart,
                              height: Sizes.s30, width: Sizes.s30)
                          .inkWell(
                          onTap: () {
                            data!.isFavourite = 0;
                            favCtrl.deleteFav(
                              context,
                              isFavId: data!.isFavouriteId,
                              id: data!.id,
                            );
                          },
                        )
                      : CommonArrow(
                          isThirteen: true,
                          arrow: eSvgAssets.like,
                          svgColor: appColor(context).primary,
                          color:
                              appColor(context).primary.withValues(alpha: 0.15),
                          onTap: () {
                            data!.isFavourite = 1;
                            favCtrl.addFav("provider", context, data!.id);
                          },
                        ),
              ],
            )
          ])
          .paddingAll(Insets.i15)
          .boxBorderExtension(context, isShadow: isHome)
          .inkWell(onTap: onTap)
          .paddingOnly(bottom: Insets.i15);
    });
  }
}
/*
// import '../../../../config.dart';

class HomeExpertServiceLayout extends StatelessWidget {
  final HighestRatedProvider? data;
  final GestureTapCallback? onTap;
  final bool isHome;
  const HomeExpertServiceLayout(
      {super.key, this.data, this.onTap, this.isHome = false});

  @override
  Widget build(BuildContext context) {
    return Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
          Row(
            children: [
              data != null && data!.media!.isNotEmpty
                  ? CommonImageLayout(
                      height: Sizes.s72,
                      width: Sizes.s72,
                      radius: 8,
                      image: data!.media![0].originalUrl!,
                      assetImage: eImageAssets.noImageFound3,
                    ).boxShapeExtension()
                  : CommonCachedImage(
                          image: eImageAssets.noImageFound3,
                          assetImage: eImageAssets.noImageFound3,
                          height: Sizes.s72,
                          width: Sizes.s72)
                      .boxShapeExtension(),
              HSpace(Sizes.s10),
              Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(
                    data!.name
                        .toString()
                        .replaceFirst("HighestRatedProviderName.", "")
                    */ /* data != null ? data!.name : "Provider" */ /*
                    */ /* capitalizeFirstLetter(
                        data != null ? data!.name! : "Provider") */ /*
                    ,
                    style: appCss.dmDenseSemiBold15
                        .textColor(appColor(context).darkText)),
               */ /* SizedBox(
                    width: Sizes.s160,
                    child: Text(
                        */ /**/ /* data!.categories != null && data!.categories!.isNotEmpty
                            ? data!.categories![0].title!
                            : */ /**/ /*
                        data!.expertise != null && data!.expertise!.isNotEmpty
                            ? data!.expertise![0].title!
                            : "Services",
                        overflow: TextOverflow.ellipsis,
                        style: appCss.dmDenseSemiBold12
                            .textColor(appColor(context).darkText))),*/ /*
                const VSpace(Sizes.s15),
                Row(children: [
                  SvgPicture.asset(eSvgAssets.locationOut, height: Sizes.s20),
                  const HSpace(Sizes.s5),
                  */ /*SizedBox(
                      width: Sizes.s140,
                      child: Text(
                          data != null && data!.primaryAddress != null
                              ? "${data!.primaryAddress!.address!}${data!.primaryAddress!.area != null ? ", ${data!.primaryAddress!.area}" : ""}, ${data!.primaryAddress!.city}"
                              : "Address Not Provided",
                          overflow: TextOverflow.ellipsis,
                          style: appCss.dmDenseSemiBold12
                              .textColor(appColor(context).lightText)))*/ /*
                ])
              ]),
            ],
          ),
          Row(children: [
            SvgPicture.asset(eSvgAssets.star),
            const HSpace(Sizes.s3),
            Text(
                data!.reviewRatings != null
                    ? data!.reviewRatings!.toStringAsFixed(2)
                    : "0",
                style: appCss.dmDenseSemiBold13
                    .textColor(appColor(context).darkText))
          ])
        ])
        .paddingAll(Insets.i15)
        .boxBorderExtension(context, isShadow: isHome)
        .inkWell(onTap: onTap)
        .paddingOnly(bottom: Insets.i15);
  }
}*/
