import 'package:flutter/foundation.dart';

import '../../model/environment_secret_model.dart';
import '../../services/environment_checklist_service.dart';

class EnvironmentChecklistProvider with ChangeNotifier {
  EnvironmentChecklistProvider({EnvironmentChecklistService? service})
      : _service = service ?? const EnvironmentChecklistService();

  final EnvironmentChecklistService _service;

  EnvironmentChecklist? _checklist;
  bool _isLoading = false;
  String? _errorMessage;
  SecretRiskFilter _filter = SecretRiskFilter.all;
  DateTime _reference = DateTime.now().toUtc();

  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  EnvironmentChecklist? get checklist => _checklist;
  SecretRiskFilter get filter => _filter;
  DateTime get generatedAt => _checklist?.generatedAt ?? _reference;
  DateTime get referenceTime => _reference;

  Future<void> bootstrap() => _load();

  Future<void> refresh() => _load();

  Future<void> _load() async {
    if (_isLoading) return;
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
    try {
      _reference = DateTime.now().toUtc();
      _checklist = await _service.loadChecklist();
    } catch (error, stackTrace) {
      debugPrint('Failed to load environment checklist: $error');
      debugPrintStack(stackTrace: stackTrace);
      _errorMessage = error.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void setFilter(SecretRiskFilter filter) {
    if (_filter == filter) return;
    _filter = filter;
    notifyListeners();
  }

  int get namespaceCount => _checklist?.namespaces.length ?? 0;
  int get secretCount => _checklist?.secrets.length ?? 0;
  int get overdueCount =>
      _checklist?.secrets.where((secret) => secret.isRotationOverdue(_reference)).length ?? 0;
  int get dueSoonCount => _checklist?.secrets
          .where((secret) =>
              secret.isRotationDueSoon(_reference, dueSoonWindowDays: 14) &&
              !secret.isRotationOverdue(_reference))
          .length ??
      0;
  int get validationAlertsCount =>
      _checklist?.secrets.where((secret) => secret.isValidationStale(_reference)).length ?? 0;

  List<EnvironmentSecret> get secrets {
    final items = _checklist?.secrets ?? const [];
    final sorted = [...items];
    sorted.sort((a, b) {
      final severityCompare =
          b.riskLevel(_reference).index.compareTo(a.riskLevel(_reference).index);
      if (severityCompare != 0) {
        return severityCompare;
      }
      final aDue = a.nextRotationDueAt ?? DateTime.fromMillisecondsSinceEpoch(0);
      final bDue = b.nextRotationDueAt ?? DateTime.fromMillisecondsSinceEpoch(0);
      return aDue.compareTo(bDue);
    });

    return sorted.where((secret) {
      switch (_filter) {
        case SecretRiskFilter.all:
          return true;
        case SecretRiskFilter.dueSoon:
          return secret.riskLevel(_reference) == SecretRiskLevel.dueSoon;
        case SecretRiskFilter.overdue:
          return secret.riskLevel(_reference) == SecretRiskLevel.overdue;
      }
    }).toList(growable: false);
  }

  Iterable<EnvironmentSecret> secretsForNamespace(
      EnvironmentNamespace namespace) {
    final map = _checklist?.secretsById ?? const {};
    return namespace.secretRefs.map((id) => map[id]).whereType<EnvironmentSecret>();
  }
}

enum SecretRiskFilter { all, dueSoon, overdue }
