import 'dart:async';

import 'package:dio/dio.dart';

import 'app_logger.dart';

class HttpMetric {
  HttpMetric({
    required this.method,
    required this.path,
    required this.duration,
    required this.statusCode,
    this.error,
  });

  final String method;
  final String path;
  final Duration duration;
  final int statusCode;
  final DioException? error;
}

class HttpMetricsRecorder {
  HttpMetricsRecorder({AppLogger? logger})
      : _logger = logger ?? AppLogger.instance,
        _controller = StreamController<HttpMetric>.broadcast();

  final AppLogger _logger;
  final List<HttpMetric> _history = <HttpMetric>[];
  final StreamController<HttpMetric> _controller;

  Stream<HttpMetric> get stream => _controller.stream;

  List<HttpMetric> get history => List.unmodifiable(_history);

  void record(HttpMetric metric) {
    _history.add(metric);
    _controller.add(metric);
    final statusLabel = metric.statusCode == 0 ? 'ERR' : metric.statusCode.toString();
    final message =
        '${metric.method.toUpperCase()} ${metric.path} => $statusLabel in ${metric.duration.inMilliseconds}ms';
    if (metric.error != null) {
      _logger.error(
        message,
        error: metric.error,
        stackTrace: metric.error?.stackTrace,
      );
    } else {
      _logger.network(message);
    }
  }

  void clear() {
    _history.clear();
  }
}
