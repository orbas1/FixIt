import 'package:dio/dio.dart';
import 'package:fixit_user/services/http/telemetry_interceptor.dart';
import 'package:fixit_user/services/logging/http_metrics_recorder.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('TelemetryInterceptor', () {
    test('records successful response metrics', () async {
      final recorder = HttpMetricsRecorder();
      final dio = Dio();
      dio.interceptors.add(TelemetryInterceptor(metricsRecorder: recorder));
      dio.interceptors.add(
        InterceptorsWrapper(
          onRequest: (options, handler) {
            handler.resolve(
              Response<dynamic>(
                requestOptions: options,
                statusCode: 200,
                data: 'ok',
              ),
            );
          },
        ),
      );

      final response = await dio.get<dynamic>('/metrics');

      expect(response.statusCode, 200);
      expect(recorder.history, isNotEmpty);
      final metric = recorder.history.single;
      expect(metric.method, equals('GET'));
      expect(metric.path, equals('/metrics'));
      expect(metric.statusCode, equals(200));
      expect(metric.duration.inMilliseconds, greaterThanOrEqualTo(0));
    });

    test('records error metrics', () async {
      final recorder = HttpMetricsRecorder();
      final dio = Dio();
      dio.interceptors.add(TelemetryInterceptor(metricsRecorder: recorder));
      dio.interceptors.add(
        InterceptorsWrapper(
          onRequest: (options, handler) {
            handler.reject(
              DioException(
                requestOptions: options,
                response: Response<dynamic>(
                  requestOptions: options,
                  statusCode: 500,
                ),
                type: DioExceptionType.badResponse,
              ),
            );
          },
        ),
      );

      await expectLater(
        () => dio.post<dynamic>('/failure'),
        throwsA(isA<DioException>()),
      );

      expect(recorder.history, isNotEmpty);
      final metric = recorder.history.single;
      expect(metric.method, equals('POST'));
      expect(metric.path, equals('/failure'));
      expect(metric.statusCode, equals(500));
      expect(metric.error, isA<DioException>());
    });
  });
}
