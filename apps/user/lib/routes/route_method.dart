//app file
import 'package:flutter/foundation.dart';
import 'package:go_router/go_router.dart';

import 'package:fixit_user/screens/app_pages_screens/audio_call/audio_call.dart';
import 'package:fixit_user/screens/app_pages_screens/chat_history_screen/chat_history_screen.dart';
import 'package:fixit_user/screens/app_pages_screens/custom_job_request/add_job_request/add_job_request.dart';
import 'package:fixit_user/screens/app_pages_screens/custom_job_request/job_request_list/job_request_list.dart';
import 'package:fixit_user/screens/app_pages_screens/maintenance_mode.dart';
import 'package:fixit_user/screens/app_pages_screens/maintenance_screen/maintenance_screen.dart';
import 'package:fixit_user/screens/app_pages_screens/payment_web_view.dart';
import 'package:fixit_user/screens/app_pages_screens/support_ticket_screen/support_ticket_list_screen.dart';
import 'package:fixit_user/screens/app_pages_screens/video_call/video_call.dart';
import 'package:fixit_user/screens/app_pages_screens/affiliate/affiliate_hub_screen.dart';
import 'package:fixit_user/screens/app_pages_screens/disputes/dispute_detail_screen.dart';
import 'package:fixit_user/screens/app_pages_screens/disputes/dispute_center_screen.dart';
import 'package:fixit_user/screens/app_pages_screens/escrow/escrow_center_screen.dart';
import 'package:fixit_user/screens/app_pages_screens/storefront/storefront_browser_screen.dart';
import 'package:fixit_user/screens/discover/discover_screen.dart';
import 'package:fixit_user/screens/feed/feed_screen.dart';
import 'package:fixit_user/screens/feed/feed_job_detail_screen.dart';
import 'package:fixit_user/screens/landing/landing_screen.dart';

import '../config.dart';
import '../models/feed_job_model.dart';
import '../screens/app_pages_screens/provider_chat_screen/provider_chat_screen.dart';
import '../services/state/app_state_store.dart';
import '../services/state/user_session_store.dart';

