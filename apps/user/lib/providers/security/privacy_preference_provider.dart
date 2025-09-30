import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../models/privacy_preference.dart';
import '../../services/auth/auth_token_store.dart';
import '../../services/security/privacy_preferences_service.dart';

class PrivacyPreferenceProvider extends ChangeNotifier {
  PrivacyPreferenceProvider({
    required SharedPreferences preferences,
    PrivacyPreferencesService? service,
    AuthTokenStore? tokenStore,
  })  : _tokenStore = tokenStore ?? AuthTokenStore(preferences: preferences),
        _service = _resolveService(
          preferences: preferences,
          tokenStore: tokenStore,
          override: service,
        );

  final PrivacyPreferencesService _service;
  final AuthTokenStore _tokenStore;

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  bool _isSubmitting = false;
  bool get isSubmitting => _isSubmitting;

  List<PrivacyPreference> _preferences = [];
  List<PrivacyPreference> get preferences => List.unmodifiable(_preferences);

  String? _lastExportToken;
  String? get lastExportToken => _lastExportToken;

  static PrivacyPreferencesService _resolveService({
    required SharedPreferences preferences,
    AuthTokenStore? tokenStore,
    PrivacyPreferencesService? override,
  }) {
    if (override != null) {
      return override;
    }

    final resolvedStore = tokenStore ?? AuthTokenStore(preferences: preferences);
    return PrivacyPreferencesService(
      preferences: preferences,
      tokenProvider: resolvedStore.read,
    );
  }

  Future<void> fetchPreferences() async {
    _isLoading = true;
    notifyListeners();
    try {
      final token = await _tokenStore.read();
      _preferences = await _service.loadPreferences();
      if (token == null) {
        _preferences = _preferences
            .map((pref) => pref.copyWith(granted: pref.key == 'essential'))
            .toList();
      }
    } catch (error, stackTrace) {
      debugPrint('Failed to load privacy preferences: $error');
      debugPrintStack(stackTrace: stackTrace);
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> updatePreference(String key, bool granted) async {
    _isSubmitting = true;
    notifyListeners();
    try {
      final updates = {for (final pref in _preferences) pref.key: pref.granted};
      updates[key] = granted;
      _preferences = await _service.updatePreferences(updates);
    } catch (error, stackTrace) {
      debugPrint('Failed to update privacy preference: $error');
      debugPrintStack(stackTrace: stackTrace);
    } finally {
      _isSubmitting = false;
      notifyListeners();
    }
  }

  Future<String> requestExport() async {
    _isSubmitting = true;
    notifyListeners();
    try {
      _lastExportToken = await _service.requestExport();
      return _lastExportToken!;
    } catch (error, stackTrace) {
      debugPrint('Failed to request data export: $error');
      debugPrintStack(stackTrace: stackTrace);
      rethrow;
    } finally {
      _isSubmitting = false;
      notifyListeners();
    }
  }

  Future<String> requestDeletion() async {
    _isSubmitting = true;
    notifyListeners();
    try {
      return await _service.requestDeletion();
    } catch (error, stackTrace) {
      debugPrint('Failed to request account deletion: $error');
      debugPrintStack(stackTrace: stackTrace);
      rethrow;
    } finally {
      _isSubmitting = false;
      notifyListeners();
    }
  }
}
