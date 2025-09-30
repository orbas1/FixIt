import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/services.dart';
import 'package:flutter/widgets.dart';
import 'package:get_it/get_it.dart';
import 'package:hive_flutter/hive_flutter.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../services/configuration/app_environment.dart';
import '../services/environment.dart';
import '../services/logging/app_logger.dart';
import 'dependency_container.dart';

class BootstrapResult {
  BootstrapResult({
    required this.environment,
    required this.sharedPreferences,
    required this.firebaseApp,
  });

  final AppEnvironment environment;
  final SharedPreferences sharedPreferences;
  final FirebaseApp firebaseApp;
}

class AppBootstrapper {
  AppBootstrapper({GetIt? getIt}) : _getIt = getIt ?? GetIt.instance;

  final GetIt _getIt;
  final AppLogger _logger = AppLogger.instance;

  Future<BootstrapResult> bootstrap(Future<void> Function(RemoteMessage message)? backgroundHandler) async {
    WidgetsFlutterBinding.ensureInitialized();

    FlutterError.onError = (details) {
      _logger.error('Flutter framework error', error: details.exception, stackTrace: details.stack);
    };
    PlatformDispatcher.instance.onError = (error, stack) {
      _logger.error('Uncaught platform error', error: error, stackTrace: stack);
      return true;
    };

    await SystemChrome.setPreferredOrientations([
      DeviceOrientation.portraitUp,
      DeviceOrientation.portraitDown,
    ]);

    final environment = await AppEnvironment.load();
    final preferences = await SharedPreferences.getInstance();
    await Hive.initFlutter();

    await EnvironmentStore.instance.configure(
      environment: environment,
      preferences: preferences,
    );

    final firebaseApp = await Firebase.initializeApp(
      options: environment.firebase.toFirebaseOptions(),
    );

    if (backgroundHandler != null) {
      FirebaseMessaging.onBackgroundMessage(backgroundHandler);
    }

    final dependencyContainer = DependencyContainer(_getIt);
    await dependencyContainer.register(
      environment: environment,
      sharedPreferences: preferences,
    );

    return BootstrapResult(
      environment: environment,
      sharedPreferences: preferences,
      firebaseApp: firebaseApp,
    );
  }
}
