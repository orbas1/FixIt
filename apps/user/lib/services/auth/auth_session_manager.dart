import 'dart:async';

import 'package:dio/dio.dart';

import '../environment.dart';
import '../logging/app_logger.dart';
import 'auth_token_store.dart';

class AuthSessionResult {
  const AuthSessionResult({
    required this.accessToken,
    this.refreshToken,
  });

  final String accessToken;
  final String? refreshToken;
}

class AuthSessionManager {
  AuthSessionManager({
    required Dio httpClient,
    required AuthTokenStore tokenStore,
    required EnvironmentStore environmentStore,
    AppLogger? logger,
  })  : _httpClient = httpClient,
        _tokenStore = tokenStore,
        _environmentStore = environmentStore,
        _logger = logger ?? AppLogger.instance;

  final Dio _httpClient;
  final AuthTokenStore _tokenStore;
  final EnvironmentStore _environmentStore;
  final AppLogger _logger;

  Completer<AuthSessionResult?>? _refreshCompleter;

  Future<AuthSessionResult?> refreshSession({bool force = false}) async {
    if (!force && _refreshCompleter != null) {
      return _refreshCompleter!.future;
    }

    final refreshToken = await _tokenStore.readRefreshToken();
    if (refreshToken == null || refreshToken.isEmpty) {
      _logger.warning('AuthSessionManager: Missing refresh token, clearing session');
      await _tokenStore.clear();
      return null;
    }

    final completer = Completer<AuthSessionResult?>();
    _refreshCompleter = completer;

    try {
      final response = await _httpClient.postUri<Map<String, dynamic>>(
        _environmentStore.refreshUri,
        data: <String, dynamic>{'refresh_token': refreshToken},
        options: Options(
          headers: _environmentStore.headers(),
          extra: const <String, Object?>{
            'skipAuthRefresh': true,
            'requiresAuth': false,
          },
        ),
      );

      final result = _parseTokens(response.data);
      if (result == null) {
        _logger.warning('AuthSessionManager: Refresh response missing tokens');
        await _tokenStore.clearRefreshToken();
        completer.complete(null);
        return completer.future;
      }

      await _tokenStore.writeTokens(
        accessToken: result.accessToken,
        refreshToken: result.refreshToken ?? refreshToken,
      );

      completer.complete(
        AuthSessionResult(
          accessToken: result.accessToken,
          refreshToken: result.refreshToken ?? refreshToken,
        ),
      );
    } on DioException catch (error, stackTrace) {
      _logger.error(
        'AuthSessionManager: Failed to refresh session',
        error: error,
        stackTrace: stackTrace,
      );
      await _tokenStore.clear();
      completer.complete(null);
    } catch (error, stackTrace) {
      _logger.error(
        'AuthSessionManager: Unexpected refresh error',
        error: error,
        stackTrace: stackTrace,
      );
      completer.complete(null);
    } finally {
      _refreshCompleter = null;
    }

    return completer.future;
  }

  Future<void> clearSession() async {
    await _tokenStore.clear();
  }

  AuthSessionResult? _parseTokens(Map<String, dynamic>? payload) {
    if (payload == null) {
      return null;
    }

    final normalized = _unwrapPayload(payload);
    if (normalized == null) {
      return null;
    }

    final accessToken = normalized['access_token'] as String? ??
        normalized['accessToken'] as String? ??
        normalized['token'] as String?;
    final refreshToken = normalized['refresh_token'] as String? ??
        normalized['refreshToken'] as String?;

    if (accessToken == null || accessToken.isEmpty) {
      return null;
    }

    return AuthSessionResult(accessToken: accessToken, refreshToken: refreshToken);
  }

  Map<String, dynamic>? _unwrapPayload(Map<String, dynamic> payload) {
    if (payload.containsKey('data') && payload['data'] is Map<String, dynamic>) {
      return Map<String, dynamic>.from(payload['data'] as Map<String, dynamic>);
    }

    if (payload.containsKey('result') && payload['result'] is Map<String, dynamic>) {
      return Map<String, dynamic>.from(payload['result'] as Map<String, dynamic>);
    }

    return payload;
  }
}
