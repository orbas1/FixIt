import 'package:hive/hive.dart';

import '../../models/escrow_model.dart';
import '../environment.dart';
import 'escrow_api_client.dart';

class EscrowRepository {
  EscrowRepository({EscrowApiClient? apiClient, HiveInterface? hive})
      : _apiClient =
            apiClient ?? EscrowApiClient(hive: hive ?? Hive, baseUrl: apiUrl),
        _hive = hive ?? Hive;

  final EscrowApiClient _apiClient;
  final HiveInterface _hive;

  Future<List<EscrowModel>> all({bool preferCache = false}) async {
    if (preferCache) {
      final cached = await _readCache();
      if (cached.isNotEmpty) {
        return cached;
      }
    }

    return _apiClient.listEscrows();
  }

  Future<EscrowModel> find(int id, {bool preferCache = false}) async {
    if (preferCache) {
      try {
        final boxName = 'escrow_cache_v1';
        if (!_hive.isBoxOpen(boxName)) {
          await _hive.openBox<String>(boxName);
        }
        final box = _hive.box<String>(boxName);
        final cached = box.get('escrows');
        if (cached != null) {
          final entries = EscrowCacheEntry.decodeList(cached);
          for (final entry in entries) {
            if (entry.payload.id == id) {
              return entry.payload;
            }
          }
        }
      } catch (_) {
        // Fall back to network
      }
    }

    return _apiClient.fetchEscrow(id);
  }

  Future<EscrowModel> release(int id, double amount) =>
      _apiClient.release(id, amount);

  Future<EscrowModel> refund(int id, double amount) =>
      _apiClient.refund(id, amount);

  Future<List<EscrowModel>> _readCache() async {
    const boxName = 'escrow_cache_v1';
    if (!_hive.isBoxOpen(boxName)) {
      await _hive.openBox<String>(boxName);
    }

    final box = _hive.box<String>(boxName);
    final cached = box.get('escrows');
    if (cached == null) {
      return [];
    }

    return EscrowCacheEntry.decodeList(cached)
        .map((entry) => entry.payload)
        .toList();
  }
}
