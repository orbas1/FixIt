import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:hive/hive.dart';

import '../../models/dispute_model.dart';
import '../environment.dart';

class DisputeApiClient {
  DisputeApiClient({
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

  final Dio _dio;
  final HiveInterface _hive;
  final Future<String?> Function()? tokenResolver;

  static const _boxName = 'dispute_cache_v1';

  Future<List<DisputeModel>> listDisputes({int page = 1}) async {
    try {
      final response = await _perform(() {
        return _dio.get<Map<String, dynamic>>(
          '/disputes',
          queryParameters: {'page': page},
        );
      });

      final data = response.data?['data'] as List<dynamic>? ?? [];
      final disputes = data
          .map((item) => DisputeModel.fromJson(
              Map<String, dynamic>.from(item as Map<dynamic, dynamic>)))
          .toList();

      await _cache(disputes);
      return disputes;
    } on DioException catch (error) {
      debugPrint('DisputeApiClient list error: ${error.message}');
      final cached = await _readCache();
      if (cached.isNotEmpty) {
        return cached;
      }
      rethrow;
    }
  }

  Future<DisputeModel> getDispute(int id) async {
    try {
      final response = await _perform(() {
        return _dio.get<Map<String, dynamic>>('/disputes/$id');
      });

      final payload = response.data?['data'] as Map<dynamic, dynamic>?;
      if (payload == null) {
        throw StateError('Dispute payload missing');
      }
      final dispute =
          DisputeModel.fromJson(Map<String, dynamic>.from(payload));
      await _cache([dispute]);
      return dispute;
    } on DioException catch (error) {
      debugPrint('DisputeApiClient fetch error: ${error.message}');
      final cached = await _readCache();
      final match = cached.firstWhere(
        (item) => item.id == id,
        orElse: () => throw error,
      );
      return match;
    }
  }

  Future<DisputeModel> postMessage(
    int id,
    String body, {
    String visibility = 'parties',
    List<dynamic>? attachments,
  }) async {
    final response = await _perform(() {
      return _dio.post<Map<String, dynamic>>(
        '/disputes/$id/message',
        data: jsonEncode({
          'body': body,
          'visibility': visibility,
          if (attachments != null) 'attachments': attachments,
        }),
        options: Options(contentType: Headers.jsonContentType),
      );
    });

    final payload = response.data?['data'] as Map<dynamic, dynamic>?;
    if (payload == null) {
      throw StateError('Dispute message response invalid');
    }

    final dispute =
        DisputeModel.fromJson(Map<String, dynamic>.from(payload));
    await _cache([dispute]);
    return dispute;
  }

  Future<DisputeModel> advanceStage(
    int id,
    String targetStage, {
    String? note,
  }) async {
    final response = await _perform(() {
      return _dio.post<Map<String, dynamic>>(
        '/disputes/$id/advance',
        data: jsonEncode({
          'stage': targetStage,
          if (note != null) 'note': note,
        }),
        options: Options(contentType: Headers.jsonContentType),
      );
    });

    final payload = response.data?['data'] as Map<dynamic, dynamic>?;
    if (payload == null) {
      throw StateError('Dispute advance response invalid');
    }

    final dispute =
        DisputeModel.fromJson(Map<String, dynamic>.from(payload));
    await _cache([dispute]);
    return dispute;
  }

  Future<DisputeModel> settle(
    int id, {
    required double releaseAmount,
    required double refundAmount,
    Map<String, dynamic>? resolutionDetails,
  }) async {
    final response = await _perform(() {
      return _dio.post<Map<String, dynamic>>(
        '/disputes/$id/settle',
        data: jsonEncode({
          'release_amount': releaseAmount,
          'refund_amount': refundAmount,
          if (resolutionDetails != null) 'resolution': resolutionDetails,
        }),
        options: Options(contentType: Headers.jsonContentType),
      );
    });

    final payload = response.data?['data'] as Map<dynamic, dynamic>?;
    if (payload == null) {
      throw StateError('Dispute settle response invalid');
    }

    final dispute =
        DisputeModel.fromJson(Map<String, dynamic>.from(payload));
    await _cache([dispute]);
    return dispute;
  }

  Future<Response<T>> _perform<T>(
      Future<Response<T>> Function() operation) async {
    if (tokenResolver != null) {
      final token = await tokenResolver!.call();
      if (token != null) {
        _dio.options.headers.addAll(headersToken(token) ?? {});
      }
    } else {
      _dio.options.headers.addAll(headers ?? {});
    }

    return operation();
  }

  Future<void> _cache(List<DisputeModel> disputes) async {
    if (!_hive.isBoxOpen(_boxName)) {
      await _hive.openBox<String>(_boxName);
    }
    final box = _hive.box<String>(_boxName);

    final entries = disputes
        .map((dispute) => DisputeCacheEntry(
              dispute: dispute,
              cachedAt: DateTime.now(),
            ))
        .toList();

    final cached = box.get('disputes');
    final existing = cached != null
        ? DisputeCacheEntry.decodeList(cached)
        : <DisputeCacheEntry>[];

    final merged = <int, DisputeCacheEntry>{
      for (final entry in existing) entry.dispute.id: entry,
      for (final entry in entries) entry.dispute.id: entry,
    };

    await box.put(
      'disputes',
      DisputeCacheEntry.encodeList(merged.values.toList()),
    );
  }

  Future<List<DisputeModel>> _readCache() async {
    if (!_hive.isBoxOpen(_boxName)) {
      await _hive.openBox<String>(_boxName);
    }

    final box = _hive.box<String>(_boxName);
    final cached = box.get('disputes');
    if (cached == null) {
      return [];
    }

    return DisputeCacheEntry.decodeList(cached)
        .map((entry) => entry.dispute)
        .toList();
  }
}
