import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/widgets.dart';

import '../auth/auth_token_store.dart';
import '../logging/app_logger.dart';
import '../realtime/app_realtime_bridge.dart';

class AppStateStore extends ChangeNotifier with WidgetsBindingObserver {
  AppStateStore({
    required Connectivity connectivity,
    required AuthTokenStore tokenStore,
    required AppRealtimeBridge realtimeBridge,
    AppLogger? logger,
    Stream<List<ConnectivityResult>>? connectivityStream,
  })  : _connectivity = connectivity,
        _tokenStore = tokenStore,
        _realtimeBridge = realtimeBridge,
        _logger = logger ?? AppLogger.instance,
        _connectivityStreamOverride = connectivityStream;

  final Connectivity _connectivity;
  final AuthTokenStore _tokenStore;
  final AppRealtimeBridge _realtimeBridge;
  final AppLogger _logger;
  final Stream<List<ConnectivityResult>>? _connectivityStreamOverride;

  StreamSubscription<List<ConnectivityResult>>? _connectivitySubscription;

  bool _isOnline = true;
  bool _isAuthenticated = false;
  AppLifecycleState _lifecycleState = AppLifecycleState.resumed;
  DateTime? _lastInteractionAt;
  DateTime? _lastSessionRefreshAt;

  bool get isOnline => _isOnline;
  bool get isAuthenticated => _isAuthenticated;
  AppLifecycleState get lifecycleState => _lifecycleState;
  DateTime? get lastInteractionAt => _lastInteractionAt;
  DateTime? get lastSessionRefreshAt => _lastSessionRefreshAt;

  Future<void> initialize() async {
    WidgetsBinding.instance.addObserver(this);
    await _hydrateConnectivity();
    await _hydrateAuthentication();
    _listenToConnectivityChanges();
  }

  Future<void> refreshAuthState() async {
    await _hydrateAuthentication();
  }

  Future<void> storeSession({
    required String accessToken,
    String? refreshToken,
  }) async {
    await _tokenStore.writeTokens(accessToken: accessToken, refreshToken: refreshToken);
    _isAuthenticated = true;
    _lastSessionRefreshAt = DateTime.now();
    await _realtimeBridge.resume();
    notifyListeners();
  }

  Future<void> clearSession() async {
    await _tokenStore.clear();
    _isAuthenticated = false;
    _lastSessionRefreshAt = DateTime.now();
    await _realtimeBridge.pause();
    notifyListeners();
  }

  void trackUserInteraction() {
    _lastInteractionAt = DateTime.now();
    notifyListeners();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    _lifecycleState = state;
    switch (state) {
      case AppLifecycleState.resumed:
        if (_isOnline) {
          _realtimeBridge.resume();
        }
        break;
      case AppLifecycleState.inactive:
      case AppLifecycleState.hidden:
      case AppLifecycleState.paused:
        _realtimeBridge.pause();
        break;
      case AppLifecycleState.detached:
        _realtimeBridge.pause();
        break;
    }
    notifyListeners();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _connectivitySubscription?.cancel();
    super.dispose();
  }

  Future<void> _hydrateConnectivity() async {
    try {
      final results = await _connectivity.checkConnectivity();
      _setConnectivity(results);
    } catch (error, stackTrace) {
      _logger.error('AppStateStore: Failed to fetch connectivity status', error: error, stackTrace: stackTrace);
    }
  }

  Future<void> _hydrateAuthentication() async {
    final token = await _tokenStore.read();
    final refreshToken = await _tokenStore.readRefreshToken();
    final isAuthenticated = (token != null && token.isNotEmpty) || (refreshToken != null && refreshToken.isNotEmpty);
    if (_isAuthenticated != isAuthenticated) {
      _isAuthenticated = isAuthenticated;
      notifyListeners();
    }
  }

  void _listenToConnectivityChanges() {
    final stream = _connectivityStreamOverride ?? _connectivity.onConnectivityChanged;
    _connectivitySubscription = stream.listen(
      _setConnectivity,
      onError: (error, stackTrace) {
        final resolvedStackTrace = stackTrace is StackTrace ? stackTrace : null;
        _logger.error('AppStateStore: Connectivity stream error', error: error, stackTrace: resolvedStackTrace);
      },
    );
  }

  void _setConnectivity(List<ConnectivityResult> results) {
    final online = !results.contains(ConnectivityResult.none);
    if (_isOnline == online) {
      return;
    }
    _isOnline = online;
    _logger.info('AppStateStore: Connectivity changed -> ${online ? 'online' : 'offline'}', category: 'AppState');
    if (online) {
      _realtimeBridge.retryPendingSubscriptions();
    } else {
      _realtimeBridge.pause();
    }
    notifyListeners();
  }
}
