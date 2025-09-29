import 'package:hive/hive.dart';

import '../../models/dispute_model.dart';
import '../environment.dart';
import 'dispute_api_client.dart';

class DisputeRepository {
  DisputeRepository({DisputeApiClient? apiClient, HiveInterface? hive})
      : _apiClient =
            apiClient ?? DisputeApiClient(hive: hive ?? Hive, baseUrl: apiUrl),
        _hive = hive ?? Hive;

  final DisputeApiClient _apiClient;
  final HiveInterface _hive;

  Future<List<DisputeModel>> all({bool preferCache = false}) async {
    if (preferCache) {
      final cached = await _readCache();
      if (cached.isNotEmpty) {
        return cached;
      }
    }

    return _apiClient.listDisputes();
  }

  Future<DisputeModel> find(int id, {bool preferCache = false}) async {
    if (preferCache) {
      final cached = await _readCache();
      for (final dispute in cached) {
        if (dispute.id == id) {
          return dispute;
        }
      }
    }

    return _apiClient.getDispute(id);
  }

  Future<DisputeModel> postMessage(
    int id,
    String body, {
    String visibility = 'parties',
    List<dynamic>? attachments,
  }) {
    return _apiClient.postMessage(
      id,
      body,
      visibility: visibility,
      attachments: attachments,
    );
  }

  Future<DisputeModel> advance(int id, String stage, {String? note}) {
    return _apiClient.advanceStage(id, stage, note: note);
  }

  Future<DisputeModel> settle(
    int id, {
    required double releaseAmount,
    required double refundAmount,
    Map<String, dynamic>? resolution,
  }) {
    return _apiClient.settle(
      id,
      releaseAmount: releaseAmount,
      refundAmount: refundAmount,
      resolutionDetails: resolution,
    );
  }

  Future<List<DisputeModel>> _readCache() async {
    const boxName = 'dispute_cache_v1';
    if (!_hive.isBoxOpen(boxName)) {
      await _hive.openBox<String>(boxName);
    }

    final box = _hive.box<String>(boxName);
    final cached = box.get('disputes');
    if (cached == null) {
      return [];
    }

    return DisputeCacheEntry.decodeList(cached)
        .map((entry) => entry.dispute)
        .toList();
  }
}
