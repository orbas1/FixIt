import 'dart:developer';

import 'package:flutter/foundation.dart';

import '../../models/chat_model.dart';
import '../../models/message_model.dart';
import '../../services/chat/thread_api_client.dart';
import '../../services/chat/thread_realtime_service.dart';
import '../../services/environment.dart';

class ThreadConversationProvider with ChangeNotifier {
  ThreadConversationProvider({
    ThreadApiClient? apiClient,
    ThreadRealtimeService? realtimeService,
  })  : _apiClient = apiClient ?? ThreadApiClient(tokenResolver: _tokenResolver),
        _realtimeService = realtimeService ?? ThreadRealtimeService();

  final ThreadApiClient _apiClient;
  final ThreadRealtimeService _realtimeService;

  ThreadDetail? thread;
  bool isLoading = false;
  bool hasError = false;
  String? errorMessage;

  Future<void> loadThread(String threadId) async {
    isLoading = true;
    hasError = false;
    errorMessage = null;
    notifyListeners();

    try {
      thread = await _apiClient.fetchThread(threadId);
      await _realtimeService.subscribeToThread(
        threadId: threadId,
        onMessage: _handleRealtimeMessage,
      );
    } catch (error, stackTrace) {
      log('ThreadConversationProvider: Failed to load thread', error: error, stackTrace: stackTrace);
      hasError = true;
      errorMessage = error.toString();
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> markRead() async {
    final current = thread;
    if (current == null) return;

    try {
      await _apiClient.markRead(current.id);
    } catch (error, stackTrace) {
      log('ThreadConversationProvider: Failed to mark read', error: error, stackTrace: stackTrace);
    }
  }

  Future<void> sendMessage(ComposeMessageRequest request) async {
    final current = thread;
    if (current == null) {
      throw StateError('Thread not loaded');
    }

    final message = await _apiClient.sendMessage(current.id, request);
    _appendMessage(message);
    notifyListeners();
  }

  Future<void> disposeRealtime() async {
    await _realtimeService.disconnect();
  }

  void _handleRealtimeMessage(Map<String, dynamic> payload) {
    final message = ThreadMessage.fromJson(payload);
    _appendMessage(message);
    notifyListeners();
  }

  void _appendMessage(ThreadMessage message) {
    final current = thread;
    if (current == null) {
      return;
    }

    final updatedMessages = [...current.messages, message];
    thread = ThreadDetail(
      id: current.id,
      type: current.type,
      status: current.status,
      subject: current.subject,
      lastMessageAt: message.createdAt,
      participants: current.participants,
      latestMessage: message,
      serviceRequestId: current.serviceRequestId,
      bookingId: current.bookingId,
      messages: updatedMessages,
    );
  }
}

Future<String?> _tokenResolver() async {
  return sharedPreferences.getString('token');
}
