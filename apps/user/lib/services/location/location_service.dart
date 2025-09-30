import 'dart:async';
import 'dart:convert';

import 'package:geolocator/geolocator.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter/material.dart';

import '../../services/location/h3_indexer.dart';
import 'ip_location_client.dart';

class LocationSnapshot {
  const LocationSnapshot({
    required this.latitude,
    required this.longitude,
    required this.h3Index,
    required this.source,
    required this.capturedAt,
    this.isMocked = false,
  });

  final double latitude;
  final double longitude;
  final int h3Index;
  final String source;
  final DateTime capturedAt;
  final bool isMocked;

  Map<String, dynamic> toJson() => {
        'latitude': latitude,
        'longitude': longitude,
        'h3Index': h3Index,
        'source': source,
        'capturedAt': capturedAt.toIso8601String(),
        'isMocked': isMocked,
      };

  factory LocationSnapshot.fromJson(Map<String, dynamic> json) {
    return LocationSnapshot(
      latitude: (json['latitude'] as num).toDouble(),
      longitude: (json['longitude'] as num).toDouble(),
      h3Index: json['h3Index'] as int,
      source: json['source'] as String,
      capturedAt: DateTime.parse(json['capturedAt'] as String),
      isMocked: json['isMocked'] as bool? ?? false,
    );
  }
}

class LocationService {
  LocationService({
    GeolocatorPlatform? geolocator,
    required IpLocationClient ipLocationClient,
    required SharedPreferences preferences,
    H3Indexer? indexer,
  })  : _geolocator = geolocator ?? GeolocatorPlatform.instance,
        _ipLocationClient = ipLocationClient,
        _preferences = preferences,
        _indexer = indexer ?? const H3Indexer();

  static const _storageKey = 'location:last_snapshot';
  static const _foregroundThrottle = Duration(seconds: 30);
  static const _backgroundThrottle = Duration(minutes: 5);

  final GeolocatorPlatform _geolocator;
  final IpLocationClient _ipLocationClient;
  final SharedPreferences _preferences;
  final H3Indexer _indexer;

  final StreamController<LocationSnapshot> _controller = StreamController<LocationSnapshot>.broadcast();
  StreamSubscription<Position>? _positionSubscription;
  LocationSnapshot? _current;
  DateTime? _lastPreciseUpdate;

  Stream<LocationSnapshot> get stream => _controller.stream;
  LocationSnapshot? get currentSnapshot => _current;

  Future<LocationSnapshot> ensureLatest({bool forceRefresh = false, BuildContext? context}) async {
    if (!forceRefresh && _current != null) {
      return _current!;
    }

    final permission = await _ensurePermission(context: context);
    if (_isDenied(permission)) {
      final fallback = await _resolveCoarseLocation();
      _publish(fallback);
      return fallback;
    }

    try {
      final position = await _geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(accuracy: LocationAccuracy.high),
      );
      final snapshot = _snapshotFromPosition(position, source: 'precise');
      _publish(snapshot);
      _startListening();
      return snapshot;
    } catch (_) {
      final fallback = await _resolveCoarseLocation();
      _publish(fallback);
      return fallback;
    }
  }

  Future<LocationSnapshot?> loadPersistedSnapshot() async {
    final raw = _preferences.getString(_storageKey);
    if (raw == null) return null;
    try {
      final decoded = jsonDecode(raw) as Map<String, dynamic>;
      final snapshot = LocationSnapshot.fromJson(decoded);
      _current = snapshot;
      return snapshot;
    } catch (_) {
      return null;
    }
  }

  Future<LocationPermission> _ensurePermission({BuildContext? context}) async {
    var permission = await _geolocator.checkPermission();
    if (_isDenied(permission)) {
      final handlerStatus = await Permission.location.status;
      if (handlerStatus.isDenied && context != null) {
        await _showRationale(context);
      }
      permission = await _geolocator.requestPermission();
    }
    return permission;
  }

  bool _isDenied(LocationPermission permission) {
    return permission == LocationPermission.denied || permission == LocationPermission.deniedForever;
  }

  Future<void> _showRationale(BuildContext context) async {
    if (!context.mounted) return;
    await showDialog<void>(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          title: const Text('Enable location access'),
          content: const Text('We use your location to surface nearby jobs and providers. You can continue with approximate results if you prefer.'),
          actions: [
            TextButton(onPressed: () => Navigator.of(dialogContext).pop(), child: const Text('Continue')),
          ],
        );
      },
    );
  }

  LocationSnapshot _snapshotFromPosition(Position position, {required String source}) {
    final h3Index = _indexer.indexFor(position.latitude, position.longitude);
    return LocationSnapshot(
      latitude: position.latitude,
      longitude: position.longitude,
      h3Index: h3Index,
      source: source,
      capturedAt: DateTime.now(),
      isMocked: position.isMocked ?? false,
    );
  }

  Future<LocationSnapshot> _resolveCoarseLocation() async {
    try {
      final payload = await _ipLocationClient.resolve();
      if (payload != null) {
        final latitude = (payload['latitude'] as num).toDouble();
        final longitude = (payload['longitude'] as num).toDouble();
        final h3Index = _indexer.indexFor(latitude, longitude);
        return LocationSnapshot(
          latitude: latitude,
          longitude: longitude,
          h3Index: h3Index,
          source: payload['source']?.toString() ?? 'coarse_ip',
          capturedAt: DateTime.now(),
        );
      }
    } catch (_) {}

    final cached = await loadPersistedSnapshot();
    if (cached != null) {
      return cached;
    }

    // Fallback to San Francisco coordinates configured on the backend.
    return LocationSnapshot(
      latitude: 37.773972,
      longitude: -122.431297,
      h3Index: _indexer.indexFor(37.773972, -122.431297),
      source: 'fallback',
      capturedAt: DateTime.now(),
    );
  }

  void _startListening() {
    if (_positionSubscription != null) {
      return;
    }

    final stream = _geolocator.getPositionStream(
      locationSettings: const LocationSettings(
        accuracy: LocationAccuracy.high,
        distanceFilter: 50,
      ),
    );

    _positionSubscription = stream.listen((position) {
      final now = DateTime.now();
      final lifecycle = WidgetsBinding.instance.lifecycleState;
      final throttle = lifecycle == AppLifecycleState.resumed ? _foregroundThrottle : _backgroundThrottle;
      if (_lastPreciseUpdate != null && now.difference(_lastPreciseUpdate!) < throttle) {
        return;
      }

      final snapshot = _snapshotFromPosition(position, source: 'precise_stream');
      if (_current != null && _current!.h3Index == snapshot.h3Index) {
        return;
      }

      _lastPreciseUpdate = now;
      _publish(snapshot);
    });
  }

  void _publish(LocationSnapshot snapshot) {
    _current = snapshot;
    _preferences.setString(_storageKey, jsonEncode(snapshot.toJson()));
    if (!_controller.isClosed) {
      _controller.add(snapshot);
    }
  }

  Future<void> dispose() async {
    await _positionSubscription?.cancel();
    await _controller.close();
  }
}
