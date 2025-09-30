import 'package:dio/dio.dart';

import '../configuration/app_environment.dart';

class TimeoutInterceptor extends Interceptor {
  TimeoutInterceptor(ApiConfiguration configuration)
      : _connectTimeout = Duration(milliseconds: configuration.connectTimeoutMs),
        _receiveTimeout = Duration(milliseconds: configuration.receiveTimeoutMs);

  final Duration _connectTimeout;
  final Duration _receiveTimeout;

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    options.connectTimeout = _connectTimeout;
    options.receiveTimeout = _receiveTimeout;
    handler.next(options);
  }
}
