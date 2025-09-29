import 'package:flutter_test/flutter_test.dart';

import 'package:fixit_user/models/chat_model.dart';
import 'package:fixit_user/models/message_model.dart';
import 'package:fixit_user/providers/chat/thread_inbox_provider.dart';
import 'package:fixit_user/services/chat/thread_api_client.dart';

class _StubThreadApiClient extends ThreadApiClient {
  _StubThreadApiClient({required this.stubThreads}) : super(dio: null);

  final List<ThreadSummary> stubThreads;

  @override
  Future<List<ThreadSummary>> fetchThreads({int page = 1, int perPage = 20}) async {
    return stubThreads;
  }
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('ThreadInboxProvider', () {
    test('loadThreads populates threads list', () async {
      final message = ThreadMessage(
        id: 'msg-1',
        threadId: 'thread-1',
        body: 'Hello',
        attachments: const [],
        meta: const {},
        isSystem: false,
        author: const ThreadMessageAuthor(id: 1, name: 'Alice'),
        createdAt: DateTime.now(),
      );

      final stubThread = ThreadSummary(
        id: 'thread-1',
        type: 'buyer_provider',
        status: 'open',
        subject: 'Test',
        lastMessageAt: DateTime.now(),
        participants: const <ThreadParticipant>[],
        latestMessage: message,
      );

      final provider = ThreadInboxProvider(
        apiClient: _StubThreadApiClient(stubThreads: [stubThread]),
      );

      await provider.loadThreads(force: true);

      expect(provider.threads, isNotEmpty);
      expect(provider.threads.first.id, equals('thread-1'));
    });
  });
}
