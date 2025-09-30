import 'dart:convert';

import 'package:fixit_user/common/session.dart';
import 'package:fixit_user/models/feed_job_model.dart';
import 'package:fixit_user/services/api_methods.dart';
import 'package:fixit_user/services/api_service.dart';
import 'package:fixit_user/services/error/exceptions.dart';
import 'package:fixit_user/services/feed/feed_api_client.dart';
import 'package:flutter/foundation.dart';
import 'package:get_it/get_it.dart';
import 'package:hive/hive.dart';
import 'package:shared_preferences/shared_preferences.dart';

class FeedJobRepository {
  FeedJobRepository({
    FeedApiClient? client,
    ApiServices? apiServices,
    ApiMethods? apiMethods,
    HiveInterface? hive,
  })  : _apiMethods = apiMethods ?? ApiMethods(),
        _apiServices = apiServices ?? GetIt.I<ApiServices>(),
        _hive = hive ?? Hive,
        _client = client ??
            FeedApiClient(
              baseUrl: (apiMethods ?? ApiMethods()).feedServiceRequests,
              tokenResolver: () async {
                final prefs = await SharedPreferences.getInstance();
                return prefs.getString(Session().accessToken);
              },
            );

  static const _cacheBox = 'feed_job_detail_box';
  static const _cacheTtl = Duration(minutes: 30);

  final FeedApiClient _client;
  final ApiServices _apiServices;
  final ApiMethods _apiMethods;
  final HiveInterface _hive;

  Future<FeedJobModel> fetchJobDetail(int id) async {
    final cached = await _readFromCache(id);
    if (cached != null) {
      return cached;
    }

    final job = await _client.fetchJobDetail(id);
    await _writeToCache(job);
    return job;
  }

  Future<FeedJobModel> refreshJobDetail(int id) async {
    final job = await _client.fetchJobDetail(id);
    await _writeToCache(job, overwrite: true);
    return job;
  }

  Future<void> submitBid({
    required int jobId,
    required double amount,
    String? message,
    int? durationDays,
  }) async {
    final payload = <String, dynamic>{
      'service_request_id': jobId,
      'amount': amount,
      if (message != null && message.trim().isNotEmpty) 'message': message.trim(),
      if (durationDays != null) 'duration_days': durationDays,
    };

    final response = await _apiServices.postApi(
      _apiMethods.bid,
      payload,
      isToken: true,
      isData: true,
    );

    if (response.isSuccess != true) {
      throw RemoteException(
        statusCode: 400,
        message: response.message,
      );
    }
  }

  Future<bool> toggleBookmark({required int jobId, required bool shouldBookmark}) async {
    final endpoint = shouldBookmark
        ? '${_apiMethods.feedServiceRequests}/$jobId/bookmark'
        : '${_apiMethods.feedServiceRequests}/$jobId/bookmark';

    final response = await _apiServices.postApi(
      endpoint,
      {'bookmark': shouldBookmark},
      isToken: true,
      isData: true,
    );

    if (response.isSuccess != true) {
      throw RemoteException(statusCode: 400, message: response.message);
    }
    return shouldBookmark;
  }

  Future<void> _writeToCache(FeedJobModel job, {bool overwrite = false}) async {
    try {
      final box = await _ensureBox();
      final snapshot = {
        'savedAt': DateTime.now().toIso8601String(),
        'payload': job.toJson(),
      };
      if (overwrite || !box.containsKey(job.id.toString())) {
        await box.put(job.id.toString(), jsonEncode(snapshot));
      }
    } catch (error, stackTrace) {
      debugPrint('FeedJobRepository cache write failed: $error');
      debugPrintStack(stackTrace: stackTrace);
    }
  }

  Future<FeedJobModel?> _readFromCache(int id) async {
    try {
      final box = await _ensureBox();
      final raw = box.get(id.toString());
      if (raw == null) return null;
      final decoded = jsonDecode(raw as String) as Map<String, dynamic>;
      final savedAtRaw = decoded['savedAt'] as String?;
      final savedAt = savedAtRaw != null ? DateTime.tryParse(savedAtRaw) : null;
      if (savedAt != null && DateTime.now().difference(savedAt) > _cacheTtl) {
        await box.delete(id.toString());
        return null;
      }
      final payload = decoded['payload'];
      if (payload is Map<String, dynamic>) {
        return FeedJobModel.fromJson(Map<String, dynamic>.from(payload));
      }
    } catch (error, stackTrace) {
      debugPrint('FeedJobRepository cache read failed: $error');
      debugPrintStack(stackTrace: stackTrace);
    }
    return null;
  }

  Future<Box<dynamic>> _ensureBox() async {
    if (_hive.isBoxOpen(_cacheBox)) {
      return _hive.box<dynamic>(_cacheBox);
    }
    return _hive.openBox<dynamic>(_cacheBox);
  }
}
