import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:fixit_user/services/auth/auth_token_store.dart';
import 'package:fixit_user/services/realtime/app_realtime_bridge.dart';
import 'package:fixit_user/services/state/app_state_store.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

class MockConnectivity extends Mock implements Connectivity {}

class MockAuthTokenStore extends Mock implements AuthTokenStore {}

class MockRealtimeBridge extends Mock implements AppRealtimeBridge {}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('AppStateStore', () {
    late MockConnectivity connectivity;
    late MockAuthTokenStore tokenStore;
    late MockRealtimeBridge realtimeBridge;
    late StreamController<List<ConnectivityResult>> connectivityController;

    setUp(() {
      connectivity = MockConnectivity();
      tokenStore = MockAuthTokenStore();
      realtimeBridge = MockRealtimeBridge();
      connectivityController = StreamController<List<ConnectivityResult>>.broadcast();

      when(() => tokenStore.read()).thenAnswer((_) async => null);
      when(() => tokenStore.readRefreshToken()).thenAnswer((_) async => null);
      when(() => tokenStore.writeTokens(accessToken: any(named: 'accessToken'), refreshToken: any(named: 'refreshToken')))
          .thenAnswer((_) async {});
      when(() => tokenStore.clear()).thenAnswer((_) async {});
      when(() => realtimeBridge.resume()).thenAnswer((_) async {});
      when(() => realtimeBridge.pause()).thenAnswer((_) async {});
      when(() => realtimeBridge.retryPendingSubscriptions()).thenAnswer((_) async {});

      when(() => connectivity.checkConnectivity())
          .thenAnswer((_) async => <ConnectivityResult>[ConnectivityResult.wifi]);
    });

    tearDown(() async {
      await connectivityController.close();
    });

    AppStateStore createStore() {
      return AppStateStore(
        connectivity: connectivity,
        tokenStore: tokenStore,
        realtimeBridge: realtimeBridge,
        connectivityStream: connectivityController.stream,
      );
    }

    test('initializes connectivity and authentication state', () async {
      when(() => tokenStore.read()).thenAnswer((_) async => 'token-123');
      final store = createStore();

      await store.initialize();

      expect(store.isOnline, isTrue);
      expect(store.isAuthenticated, isTrue);
    });

    test('reacts to connectivity changes by pausing and resuming realtime', () async {
      final store = createStore();
      await store.initialize();

      connectivityController.add(<ConnectivityResult>[ConnectivityResult.none]);
      await Future<void>.delayed(const Duration(milliseconds: 10));

      expect(store.isOnline, isFalse);
      verify(() => realtimeBridge.pause()).called(greaterThan(0));

      connectivityController.add(<ConnectivityResult>[ConnectivityResult.mobile]);
      await Future<void>.delayed(const Duration(milliseconds: 10));

      expect(store.isOnline, isTrue);
      verify(() => realtimeBridge.retryPendingSubscriptions()).called(1);
    });

    test('storeSession persists tokens and resumes realtime bridge', () async {
      final store = createStore();
      await store.initialize();

      await store.storeSession(accessToken: 'access', refreshToken: 'refresh');

      expect(store.isAuthenticated, isTrue);
      expect(store.lastSessionRefreshAt, isNotNull);
      verify(() => tokenStore.writeTokens(accessToken: 'access', refreshToken: 'refresh')).called(1);
      verify(() => realtimeBridge.resume()).called(greaterThan(0));
    });

    test('clearSession clears tokens and pauses realtime bridge', () async {
      final store = createStore();
      await store.initialize();

      await store.clearSession();

      expect(store.isAuthenticated, isFalse);
      verify(() => tokenStore.clear()).called(1);
      verify(() => realtimeBridge.pause()).called(greaterThan(0));
    });

    test('lifecycle changes pause and resume realtime bridge', () async {
      final store = createStore();
      await store.initialize();

      store.didChangeAppLifecycleState(AppLifecycleState.paused);
      verify(() => realtimeBridge.pause()).called(greaterThan(0));

      store.didChangeAppLifecycleState(AppLifecycleState.resumed);
      verify(() => realtimeBridge.resume()).called(greaterThan(0));
    });
  });
}
