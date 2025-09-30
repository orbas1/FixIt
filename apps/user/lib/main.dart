import 'dart:async';

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import 'package:sentry_flutter/sentry_flutter.dart';
import 'package:upgrader/upgrader.dart';

import 'bootstrap/app_bootstrapper.dart';
import 'bootstrap/provider_registry.dart';
import 'common/languages/language_change.dart';
import 'common/theme/theme_service.dart';
import 'common/theme/app_theme.dart';
import 'config.dart';
import 'helper/notification.dart';
import 'providers/common_providers/currency_provider.dart';
import 'routes/route_method.dart';
import 'services/environment.dart';
import 'services/logging/app_logger.dart';
import 'services/state/app_state_store.dart';
import 'services/state/user_session_store.dart';
import 'services/background/background_sync.dart';

final AppLogger _logger = AppLogger.instance;

Future<void> main() async {
  final bootstrapper = AppBootstrapper();
  final bootstrapResult = await bootstrapper.bootstrap(_firebaseMessagingBackgroundHandler);

  await BackgroundSyncScheduler.instance.initialize();
  await BackgroundSyncScheduler.instance.scheduleFeedRefresh();

  final telemetry = bootstrapResult.environment.telemetry;

  await SentryFlutter.init(
    (options) {
      options.dsn = telemetry.sentryDsn;
      options.environment = bootstrapResult.environment.app.flavor;
      options.release = bootstrapResult.environment.app.name;
      options.tracesSampleRate = 0.2;
      options.profilesSampleRate = 0.1;
      options.enableAutoPerformanceTracking = true;
    },
    appRunner: () {
      runZonedGuarded(
        () => runApp(MyApp(bootstrapResult: bootstrapResult)),
        (error, stackTrace) {
          _logger.error('Uncaught error', error: error, stackTrace: stackTrace);
          Sentry.captureException(error, stackTrace: stackTrace);
        },
      );
    },
  );
}

class MyApp extends StatelessWidget {
  const MyApp({super.key, required this.bootstrapResult});

  final BootstrapResult bootstrapResult;

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: AppProviderRegistry.buildProviders(
        context,
        bootstrapResult.sharedPreferences,
      ),
      child: UpgradeAlert(
        dialogStyle: UpgradeDialogStyle.cupertino,
        showIgnore: false,
        showLater: false,
        barrierDismissible: false,
        upgrader: Upgrader(
          storeController: UpgraderStoreController(onAndroid: () => UpgraderPlayStore()),
        ),
        child: const RouteToPage(),
      ),
    );
  }
}

class RouteToPage extends StatefulWidget {
  const RouteToPage({super.key});

  @override
  State<RouteToPage> createState() => _RouteToPageState();
}

class _RouteToPageState extends State<RouteToPage> {
  GoRouter? _router;

  @override
  void initState() {
    super.initState();
    CustomNotificationController().initFirebaseMessaging();
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final appStateStore = Provider.of<AppStateStore>(context);
    final sessionStore = Provider.of<UserSessionStore>(context);
    _router ??= AppRouter(
      appStateStore: appStateStore,
      sessionStore: sessionStore,
    ).router;
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<ThemeService>(builder: (context, theme, child) {
      return Consumer<LanguageProvider>(builder: (context, lang, child) {
        return Consumer<CurrencyProvider>(builder: (context, currency, child) {
          if (currency.currency == null) {
            currency.setVal();
          }

          final provider = Provider.of<LanguageProvider>(context, listen: true);

          final router = _router;
          if (router == null) {
            return const SizedBox.shrink();
          }
          return MaterialApp.router(
            title: 'Fixit User',
            routerConfig: router,
            debugShowCheckedModeBanner: false,
            theme: AppTheme.fromType(ThemeType.light).themeData,
            darkTheme: AppTheme.fromType(ThemeType.dark).themeData,
            locale: provider.locale,
            localizationsDelegates: const [
              AppLocalizations.delegate,
              AppLocalizationDelagate(),
              GlobalMaterialLocalizations.delegate,
              GlobalWidgetsLocalizations.delegate,
              GlobalCupertinoLocalizations.delegate,
            ],
            themeMode: theme.theme,
            navigatorObservers: const [SentryNavigatorObserver()],
            builder: (context, child) {
              return Directionality(
                textDirection: lang.locale?.languageCode == 'ar'
                    ? TextDirection.rtl
                    : TextDirection.ltr,
                child: child!,
              );
            },
          );
        });
      });
    });
  }
}

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  final environment = environmentStore.environment;
  await Firebase.initializeApp(options: environment.firebase.toFirebaseOptions());

  final title = message.data['title'] ?? '';
  final AndroidNotificationChannel channel = AndroidNotificationChannel(
    'high_importance_channel',
    'High Importance Notifications for Astrologically',
    description: 'This channel is used for important notifications.',
    importance: Importance.high,
    playSound: true,
    sound: (title == 'Incoming Audio Call...' || title == 'Incoming Video Call...')
        ? const RawResourceAndroidNotificationSound('callsound')
        : null,
  );

  showNotification(message, channel);
}

Future<void> showNotification(RemoteMessage remote, AndroidNotificationChannel channel) async {
  final String title = remote.notification?.title ?? '';
  final String body = remote.notification?.body ?? '';

  final androidDetails = AndroidNotificationDetails(
    channel.id,
    channel.name,
    channelDescription: channel.description,
    importance: Importance.max,
    priority: Priority.high,
    playSound: true,
    icon: "ic_notification",
    sound: (remote.data['title'] == 'Incoming Audio Call...' ||
            remote.data['title'] == 'Incoming Video Call...')
        ? const RawResourceAndroidNotificationSound('callsound')
        : null,
    fullScreenIntent: true,
    visibility: NotificationVisibility.public,
  );

  const iOSDetails = DarwinNotificationDetails(
    sound: 'callsound.wav',
    presentSound: true,
  );

  final notificationDetails = NotificationDetails(
    android: androidDetails,
    iOS: iOSDetails,
  );

  await flutterLocalNotificationsPlugin?.show(
    0,
    title,
    body,
    notificationDetails,
    payload: remote.data.toString(),
  );
}
