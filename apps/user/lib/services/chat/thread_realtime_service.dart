import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';

typedef ThreadMessageCallback = void Function(Map<String, dynamic> event);

typedef TypingCallback = void Function(bool isTyping);

class ThreadRealtimeService {
  ThreadRealtimeService({PusherChannelsFlutter? client})
      : _client = client ?? PusherChannelsFlutter.getInstance();

  final PusherChannelsFlutter _client;

  Future<void> connect({
    required String apiKey,
    required String cluster,
    String? authEndpoint,
    Map<String, String>? headers,
  }) async {
    await _client.init(
      apiKey: apiKey,
      cluster: cluster,
      authEndpoint: authEndpoint,
      onAuthorizer: (channelName, socketId, options) async {
        return headers ?? {};
      },
      onConnectionStateChange: (state) {},
      onSubscriptionError: (message, [error]) {},
      onError: (message, code, exception) {},
      logToConsole: false,
    );

    await _client.connect();
  }

  Future<void> subscribeToThread({
    required String threadId,
    ThreadMessageCallback? onMessage,
    TypingCallback? onTyping,
  }) async {
    final channelName = 'private-thread.$threadId.message';
    await _client.subscribe(
      channelName: channelName,
      onEvent: (event) {
        final payload = event.data;
        if (payload is Map<String, dynamic> && payload.containsKey('message')) {
          onMessage?.call(Map<String, dynamic>.from(payload['message'] as Map));
        }
      },
    );

    if (onTyping != null) {
      final typingChannel = 'private-thread.$threadId.typing';
      await _client.subscribe(
        channelName: typingChannel,
        onEvent: (event) => onTyping(event.data == 'typing'),
      );
    }
  }

  Future<void> disconnect() async {
    await _client.disconnect();
  }
}
