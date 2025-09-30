import 'dart:async';
import 'dart:convert';

import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';

import '../auth/auth_token_store.dart';
import '../configuration/app_environment.dart';
import '../environment.dart';
import '../logging/app_logger.dart';

typedef RealtimeEventHandler = void Function(PusherEvent event);

class _RealtimeSubscription {
  _RealtimeSubscription({
    required this.channelName,
    required this.subscribeAction,
  });

  final String channelName;
  final Future<void> Function() subscribeAction;
}

class AppRealtimeBridge {
  AppRealtimeBridge({
    required RealtimeConfiguration configuration,
    required AuthTokenStore tokenStore,
    required EnvironmentStore environmentStore,
    AppLogger? logger,
    PusherChannelsFlutter? client,
  })  : _configuration = configuration,
        _tokenStore = tokenStore,
        _environmentStore = environmentStore,
        _logger = logger ?? AppLogger.instance,
        _client = client ?? PusherChannelsFlutter.getInstance();

  final RealtimeConfiguration _configuration;
  final AuthTokenStore _tokenStore;
  final EnvironmentStore _environmentStore;
  final AppLogger _logger;
  final PusherChannelsFlutter _client;

  final Map<String, _RealtimeSubscription> _subscriptions = <String, _RealtimeSubscription>{};
  bool _isInitialized = false;
  bool _isConnected = false;
  bool _shouldReconnect = false;
  Completer<void>? _connectionCompleter;

  Future<void> ensureConnected() async {
    await _initialize();
    if (_isConnected) {
      return;
    }

    _connectionCompleter ??= Completer<void>();
    await _client.connect();
    await _connectionCompleter!.future.timeout(
      Duration(milliseconds: _configuration.activityTimeoutMs),
      onTimeout: () {
        final timeout = TimeoutException('Realtime connection timeout');
        _logger.error('Realtime connection timed out', error: timeout);
        _connectionCompleter?.completeError(timeout);
        _connectionCompleter = null;
        throw timeout;
      },
    );
  }

  Future<void> subscribe({
    required String channelName,
    required RealtimeEventHandler handler,
    bool autoResubscribe = true,
  }) async {
    Future<void> subscribeAction() async {
      await _client.subscribe(
        channelName: channelName,
        onEvent: (event) {
          try {
            handler(event);
          } catch (error, stackTrace) {
            _logger.error(
              'Realtime handler error for $channelName',
              error: error,
              stackTrace: stackTrace,
            );
          }
        },
      );
    }

    if (autoResubscribe) {
      _subscriptions[channelName] = _RealtimeSubscription(
        channelName: channelName,
        subscribeAction: subscribeAction,
      );
    }

    await ensureConnected();
    await subscribeAction();
  }

  Future<void> unsubscribe(String channelName) async {
    _subscriptions.remove(channelName);
    try {
      await _client.unsubscribe(channelName: channelName);
    } catch (error, stackTrace) {
      _logger.error('Realtime unsubscribe failed for $channelName', error: error, stackTrace: stackTrace);
    }
  }

  Future<void> pause() async {
    if (!_isConnected) {
      return;
    }
    _shouldReconnect = true;
    try {
      await _client.disconnect();
    } catch (error, stackTrace) {
      _logger.error('Realtime pause failed', error: error, stackTrace: stackTrace);
    } finally {
      _isConnected = false;
    }
  }

  Future<void> resume() async {
    if (!_shouldReconnect) {
      return;
    }
    await ensureConnected();
    await _resubscribeAll();
    _shouldReconnect = false;
  }

  Future<void> retryPendingSubscriptions() async {
    if (_subscriptions.isEmpty) {
      return;
    }
    await ensureConnected();
    await _resubscribeAll();
  }

  Future<void> disconnect() async {
    _subscriptions.clear();
    _shouldReconnect = false;
    try {
      await _client.disconnect();
    } catch (error, stackTrace) {
      _logger.error('Realtime disconnect failed', error: error, stackTrace: stackTrace);
    } finally {
      _isConnected = false;
    }
  }

  Future<void> _initialize() async {
    if (_isInitialized) {
      return;
    }

    await _client.init(
      apiKey: _configuration.key,
      cluster: _configuration.cluster,
      authEndpoint: _environmentStore.realtimeAuthUrl,
      onAuthorizer: (channelName, socketId, options) async {
        final token = await _tokenStore.read();
        final headers = _environmentStore.headers(token: token);
        return headers;
      },
      onConnectionStateChange: (state) {
        if (state.currentState == 'CONNECTED') {
          _isConnected = true;
          _connectionCompleter?.complete();
          _connectionCompleter = null;
          unawaited(_resubscribeAll());
        } else if (state.currentState == 'DISCONNECTED') {
          _isConnected = false;
        }
        _logger.info('Realtime connection state: ${state.currentState}', category: 'Realtime');
      },
      onError: (message, code, exception) {
        _logger.error(
          'Realtime error [$code]: $message',
          error: exception,
        );
      },
      logToConsole: false,
    );

    _isInitialized = true;
  }

  Future<void> _resubscribeAll() async {
    if (_subscriptions.isEmpty) {
      return;
    }

    for (final entry in _subscriptions.values) {
      try {
        await entry.subscribeAction();
      } catch (error, stackTrace) {
        _logger.error(
          'Realtime resubscription failed for ${entry.channelName}',
          error: error,
          stackTrace: stackTrace,
        );
      }
    }
  }

  Map<String, dynamic>? decodeEvent(PusherEvent event) {
    final data = event.data;
    if (data is Map<String, dynamic>) {
      return data;
    }

    if (data is String && data.isNotEmpty) {
      try {
        return jsonDecode(data) as Map<String, dynamic>;
      } catch (error) {
        _logger.warning('Failed to decode realtime payload for ${event.channelName}');
      }
    }
    return null;
  }
}
