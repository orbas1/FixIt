import 'package:flutter/widgets.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'configuration/app_environment.dart';

class EnvironmentStore {
  EnvironmentStore._();

  static final EnvironmentStore instance = EnvironmentStore._();

  AppEnvironment? _environment;
  SharedPreferences? _preferences;
  Locale _locale = const Locale('en');

  Future<void> configure({
    required AppEnvironment environment,
    required SharedPreferences preferences,
  }) async {
    _environment = environment;
    _preferences = preferences;
    final persistedLocale = preferences.getString('selectedLocale');
    _locale = Locale(persistedLocale ?? environment.app.defaultLocale);
  }

  AppEnvironment get environment {
    final env = _environment;
    if (env == null) {
      throw StateError('Environment has not been configured');
    }
    return env;
  }

  SharedPreferences get preferences {
    final prefs = _preferences;
    if (prefs == null) {
      throw StateError('SharedPreferences has not been configured');
    }
    return prefs;
  }

  Locale get locale => _locale;

  Future<void> updateLocale(Locale locale) async {
    _locale = locale;
    await preferences.setString('selectedLocale', locale.languageCode);
  }

  String get apiBaseUrl => environment.api.baseUrl;
  String get paymentBaseUrl => environment.api.paymentUrl;
  String get playStoreUrl => environment.store.playStoreUrl;
  String get userAppPlayStoreUrl => environment.store.userAppPlayStoreUrl;
  RealtimeConfiguration get realtime => environment.realtime;
  Uri get refreshUri => environment.api.refreshUri(apiBaseUrl);
  String get realtimeAuthUrl => environment.realtime.authUri(apiBaseUrl).toString();

  Map<String, String> headers({String? token}) {
    final headers = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Accept-Lang': _locale.languageCode,
    };
    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }
}

EnvironmentStore get environmentStore => EnvironmentStore.instance;

String get apiUrl => environmentStore.apiBaseUrl;
String get paymentUrl => environmentStore.paymentBaseUrl;
String get playstoreUrl => environmentStore.playStoreUrl;
String get userAppPlayStoreUrl => environmentStore.userAppPlayStoreUrl;
Locale get currentLocale => environmentStore.locale;
RealtimeConfiguration get realtimeConfiguration => environmentStore.realtime;
Uri get refreshUri => environmentStore.refreshUri;

Map<String, String>? headersToken(String? token) => environmentStore.headers(token: token);
Map<String, String>? get headers => environmentStore.headers();
