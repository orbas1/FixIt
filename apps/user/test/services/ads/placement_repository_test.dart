import 'dart:io';

import 'package:fixit_user/models/ads/placement_models.dart';
import 'package:fixit_user/services/ads/placement_api_client.dart';
import 'package:fixit_user/services/ads/placement_cache.dart';
import 'package:fixit_user/services/ads/placement_repository.dart';
import 'package:hive/hive.dart';
import 'package:mocktail/mocktail.dart';
import 'package:test/test.dart';

class MockPlacementApiClient extends Mock implements PlacementApiClient {}

void main() {
  late Directory tempDir;
  late PlacementCache cache;
  late MockPlacementApiClient client;
  late PlacementRepository repository;
  late DateTime currentTime;

  setUp(() async {
    tempDir = await Directory.systemTemp.createTemp('placement_repository_test');
    Hive.init(tempDir.path);
    cache = PlacementCache(boxName: 'test_${DateTime.now().microsecondsSinceEpoch}');
    client = MockPlacementApiClient();
    currentTime = DateTime(2024, 1, 1, 12, 0, 0);
    repository = PlacementRepository(
      apiClient: client,
      cache: cache,
      clock: () => currentTime,
    );
  });

  tearDown(() async {
    try {
      await Hive.deleteBoxFromDisk(cache.boxName);
    } catch (_) {}
    await Hive.close();
    await tempDir.delete(recursive: true);
  });

  PlacementPayload buildPayload(String slot, DateTime servedAt) {
    final creative = PlacementCreative(
      kind: 'image',
      sources: [PlacementSource(url: 'https://example.com/banner.jpg', type: 'image/jpeg')],
      placeholderColor: '#ffffff',
      aspectRatio: 16 / 9,
    );
    final serviceMedia = PlacementServiceMedia(
      id: 1,
      url: 'https://example.com/service.jpg',
      collection: 'thumbnail',
    );
    final item = PlacementItem(
      id: 1,
      slot: slot,
      type: 'banner',
      variant: 'image',
      creative: creative,
      provider: PlacementProvider(id: 99, slug: 'provider-99', name: 'Provider 99'),
      services: [
        PlacementService(
          id: 42,
          title: 'Premium Cleaning',
          price: 49,
          discount: 0,
          requiredServicemen: 2,
          media: [serviceMedia],
        ),
      ],
      window: PlacementWindow(
        start: servedAt,
        end: servedAt.add(const Duration(days: 7)),
      ),
      zone: '1',
    );

    return PlacementPayload(
      slot: slot,
      items: [item],
      meta: PlacementMeta(
        servedAt: servedAt,
        ttl: 60,
        staleTtl: 300,
        fresh: true,
      ),
    );
  }

  test('loads from API when cache is empty and stores payload', () async {
    final payload = buildPayload('home_special_offers', currentTime);
    when(() => client.fetchPlacement(
          any(),
          zones: any(named: 'zones'),
          locale: any(named: 'locale'),
          etag: any(named: 'etag'),
        )).thenAnswer((_) async => PlacementApiResponse(payload: payload, etag: 'etag-1'));

    final result = await repository.loadPlacement(slot: 'home_special_offers', locale: 'en');

    expect(result.source, PlacementDataSource.network);
    expect(result.payload.items, isNotEmpty);
    expect(result.isFresh, isTrue);

    final cached = await repository.loadPlacement(slot: 'home_special_offers', locale: 'en');
    expect(cached.source, PlacementDataSource.cache);
    expect(cached.payload.items.first.id, equals(result.payload.items.first.id));

    verify(() => client.fetchPlacement(
          'home_special_offers',
          zones: any(named: 'zones'),
          locale: any(named: 'locale'),
          etag: null,
        )).called(1);
    verifyNoMoreInteractions(client);
  });

  test('returns cached placement and flags revalidation when within stale window', () async {
    final payload = buildPayload('home_special_offers', currentTime);
    when(() => client.fetchPlacement(
          any(),
          zones: any(named: 'zones'),
          locale: any(named: 'locale'),
          etag: any(named: 'etag'),
        )).thenAnswer((_) async => PlacementApiResponse(payload: payload, etag: 'etag-1'));

    final initial = await repository.loadPlacement(slot: 'home_special_offers', locale: 'en');
    expect(initial.source, PlacementDataSource.network);

    currentTime = currentTime.add(const Duration(seconds: 120));

    final cached = await repository.loadPlacement(slot: 'home_special_offers', locale: 'en');
    expect(cached.source, PlacementDataSource.cache);
    expect(cached.shouldRevalidate, isTrue);

    verify(() => client.fetchPlacement(
          'home_special_offers',
          zones: any(named: 'zones'),
          locale: any(named: 'locale'),
          etag: null,
        )).called(1);
    verifyNoMoreInteractions(client);
  });

  test('refreshes from API when stale window expires and sends etag', () async {
    final payload = buildPayload('home_special_offers', currentTime);
    when(() => client.fetchPlacement(
          any(),
          zones: any(named: 'zones'),
          locale: any(named: 'locale'),
          etag: any(named: 'etag'),
        )).thenAnswer((_) async => PlacementApiResponse(payload: payload, etag: 'etag-1'));

    await repository.loadPlacement(slot: 'home_special_offers', locale: 'en');

    final refreshedPayload = buildPayload('home_special_offers', currentTime.add(const Duration(minutes: 10)));
    when(() => client.fetchPlacement(
          any(),
          zones: any(named: 'zones'),
          locale: any(named: 'locale'),
          etag: any(named: 'etag'),
        )).thenAnswer((_) async => PlacementApiResponse(payload: refreshedPayload, etag: 'etag-2'));

    currentTime = currentTime.add(const Duration(seconds: 400));

    final refreshed = await repository.loadPlacement(slot: 'home_special_offers', locale: 'en');

    expect(refreshed.source, PlacementDataSource.network);
    expect(refreshed.payload.meta.servedAt, equals(refreshedPayload.meta.servedAt));

    verify(() => client.fetchPlacement(
          'home_special_offers',
          zones: any(named: 'zones'),
          locale: any(named: 'locale'),
          etag: null,
        )).called(1);
    verify(() => client.fetchPlacement(
          'home_special_offers',
          zones: any(named: 'zones'),
          locale: any(named: 'locale'),
          etag: 'etag-1',
        )).called(1);
  });
}
