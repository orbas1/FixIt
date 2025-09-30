import 'dart:async';
import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:hive/hive.dart';
import 'package:path_provider/path_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:workmanager/workmanager.dart';

import '../../common/session.dart';
import '../../models/feed_job_model.dart';
import '../../services/api_methods.dart';
import '../../services/environment.dart';
import '../../services/configuration/app_environment.dart';
import '../feed/feed_api_client.dart';

const String backgroundFeedRefreshTask = 'fixit.background.feed.refresh';
const String _cacheBoxName = 'feed_cache_box';
const String _cacheKeyPrefix = 'feed_v2_cache';
const String _lruIndexKey = 'feed_v2_cache:lru';
const int _maxCachedJobs = 200;

class BackgroundSyncScheduler {
  BackgroundSyncScheduler._();

  static final BackgroundSyncScheduler instance = BackgroundSyncScheduler._();

  Future<void> initialize() async {
    await Workmanager().initialize(backgroundSyncDispatcher, isInDebugMode: kDebugMode);
  }

  Future<void> scheduleFeedRefresh() async {
    await Workmanager().cancelByUniqueName(backgroundFeedRefreshTask);
    await Workmanager().registerPeriodicTask(
      backgroundFeedRefreshTask,
      backgroundFeedRefreshTask,
      frequency: const Duration(minutes: 30),
      initialDelay: const Duration(minutes: 10),
      constraints: Constraints(
        networkType: NetworkType.connected,
        requiresBatteryNotLow: true,
      ),
      backoffPolicy: BackoffPolicy.linear,
      backoffPolicyDelay: const Duration(minutes: 10),
    );
  }
}

@pragma('vm:entry-point')
void backgroundSyncDispatcher() {
  Workmanager().executeTask((taskName, inputData) async {
    WidgetsFlutterBinding.ensureInitialized();

    final stopwatch = Stopwatch()..start();

    try {
      final documentsDir = await getApplicationDocumentsDirectory();
      Hive.init(documentsDir.path);

      final preferences = await SharedPreferences.getInstance();
      final environment = await AppEnvironment.load();

      await EnvironmentStore.instance.configure(
        environment: environment,
        preferences: preferences,
      );

      final token = preferences.getString(Session().accessToken);
      final headers = headersToken(token) ?? <String, String>{};
      headers['Accept-Encoding'] = 'gzip, deflate, br';

      final dio = Dio(
        BaseOptions(
          baseUrl: environment.api.baseUrl,
          connectTimeout: Duration(milliseconds: environment.api.connectTimeoutMs),
          receiveTimeout: Duration(milliseconds: environment.api.receiveTimeoutMs),
          headers: headers,
        ),
      );

      final apiMethods = ApiMethods();
      final client = FeedApiClient(
        baseUrl: apiMethods.feedServiceRequests,
        dio: dio,
        tokenResolver: () async => token,
      );

      final query = FeedQuery(page: 1, perPage: 10, filters: inputData ?? {});

      final response = await Future.any([
        client.fetchJobs(query),
        Future<FeedResponse>.delayed(
          const Duration(seconds: 9),
          () => throw TimeoutException('Feed refresh exceeded 9s budget'),
        ),
      ]);

      final box = await _openCacheBox();
      await _persistSnapshot(box, response, query);
      await box.close();

      return true;
    } catch (error, stackTrace) {
      debugPrint('Background feed refresh failed: $error');
      debugPrintStack(stackTrace: stackTrace);
      return false;
    } finally {
      stopwatch.stop();
    }
  });
}

Future<Box<dynamic>> _openCacheBox() async {
  if (Hive.isBoxOpen(_cacheBoxName)) {
    return Hive.box<dynamic>(_cacheBoxName);
  }
  return Hive.openBox<dynamic>(_cacheBoxName);
}

Future<void> _persistSnapshot(
  Box<dynamic> box,
  FeedResponse response,
  FeedQuery query,
) async {
  final jobsJson = response.jobs.take(_maxCachedJobs).map((job) => job.toJson()).toList();
  final currentPage = response.meta['current_page'] is int
      ? response.meta['current_page'] as int
      : 1;
  final lastPage = response.meta['last_page'] is int
      ? response.meta['last_page'] as int
      : currentPage;

  final snapshot = <String, dynamic>{
    'items': jobsJson,
    'meta': response.meta,
    'hasMore': currentPage < lastPage,
    'filters': query.filters,
    'savedAt': DateTime.now().toIso8601String(),
  };

  final key = '$_cacheKeyPrefix:${query.signature()}';
  await box.put(key, jsonEncode(snapshot));
  await _updateLru(box, key);
}

Future<void> _updateLru(Box<dynamic> box, String key) async {
  final existing = (box.get(_lruIndexKey) as List?)?.cast<String>() ?? <String>[];

  existing.remove(key);
  existing.insert(0, key);

  if (existing.length > _maxCachedJobs) {
    final overflow = existing.sublist(_maxCachedJobs);
    for (final staleKey in overflow) {
      await box.delete(staleKey);
    }
    existing.removeRange(_maxCachedJobs, existing.length);
  }

  await box.put(_lruIndexKey, existing);
}
