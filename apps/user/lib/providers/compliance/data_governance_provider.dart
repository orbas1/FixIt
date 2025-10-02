import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../models/data_governance_asset.dart';
import '../../services/auth/auth_token_store.dart';
import '../../services/compliance/data_governance_service.dart';

class DataGovernanceProvider extends ChangeNotifier {
  factory DataGovernanceProvider({
    required SharedPreferences preferences,
    DataGovernanceService? service,
    AuthTokenStore? tokenStore,
  }) {
    final resolvedStore = tokenStore ?? AuthTokenStore(preferences: preferences);
    final resolvedService = service ?? DataGovernanceService(
      preferences: preferences,
      tokenProvider: resolvedStore.read,
    );

    return DataGovernanceProvider._(resolvedService);
  }

  DataGovernanceProvider._(this._service);

  final DataGovernanceService _service;

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  bool _isRefreshing = false;
  bool get isRefreshing => _isRefreshing;

  String? _errorMessage;
  String? get errorMessage => _errorMessage;

  List<DataGovernanceAsset> _assets = const [];
  List<DataGovernanceAsset> get assets => List.unmodifiable(_assets);

  Future<void> loadAssets() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _assets = await _service.fetchAssets();
    } catch (error, stackTrace) {
      _errorMessage = 'Unable to load governance assets';
      debugPrint('DataGovernanceProvider error: $error');
      debugPrintStack(stackTrace: stackTrace);
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<DataGovernanceAsset?> ensureCoverage(
    int assetId, {
    List<Map<String, dynamic>>? mitigationActions,
    List<Map<String, dynamic>>? residualRisks,
  }) async {
    _isRefreshing = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final updated = await _service.ensureDpia(
        assetId,
        mitigationActions: mitigationActions,
        residualRisks: residualRisks,
      );
      _assets = _assets
          .map((asset) => asset.id == updated.id
              ? asset.copyWith(
                  dpiaRecords: updated.dpiaRecords,
                  complianceIssues: updated.complianceIssues,
                  complianceStatus: updated.complianceStatus,
                )
              : asset)
          .toList();
      return updated;
    } catch (error, stackTrace) {
      _errorMessage = 'Unable to reconcile DPIA coverage';
      debugPrint('DataGovernanceProvider ensure error: $error');
      debugPrintStack(stackTrace: stackTrace);
      return null;
    } finally {
      _isRefreshing = false;
      notifyListeners();
    }
  }
}
