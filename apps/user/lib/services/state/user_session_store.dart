import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../common/session.dart';
import '../../models/user_model.dart';

/// Stores the authenticated user profile and exposes derived flags that are
/// required for navigation guards (e.g. KYC completion).
class UserSessionStore extends ChangeNotifier {
  UserSessionStore({
    required SharedPreferences preferences,
  }) : _preferences = preferences {
    _hydrateFromCache();
  }

  final SharedPreferences _preferences;
  final Session _session = Session();

  UserModel? _user;

  /// Latest hydrated user profile if available.
  UserModel? get user => _user;

  /// Whether the current session represents an authenticated account.
  bool get isAuthenticated =>
      (_preferences.getString(_session.accessToken) ?? '').isNotEmpty;

  /// Whether the user has completed identity/KYC verification. We map this to
  /// the `isVerified` flag exposed by the backend user payload.
  bool get isKycVerified => (_user?.isVerified ?? 0) == 1;

  /// Persist the provided [user] model and update downstream listeners.
  Future<void> cacheUser(UserModel user) async {
    _user = user;
    try {
      final payload = jsonEncode(user.toJson());
      await _preferences.setString(_session.user, payload);
    } catch (error, stackTrace) {
      if (kDebugMode) {
        debugPrint('UserSessionStore: failed to encode user payload: $error');
        debugPrintStack(stackTrace: stackTrace);
      }
    }
    notifyListeners();
  }

  /// Clears the cached user profile. Invoked on logout/session expiry.
  Future<void> clear() async {
    _user = null;
    await _preferences.remove(_session.user);
    notifyListeners();
  }

  void _hydrateFromCache() {
    final cached = _preferences.getString(_session.user);
    if (cached == null || cached.isEmpty) {
      return;
    }

    try {
      final Map<String, dynamic> decoded =
          jsonDecode(cached) as Map<String, dynamic>;
      _user = UserModel.fromJson(decoded);
    } catch (error, stackTrace) {
      if (kDebugMode) {
        debugPrint('UserSessionStore: failed to hydrate cache: $error');
        debugPrintStack(stackTrace: stackTrace);
      }
    }
  }
}
