import 'dart:developer';
import 'dart:io';

import '../config.dart';

enum EnvironmentTarget { production, staging, qa, development }

class EnvironmentProfile {
  final EnvironmentTarget target;
  final Uri apiBase;
  final Uri paymentBase;
  final Uri providerPortal;
  final bool enableVerboseLogging;
  final String description;

  const EnvironmentProfile({
    required this.target,
    required this.apiBase,
    required this.paymentBase,
    required this.providerPortal,
    this.enableVerboseLogging = false,
    this.description = '',
  });
}

class EnvironmentConfig {
  static const EnvironmentTarget _fallbackTarget = EnvironmentTarget.staging;

  static final Map<EnvironmentTarget, EnvironmentProfile> _profiles = {
    EnvironmentTarget.production: EnvironmentProfile(
      target: EnvironmentTarget.production,
      apiBase: Uri.parse('https://api.fixitmarketplace.com/api/provider'),
      paymentBase: Uri.parse('https://payments.fixitmarketplace.com'),
      providerPortal: Uri.parse('https://provider.fixitmarketplace.com'),
      description: 'Primary production stack serving global FixIt providers.',
    ),
    EnvironmentTarget.staging: EnvironmentProfile(
      target: EnvironmentTarget.staging,
      apiBase: Uri.parse('https://staging-api.fixitmarketplace.com/api/provider'),
      paymentBase: Uri.parse('https://staging-payments.fixitmarketplace.com'),
      providerPortal: Uri.parse('https://staging-provider.fixitmarketplace.com'),
      description: 'Pre-production environment for release validation and contract tests.',
    ),
    EnvironmentTarget.qa: EnvironmentProfile(
      target: EnvironmentTarget.qa,
      apiBase: Uri.parse('https://qa-api.fixitmarketplace.com/api/provider'),
      paymentBase: Uri.parse('https://qa-payments.fixitmarketplace.com'),
      providerPortal: Uri.parse('https://qa-provider.fixitmarketplace.com'),
      description: 'Quality assurance environment used for automation, chaos drills, and dispute rehearsals.',
      enableVerboseLogging: true,
    ),
    EnvironmentTarget.development: EnvironmentProfile(
      target: EnvironmentTarget.development,
      apiBase: Uri.parse('http://localhost:8000/api/provider'),
      paymentBase: Uri.parse('http://localhost:8100'),
      providerPortal: Uri.parse('http://localhost:3000'),
      description: 'Local developer sandbox served through Docker or Laravel Valet.',
      enableVerboseLogging: true,
    ),
  };

  static EnvironmentProfile get current =>
      _profiles[_resolveTarget()] ?? _profiles[_fallbackTarget]!;

  static EnvironmentTarget _resolveTarget() {
    const buildTime = String.fromEnvironment('FIXIT_ENV');
    if (buildTime.isNotEmpty) {
      final target = _parseTarget(buildTime);
      if (target != null) return target;
    }

    final runtimeEnv = Platform.environment['FIXIT_ENV'] ??
        Platform.environment['APP_ENV'] ??
        Platform.environment['FLUTTER_ENV'];
    if (runtimeEnv != null && runtimeEnv.isNotEmpty) {
      final target = _parseTarget(runtimeEnv);
      if (target != null) return target;
    }

    return _fallbackTarget;
  }

  static EnvironmentTarget? _parseTarget(String raw) {
    switch (raw.toLowerCase()) {
      case 'prod':
      case 'production':
        return EnvironmentTarget.production;
      case 'stg':
      case 'stage':
      case 'staging':
        return EnvironmentTarget.staging;
      case 'qa':
        return EnvironmentTarget.qa;
      case 'dev':
      case 'development':
      case 'local':
        return EnvironmentTarget.development;
      default:
        return null;
    }
  }
}

final EnvironmentProfile _profile = EnvironmentConfig.current;

final String apiUrl = _profile.apiBase.toString();
final String paymentUrl = _profile.paymentBase.toString();
final String providerAppUrl = _profile.providerPortal.toString();

bool get enableVerboseNetworkLogging => _profile.enableVerboseLogging;
EnvironmentProfile get environmentProfile => _profile;

void logActiveEnvironment() {
  log('Environment: ${_profile.target.name} -> ${_profile.apiBase}',
      name: 'EnvironmentConfig');
}

// Global SharedPreferences and Locale
late SharedPreferences sharedPreferences;
String local = appSettingModel!.general!.defaultLanguage!.locale!;

// Initialize SharedPreferences and Locale
Future<void> initializeAppSettings() async {
  sharedPreferences = await SharedPreferences.getInstance();
  local = sharedPreferences.getString('selectedLocale') ?? 'en';
  logActiveEnvironment();
  log('set language:: $local');
}

Map<String, String>? headersToken(
  String? token, {
  bool isLang = false,
  String? localLang,
}) =>
    {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Accept-Lang': isLang ? '$localLang' : local,
      'Authorization': 'Bearer $token',
    };

Map<String, String>? get headers => {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Accept-Lang': local,
    };
