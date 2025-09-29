import 'dart:developer';

import 'package:flutter/foundation.dart';

import '../../models/chat_model.dart';
import '../../models/message_model.dart';
import '../../services/chat/thread_api_client.dart';
import '../../services/environment.dart';

class ThreadInboxProvider with ChangeNotifier {
  ThreadInboxProvider({ThreadApiClient? apiClient})
      : _apiClient = apiClient ?? ThreadApiClient(tokenResolver: _defaultTokenResolver);

  final ThreadApiClient _apiClient;
  List<ThreadSummary> threads = const [];
  bool isLoading = false;
  bool hasError = false;
  String? errorMessage;

  Future<void> loadThreads({bool force = false}) async {
    if (isLoading && !force) return;
    isLoading = true;
    hasError = false;
    errorMessage = null;
    notifyListeners();

    try {
      final results = await _apiClient.fetchThreads(page: 1, perPage: 50);
      threads = results
        ..sort((a, b) => b.lastMessageAt.compareTo(a.lastMessageAt));
    } catch (error, stackTrace) {
      log('ThreadInboxProvider: Failed to fetch threads', error: error, stackTrace: stackTrace);
      hasError = true;
      errorMessage = error.toString();
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<ThreadDetail?> createThread({
    required int participantId,
    required String type,
    String? subject,
  }) async {
    try {
      final thread = await _apiClient.createThread(
        type: type,
        participantIds: [participantId],
        subject: subject,
      );

      threads = [
        ThreadSummary.fromJson(thread.toJson()),
        ...threads,
      ];
      notifyListeners();
      return thread;
    } catch (error, stackTrace) {
      log('ThreadInboxProvider: Failed to create thread', error: error, stackTrace: stackTrace);
      hasError = true;
      errorMessage = error.toString();
      notifyListeners();
      return null;
    }
  }

  Future<void> updateThreadOrdering(ThreadMessage message) async {
    final index = threads.indexWhere((element) => element.id == message.threadId);
    if (index == -1) {
      await loadThreads(force: true);
      return;
    }

    final summary = threads[index];
    final updated = ThreadSummary.fromJson({
      ...summary.toJson(),
      'latest_message': message.toJson(),
      'last_message_at': message.createdAt.toIso8601String(),
    });

    threads = [updated, ...threads.where((element) => element.id != updated.id)];
    notifyListeners();
  }
}

Future<String?> _defaultTokenResolver() async {
  return sharedPreferences.getString('token');
}