class AppRoute {
  Map<String, Widget Function(BuildContext)> route = {
    routeName.splash: (p0) => const SplashScreen(),
    routeName.onBoarding: (p0) => const OnBoardingScreen(),
    routeName.login: (p0) => const LoginScreen(),
    routeName.loginWithPhone: (p0) => const LoginWithPhoneScreen(),
    routeName.verifyOtp: (p0) => const VerifyOtpScreen(),
    routeName.forgetPassword: (p0) => const ForgotPasswordScreen(),
    routeName.resetPass: (p0) => const ResetPasswordScreen(),
    routeName.registerUser: (p0) => const RegisterScreen(),
    routeName.dashboard: (p0) => const Dashboard(),
    routeName.changePass: (p0) => const ChangePasswordScreen(),
    routeName.appSetting: (p0) => const AppSettingScreen(),
    routeName.changeLanguage: (p0) => const ChangeLanguageScreen(),
    routeName.profileDetail: (p0) => const ProfileDetailScreen(),
    routeName.walletBalance: (p0) => const WalletBalanceScreen(),
    routeName.favoriteList: (p0) => const FavouriteListScreen(),
    routeName.myLocation: (p0) => const MyLocationScreen(),
    routeName.review: (p0) => const ReviewScreen(),
    routeName.editReview: (p0) => const EditReviewScreen(),
    routeName.appDetails: (p0) => const AppDetailsScreen(),
    routeName.rateApp: (p0) => const RateAppScreen(),
    routeName.contactUs: (p0) => const ContactUsScreen(),
    routeName.helpSupport: (p0) => const HelpSupportScreen(),
    routeName.notifications: (p0) => const NotificationScreen(),
    routeName.location: (p0) => const LocationScreen(),
    routeName.currentLocation: (p0) => const CurrentLocationScreen(),
    routeName.addNewLocation: (p0) => const AddNewLocation(),
    routeName.search: (p0) => const SearchScreen(),
    routeName.latestBlogViewAll: (p0) => const LatestBlogViewAll(),
    routeName.latestBlogDetails: (p0) => const LatestBlogDetailsScreen(),
    routeName.noInternet: (p0) => const NoInternetScreen(),
    routeName.bookingList: (p0) => const BookingScreen(),
    routeName.categoriesListScreen: (p0) => const CategoriesListScreen(),
    routeName.categoriesDetailsScreen: (p0) => const CategoryDetailScreen(),
    routeName.servicesDetailsScreen: (p0) => const ServicesDetailsScreen(),
    routeName.servicesReviewScreen: (p0) => const ServiceReviewScreen(),
    routeName.providerDetailsScreen: (p0) => const ProviderDetailsScreen(),
    routeName.slotBookingScreen: (p0) => const SlotBookingScreen(),
    routeName.cartScreen: (p0) => const CartScreen(),
    routeName.couponListScreen: (p0) => const CouponListScreen(),
    routeName.paymentScreen: (p0) => const PaymentScreen(),
    routeName.serviceSelectedUserScreen: (p0) =>
        const ServiceSelectedUserScreen(),
    routeName.servicemanListScreen: (p0) => const ServicemanListScreen(),
    routeName.servicemanDetailScreen: (p0) => const ServicemanDetailScreen(),
    routeName.featuredServiceScreen: (p0) => const FeaturedServiceScreen(),
    routeName.expertServiceScreen: (p0) => const ExpertServiceScreen(),
    routeName.servicePackagesScreen: (p0) => const ServicePackagesScreen(),
    routeName.packageDetailsScreen: (p0) => const PackageDetailsScreen(),
    routeName.selectServiceScreen: (p0) => const SelectServiceScreen(),
    routeName.pendingBookingScreen: (p0) => const PendingBookingScreen(),
    routeName.acceptedBookingScreen: (p0) => const AcceptedBookingScreen(),
    routeName.chatScreen: (p0) => const ChatScreen(),
    routeName.ongoingBookingScreen: (p0) => const OngoingBookingScreen(),
    routeName.completedServiceScreen: (p0) => const CompletedServiceScreen(),
    routeName.cancelledServiceScreen: (p0) => const CancelledBookingScreen(),
    routeName.checkoutWebView: (p0) => const CheckoutWebView(),
    routeName.chatHistory: (p0) => const ChatHistoryScreen(),
    routeName.jobRequestList: (p0) => const JobRequestList(),
    routeName.jobRequestDetail  : (p0) => const JobRequestDetails(),
    routeName.addJobRequestList  : (p0) => const AddJobRequest(),
    routeName.maintenanceMode  : (p0) => const MaintenanceMode(),
    // routeName.audioCall  : (p0) => const AudioCall(),
    // routeName.videoCall  : (p0) => const VideoCall(),
    routeName.providerChatScreen: (p0) => const ProviderChatScreen(),


    routeName.maintenance: (p0) => const MaintenanceScreen(),
    routeName.supportTicketListScreen: (p0) => const SupportTicketListScreen(),
  };

  List<GoRoute> buildGoRoutes() {
    return route.entries
        .map(
          (entry) => _materialRoute(
            path: entry.key,
            builder: entry.value,
          ),
        )
        .toList();
  }

  GoRoute _materialRoute({
    required String path,
    required Widget Function(BuildContext) builder,
  }) {
    return GoRoute(
      path: path,
      pageBuilder: (context, state) => MaterialPage(
        key: state.pageKey,
        name: path,
        arguments: state.extra,
        child: Builder(builder: builder),
      ),
    );
  }
}

class AppRouter {
  AppRouter({
    required AppStateStore appStateStore,
    required UserSessionStore sessionStore,
  })  : _appStateStore = appStateStore,
        _sessionStore = sessionStore;

  final AppStateStore _appStateStore;
  final UserSessionStore _sessionStore;

