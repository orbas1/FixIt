import 'package:flutter/widgets.dart';
import 'package:provider/provider.dart';
import 'package:provider/single_child_widget.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../common/languages/language_change.dart';
import '../common/theme/theme_service.dart';
import '../providers/index.dart';

class AppProviderRegistry {
  static List<SingleChildWidget> buildProviders(
    BuildContext context,
    SharedPreferences sharedPreferences,
  ) {
    return [
      ChangeNotifierProvider(create: (_) => ThemeService(sharedPreferences)),
      ChangeNotifierProvider(create: (_) => SplashProvider()),
      ChangeNotifierProvider(
          create: (_) => LanguageProvider(sharedPreferences, context)),
      ChangeNotifierProvider(create: (_) => CommonApiProvider()),
      ChangeNotifierProvider(create: (_) => OnBoardingProvider()),
      ChangeNotifierProvider(create: (_) => LoginProvider()),
      ChangeNotifierProvider(create: (_) => OfferChatProvider()),
      ChangeNotifierProvider(create: (_) => LoginWithPhoneProvider()),
      ChangeNotifierProvider(create: (_) => VerifyOtpProvider()),
      ChangeNotifierProvider(create: (_) => ForgetPasswordProvider()),
      ChangeNotifierProvider(create: (_) => RegisterProvider()),
      ChangeNotifierProvider(create: (_) => ResetPasswordProvider()),
      ChangeNotifierProvider(create: (_) => LoadingProvider()),
      ChangeNotifierProvider(create: (_) => DashboardProvider()),
      ChangeNotifierProvider(create: (_) => HomeScreenProvider()),
      ChangeNotifierProvider(create: (_) => ProfileProvider()),
      ChangeNotifierProvider(create: (_) => AppSettingProvider(sharedPreferences)),
      ChangeNotifierProvider(create: (_) => CurrencyProvider()),
      ChangeNotifierProvider(create: (_) => ProfileDetailProvider()),
      ChangeNotifierProvider(create: (_) => FavouriteListProvider()),
      ChangeNotifierProvider(create: (_) => CommonPermissionProvider()),
      ChangeNotifierProvider(create: (_) => LocationProvider()),
      ChangeNotifierProvider(create: (_) => ChangePasswordProvider()),
      ChangeNotifierProvider(create: (_) => MyReviewProvider()),
      ChangeNotifierProvider(create: (_) => EditReviewProvider()),
      ChangeNotifierProvider(create: (_) => AppDetailsProvider()),
      ChangeNotifierProvider(create: (_) => RateAppProvider()),
      ChangeNotifierProvider(create: (_) => ContactUsProvider()),
      ChangeNotifierProvider(create: (_) => NotificationProvider()),
      ChangeNotifierProvider(create: (_) => NewLocationProvider()),
      ChangeNotifierProvider(create: (_) => SearchProvider()),
      ChangeNotifierProvider(create: (_) => LatestBLogDetailsProvider()),
      ChangeNotifierProvider(create: (_) => NoInternetProvider()),
      ChangeNotifierProvider(create: (_) => CategoriesListProvider()),
      ChangeNotifierProvider(create: (_) => CategoriesDetailsProvider()),
      ChangeNotifierProvider(create: (_) => ServicesDetailsProvider()),
      ChangeNotifierProvider(create: (_) => ServiceReviewProvider()),
      ChangeNotifierProvider(create: (_) => ProviderDetailsProvider()),
      ChangeNotifierProvider(create: (_) => SlotBookingProvider()),
      ChangeNotifierProvider(create: (_) => CartProvider()),
      ChangeNotifierProvider(create: (_) => PaymentProvider()),
      ChangeNotifierProvider(create: (_) => WalletProvider()),
      ChangeNotifierProvider(create: (_) => ServicemanListProvider()),
      ChangeNotifierProvider(create: (_) => ServiceSelectProvider()),
      ChangeNotifierProvider(create: (_) => SelectServicemanProvider()),
      ChangeNotifierProvider(create: (_) => BookingProvider()),
      ChangeNotifierProvider(create: (_) => PendingBookingProvider()),
      ChangeNotifierProvider(create: (_) => AcceptedBookingProvider()),
      ChangeNotifierProvider(create: (_) => ChatProvider()),
      ChangeNotifierProvider(create: (_) => OngoingBookingProvider()),
      ChangeNotifierProvider(create: (_) => CompletedServiceProvider()),
      ChangeNotifierProvider(create: (_) => ServicesPackageDetailsProvider()),
      ChangeNotifierProvider(create: (_) => CheckoutWebViewProvider()),
      ChangeNotifierProvider(create: (_) => CancelledBookingProvider()),
      ChangeNotifierProvider(create: (_) => PackageBookingProvider()),
      ChangeNotifierProvider(create: (_) => ServicemanDetailProvider()),
      ChangeNotifierProvider(create: (_) => FeaturedServiceProvider()),
      ChangeNotifierProvider(create: (_) => ExpertServiceProvider()),
      ChangeNotifierProvider(create: (_) => ChatHistoryProvider()),
      ChangeNotifierProvider(create: (_) => ThreadInboxProvider()),
      ChangeNotifierProvider(create: (_) => ThreadConversationProvider()),
      ChangeNotifierProvider(create: (_) => DeleteDialogProvider()),
      ChangeNotifierProvider(create: (_) => JobRequestListProvider()),
      ChangeNotifierProvider(create: (_) => AddJobRequestProvider()),
      ChangeNotifierProvider(create: (_) => FeedProvider()),
      ChangeNotifierProvider(create: (_) => JobRequestDetailsProvider()),
      ChangeNotifierProvider(create: (_) => ServicePackageAllListProvider()),
    ];
  }
}
