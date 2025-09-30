import 'package:get_it/get_it.dart';

import '../realtime/app_realtime_bridge.dart';

typedef ThreadMessageCallback = void Function(Map<String, dynamic> event);

typedef TypingCallback = void Function(bool isTyping);

class ThreadRealtimeService {
  ThreadRealtimeService({AppRealtimeBridge? realtimeBridge})
      : _realtimeBridge = realtimeBridge ?? GetIt.I<AppRealtimeBridge>();

  final AppRealtimeBridge _realtimeBridge;
  final Set<String> _activeChannels = <String>{};

  Future<void> subscribeToThread({
    required String threadId,
    ThreadMessageCallback? onMessage,
    TypingCallback? onTyping,
  }) async {
    final messageChannel = 'private-thread.$threadId.message';
    await _realtimeBridge.subscribe(
      channelName: messageChannel,
      handler: (event) {
        final payload = _realtimeBridge.decodeEvent(event);
        if (payload != null) {
          final message = payload['message'];
          if (message is Map<String, dynamic>) {
            onMessage?.call(Map<String, dynamic>.from(message));
          }
        }
      },
    );
    _activeChannels.add(messageChannel);

    if (onTyping != null) {
      final typingChannel = 'private-thread.$threadId.typing';
      await _realtimeBridge.subscribe(
        channelName: typingChannel,
        handler: (event) {
          final data = event.data;
          if (data is String) {
            onTyping(data == 'typing');
          } else {
            final payload = _realtimeBridge.decodeEvent(event);
            onTyping(payload?['status'] == 'typing');
          }
        },
      );
      _activeChannels.add(typingChannel);
    }
  }

  Future<void> disconnect() async {
    for (final channel in _activeChannels) {
      await _realtimeBridge.unsubscribe(channel);
    }
    _activeChannels.clear();
  }
}
