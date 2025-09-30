import 'package:dio/dio.dart';

import '../auth/auth_session_manager.dart';
import '../logging/app_logger.dart';

class AuthRefreshInterceptor extends Interceptor {
  AuthRefreshInterceptor({
    required Dio client,
    required AuthSessionManager sessionManager,
    AppLogger? logger,
  })  : _client = client,
        _sessionManager = sessionManager,
        _logger = logger ?? AppLogger.instance;

  final Dio _client;
  final AuthSessionManager _sessionManager;
  final AppLogger _logger;

  @override
  Future<void> onError(DioException err, ErrorInterceptorHandler handler) async {
    final statusCode = err.response?.statusCode;
    final skipRefresh = err.requestOptions.extra['skipAuthRefresh'] == true;

    if (statusCode != 401 || skipRefresh) {
      handler.next(err);
      return;
    }

    _logger.warning('AuthRefreshInterceptor: Attempting token refresh for ${err.requestOptions.uri}');
    final result = await _sessionManager.refreshSession();
    if (result == null) {
      handler.next(err);
      return;
    }

    final requestOptions = err.requestOptions;
    requestOptions.headers['Authorization'] = 'Bearer ${result.accessToken}';
    requestOptions.extra['skipAuthRefresh'] = true;

    try {
      final response = await _client.fetch<dynamic>(requestOptions);
      handler.resolve(response);
    } on DioException catch (error, stackTrace) {
      _logger.error(
        'AuthRefreshInterceptor: Retried request failed',
        error: error,
        stackTrace: stackTrace,
      );
      handler.next(error);
    }
  }
}
