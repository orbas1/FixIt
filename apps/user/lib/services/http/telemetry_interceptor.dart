import 'package:dio/dio.dart';

import '../logging/app_logger.dart';
import '../logging/http_metrics_recorder.dart';

class TelemetryInterceptor extends Interceptor {
  TelemetryInterceptor({
    required HttpMetricsRecorder metricsRecorder,
    AppLogger? logger,
  })  : _metricsRecorder = metricsRecorder,
        _logger = logger ?? AppLogger.instance;

  static const String _startKey = '__requestStart';

  final HttpMetricsRecorder _metricsRecorder;
  final AppLogger _logger;

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    options.extra[_startKey] = DateTime.now();
    handler.next(options);
  }

  @override
  void onResponse(Response response, ResponseInterceptorHandler handler) {
    _recordMetric(response.requestOptions, statusCode: response.statusCode ?? 0);
    handler.next(response);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    final statusCode = err.response?.statusCode ?? 0;
    _recordMetric(err.requestOptions, statusCode: statusCode, error: err);
    handler.next(err);
  }

  void _recordMetric(RequestOptions options, {required int statusCode, DioException? error}) {
    final start = options.extra.remove(_startKey);
    if (start is! DateTime) {
      _logger.warning('TelemetryInterceptor: Missing request start time for ${options.uri}');
      return;
    }

    final duration = DateTime.now().difference(start);
    _metricsRecorder.record(
      HttpMetric(
        method: options.method,
        path: options.uri.path,
        duration: duration,
        statusCode: statusCode,
        error: error,
      ),
    );
  }
}
