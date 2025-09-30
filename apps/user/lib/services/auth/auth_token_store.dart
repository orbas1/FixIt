import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../common/session.dart';

class AuthTokenStore {
  AuthTokenStore({
    required SharedPreferences preferences,
    FlutterSecureStorage? secureStorage,
  })  : _preferences = preferences,
        _secureStorage = secureStorage ?? const FlutterSecureStorage();

  final SharedPreferences _preferences;
  final FlutterSecureStorage _secureStorage;
  final Session _session = Session();
  static const String _secureKey = 'fixit_auth_token';

  String? get token => _preferences.getString(Session().accessToken);

  Future<String?> read() async {
    final cached = _preferences.getString(_session.accessToken);
    if (cached != null && cached.isNotEmpty) {
      return cached;
    }

    final secureValue = await _secureStorage.read(key: _secureKey);
    if (secureValue != null && secureValue.isNotEmpty) {
      await _preferences.setString(_session.accessToken, secureValue);
    }
    return secureValue;
  }

  Future<void> write(String token) async {
    await _preferences.setString(_session.accessToken, token);
    await _secureStorage.write(key: _secureKey, value: token);
  }

  Future<void> clear() async {
    await _preferences.remove(_session.accessToken);
    await _secureStorage.delete(key: _secureKey);
  }
}