  late final GoRouter router = GoRouter(
    navigatorKey: navigatorKey,
    initialLocation: routeName.splash,
    refreshListenable: Listenable.merge([
      _appStateStore,
      _sessionStore,
    ]),
    routes: [
      ...AppRoute().buildGoRoutes(),
      ..._enterpriseRoutes(),
    ],
    redirect: _handleRedirect,
  );

  List<GoRoute> _enterpriseRoutes() {
    return [
      GoRoute(
        path: routeName.landing,
        builder: (context, state) => const LandingScreen(),
      ),
      GoRoute(
        path: routeName.discover,
        builder: (context, state) => const DiscoverScreen(),
      ),
      GoRoute(
        path: routeName.feed,
        builder: (context, state) => const FeedScreen(),
      ),
      GoRoute(
        path: routeName.storefronts,
        builder: (context, state) => const StorefrontBrowserScreen(),
      ),
      GoRoute(
        path: routeName.escrowCenter,
        builder: (context, state) => const EscrowCenterScreen(),
      ),
      GoRoute(
        path: routeName.disputeCenter,
        builder: (context, state) => const DisputeCenterScreen(),
      ),
      GoRoute(
        path: routeName.productDetails,
        pageBuilder: (context, state) {
          final params = Map<String, dynamic>.from(
            state.extra is Map ? state.extra as Map : {},
          );
          final id = state.pathParameters['id'];
          if (id != null) {
            params['serviceId'] = int.tryParse(id) ?? id;
          }
          return MaterialPage(
            key: state.pageKey,
            name: routeName.productDetails,
            arguments: params,
            child: const ServicesDetailsScreen(),
          );
        },
      ),
      GoRoute(
        path: routeName.store,
        pageBuilder: (context, state) {
          final params = Map<String, dynamic>.from(
            state.extra is Map ? state.extra as Map : {},
          );
          final slug = state.pathParameters['slug'];
          if (slug != null) {
            params['providerSlug'] = slug;
          }
          return MaterialPage(
            key: state.pageKey,
            name: routeName.store,
            arguments: params,
            child: const ProviderDetailsScreen(),
          );
        },
      ),
      GoRoute(
        path: routeName.jobDetails,
        pageBuilder: (context, state) {
          final idParam = state.pathParameters['id'];
          final jobId = int.tryParse(idParam ?? '');
          FeedJobModel? job;
          final extra = state.extra;
          if (extra is FeedJobModel) {
            job = extra;
          } else if (extra is Map) {
            final candidate = extra['job'];
            if (candidate is FeedJobModel) {
              job = candidate;
            }
          }
          if (jobId == null) {
            return MaterialPage(
              key: state.pageKey,
              name: routeName.jobDetails,
              child: const MaintenanceMode(),
            );
          }
          return MaterialPage(
            key: state.pageKey,
            name: routeName.jobDetails,
            arguments: {'jobId': jobId, if (job != null) 'job': job},
            child: FeedJobDetailScreen(jobId: jobId, initialJob: job),
          );
        },
      ),
      GoRoute(
        path: routeName.checkout,
        pageBuilder: (context, state) => MaterialPage(
          key: state.pageKey,
          name: routeName.checkout,
          arguments: state.extra,
          child: const PaymentScreen(),
        ),
      ),
      GoRoute(
        path: routeName.disputeDetails,
        pageBuilder: (context, state) {
          final params = Map<String, dynamic>.from(
            state.extra is Map ? state.extra as Map : {},
          );
          final id = state.pathParameters['id'];
          if (id != null) {
            params['disputeId'] = int.tryParse(id) ?? id;
          }
          return MaterialPage(
            key: state.pageKey,
            name: routeName.disputeDetails,
            arguments: params,
            child: const DisputeDetailScreen(),
          );
        },
      ),
      GoRoute(
        path: routeName.affiliate,
        builder: (context, state) => const AffiliateHubScreen(),
      ),
      GoRoute(
        path: routeName.settings,
        pageBuilder: (context, state) => MaterialPage(
          key: state.pageKey,
          name: routeName.settings,
          arguments: state.extra,
          child: const AppSettingScreen(),
        ),
      ),
    ];
  }

