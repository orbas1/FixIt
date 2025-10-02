import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../models/data_governance_asset.dart';
import '../environment.dart';

typedef TokenProvider = Future<String?> Function();

class DataGovernanceService {
  DataGovernanceService({
    Dio? dio,
    SharedPreferences? preferences,
    this.tokenProvider,
    String? baseUrl,
  })  : _dio = dio ??
            Dio(
              BaseOptions(
                baseUrl: baseUrl ?? '$apiUrl/governance',
                connectTimeout: const Duration(seconds: 10),
                receiveTimeout: const Duration(seconds: 10),
              ),
            ),
        _preferences = preferences;

  static const _cacheKey = 'data.governance.assets.cache.v1';

  final Dio _dio;
  final SharedPreferences? _preferences;
  final TokenProvider? tokenProvider;

  Future<List<DataGovernanceAsset>> fetchAssets() async {
    try {
      final response = await _performRequest(() {
        return _dio.get<Map<String, dynamic>>('/data-assets');
      });

      final data = response.data?['data'] as List<dynamic>?;
      if (data == null) {
        throw StateError('Missing data asset payload');
      }

      await _persistCache({'items': data});
      return data
          .map((item) => DataGovernanceAsset.fromJson(
              Map<String, dynamic>.from(item as Map)))
          .toList();
    } on DioException catch (error) {
      debugPrint('DataGovernanceService network error: ${error.message}');
      final cached = await _readCache();
      if (cached != null) {
        final cachedItems = cached['items'] as List<dynamic>? ?? const [];
        return cachedItems
            .map((item) => DataGovernanceAsset.fromJson(
                Map<String, dynamic>.from(item as Map)))
            .toList();
      }
      rethrow;
    }
  }

  Future<DataGovernanceAsset> ensureDpia(
    int assetId, {
    List<Map<String, dynamic>>? mitigationActions,
    List<Map<String, dynamic>>? residualRisks,
  }) async {
    final response = await _performRequest(() {
      return _dio.post<Map<String, dynamic>>(
        '/data-assets/$assetId/dpia/ensure',
        data: jsonEncode({
          if (mitigationActions != null) 'mitigation_actions': mitigationActions,
          if (residualRisks != null) 'residual_risks': residualRisks,
        }),
        options: Options(contentType: Headers.jsonContentType),
      );
    });

    final json = response.data?['data'] as Map<String, dynamic>?;
    if (json == null) {
      throw StateError('Missing data asset payload');
    }

    return DataGovernanceAsset.fromJson(json);
  }

  Future<Response<T>> _performRequest<T>(Future<Response<T>> Function() request) async {
    final token = tokenProvider != null ? await tokenProvider!() : null;
    final headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
    _dio.options.headers = headers;
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
}
