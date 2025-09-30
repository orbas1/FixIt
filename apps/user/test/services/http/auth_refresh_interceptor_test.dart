import 'package:dio/dio.dart';
import 'package:fixit_user/services/auth/auth_session_manager.dart';
import 'package:fixit_user/services/http/auth_refresh_interceptor.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

class MockAuthSessionManager extends Mock implements AuthSessionManager {}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('AuthRefreshInterceptor', () {
    late Dio dio;
    late MockAuthSessionManager sessionManager;

    setUp(() {
      dio = Dio();
      sessionManager = MockAuthSessionManager();
      dio.interceptors.add(AuthRefreshInterceptor(client: dio, sessionManager: sessionManager));
    });

    test('refreshes token and retries original request', () async {
      when(() => sessionManager.refreshSession()).thenAnswer(
        (_) async => const AuthSessionResult(accessToken: 'new-token', refreshToken: 'refresh-token'),
      );

      final capturedHeaders = <Map<String, dynamic>>[];

      dio.interceptors.add(
        InterceptorsWrapper(
          onRequest: (options, handler) {
            final skip = options.extra['skipAuthRefresh'] == true;
            if (skip) {
              capturedHeaders.add(Map<String, dynamic>.from(options.headers));
              handler.resolve(
                Response<dynamic>(
                  requestOptions: options,
                  statusCode: 200,
                  data: 'ok',
                ),
              );
            } else {
              handler.reject(
                DioException(
                  requestOptions: options,
                  response: Response<dynamic>(
                    requestOptions: options,
                    statusCode: 401,
                  ),
                  type: DioExceptionType.badResponse,
                ),
              );
            }
          },
        ),
      );

      final response = await dio.get<dynamic>(
        '/secure',
        options: Options(headers: <String, dynamic>{'Authorization': 'Bearer old-token'}),
      );

      expect(response.statusCode, 200);
      expect(capturedHeaders.single['Authorization'], 'Bearer new-token');
      verify(() => sessionManager.refreshSession()).called(1);
    });

    test('propagates error when refresh fails', () async {
      when(() => sessionManager.refreshSession()).thenAnswer((_) async => null);

      dio.interceptors.add(
        InterceptorsWrapper(
          onRequest: (options, handler) {
            handler.reject(
              DioException(
                requestOptions: options,
                response: Response<dynamic>(
                  requestOptions: options,
                  statusCode: 401,
                ),
                type: DioExceptionType.badResponse,
              ),
            );
          },
        ),
      );

      expect(
        () => dio.get<dynamic>('/secure'),
        throwsA(isA<DioException>().having((error) => error.response?.statusCode, 'status', 401)),
      );
      verify(() => sessionManager.refreshSession()).called(1);
    });
  });
}
