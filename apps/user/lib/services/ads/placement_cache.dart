import 'dart:async';

import 'package:hive/hive.dart';

import '../../models/ads/placement_models.dart';

class PlacementCache {
  PlacementCache({this.boxName = 'placement_cache_box'});

  final String boxName;
  Box<dynamic>? _box;

  Future<Box<dynamic>> _ensureBox() async {
    if (_box != null && _box!.isOpen) {
      return _box!;
    }
    _box = await Hive.openBox<dynamic>(boxName);
    return _box!;
  }

  Future<void> write(
    String key,
    PlacementPayload payload, {
    String? etag,
    DateTime? now,
  }) async {
    final box = await _ensureBox();
    final meta = payload.meta;
    final timestamp = (now ?? DateTime.now()).toIso8601String();
    await box.put(key, {
      'payload': placementPayloadToJson(payload),
      'etag': etag,
      'fetched_at': timestamp,
      'ttl': meta.ttl,
      'stale_ttl': meta.staleTtl,
    });
  }

  Future<PlacementCacheEntry?> touch(String key, {DateTime? now}) async {
    final box = await _ensureBox();
    final raw = box.get(key);
    if (raw is! Map) {
      return null;
    }

    final updated = Map<String, dynamic>.from(raw);
    updated['fetched_at'] = (now ?? DateTime.now()).toIso8601String();
    await box.put(key, updated);
    return read(key);
  }

  Future<void> delete(String key) async {
    final box = await _ensureBox();
    await box.delete(key);
  }

  Future<PlacementCacheEntry?> read(String key) async {
    final box = await _ensureBox();
    final raw = box.get(key);
    if (raw is! Map) {
      return null;
    }

    try {
      final payload = placementPayloadFromJson(raw['payload'] as String);
      final fetchedAt = DateTime.tryParse(raw['fetched_at'] as String? ?? '') ?? DateTime.fromMillisecondsSinceEpoch(0);
      final ttl = (raw['ttl'] as num?)?.toInt() ?? payload.meta.ttl;
      final staleTtl = (raw['stale_ttl'] as num?)?.toInt() ?? payload.meta.staleTtl;
      return PlacementCacheEntry(
        payload: payload,
        etag: raw['etag'] as String?,
        fetchedAt: fetchedAt,
        ttl: ttl,
        staleTtl: staleTtl,
      );
    } catch (_) {
      await box.delete(key);
      return null;
    }
  }
}

class PlacementCacheEntry {
  PlacementCacheEntry({
    required this.payload,
    required this.etag,
    required this.fetchedAt,
    required this.ttl,
    required this.staleTtl,
  });

  final PlacementPayload payload;
  final String? etag;
  final DateTime fetchedAt;
  final int ttl;
  final int staleTtl;

  bool isFresh({DateTime? now}) {
    final reference = now ?? DateTime.now();
    return reference.difference(fetchedAt) < Duration(seconds: ttl);
  }

  bool isStale({DateTime? now}) {
    final reference = now ?? DateTime.now();
    return reference.difference(fetchedAt) < Duration(seconds: staleTtl);
  }

  PlacementCacheEntry copyWith({DateTime? fetchedAtOverride}) {
    return PlacementCacheEntry(
      payload: payload,
      etag: etag,
      fetchedAt: fetchedAtOverride ?? fetchedAt,
      ttl: ttl,
      staleTtl: staleTtl,
    );
  }
}
