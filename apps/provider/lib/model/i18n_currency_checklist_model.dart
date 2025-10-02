import 'package:collection/collection.dart';

import 'currency_model.dart';

class I18nCurrencyChecklist {
  I18nCurrencyChecklist({
    required this.generatedAt,
    required this.locales,
    required this.currencyProfiles,
  });

  final DateTime generatedAt;
  final List<I18nLocaleStatus> locales;
  final Map<String, I18nCurrencyProfile> currencyProfiles;

  factory I18nCurrencyChecklist.fromJson(Map<String, dynamic> json) {
    final generatedAtRaw = json['generated_at'] as String?;
    final generatedAt = generatedAtRaw != null
        ? DateTime.tryParse(generatedAtRaw)?.toUtc()
        : null;
    final localesJson = json['locales'] as List<dynamic>? ?? const [];
    final profilesJson = json['currency_profiles'] as List<dynamic>? ?? const [];
    return I18nCurrencyChecklist(
      generatedAt: generatedAt ?? DateTime.fromMillisecondsSinceEpoch(0, isUtc: true),
      locales: localesJson
          .map((entry) => I18nLocaleStatus.fromJson(entry as Map<String, dynamic>))
          .toList(growable: false),
      currencyProfiles: {
        for (final profile in profilesJson)
          (profile['code'] as String?) ?? 'UNKNOWN':
              I18nCurrencyProfile.fromJson(profile as Map<String, dynamic>),
      },
    );
  }

  I18nCurrencyProfile? profileForLocale(String code) {
    final locale = locales.firstWhereOrNull((element) => element.currencyProfileCode == code);
    if (locale == null) {
      return null;
    }
    return currencyProfiles[locale.currencyProfileCode];
  }
}

class I18nLocaleStatus {
  I18nLocaleStatus({
    required this.code,
    required this.name,
    required this.rtl,
    required this.expectation,
    required this.currencyProfileCode,
  });

  final String code;
  final String name;
  final bool rtl;
  final LocaleExpectation expectation;
  final String currencyProfileCode;

  factory I18nLocaleStatus.fromJson(Map<String, dynamic> json) {
    return I18nLocaleStatus(
      code: (json['code'] as String? ?? '').trim(),
      name: (json['name'] as String? ?? '').trim(),
      rtl: json['rtl'] as bool? ?? false,
      expectation: LocaleExpectation.fromJson(
        json['expected'] as Map<String, dynamic>? ?? const {},
      ),
      currencyProfileCode: (json['currency_profile'] as String? ?? '').trim(),
    );
  }
}

class LocaleExpectation {
  const LocaleExpectation({
    required this.flutterCoverage,
    required this.laravelCoverage,
    required this.missingFlutterKeys,
    required this.missingLaravelKeys,
  });

  final double flutterCoverage;
  final double laravelCoverage;
  final List<String> missingFlutterKeys;
  final List<String> missingLaravelKeys;

  factory LocaleExpectation.fromJson(Map<String, dynamic> json) {
    return LocaleExpectation(
      flutterCoverage: (json['flutter_coverage'] as num? ?? 0).toDouble(),
      laravelCoverage: (json['laravel_coverage'] as num? ?? 0).toDouble(),
      missingFlutterKeys: List<String>.from(
        (json['missing_flutter_keys'] as List<dynamic>? ?? const [])
            .map((entry) => entry.toString()),
      ),
      missingLaravelKeys: List<String>.from(
        (json['missing_laravel_keys'] as List<dynamic>? ?? const [])
            .map((entry) => entry.toString()),
      ),
    );
  }

  bool get isPerfect => flutterCoverage >= 1 && laravelCoverage >= 1;
}

class I18nCurrencyProfile {
  I18nCurrencyProfile({
    required this.code,
    required this.symbol,
    required this.symbolPosition,
    required this.decimalDigits,
    required this.decimalSeparator,
    required this.thousandsSeparator,
    required this.samples,
  });

  final String code;
  final String symbol;
  final String symbolPosition;
  final int decimalDigits;
  final String decimalSeparator;
  final String thousandsSeparator;
  final List<I18nCurrencySample> samples;

  factory I18nCurrencyProfile.fromJson(Map<String, dynamic> json) {
    return I18nCurrencyProfile(
      code: (json['code'] as String? ?? '').trim(),
      symbol: (json['symbol'] as String? ?? '').trim(),
      symbolPosition: (json['symbol_position'] as String? ?? 'left').toLowerCase(),
      decimalDigits: (json['decimal_digits'] as num? ?? 2).toInt(),
      decimalSeparator: (json['decimal_separator'] as String? ?? '.').trim(),
      thousandsSeparator: (json['thousands_separator'] as String? ?? ',').trim(),
      samples: List<Map<String, dynamic>>.from(json['samples'] as List<dynamic>? ?? const [])
          .map(I18nCurrencySample.fromJson)
          .toList(growable: false),
    );
  }

  CurrencyModel toCurrencyModel() {
    return CurrencyModel(
      code: code,
      symbol: symbol,
      symbolPosition: symbolPosition,
      noOfDecimal: decimalDigits,
      exchangeRate: 1,
      decimalSeparator: decimalSeparator,
      thousandsSeparator: thousandsSeparator,
    );
  }
}

class I18nCurrencySample {
  I18nCurrencySample({
    required this.amount,
    required this.expected,
    this.locale,
  });

  final double amount;
  final String expected;
  final String? locale;

  factory I18nCurrencySample.fromJson(Map<String, dynamic> json) {
    return I18nCurrencySample(
      amount: (json['amount'] as num? ?? 0).toDouble(),
      expected: (json['expected'] as String? ?? '').trim(),
      locale: (json['locale'] as String?)?.trim(),
    );
  }
}
