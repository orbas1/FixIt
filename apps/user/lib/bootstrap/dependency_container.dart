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
      TimeoutInterceptor(environment.api),
      ConnectivityInterceptor(connectivity: connectivity),
      RetryInterceptor(
        connectivity: connectivity,
        logger: AppLogger.instance,
        dio: dio,
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
  }
}
