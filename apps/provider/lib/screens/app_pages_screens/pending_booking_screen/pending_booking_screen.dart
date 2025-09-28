import 'package:fixit_provider/screens/app_pages_screens/pending_booking_screen/layouts/pending_booking_layout.dart';

import '../../../config.dart';
import '../../bottom_screens/booking_screen/booking_shimmer/booking_detail_shimmer.dart';

class PendingBookingScreen extends StatelessWidget {
  const PendingBookingScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<PendingBookingProvider>(builder: (context, value, child) {
      return PopScope(
        canPop: true,
        onPopInvoked: (didPop) {
          value.onBack(context, false);
          if (didPop) return;
        },
        child: StatefulWrapper(
            onInit: /*() => value.onReady(
                context)*/  () => Future.delayed(const Duration(milliseconds: 100),
                () => value.onReady(context)) ,
            child: Scaffold(
                appBar: AppBarCommon(
                    title: translations!.pendingBooking,
                    onTap: () => value.onBack(context, true)),
                body:
                    value.isLoading == true /* && value.bookingModel == null */
                        ? const BookingDetailShimmer()
                        : SafeArea(
                            child: RefreshIndicator(
                                onRefresh: () async {
                                  value.onRefresh(context);
                                },
                                child: value.isLoading == true
                                    ? const BookingDetailShimmer()
                                    : value.bookingModel == null
                                        ? const BookingDetailShimmer()
                                        : const PendingBookingLayout()
                              /*  Stack(
                                alignment: Alignment.bottomCenter,
                                children: [
                                  SingleChildScrollView(
                                          child: Column(
                                              crossAxisAlignment:
                                                  CrossAxisAlignment.start,
                                              children: [
                                        StatusDetailLayout(
                                            data: value.bookingModel,
                                            onTapStatus: () =>
                                                showBookingStatus(context,
                                                    value.bookingModel)),
                                        if (value.isAmount)
                                          ServicemenPayableLayout(
                                              amount: value.amountCtrl.text),
                                        Text(
                                                language(context,
                                                    translations!.billSummary),
                                                style: appCss.dmDenseMedium14
                                                    .textColor(appColor(context)
                                                        .appTheme
                                                        .darkText))
                                            .paddingOnly(
                                                top: Insets.i25,
                                                bottom: Insets.i10),
                                        CancelledBillSummary(
                                            bookingModel: value.bookingModel),
                                        const VSpace(Sizes.s20),
                                        if (value.bookingModel?.service
                                                    ?.reviews !=
                                                null &&
                                            value.bookingModel!.service!
                                                .reviews!.isNotEmpty)
                                          ReviewListWithTitle(
                                              reviews: value.bookingModel!
                                                  .service!.reviews!)
                                      ]).paddingAll(Insets.i20))
                                      .paddingOnly(bottom: Insets.i100),
                                  if (value.isLoading == false)
                                    Material(
                                        elevation: 20,
                                        child: BottomSheetButtonCommon(
                                                textOne: translations!.reject,
                                                textTwo: translations!.accept,
                                                clearTap: () => value
                                                    .onRejectBooking(context),
                                                applyTap: () {
                                                  value.loading = true;
                                                  value
                                                      .onAcceptBooking(context);
                                                })
                                            .paddingAll(Insets.i20)
                                            .decorated(
                                                color: appColor(context)
                                                    .appTheme
                                                    .whiteBg))
                                ]) */
                                ),
                          ))),
      );
    });
  }
}
