import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:dio/dio.dart';
import 'package:get_it/get_it.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../services/configuration/app_environment.dart';
import '../services/environment.dart';
import '../services/logging/app_logger.dart';
import '../services/auth/auth_token_store.dart';
import '../services/http/connectivity_interceptor.dart';
import '../services/http/logging_interceptor.dart';
import '../services/http/retry_interceptor.dart';
import '../services/http/timeout_interceptor.dart';
import '../services/api_service.dart';
import '../services/auth/auth_session_manager.dart';
import '../services/http/auth_refresh_interceptor.dart';
import '../services/http/telemetry_interceptor.dart';
import '../services/logging/http_metrics_recorder.dart';
import '../services/realtime/app_realtime_bridge.dart';
import '../services/location/ip_location_client.dart';
import '../services/location/location_service.dart';
import '../services/security/file_security_service.dart';
import '../services/security/security_incident_service.dart';
import '../services/notifications/notification_preferences.dart';
import '../helper/notification.dart';

class DependencyContainer {
  DependencyContainer(this._getIt);

  final GetIt _getIt;

  Future<void> register({
    required AppEnvironment environment,
    required SharedPreferences sharedPreferences,
  }) async {
    if (_getIt.isRegistered<AppEnvironment>()) {
      _getIt.unregister<AppEnvironment>();
    }
    _getIt.registerSingleton<AppEnvironment>(environment);

    if (_getIt.isRegistered<SharedPreferences>()) {
      _getIt.unregister<SharedPreferences>();
    }
    _getIt.registerSingleton<SharedPreferences>(sharedPreferences);

    final authTokenStore = AuthTokenStore(preferences: sharedPreferences);
    if (_getIt.isRegistered<AuthTokenStore>()) {
      _getIt.unregister<AuthTokenStore>();
    }
    _getIt.registerSingleton<AuthTokenStore>(authTokenStore);

    final connectivity = Connectivity();
    if (_getIt.isRegistered<Connectivity>()) {
      _getIt.unregister<Connectivity>();
    }
    _getIt.registerSingleton<Connectivity>(connectivity);

    final metricsRecorder = HttpMetricsRecorder();
    if (_getIt.isRegistered<HttpMetricsRecorder>()) {
      _getIt.unregister<HttpMetricsRecorder>();
    }
    _getIt.registerSingleton<HttpMetricsRecorder>(metricsRecorder);

    final realtimeBridge = AppRealtimeBridge(
      configuration: environment.realtime,
      tokenStore: authTokenStore,
      environmentStore: EnvironmentStore.instance,
    );
    if (_getIt.isRegistered<AppRealtimeBridge>()) {
      _getIt.unregister<AppRealtimeBridge>();
    }
    _getIt.registerSingleton<AppRealtimeBridge>(realtimeBridge);

    final refreshClient = Dio(
      BaseOptions(
        baseUrl: environment.api.baseUrl,
        connectTimeout: Duration(milliseconds: environment.api.connectTimeoutMs),
        receiveTimeout: Duration(milliseconds: environment.api.receiveTimeoutMs),
        headers: EnvironmentStore.instance.headers(),
      ),
    );

    final sessionManager = AuthSessionManager(
      httpClient: refreshClient,
      tokenStore: authTokenStore,
      environmentStore: EnvironmentStore.instance,
    );
    if (_getIt.isRegistered<AuthSessionManager>()) {
      _getIt.unregister<AuthSessionManager>();
    }
    _getIt.registerSingleton<AuthSessionManager>(sessionManager);

    final dio = Dio(
      BaseOptions(
        baseUrl: environment.api.baseUrl,
        connectTimeout: Duration(milliseconds: environment.api.connectTimeoutMs),
        receiveTimeout: Duration(milliseconds: environment.api.receiveTimeoutMs),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ),
    );

    dio.interceptors.addAll([
      TelemetryInterceptor(metricsRecorder: metricsRecorder),
      TimeoutInterceptor(environment.api),
      ConnectivityInterceptor(connectivity: connectivity),
      RetryInterceptor(
        connectivity: connectivity,
        logger: AppLogger.instance,
        dio: dio,
      ),
      AuthRefreshInterceptor(
        client: dio,
        sessionManager: sessionManager,
      ),
      LoggingInterceptor(logger: AppLogger.instance),
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await authTokenStore.read();
          options.headers.addAll(
            EnvironmentStore.instance.headers(token: token),
          );
          handler.next(options);
        },
      ),
    ]);

    if (_getIt.isRegistered<Dio>()) {
      _getIt.unregister<Dio>();
    }
    _getIt.registerSingleton<Dio>(dio);

    if (_getIt.isRegistered<ApiServices>()) {
      _getIt.unregister<ApiServices>();
    }
    _getIt.registerSingleton<ApiServices>(
      ApiServices.withDependencies(
        dio: dio,
        environmentStore: EnvironmentStore.instance,
        tokenStore: authTokenStore,
      ),
    );

    final ipLocationClient = IpLocationClient(httpClient: dio);
    if (_getIt.isRegistered<IpLocationClient>()) {
      _getIt.unregister<IpLocationClient>();
    }
    _getIt.registerSingleton<IpLocationClient>(ipLocationClient);

    final locationService = LocationService(
      ipLocationClient: ipLocationClient,
      preferences: sharedPreferences,
    );
    if (_getIt.isRegistered<LocationService>()) {
      _getIt.unregister<LocationService>();
    }
    _getIt.registerSingleton<LocationService>(locationService);

    if (_getIt.isRegistered<FileSecurityService>()) {
      _getIt.unregister<FileSecurityService>();
    }
    _getIt.registerSingleton<FileSecurityService>(const FileSecurityService());

    if (_getIt.isRegistered<SecurityIncidentService>()) {
      _getIt.unregister<SecurityIncidentService>();
    }
    _getIt.registerSingleton<SecurityIncidentService>(SecurityIncidentService());

    if (_getIt.isRegistered<NotificationPreferenceStore>()) {
      _getIt.unregister<NotificationPreferenceStore>();
    }
    _getIt.registerSingleton<NotificationPreferenceStore>(
      NotificationPreferenceStore(preferences: sharedPreferences),
    );

    if (_getIt.isRegistered<PushNotificationService>()) {
      _getIt.unregister<PushNotificationService>();
    }
    _getIt.registerSingleton<PushNotificationService>(PushNotificationService.instance);
  }
}
