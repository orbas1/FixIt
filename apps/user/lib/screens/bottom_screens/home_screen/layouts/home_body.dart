import 'dart:developer';

import '../../../../config.dart';
import '../../../../widgets/ad_slot_widget.dart';
import 'horizontal_blog_list.dart';
import 'new_job_request_layout.dart';

class HomeBody extends StatelessWidget {
  const HomeBody({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer3<DashboardProvider, HomeScreenProvider, CommonApiProvider>(
        builder: (context3, dash, value, commonApi, child) {
      return StatefulWrapper(
        onInit: () {},
        /*   onInit: () => Future.delayed(
            const Duration(milliseconds: 100), () => dash.getCoupons()), */
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
          if (commonApi.dashboardModel != null &&
              commonApi.dashboardModel!.coupons!.isNotEmpty)
            Column(children: [
              if (commonApi.dashboardModel!.coupons!
                  .isNotEmpty /* dash.couponList.isNotEmpty */)
                const VSpace(Sizes.s20),
              HeadingRowCommon(
                  title: translations!.coupons,
                  isTextSize: true,
                  onTap: () {
                    dash.getCoupons();
                    route.pushNamed(context, routeName.couponListScreen,
                        arg: true);
                  }).paddingSymmetric(horizontal: Insets.i20),
              if (commonApi.dashboardModel!.coupons!
                  .isNotEmpty /* dash.couponList.isNotEmpty */)
                const VSpace(Sizes.s15),
              if (commonApi.dashboardModel!.coupons!
                  .isNotEmpty /* dash.couponList.isNotEmpty */)
                SizedBox(
                    height: Sizes.s70,
                    child: ListView.builder(
                        physics: const BouncingScrollPhysics(),
                        scrollDirection: Axis.horizontal,
                        itemCount: commonApi.dashboardModel!.coupons
                            ?.length /* dash.couponList.length */,
                        itemBuilder: (context, index) {
                          return HomeCouponLayout(
                              data: commonApi.dashboardModel!.coupons![
                                  index] /* dash.couponList[index] */);
                        }))
            ]),
          VSpace(commonApi.dashboardModel != null &&
                  commonApi.dashboardModel!.coupons!.isNotEmpty
              ? Sizes.s25
              : Sizes.s15),
          const CategoryFeaturePackageServices(),
          if (dash.firstTwoHighRateList.isNotEmpty ||
              dash.highestRateList.isNotEmpty)
            const VSpace(Sizes.s25),
          if (appSettingModel != null &&
              appSettingModel!.activation!.blogsEnable == "1")
            /*   dash.isBlogList
                ? Column(
                    children: [
                      const RowText()
                          .padding(horizontal: Sizes.s20, bottom: Sizes.s17),
                      SingleChildScrollView(
                          scrollDirection: Axis.horizontal,
                          child: Row(children: [
                            ...List.generate(2, (index) {
                              return const BlogShimmerLayout();
                            })
                          ]).marginSymmetric(horizontal: Sizes.s20))
                    ],
                  )
                : */

            Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              HeadingRowCommon(
                  title: translations!.latestBlog,
                  isTextSize: true,
                  onTap: () {
                    route.pushNamed(context, routeName.latestBlogViewAll);
                    dash.getBlog();
                  }).paddingSymmetric(horizontal: Insets.i20),
              HorizontalBlogList(blogList: commonApi.dashboardModel2!.blogs),
              const VSpace(Sizes.s25)
            ]),
          const NewJobRequestLayout(),
          const VSpace(Sizes.s50)
        ]),
      );
    });
  }
}
