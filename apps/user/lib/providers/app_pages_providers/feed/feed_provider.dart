import 'dart:convert';

import 'package:fixit_user/config.dart';
import 'package:fixit_user/models/feed_job_model.dart';
import 'package:fixit_user/services/feed/feed_api_client.dart';
import 'package:flutter/foundation.dart';
import 'package:hive/hive.dart';
import 'package:shared_preferences/shared_preferences.dart';

class FeedProvider extends ChangeNotifier {
  static const _cacheBoxName = 'feed_cache_box';
  static const _cacheKey = 'feed_v2_cache';
  static const _staleAfter = Duration(minutes: 15);
  static const _perPage = 10;
  static const _maxCachedItems = 200;
  static const _lruIndexKey = 'feed_v2_cache:lru';
  static const _prefetchThreshold = 3;

  FeedProvider();

  List<FeedJobModel> jobs = [];
  bool isLoading = false;
  bool hasMore = true;
  DateTime? lastSyncedAt;
  bool isStale = false;
  bool lastSyncFailed = false;

  int _currentPage = 1;
  Map<String, dynamic> _filters = {};
  Box<dynamic>? _cacheBox;
  late final FeedApiClient _client = FeedApiClient(
    baseUrl: api.feedServiceRequests,
    tokenResolver: () async {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getString(session.accessToken);
    },
  );

  Future<void> bootstrap({bool forceRefresh = false, Map<String, dynamic>? filters}) async {
    _filters = filters ?? {};

    if (!forceRefresh) {
      await _ensureCacheBox();
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

    try {
      final query = _buildQuery();
      final response = await _client.fetchJobs(query);
      final newJobs = response.jobs;

      _mergeJobs(newJobs, reset: reset);

      final meta = response.meta;
      final currentPage = meta['current_page'] is int ? meta['current_page'] as int : _currentPage;
      final lastPage = meta['last_page'] is int ? meta['last_page'] as int : currentPage;
      hasMore = currentPage < lastPage;
      _currentPage = currentPage + 1;
      lastSyncedAt = DateTime.now();
      await _persistCache(meta);
      lastSyncFailed = false;
    } catch (error, stackTrace) {
      debugPrint('FeedProvider fetch error: $error');
      debugPrintStack(stackTrace: stackTrace);
      await _loadFromCache();
      lastSyncFailed = true;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> refresh() async {
    await fetchNext(reset: true);
  }

  FeedQuery _buildQuery() {
    return FeedQuery(
      page: _currentPage,
      perPage: _perPage,
      filters: Map<String, dynamic>.from(_filters),
    );
  }

  Future<void> _persistCache(Map<String, dynamic> meta) async {
    try {
      final box = await _ensureCacheBox();
      final snapshot = {
        'items': jobs.take(_maxCachedItems).map((e) => e.toJson()).toList(),
        'meta': meta,
        'filters': _filters,
        'hasMore': hasMore,
        'savedAt': DateTime.now().toIso8601String(),
      };
      await box.put(_cacheStorageKey(), jsonEncode(snapshot));
      await _updateLru(box, _cacheStorageKey());
    } catch (error) {
      debugPrint('FeedProvider cache write failed: $error');
    }
  }

  Future<void> _loadFromCache() async {
    try {
      final box = await _ensureCacheBox();
      final raw = box.get(_cacheStorageKey());
      if (raw == null) return;

      final Map<String, dynamic> cached = jsonDecode(raw as String) as Map<String, dynamic>;
      final List<dynamic> items = cached['items'] as List<dynamic>? ?? [];
      jobs = items
          .map((e) => FeedJobModel.fromJson(Map<String, dynamic>.from(e)))
          .toList();
      hasMore = cached['hasMore'] as bool? ?? true;
      final savedAt = cached['savedAt'] as String?;
      bool isStale = false;
      if (savedAt != null) {
        lastSyncedAt = DateTime.tryParse(savedAt);
        isStale = lastSyncedAt != null && DateTime.now().difference(lastSyncedAt!) > _staleAfter;
      }
      this.isStale = isStale;
      final meta = cached['meta'] as Map<String, dynamic>?;
      if (meta != null && meta['current_page'] != null) {
        _currentPage = (meta['current_page'] as int) + 1;
      }
      final filters = cached['filters'] as Map<String, dynamic>?;
      if (filters != null && filters.isNotEmpty) {
        _filters = filters;
      }

      if (isStale) {
        hasMore = true;
      }

      notifyListeners();
    } catch (error) {
      debugPrint('FeedProvider cache read failed: $error');
    }
  }

  void prefetchIfNeeded(int visibleIndex) {
    if (!hasMore || isLoading) return;
    final remaining = jobs.length - visibleIndex - 1;
    if (remaining <= _prefetchThreshold) {
      fetchNext();
    }
  }

  void _mergeJobs(List<FeedJobModel> newJobs, {required bool reset}) {
    final Map<int, FeedJobModel> unique = <int, FeedJobModel>{};

    if (!reset) {
      for (final job in jobs) {
        if (job.id != null) {
          unique[job.id!] = job;
        }
      }
    }

    for (final job in newJobs) {
      if (job.id != null) {
        unique[job.id!] = job;
      }
    }

    jobs = unique.values.take(_maxCachedItems).toList();
  }

  Future<void> _updateLru(Box<dynamic> box, String key) async {
    final entries = (box.get(_lruIndexKey) as List?)?.cast<String>() ?? <String>[];
    entries.remove(key);
    entries.insert(0, key);

    if (entries.length > _maxCachedItems) {
      final overflow = entries.sublist(_maxCachedItems);
      for (final staleKey in overflow) {
        await box.delete(staleKey);
      }
      entries.removeRange(_maxCachedItems, entries.length);
    }

    await box.put(_lruIndexKey, entries);
  }

  Future<Box<dynamic>> _ensureCacheBox() async {
    if (_cacheBox != null && _cacheBox!.isOpen) {
      return _cacheBox!;
    }

    _cacheBox = await Hive.openBox<dynamic>(_cacheBoxName);
    return _cacheBox!;
  }

  @override
  void dispose() {
    if (_cacheBox?.isOpen ?? false) {
      _cacheBox?.close();
    }
    super.dispose();
  }

  String _cacheStorageKey() {
    final signature = FeedQuery(page: 1, perPage: _perPage, filters: Map<String, dynamic>.from(_filters)).signature();
    return '$_cacheKey:$signature';
  }
}
