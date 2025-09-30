import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:hive/hive.dart';

import '../../models/escrow_model.dart';
import '../../models/escrow_payment_intent.dart';
import '../environment.dart';

typedef TokenResolver = Future<String?> Function();

class EscrowApiClient {
  EscrowApiClient({
    Dio? dio,
    HiveInterface? hive,
    this.tokenResolver,
    String? baseUrl,
  })  : _dio = dio ??
            Dio(
              BaseOptions(
                connectTimeout: const Duration(seconds: 12),
                receiveTimeout: const Duration(seconds: 12),
                baseUrl: baseUrl ?? apiUrl,
              ),
            ),
        _hive = hive ?? Hive;

  static const _cacheBox = 'escrow_cache_v1';

  final Dio _dio;
  final HiveInterface _hive;
  final TokenResolver? tokenResolver;

  Future<List<EscrowModel>> listEscrows({int page = 1}) async {
    try {
      final response = await _performAuthenticatedRequest(() {
        return _dio.get<Map<String, dynamic>>(
          '/escrows',
          queryParameters: {'page': page},
        );
      });

      final data = response.data?['data'] as List<dynamic>? ?? [];
      final escrows = data
          .map((item) => EscrowModel.fromJson(
              Map<String, dynamic>.from(item as Map<dynamic, dynamic>)))
          .toList();

      await _cacheEscrows(escrows);
      return escrows;
    } on DioException catch (error) {
      debugPrint('EscrowApiClient network error: ${error.message}');
      final cached = await _readCachedEscrows();
      if (cached.isNotEmpty) {
        return cached;
      }
      rethrow;
    }
  }

  Future<EscrowModel> fetchEscrow(int id) async {
    try {
      final response = await _performAuthenticatedRequest(() {
        return _dio.get<Map<String, dynamic>>('/escrows/$id');
      });

      final payload = response.data?['data'] as Map<dynamic, dynamic>?;
      if (payload == null) {
        throw StateError('Escrow payload missing');
      }

      final model =
          EscrowModel.fromJson(Map<String, dynamic>.from(payload));
      await _cacheEscrows([model]);
      return model;
    } on DioException catch (error) {
      debugPrint('EscrowApiClient fetch error: ${error.message}');
      final cached = await _readCachedEscrows();
      final match = cached.firstWhere(
        (entry) => entry.id == id,
        orElse: () => throw error,
      );
      return match;
    }
  }

  Future<EscrowModel> release(int id, double amount) async {
    final response = await _performAuthenticatedRequest(() {
      return _dio.post<Map<String, dynamic>>(
        '/escrows/$id/release',
        data: jsonEncode({'amount': amount}),
        options: Options(contentType: Headers.jsonContentType),
      );
    });

    final payload = response.data?['data'] as Map<dynamic, dynamic>?;
    if (payload == null) {
      throw StateError('Escrow release response invalid');
    }

    final model =
        EscrowModel.fromJson(Map<String, dynamic>.from(payload));
    await _cacheEscrows([model]);
    return model;
  }

  Future<EscrowModel> refund(int id, double amount) async {
    final response = await _performAuthenticatedRequest(() {
      return _dio.post<Map<String, dynamic>>(
        '/escrows/$id/refund',
        data: jsonEncode({'amount': amount}),
        options: Options(contentType: Headers.jsonContentType),
      );
    });

    final payload = response.data?['data'] as Map<dynamic, dynamic>?;
    if (payload == null) {
      throw StateError('Escrow refund response invalid');
    }

    final model =
        EscrowModel.fromJson(Map<String, dynamic>.from(payload));
    await _cacheEscrows([model]);
    return model;
  }

  Future<EscrowPaymentIntent> createPaymentIntent(
    int id, {
    Map<String, dynamic>? context,
  }) async {
    final response = await _performAuthenticatedRequest(() {
      return _dio.post<Map<String, dynamic>>(
        '/escrows/$id/payment-intent',
        data: jsonEncode({'context': context}),
        options: Options(contentType: Headers.jsonContentType),
      );
    });

    final payload = response.data?['data'] as Map<dynamic, dynamic>?;
    if (payload == null) {
      throw StateError('Escrow payment intent response missing.');
    }

    return EscrowPaymentIntent.fromJson(
      Map<String, dynamic>.from(payload),
    );
  }

  Future<Response<T>> _performAuthenticatedRequest<T>(
      Future<Response<T>> Function() request) async {
    if (tokenResolver != null) {
      final token = await tokenResolver!.call();
      if (token != null) {
        _dio.options.headers.addAll(headersToken(token) ?? {});
      }
    } else {
      _dio.options.headers.addAll(headers ?? {});
    }

    return request();
  }

  Future<void> _cacheEscrows(List<EscrowModel> escrows) async {
    if (!_hive.isBoxOpen(_cacheBox)) {
      await _hive.openBox<String>(_cacheBox);
    }
    final box = _hive.box<String>(_cacheBox);

    final entries = escrows
        .map((escrow) => EscrowCacheEntry(
              payload: escrow,
              lastUpdated: DateTime.now(),
            ))
        .toList();

    final cached = box.get('escrows');
    final existing = cached != null
        ? EscrowCacheEntry.decodeList(cached)
        : <EscrowCacheEntry>[];

    final merged = <String, EscrowCacheEntry>{
      for (final entry in existing) entry.payload.cacheKey(): entry,
      for (final entry in entries) entry.payload.cacheKey(): entry,
    };

    await box.put('escrows', EscrowCacheEntry.encodeList(merged.values.toList()));
  }

  Future<List<EscrowModel>> _readCachedEscrows() async {
    if (!_hive.isBoxOpen(_cacheBox)) {
      await _hive.openBox<String>(_cacheBox);
    }

    final box = _hive.box<String>(_cacheBox);
    final cached = box.get('escrows');
    if (cached == null) {
      return [];
    }

    return EscrowCacheEntry.decodeList(cached)
        .map((entry) => entry.payload)
        .toList();
  }
}
