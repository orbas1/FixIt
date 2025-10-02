class DataGovernanceAsset {
  DataGovernanceAsset({
    required this.id,
    required this.key,
    required this.name,
    required this.classification,
    required this.requiresDpia,
    required this.retentionPeriodDays,
    required this.complianceStatus,
    required this.complianceIssues,
    required this.residencyPolicies,
    required this.dpiaRecords,
  });

  factory DataGovernanceAsset.fromJson(Map<String, dynamic> json) {
    return DataGovernanceAsset(
      id: json['id'] as int,
      key: json['key'] as String,
      name: json['name'] as String,
      classification: json['classification'] as String,
      requiresDpia: json['requires_dpia'] as bool? ?? false,
      retentionPeriodDays: json['retention_period_days'] as int? ?? 0,
      complianceStatus: json['compliance'] != null
          ? json['compliance']['status'] as String? ?? 'unknown'
          : 'unknown',
      complianceIssues: json['compliance'] != null
          ? List<String>.from(json['compliance']['issues'] as List? ?? const [])
          : const <String>[],
      residencyPolicies: (json['residency_policies'] as List<dynamic>? ?? const [])
          .map((policy) => DataResidencyPolicySummary.fromJson(
              policy as Map<String, dynamic>))
          .toList(),
      dpiaRecords: (json['dpia_records'] as List<dynamic>? ?? const [])
          .map((record) => DpiaRecordSummary.fromJson(
              record as Map<String, dynamic>))
          .toList(),
    );
  }

  final int id;
  final String key;
  final String name;
  final String classification;
  final bool requiresDpia;
  final int retentionPeriodDays;
  final String complianceStatus;
  final List<String> complianceIssues;
  final List<DataResidencyPolicySummary> residencyPolicies;
  final List<DpiaRecordSummary> dpiaRecords;

  bool get isCompliant => complianceStatus == 'compliant';
  bool get needsAttention => complianceStatus == 'attention_required';

  DataGovernanceAsset copyWith({
    List<DpiaRecordSummary>? dpiaRecords,
    List<String>? complianceIssues,
    String? complianceStatus,
  }) {
    return DataGovernanceAsset(
      id: id,
      key: key,
      name: name,
      classification: classification,
      requiresDpia: requiresDpia,
      retentionPeriodDays: retentionPeriodDays,
      complianceStatus: complianceStatus ?? this.complianceStatus,
      complianceIssues: complianceIssues ?? this.complianceIssues,
      residencyPolicies: residencyPolicies,
      dpiaRecords: dpiaRecords ?? this.dpiaRecords,
    );
  }
}

class DataResidencyPolicySummary {
  DataResidencyPolicySummary({
    required this.storageRole,
    required this.lawfulBasis,
    required this.encryptionProfile,
    required this.crossBorderAllowed,
    required this.zoneCode,
    required this.zoneName,
    required this.zoneRegion,
  });

  factory DataResidencyPolicySummary.fromJson(Map<String, dynamic> json) {
    final zone = json['zone'] as Map<String, dynamic>?;
    return DataResidencyPolicySummary(
      storageRole: json['storage_role'] as String? ?? '',
      lawfulBasis: json['lawful_basis'] as String? ?? '',
      encryptionProfile: json['encryption_profile'] as String? ?? '',
      crossBorderAllowed: json['cross_border_allowed'] as bool? ?? false,
      zoneCode: zone?['code'] as String? ?? '',
      zoneName: zone?['name'] as String? ?? '',
      zoneRegion: zone?['region'] as String? ?? '',
    );
  }

  final String storageRole;
  final String lawfulBasis;
  final String encryptionProfile;
  final bool crossBorderAllowed;
  final String zoneCode;
  final String zoneName;
  final String zoneRegion;
}

class DpiaRecordSummary {
  DpiaRecordSummary({
    required this.id,
    required this.status,
    required this.riskLevel,
    required this.riskScore,
    required this.mitigationActions,
    required this.residualRisks,
    required this.findings,
    required this.nextReviewAt,
  });

  factory DpiaRecordSummary.fromJson(Map<String, dynamic> json) {
    return DpiaRecordSummary(
      id: json['id'] as int,
      status: json['status'] as String? ?? 'draft',
      riskLevel: json['risk_level'] as String? ?? 'low',
      riskScore: json['risk_score'] as int? ?? 0,
      mitigationActions: (json['mitigation_actions'] as List<dynamic>? ?? const [])
          .map((entry) => Map<String, dynamic>.from(entry as Map))
          .toList(),
      residualRisks: (json['residual_risks'] as List<dynamic>? ?? const [])
          .map((entry) => Map<String, dynamic>.from(entry as Map))
          .toList(),
      findings: (json['findings'] as List<dynamic>? ?? const [])
          .map((entry) => DpiaFindingSummary.fromJson(
              entry as Map<String, dynamic>))
          .toList(),
      nextReviewAt: json['next_review_at'] != null
          ? DateTime.tryParse(json['next_review_at'] as String)
          : null,
    );
  }

  final int id;
  final String status;
  final String riskLevel;
  final int riskScore;
  final List<Map<String, dynamic>> mitigationActions;
  final List<Map<String, dynamic>> residualRisks;
  final List<DpiaFindingSummary> findings;
  final DateTime? nextReviewAt;
}

class DpiaFindingSummary {
  DpiaFindingSummary({
    required this.category,
    required this.severity,
    required this.status,
    required this.description,
    this.dueAt,
  });

  factory DpiaFindingSummary.fromJson(Map<String, dynamic> json) {
    return DpiaFindingSummary(
      category: json['category'] as String? ?? '',
      severity: json['severity'] as String? ?? 'low',
      status: json['status'] as String? ?? 'open',
      description: json['finding'] as String? ?? '',
      dueAt: json['due_at'] != null
          ? DateTime.tryParse(json['due_at'] as String)
          : null,
    );
  }

  final String category;
  final String severity;
  final String status;
  final String description;
  final DateTime? dueAt;
}
