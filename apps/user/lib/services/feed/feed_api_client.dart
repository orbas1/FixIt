import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:fixit_user/models/feed_job_model.dart';
import 'package:fixit_user/services/environment.dart';

typedef TokenResolver = Future<String?> Function();

class FeedQuery {
  FeedQuery({
    required this.page,
    required this.perPage,
    Map<String, dynamic>? filters,
  }) : filters = filters ?? {};

  final int page;
  final int perPage;
  final Map<String, dynamic> filters;

  Map<String, dynamic> toJson() {
    final params = <String, dynamic>{
      'page': page,
      'per_page': perPage,
    };

    filters.forEach((key, value) {
      if (value == null) return;
      params[key] = value;
    });

    return params;
  }

  String signature() {
    final buffer = StringBuffer('$page:$perPage');
    if (filters.isNotEmpty) {
      final entries = filters.entries
          .where((entry) => entry.value != null)
          .map((entry) => '${entry.key}=${entry.value}')
          .toList()
        ..sort();
      buffer.write('::${entries.join('&')}');
    }

    return buffer.toString();
  }
}

class FeedResponse {
  FeedResponse({
    required this.jobs,
    required this.meta,
    required this.links,
  });

  final List<FeedJobModel> jobs;
  final Map<String, dynamic> meta;
  final Map<String, dynamic> links;

  factory FeedResponse.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as List<dynamic>? ?? [];
    return FeedResponse(
      jobs: data
          .map((item) => FeedJobModel.fromJson(Map<String, dynamic>.from(item as Map)))
          .toList(),
      meta: Map<String, dynamic>.from(json['meta'] as Map? ?? {}),
      links: Map<String, dynamic>.from(json['links'] as Map? ?? {}),
    );
  }
}

class FeedApiClient {
  FeedApiClient({
    required this.baseUrl,
    Dio? dio,
    this.tokenResolver,
  }) : _dio = dio ?? Dio(BaseOptions(connectTimeout: const Duration(seconds: 12), receiveTimeout: const Duration(seconds: 12))) {
    _dio.interceptors.add(QueuedInterceptorsWrapper(onRequest: (options, handler) async {
      options.headers.addAll(headers);
      if (tokenResolver != null) {
        final token = await tokenResolver!.call();
        if (token != null) {
          options.headers.addAll(headersToken(token) ?? {});
        }
      }
      handler.next(options);
    }, onError: (error, handler) {
      debugPrint('FeedApiClient error: ${error.message}');
      handler.next(error);
    }));

    if (kDebugMode) {
      _dio.interceptors.add(LogInterceptor(responseBody: false, requestBody: false));
    }
  }

  final Dio _dio;
  final String baseUrl;
  final TokenResolver? tokenResolver;

  Future<FeedResponse> fetchJobs(FeedQuery query) async {
    final response = await _dio.get<Map<String, dynamic>>(baseUrl, queryParameters: query.toJson());
    return FeedResponse.fromJson(response.data ?? {});
  }

  Future<FeedJobModel> fetchJobDetail(int id) async {
    final response = await _dio.get<Map<String, dynamic>>('$baseUrl/$id');
    final data = response.data;
    if (data == null) {
      throw StateError('Empty response while fetching job detail for id $id');
    }
    final payload = data['data'];
    if (payload is Map<String, dynamic>) {
      return FeedJobModel.fromJson(Map<String, dynamic>.from(payload));
    }
    if (data is Map<String, dynamic>) {
      return FeedJobModel.fromJson(Map<String, dynamic>.from(data));
    }
    throw StateError('Unexpected payload for job $id');
  }
}
