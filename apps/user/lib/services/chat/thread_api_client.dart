import 'package:dio/dio.dart';

import '../../models/chat_model.dart';
import '../../models/message_model.dart';
import '../environment.dart';

typedef TokenResolver = Future<String?> Function();

typedef ThreadJson = Map<String, dynamic>;

typedef MessageJson = Map<String, dynamic>;

class ThreadApiClient {
  ThreadApiClient({
    Dio? dio,
    this.tokenResolver,
    String? baseUrl,
  }) : _dio = dio ??
            Dio(
              BaseOptions(
                connectTimeout: const Duration(seconds: 12),
                receiveTimeout: const Duration(seconds: 12),
              ),
            ) {
    _baseUrl = baseUrl ?? '${apiUrl.trim()}/api/threads';
    _dio.interceptors.add(QueuedInterceptorsWrapper(onRequest: (options, handler) async {
      options.headers.addAll(headers ?? {});
      if (tokenResolver != null) {
        final token = await tokenResolver!.call();
        if (token != null) {
          options.headers.addAll(headersToken(token) ?? {});
        }
      }
      handler.next(options);
    }));
  }

  final Dio _dio;
  final TokenResolver? tokenResolver;
  late final String _baseUrl;

  Future<List<ThreadSummary>> fetchThreads({int page = 1, int perPage = 20}) async {
    final response = await _dio.get<Map<String, dynamic>>(
      _baseUrl,
      queryParameters: {'page': page, 'per_page': perPage},
    );
    final data = response.data?['data'] as List<dynamic>? ?? [];
    return data
        .map((item) => ThreadSummary.fromJson(ThreadJson.from(item as Map)))
        .toList();
  }

  Future<ThreadDetail> fetchThread(String id) async {
    final response = await _dio.get<Map<String, dynamic>>('$_baseUrl/$id');
    final json = ThreadJson.from(response.data?['data'] as Map? ?? {});
    return ThreadDetail.fromJson(json);
  }

  Future<ThreadDetail> createThread({
    required String type,
    required List<int> participantIds,
    String? subject,
    int? serviceRequestId,
    int? bookingId,
  }) async {
    final response = await _dio.post<Map<String, dynamic>>(
      _baseUrl,
      data: {
        'type': type,
        'participants': participantIds,
        if (subject != null) 'subject': subject,
        if (serviceRequestId != null) 'service_request_id': serviceRequestId,
        if (bookingId != null) 'booking_id': bookingId,
      },
    );

    final json = ThreadJson.from(response.data?['data'] as Map? ?? {});
    return ThreadDetail.fromJson(json);
  }

  Future<ThreadMessage> sendMessage(String threadId, ComposeMessageRequest request) async {
    final response = await _dio.post<Map<String, dynamic>>(
      '$_baseUrl/$threadId/messages',
      data: request.toJson(),
    );

    final json = MessageJson.from(response.data?['data'] as Map? ?? {});
    return ThreadMessage.fromJson(json);
  }

  Future<void> markRead(String threadId) async {
    await _dio.post<void>('$_baseUrl/$threadId/read');
  }
}