  String? _handleRedirect(BuildContext context, GoRouterState state) {
    final matchedLocation = state.matchedLocation;
    final path = state.uri.path;
    final isAuthenticated = _appStateStore.isAuthenticated;
    final isPublic = _isPublicPath(matchedLocation, path);

    if (!isAuthenticated) {
      if (!isPublic) {
        final redirectUri = state.uri.toString();
        if (_authEntryPoints.contains(matchedLocation) ||
            _authEntryPoints.contains(path)) {
          return null;
        }
        return Uri(
          path: routeName.login,
          queryParameters: {'redirect': redirectUri},
        ).toString();
      }
      return null;
    }

    if (_authEntryPoints.contains(matchedLocation) ||
        _authEntryPoints.contains(path)) {
      final redirectTarget = state.uri.queryParameters['redirect'];
      if (redirectTarget != null && redirectTarget.isNotEmpty) {
        return redirectTarget;
      }
      return routeName.feed;
    }

    if (!_sessionStore.isKycVerified &&
        _requiresKyc(matchedLocation, path)) {
      if (matchedLocation == routeName.settings ||
          matchedLocation == routeName.appSetting) {
        return null;
      }

      return Uri(
        path: routeName.settings,
        queryParameters: {
          'kyc': 'required',
          'redirect': state.uri.toString(),
        },
      ).toString();
    }

    if (matchedLocation == routeName.splash) {
      return routeName.feed;
    }

    return null;
  }

  bool _isPublicPath(String matchedLocation, String path) {
    if (_publicExactLocations.contains(matchedLocation) ||
        _publicExactLocations.contains(path)) {
      return true;
    }
    for (final prefix in _publicPrefixes) {
      if (matchedLocation.startsWith(prefix) || path.startsWith(prefix)) {
        return true;
      }
    }
    return false;
  }

  bool _requiresKyc(String matchedLocation, String path) {
    if (_isPublicPath(matchedLocation, path)) {
      return false;
    }
    if (_kycExact.contains(matchedLocation) || _kycExact.contains(path)) {
      return true;
    }
    for (final prefix in _kycPrefixes) {
      if (matchedLocation.startsWith(prefix) || path.startsWith(prefix)) {
        return true;
      }
    }
    return false;
  }

  static const Set<String> _publicExactLocations = {
    '/',
    '/landing',
    '/discover',
    '/login',
    '/loginWithPhone',
    '/verifyOtp',
    '/forgetPassword',
    '/resetPass',
    '/registerUser',
    '/onBoarding',
    '/changeLanguage',
    '/latestBlogViewAll',
    '/latestBlogDetails',
    '/noInternet',
    '/appDetails',
    '/rateApp',
    '/contactUs',
    '/helpSupport',
    '/search',
  };

  static const Set<String> _authEntryPoints = {
    '/login',
    '/loginWithPhone',
    '/verifyOtp',
    '/forgetPassword',
    '/resetPass',
    '/registerUser',
  };

  static const Set<String> _kycExact = {
    '/checkout',
    '/walletBalance',
    '/paymentScreen',
    '/profileDetail',
    '/appSetting',
    '/supportTicketListScreen',
    '/jobRequestList',
    '/jobRequestDetail',
    '/addJobRequestList',
    '/cartScreen',
    '/slotBookingScreen',
    '/checkoutWebView',
    '/chatHistory',
    '/chatScreen',
    '/providerChatScreen',
    '/servicemanDetailScreen',
    '/affiliate',
    '/escrows',
    '/disputes',
  };

  static const List<String> _publicPrefixes = [
    '/product/',
    '/store/',
  ];

  static const List<String> _kycPrefixes = [
    '/feed',
    '/jobs/',
    '/checkout',
    '/disputes/',
    '/settings',
  ];
}
