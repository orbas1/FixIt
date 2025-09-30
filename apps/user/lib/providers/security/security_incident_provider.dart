import 'package:flutter/foundation.dart';
import 'package:get_it/get_it.dart';

import '../../models/security_incident.dart';
import '../../services/security/security_incident_service.dart';

class SecurityIncidentProvider with ChangeNotifier {
  SecurityIncidentProvider({SecurityIncidentService? incidentService})
      : _incidentService =
            incidentService ?? GetIt.I<SecurityIncidentService>();

  final SecurityIncidentService _incidentService;

  final List<SecurityIncident> _incidents = [];
  bool _isLoading = false;
  String? _error;

  List<SecurityIncident> get incidents => List.unmodifiable(_incidents);
  bool get isLoading => _isLoading;
  String? get error => _error;

  Future<void> loadIncidents({bool forceRefresh = false}) async {
    if (_isLoading) return;
    _isLoading = true;
    _error = null;
    if (forceRefresh) {
      _incidents.clear();
    }
    notifyListeners();

    try {
      final results = await _incidentService.fetchIncidents();
      _incidents
        ..clear()
        ..addAll(results);
    } catch (error, stackTrace) {
      debugPrint('SecurityIncidentProvider load error: $error');
      debugPrintStack(stackTrace: stackTrace);
      _error = error.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
