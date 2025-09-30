import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../models/privacy_preference.dart';
import '../environment.dart';

typedef TokenProvider = Future<String?> Function();

class PrivacyPreferencesService {
  PrivacyPreferencesService({
    Dio? dio,
    SharedPreferences? preferences,
    this.tokenProvider,
    String? baseUrl,
  })  : _dio = dio ??
            Dio(
              BaseOptions(
                baseUrl: baseUrl ?? apiUrl,
                connectTimeout: const Duration(seconds: 10),
                receiveTimeout: const Duration(seconds: 10),
              ),
            ),
        _preferences = preferences;

  static const _cacheKey = 'privacy.preferences.cache.v1';

  final Dio _dio;
  final SharedPreferences? _preferences;
  final TokenProvider? tokenProvider;

  Future<List<PrivacyPreference>> loadPreferences() async {
    try {
      final response = await _performRequest(() {
        return _dio.get<Map<String, dynamic>>('/privacy/preferences');
      });

      final data = response.data?['preferences'] as Map<String, dynamic>?;
      if (data == null) {
        throw StateError('Missing privacy preferences payload');
      }

      await _persistCache(data);
      return _mapFromJson(data);
    } on DioException catch (error) {
      debugPrint('PrivacyPreferencesService network error: ${error.message}');
      final cached = await _readCache();
      if (cached != null) {
        return _mapFromJson(cached);
      }
      rethrow;
    }
  }

  Future<List<PrivacyPreference>> updatePreferences(Map<String, bool> updates) async {
    final response = await _performRequest(() {
      return _dio.put<Map<String, dynamic>>(
        '/privacy/preferences',
        data: jsonEncode({'preferences': updates}),
        options: Options(contentType: Headers.jsonContentType),
      );
    });

    final data = response.data?['preferences'] as Map<String, dynamic>?;
    if (data == null) {
      throw StateError('Missing privacy preferences payload');
    }

    await _persistCache(data);
    return _mapFromJson(data);
  }

  Future<String> requestExport() async {
    final response = await _performRequest(() {
      return _dio.post<Map<String, dynamic>>('/privacy/export');
    });

    return response.data?['token'] as String? ?? '';
  }

  Future<String> requestDeletion() async {
    final response = await _performRequest(() {
      return _dio.delete<Map<String, dynamic>>('/privacy/account');
    });

    return response.data?['token'] as String? ?? '';
  }

  Future<Response<T>> _performRequest<T>(Future<Response<T>> Function() request) async {
    final token = tokenProvider != null ? await tokenProvider!() : null;
    if (token != null) {
      _dio.options.headers = {
        ..._dio.options.headers,
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      };
    } else {
      _dio.options.headers = {
        ..._dio.options.headers,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      };
    }

    return request();
  }

  Future<void> _persistCache(Map<String, dynamic> payload) async {
    if (_preferences != null) {
      await _preferences!.setString(_cacheKey, jsonEncode(payload));
    }
  }

  Future<Map<String, dynamic>?> _readCache() async {
    if (_preferences == null) {
      return null;
    }

    final cached = _preferences!.getString(_cacheKey);
    if (cached == null) {
      return null;
    }

    return jsonDecode(cached) as Map<String, dynamic>;
  }

  List<PrivacyPreference> _mapFromJson(Map<String, dynamic> payload) {
    return payload.entries
        .map((entry) => PrivacyPreference.fromJson(
            entry.key, Map<String, dynamic>.from(entry.value as Map)))
        .toList()
      ..sort((a, b) => a.key.compareTo(b.key));
  }
}
