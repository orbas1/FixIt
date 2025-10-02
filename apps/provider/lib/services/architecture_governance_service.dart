import 'dart:convert';

import 'package:flutter/services.dart' show AssetBundle, rootBundle;

import '../model/architecture_governance_model.dart';

class ArchitectureGovernanceService {
  const ArchitectureGovernanceService({AssetBundle? bundle})
      : _bundle = bundle ?? rootBundle;

  final AssetBundle _bundle;
  static const String assetPath =
      'assets/config/architecture_governance_checklist.json';

  Future<ArchitectureGovernanceChecklist> loadChecklist() async {
    final payload = await _bundle.loadString(assetPath);
    final Map<String, dynamic> jsonMap =
        json.decode(payload) as Map<String, dynamic>;
    return ArchitectureGovernanceChecklist.fromJson(jsonMap);
  }
}
