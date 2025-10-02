import 'dart:convert';

import 'package:flutter/services.dart' show AssetBundle, rootBundle;

import '../model/environment_secret_model.dart';

class EnvironmentChecklistService {
  const EnvironmentChecklistService({AssetBundle? bundle})
      : _bundle = bundle ?? rootBundle;

  final AssetBundle _bundle;
  static const String assetPath = 'assets/config/environment_checklist.json';

  Future<EnvironmentChecklist> loadChecklist() async {
    final payload = await _bundle.loadString(assetPath);
    final Map<String, dynamic> jsonMap =
        json.decode(payload) as Map<String, dynamic>;
    return EnvironmentChecklist.fromJson(jsonMap);
  }
}
