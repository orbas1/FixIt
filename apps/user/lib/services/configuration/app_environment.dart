import 'dart:convert';

import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/services.dart';

class AppEnvironment {
  const AppEnvironment({
    required this.app,
    required this.api,
    required this.store,
    required this.firebase,
    required this.telemetry,
  });

  final AppInfo app;
  final ApiConfiguration api;
  final StoreConfiguration store;
  final FirebaseConfiguration firebase;
  final TelemetryConfiguration telemetry;

  static Future<AppEnvironment> load({String? flavor}) async {
    final bundle = rootBundle;
    final normalizedFlavor = (flavor ?? const String.fromEnvironment('APP_ENV', defaultValue: 'dev')).toLowerCase();
    final candidatePaths = <String>[
      'assets/config/environment.$normalizedFlavor.json',
      'assets/config/environment.dev.json',
    ];

    dynamic lastError;
    for (final path in candidatePaths) {
      try {
        final raw = await bundle.loadString(path);
        final jsonMap = jsonDecode(raw) as Map<String, dynamic>;
        return AppEnvironment.fromJson(jsonMap);
      } on FlutterError catch (error) {
        lastError = error;
      }
    }

    throw StateError('Unable to load environment configuration. Last error: $lastError');
  }

  factory AppEnvironment.fromJson(Map<String, dynamic> json) {
    return AppEnvironment(
      app: AppInfo.fromJson(json['app'] as Map<String, dynamic>),
      api: ApiConfiguration.fromJson(json['api'] as Map<String, dynamic>),
      store: StoreConfiguration.fromJson(json['store'] as Map<String, dynamic>),
      firebase: FirebaseConfiguration.fromJson(json['firebase'] as Map<String, dynamic>),
      telemetry: TelemetryConfiguration.fromJson(json['telemetry'] as Map<String, dynamic>),
    );
  }
}

class AppInfo {
  const AppInfo({
    required this.name,
    required this.flavor,
    required this.defaultLocale,
    required this.supportedLocales,
    required this.minimumSupportedVersion,
  });

  final String name;
  final String flavor;
  final String defaultLocale;
  final List<String> supportedLocales;
  final String minimumSupportedVersion;

  factory AppInfo.fromJson(Map<String, dynamic> json) {
    return AppInfo(
      name: json['name'] as String,
      flavor: json['flavor'] as String,
      defaultLocale: json['defaultLocale'] as String,
      supportedLocales: (json['supportedLocales'] as List<dynamic>).cast<String>(),
      minimumSupportedVersion: json['minimumSupportedVersion'] as String,
    );
  }
}

class ApiConfiguration {
  const ApiConfiguration({
    required this.baseUrl,
    required this.paymentUrl,
    required this.connectTimeoutMs,
    required this.receiveTimeoutMs,
  });

  final String baseUrl;
  final String paymentUrl;
  final int connectTimeoutMs;
  final int receiveTimeoutMs;

  factory ApiConfiguration.fromJson(Map<String, dynamic> json) {
    return ApiConfiguration(
      baseUrl: json['baseUrl'] as String,
      paymentUrl: json['paymentUrl'] as String,
      connectTimeoutMs: json['connectTimeoutMs'] as int,
      receiveTimeoutMs: json['receiveTimeoutMs'] as int,
    );
  }
}

class StoreConfiguration {
  const StoreConfiguration({
    required this.playStoreUrl,
    required this.userAppPlayStoreUrl,
  });

  final String playStoreUrl;
  final String userAppPlayStoreUrl;

  factory StoreConfiguration.fromJson(Map<String, dynamic> json) {
    return StoreConfiguration(
      playStoreUrl: json['playStoreUrl'] as String,
      userAppPlayStoreUrl: json['userAppPlayStoreUrl'] as String,
    );
  }
}

class FirebaseConfiguration {
  const FirebaseConfiguration({
    required this.apiKey,
    required this.appId,
    required this.messagingSenderId,
    required this.projectId,
    required this.storageBucket,
    required this.iosBundleId,
    required this.androidClientId,
  });

  final String apiKey;
  final String appId;
  final String messagingSenderId;
  final String projectId;
  final String storageBucket;
  final String iosBundleId;
  final String androidClientId;

  FirebaseOptions toFirebaseOptions() {
    return FirebaseOptions(
      apiKey: apiKey,
      appId: appId,
      messagingSenderId: messagingSenderId,
      projectId: projectId,
      storageBucket: storageBucket,
      iosBundleId: iosBundleId,
    );
  }

  factory FirebaseConfiguration.fromJson(Map<String, dynamic> json) {
    return FirebaseConfiguration(
      apiKey: json['apiKey'] as String,
      appId: json['appId'] as String,
      messagingSenderId: json['messagingSenderId'] as String,
      projectId: json['projectId'] as String,
      storageBucket: json['storageBucket'] as String,
      iosBundleId: json['iosBundleId'] as String,
      androidClientId: json['androidClientId'] as String,
    );
  }
}

class TelemetryConfiguration {
  const TelemetryConfiguration({
    required this.analyticsEnabled,
    required this.crashlyticsEnabled,
    required this.sentryDsn,
  });

  final bool analyticsEnabled;
  final bool crashlyticsEnabled;
  final String sentryDsn;

  factory TelemetryConfiguration.fromJson(Map<String, dynamic> json) {
    return TelemetryConfiguration(
      analyticsEnabled: json['analyticsEnabled'] as bool,
      crashlyticsEnabled: json['crashlyticsEnabled'] as bool,
      sentryDsn: json['sentryDsn'] as String,
    );
  }
}
