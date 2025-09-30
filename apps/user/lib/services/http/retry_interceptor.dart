import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:dio/dio.dart';

import '../logging/app_logger.dart';

class RetryInterceptor extends Interceptor {
  RetryInterceptor({
    required Connectivity connectivity,
    required AppLogger logger,
    required Dio dio,
    this.maxRetries = 2,
  })  : _connectivity = connectivity,
        _logger = logger,
        _dio = dio;

  final Connectivity _connectivity;
  final AppLogger _logger;
  final Dio _dio;
  final int maxRetries;

  @override
  Future<void> onError(DioException err, ErrorInterceptorHandler handler) async {
    if (!_shouldRetry(err)) {
      handler.next(err);
      return;
    }

    for (var attempt = 1; attempt <= maxRetries; attempt++) {
      final delay = Duration(milliseconds: 200 * attempt * attempt);
      await Future<void>.delayed(delay);
      final connectivityResults = await _connectivity.checkConnectivity();
      if (connectivityResults.contains(ConnectivityResult.none)) {
        continue;
      }
      try {
        _logger.warning('Retrying request attempt $attempt for ${err.requestOptions.uri}');
        final response = await _dio.fetch<dynamic>(err.requestOptions);
        handler.resolve(response);
        return;
      } on DioException catch (retryError, stackTrace) {
        _logger.error('Retry attempt $attempt failed', error: retryError, stackTrace: stackTrace);
        if (attempt == maxRetries) {
          handler.next(retryError);
          return;
        }
      }
    }

    handler.next(err);
  }

  bool _shouldRetry(DioException error) {
    return error.type == DioExceptionType.connectionError ||
        error.type == DioExceptionType.receiveTimeout ||
        error.type == DioExceptionType.sendTimeout;
  }
}
