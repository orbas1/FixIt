import 'dart:async';

import 'package:collection/collection.dart';

import '../../models/ads/placement_models.dart';
import 'placement_api_client.dart';
import 'placement_cache.dart';

class PlacementRepository {
  PlacementRepository({
    required PlacementApiClient apiClient,
    required PlacementCache cache,
    DateTime Function()? clock,
  })  : _apiClient = apiClient,
        _cache = cache,
        _clock = clock ?? DateTime.now;

  final PlacementApiClient _apiClient;
  final PlacementCache _cache;
  final DateTime Function() _clock;
  final Map<String, Future<PlacementLoadResult>> _inFlight = {};

  Future<PlacementLoadResult> loadPlacement({
    required String slot,
    List<dynamic> zones = const [],
    String? locale,
    bool forceRefresh = false,
  }) async {
    final resolvedLocale = (locale ?? 'en').toLowerCase();
    final normalizedZones = _normalizeZones(zones);
    final key = _cacheKey(slot, normalizedZones, resolvedLocale);
    final cached = await _cache.read(key);
    final now = _clock();

    if (!forceRefresh && cached != null) {
      final cacheResult = PlacementLoadResult.fromCache(
        cacheKey: key,
        entry: cached,
        now: now,
      );
      if (cacheResult.isFresh || cacheResult.isStale) {
        return cacheResult;
      }
    }

    return _fetchAndPersist(
      slot: slot,
      normalizedZones: normalizedZones,
      locale: resolvedLocale,
      cacheKey: key,
      cached: cached,
      allowNotModified: cached != null,
    );
  }

  Future<PlacementLoadResult> revalidatePlacement({
    required String slot,
    List<dynamic> zones = const [],
    String? locale,
  }) async {
    final resolvedLocale = (locale ?? 'en').toLowerCase();
    final normalizedZones = _normalizeZones(zones);
    final key = _cacheKey(slot, normalizedZones, resolvedLocale);
    final cached = await _cache.read(key);

    return _fetchAndPersist(
      slot: slot,
      normalizedZones: normalizedZones,
      locale: resolvedLocale,
      cacheKey: key,
      cached: cached,
      allowNotModified: true,
    );
  }

  Future<void> clearCache(String slot, {List<dynamic> zones = const [], String? locale}) async {
    final resolvedLocale = (locale ?? 'en').toLowerCase();
    final normalizedZones = _normalizeZones(zones);
    final key = _cacheKey(slot, normalizedZones, resolvedLocale);
    await _cache.delete(key);
  }

  List<String> _normalizeZones(List<dynamic> zones) {
    final buffer = <String>[];
    for (final zone in zones) {
      if (zone == null) {
        continue;
      }
      final value = zone.toString().trim();
      if (value.isEmpty) {
        continue;
      }
      buffer.add(value);
    }
    buffer.sort(compareAsciiLowerCase);
    return List.unmodifiable(buffer);
  }

  String _cacheKey(String slot, List<String> zones, String locale) {
    final zoneKey = zones.isEmpty ? 'all' : zones.join('.');
    return 'placements:$slot:$locale:$zoneKey';
  }

  Future<PlacementLoadResult> _fetchAndPersist({
    required String slot,
    required List<String> normalizedZones,
    required String locale,
    required String cacheKey,
    PlacementCacheEntry? cached,
    bool allowNotModified = false,
  }) {
    final inflightKey = '$cacheKey::$locale';
    final existing = _inFlight[inflightKey];
    if (existing != null) {
      return existing;
    }

    final future = () async {
      final response = await _apiClient.fetchPlacement(
        slot,
        zones: normalizedZones,
        locale: locale,
        etag: allowNotModified ? cached?.etag : null,
      );

      if (response.notModified) {
        final cachedEntry = cached ?? await _cache.read(cacheKey);
        if (cachedEntry == null) {
          throw StateError('Placement cache miss for $slot with not-modified response');
        }
        final now = _clock();
        final touched = await _cache.touch(cacheKey, now: now) ?? cachedEntry.copyWith(fetchedAtOverride: now);
        return PlacementLoadResult.fromCache(
          cacheKey: cacheKey,
          entry: touched,
          now: now,
        );
      }

      if (!response.hasPayload) {
        throw StateError('Placement API returned empty payload for $slot');
      }

      final payload = response.payload!;
      final now = _clock();
      await _cache.write(cacheKey, payload, etag: response.etag, now: now);

      return PlacementLoadResult.fromNetwork(
        cacheKey: cacheKey,
        payload: payload,
        etag: response.etag,
        now: now,
      );
    }();

    _inFlight[inflightKey] = future;

    return future.whenComplete(() {
      if (identical(_inFlight[inflightKey], future)) {
        _inFlight.remove(inflightKey);
      }
    });
  }
}

class PlacementLoadResult {
  PlacementLoadResult({
    required this.cacheKey,
    required this.payload,
    required this.source,
    required this.isFresh,
    required this.isStale,
    required this.fetchedAt,
    required this.etag,
  });

  final String cacheKey;
  final PlacementPayload payload;
  final PlacementDataSource source;
  final bool isFresh;
  final bool isStale;
  final DateTime fetchedAt;
  final String? etag;

  bool get shouldRevalidate => !isFresh && isStale;
  bool get isEmpty => payload.items.isEmpty;

  factory PlacementLoadResult.fromCache({
    required String cacheKey,
    required PlacementCacheEntry entry,
    required DateTime now,
  }) {
    return PlacementLoadResult(
      cacheKey: cacheKey,
      payload: entry.payload,
      source: PlacementDataSource.cache,
      isFresh: entry.isFresh(now: now),
      isStale: entry.isStale(now: now),
      fetchedAt: entry.fetchedAt,
      etag: entry.etag,
    );
  }

  factory PlacementLoadResult.fromNetwork({
    required String cacheKey,
    required PlacementPayload payload,
    required String? etag,
    required DateTime now,
  }) {
    return PlacementLoadResult(
      cacheKey: cacheKey,
      payload: payload,
      source: PlacementDataSource.network,
      isFresh: true,
      isStale: true,
      fetchedAt: now,
      etag: etag,
    );
  }
}

enum PlacementDataSource { cache, network }
