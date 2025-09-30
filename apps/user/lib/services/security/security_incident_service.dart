import 'dart:convert';

import '../api_methods.dart';
import '../api_service.dart';
import '../../models/security_incident.dart';

class SecurityIncidentService {
  SecurityIncidentService({ApiServices? apiServices, ApiMethods? apiMethods})
      : _apiServices = apiServices ?? ApiServices(),
        _apiMethods = apiMethods ?? ApiMethods();

  final ApiServices _apiServices;
  final ApiMethods _apiMethods;

  Future<List<SecurityIncident>> fetchIncidents() async {
    final response = await _apiServices.getApi(
      _apiMethods.securityIncidents,
      null,
      isToken: true,
      isData: true,
    );

    if (!response.isSuccess) {
      throw StateError(response.message);
    }

    final data = response.data;
    if (data is Map<String, dynamic> && data['data'] is List) {
      return (data['data'] as List)
          .map((item) => SecurityIncident.fromJson(
              Map<String, dynamic>.from(item as Map)))
          .toList();
    }

    if (data is List) {
      return data
          .map((item) => SecurityIncident.fromJson(
              Map<String, dynamic>.from(item as Map)))
          .toList();
    }

    throw const FormatException('Unexpected security incidents payload');
  }

  Future<SecurityIncident> fetchIncident(String publicId) async {
    final endpoint = '${_apiMethods.securityIncidents}/$publicId';
    final response = await _apiServices.getApi(
      endpoint,
      null,
      isToken: true,
      isData: true,
    );

    if (!response.isSuccess) {
      throw StateError(response.message);
    }

    if (response.data is Map<String, dynamic>) {
      final map = Map<String, dynamic>.from(response.data as Map);
      if (map.containsKey('data')) {
        return SecurityIncident.fromJson(
            Map<String, dynamic>.from(map['data'] as Map));
      }
      return SecurityIncident.fromJson(map);
    }

    if (response.data is String) {
      return SecurityIncident.fromJson(
          jsonDecode(response.data as String) as Map<String, dynamic>);
    }

    throw const FormatException('Unexpected security incident payload');
  }
}
