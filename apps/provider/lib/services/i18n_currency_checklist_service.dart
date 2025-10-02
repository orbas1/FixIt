import 'dart:convert';

import 'package:flutter/services.dart' show AssetBundle, rootBundle;

import '../model/i18n_currency_checklist_model.dart';

class I18nCurrencyChecklistService {
  const I18nCurrencyChecklistService({AssetBundle? bundle})
      : _bundle = bundle ?? rootBundle;

  final AssetBundle _bundle;
  static const String assetPath =
      'assets/config/i18n_currency_checklist.json';

  Future<I18nCurrencyChecklist> loadChecklist() async {
    final payload = await _bundle.loadString(assetPath);
    final Map<String, dynamic> jsonMap =
        json.decode(payload) as Map<String, dynamic>;
    return I18nCurrencyChecklist.fromJson(jsonMap);
  }
}
