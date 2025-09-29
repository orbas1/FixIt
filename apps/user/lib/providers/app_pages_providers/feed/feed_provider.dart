import 'dart:convert';

import 'package:fixit_user/config.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

class FeedProvider extends ChangeNotifier {
  static const _cacheKey = 'feed_v1_cache';
  static const _perPage = 10;

  FeedProvider();

  List<FeedJobModel> jobs = [];
  bool isLoading = false;
  bool hasMore = true;
  DateTime? lastSyncedAt;

  int _currentPage = 1;
  Map<String, dynamic> _filters = {};

  Future<void> bootstrap({bool forceRefresh = false, Map<String, dynamic>? filters}) async {
    _filters = filters ?? {};

    if (!forceRefresh) {
      await _loadFromCache();
    }

    await fetchNext(reset: true, filters: filters);
  }

  Future<void> fetchNext({bool reset = false, Map<String, dynamic>? filters}) async {
    if (isLoading) return;
    if (!hasMore && !reset) return;

    if (filters != null) {
      _filters = filters;
    }

    if (reset) {
      _currentPage = 1;
      hasMore = true;
      jobs = [];
      notifyListeners();
    }

    isLoading = true;
    notifyListeners();

    final query = _buildQuery();
    final endpoint = '${api.feedServiceRequests}?$query';

    try {
      final response = await apiServices.getApi(endpoint, [],
          isToken: true, isData: true, isMessage: false);

      if (response.isSuccess ?? false) {
        final payload = Map<String, dynamic>.from(response.data as Map);
        final List<dynamic> rawItems = payload['data'] as List<dynamic>? ?? [];
        final newJobs = rawItems
            .map((e) => FeedJobModel.fromJson(Map<String, dynamic>.from(e)))
            .toList();

        if (reset) {
          jobs = newJobs;
        } else {
          jobs = [...jobs, ...newJobs];
        }

        final meta = Map<String, dynamic>.from(payload['meta'] ?? {});
        final currentPage = meta['current_page'] is int
            ? meta['current_page'] as int
            : _currentPage;
        final lastPage = meta['last_page'] is int ? meta['last_page'] as int : currentPage;
        hasMore = currentPage < lastPage;
        _currentPage = currentPage + 1;
        lastSyncedAt = DateTime.now();
        await _persistCache(meta);
      } else {
        await _loadFromCache();
      }
    } catch (error, stackTrace) {
      debugPrint('FeedProvider fetch error: $error');
      debugPrintStack(stackTrace: stackTrace);
      await _loadFromCache();
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> refresh() async {
    await fetchNext(reset: true);
  }

  String _buildQuery() {
    final filters = <String, String>{
      'page': _currentPage.toString(),
      'per_page': _perPage.toString(),
    };

    _filters.forEach((key, value) {
      if (value == null) return;
      filters[key] = value.toString();
    });

    return filters.entries
        .map((entry) => '${entry.key}=${Uri.encodeComponent(entry.value)}')
        .join('&');
  }

  Future<void> _persistCache(Map<String, dynamic> meta) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final snapshot = {
        'items': jobs.take(25).map((e) => e.toJson()).toList(),
        'meta': meta,
        'filters': _filters,
        'hasMore': hasMore,
        'savedAt': DateTime.now().toIso8601String(),
      };
      await prefs.setString(_cacheKey, jsonEncode(snapshot));
    } catch (error) {
      debugPrint('FeedProvider cache write failed: $error');
    }
  }

  Future<void> _loadFromCache() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final raw = prefs.getString(_cacheKey);
      if (raw == null) return;

      final Map<String, dynamic> cached = jsonDecode(raw) as Map<String, dynamic>;
      final List<dynamic> items = cached['items'] as List<dynamic>? ?? [];
      jobs = items
          .map((e) => FeedJobModel.fromJson(Map<String, dynamic>.from(e)))
          .toList();
      hasMore = cached['hasMore'] as bool? ?? true;
      final savedAt = cached['savedAt'] as String?;
      if (savedAt != null) {
        lastSyncedAt = DateTime.tryParse(savedAt);
      }
      final meta = cached['meta'] as Map<String, dynamic>?;
      if (meta != null && meta['current_page'] != null) {
        _currentPage = (meta['current_page'] as int) + 1;
      }
      final filters = cached['filters'] as Map<String, dynamic>?;
      if (filters != null) {
        _filters = filters;
      }
      notifyListeners();
    } catch (error) {
      debugPrint('FeedProvider cache read failed: $error');
    }
  }
}
