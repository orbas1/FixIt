import 'package:flutter/foundation.dart';

import '../../model/architecture_governance_model.dart';
import '../../services/architecture_governance_service.dart';

class ArchitectureGovernanceProvider with ChangeNotifier {
  ArchitectureGovernanceProvider({ArchitectureGovernanceService? service})
      : _service = service ?? const ArchitectureGovernanceService();

  final ArchitectureGovernanceService _service;

  ArchitectureGovernanceChecklist? _checklist;
  bool _isLoading = false;
  String? _errorMessage;

  ArchitectureGovernanceChecklist? get checklist => _checklist;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  DateTime get generatedAt =>
      _checklist?.generatedAt ?? DateTime.fromMillisecondsSinceEpoch(0, isUtc: true);

  GovernanceBoard? get board => _checklist?.board;

  List<GovernanceCadence> get cadences => _checklist?.cadences ?? const [];

  List<GovernanceDecision> get decisions => _checklist?.decisions ?? const [];

  List<GovernanceCapability> get capabilities =>
      _checklist?.capabilities ?? const [];

  List<GovernanceRisk> get risks => _checklist?.risks ?? const [];

  GovernanceQualityGates get qualityGates =>
      _checklist?.qualityGates ?? const GovernanceQualityGates(
        readinessStaleDays: 7,
        decisionRenewalWarningDays: 30,
      );

  bool get quorumSatisfied => board?.quorumSatisfied ?? false;

  double get dependencyCoverage => _checklist?.dependencyCoverage ?? 0;

  Map<String, List<GovernanceCapability>> get capabilitiesByQuarter =>
      _checklist?.capabilitiesByQuarter ??
      const <String, List<GovernanceCapability>>{};

  List<GovernanceDecision> get decisionsRequiringAttention {
    if (_checklist == null) {
      return const <GovernanceDecision>[];
    }
    return _checklist!
        .decisionsRequiringAttention(DateTime.now().toUtc());
  }

  List<GovernanceCapability> get staleCapabilities {
    if (_checklist == null) {
      return const <GovernanceCapability>[];
    }
    return _checklist!.staleCapabilities(DateTime.now().toUtc());
  }

  bool get hasBlockingIssues =>
      staleCapabilities.isNotEmpty ||
      decisions.any((decision) => decision.status == 'accepted' && !decision.hasEvidence);

  Future<void> bootstrap() => _load();

  Future<void> refresh() => _load(force: true);

  Future<void> _load({bool force = false}) async {
    if (_isLoading && !force) {
      return;
    }
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
    try {
      _checklist = await _service.loadChecklist();
    } catch (error, stackTrace) {
      debugPrint('Failed to load architecture governance checklist: $error');
      debugPrintStack(stackTrace: stackTrace);
      _errorMessage = error.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
