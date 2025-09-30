import 'dart:developer';

import '../../../../config.dart';
import '../../../../widgets/ad_slot_widget.dart';
import '../layouts/horizontal_blog_list.dart';
import '../layouts/new_job_request_layout.dart';

class NewYorkBody extends StatelessWidget {
  const NewYorkBody({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer3<DashboardProvider, HomeScreenProvider, CommonApiProvider>(
        builder: (context3, dash, value, commonApi, child) {
      return StatefulWrapper(
          onInit: () {
            Future.delayed(const Duration(milliseconds: 150)).then((value) {
              /*  dash.getOffer(); */
            });
          },
          child: ListView(children: [
            Column(
              children: [
                AdSlotWidget(
                  slot: 'home_special_offers',
                  margin: const EdgeInsets.symmetric(horizontal: Insets.i20),
                  borderRadius: BorderRadius.circular(AppRadius.r12),
                  onPlacementTap: (item) => value.onPlacementTap(context, item),
                ),
                const VSpace(Sizes.s20),
              ],
            ),
            Column(
              children: [
                if (commonApi.dashboardModel != null &&
                    commonApi.dashboardModel!.coupons!.isNotEmpty)
                  HeadingRowCommon(
                      title: translations!.coupons,
                      isTextSize: true,
                      onTap: () {
                        dash.getCoupons();
                        route.pushNamed(context, routeName.couponListScreen,
                            arg: true);
                      }).paddingSymmetric(horizontal: Insets.i20),
                if (commonApi.dashboardModel != null &&
                    commonApi.dashboardModel!.coupons!.isNotEmpty)
                  const VSpace(Sizes.s15),
                if (commonApi.dashboardModel != null &&
                    commonApi.dashboardModel!.coupons!.isNotEmpty)
                  SizedBox(
                    height: Sizes.s70,
                    child: ListView.builder(
                        physics: const BouncingScrollPhysics(),
                        scrollDirection: Axis.horizontal,
                        itemCount: commonApi.dashboardModel!.coupons?.length,
                        itemBuilder: (context, index) {
                          return HomeCouponLayout(
                              data: commonApi.dashboardModel!.coupons![index]);
                        }),
                  ),
              ],
            ),
            VSpace(commonApi.dashboardModel != null &&
                    commonApi.dashboardModel!.coupons!.isNotEmpty
                ? Sizes.s25
                : Sizes.s15),
            const CategoryFeaturePackageServices(isNewYork: true),
            const VSpace(Sizes.s25),
            if (appSettingModel != null &&
                appSettingModel!.activation!.blogsEnable == "1")
              Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                if (commonApi.dashboardModel2 != null &&
                    commonApi.dashboardModel != null &&
                    commonApi.dashboardModel2!.blogs!.isNotEmpty)
                  HeadingRowCommon(
                      title: translations!.latestBlog,
                      isTextSize: true,
                      onTap: () {
                        dash.getBlog().then((value) {
                          route.pushNamed(context, routeName.latestBlogViewAll);
                        });
                      }).paddingSymmetric(horizontal: Insets.i20),
                if (commonApi.dashboardModel2 != null)
                  HorizontalBlogList(
                      blogList: commonApi.dashboardModel2!.blogs),
                const VSpace(Sizes.s25)
              ]),
            if (commonApi.dashboardModel != null)
              if (appSettingModel != null &&
                  appSettingModel?.serviceRequest?.status == "1")
                const NewJobRequestLayout(),
            const VSpace(Sizes.s50)
          ]));
    });
  }
}
