import 'package:flutter/foundation.dart';

@immutable
class EnvironmentChecklist {
  const EnvironmentChecklist({
    required this.generatedAt,
    required this.namespaces,
    required this.secrets,
  });

  factory EnvironmentChecklist.fromJson(Map<String, dynamic> json) {
    final generatedAtRaw = json['generated_at'] as String?;
    final generatedAt = generatedAtRaw != null
        ? DateTime.parse(generatedAtRaw).toUtc()
        : DateTime.now().toUtc();
    final namespacesJson = json['namespaces'] as List<dynamic>? ?? const [];
    final secretsJson = json['secrets'] as List<dynamic>? ?? const [];
    return EnvironmentChecklist(
      generatedAt: generatedAt,
      namespaces: List.unmodifiable(
        namespacesJson
            .whereType<Map<String, dynamic>>()
            .map(EnvironmentNamespace.fromJson),
      ),
      secrets: List.unmodifiable(
        secretsJson
            .whereType<Map<String, dynamic>>()
            .map(EnvironmentSecret.fromJson),
      ),
    );
  }

  final DateTime generatedAt;
  final List<EnvironmentNamespace> namespaces;
  final List<EnvironmentSecret> secrets;

  Map<String, EnvironmentSecret> get secretsById => {
        for (final secret in secrets) secret.id: secret,
      };
}

@immutable
class EnvironmentNamespace {
  const EnvironmentNamespace({
    required this.id,
    required this.name,
    required this.tier,
    required this.owner,
    required this.escalation,
    required this.delegates,
    required this.regions,
    required this.description,
    required this.secretRefs,
    required this.complianceTier,
    required this.validations,
  });

  factory EnvironmentNamespace.fromJson(Map<String, dynamic> json) {
    return EnvironmentNamespace(
      id: json['id'] as String? ?? 'unknown',
      name: json['name'] as String? ?? 'Unknown',
      tier: json['tier'] as String? ?? 'tier3',
      owner: json['owner'] as String? ?? 'Unassigned',
      escalation: json['escalation'] as String? ?? '-',
      delegates: List.unmodifiable(
        (json['delegates'] as List<dynamic>? ?? const [])
            .map((delegate) => delegate.toString()),
      ),
      regions: List.unmodifiable(
        (json['regions'] as List<dynamic>? ?? const [])
            .map((region) => region.toString()),
      ),
      description: json['description'] as String? ?? '',
      secretRefs: List.unmodifiable(
        (json['secret_refs'] as List<dynamic>? ?? const [])
            .map((ref) => ref.toString()),
      ),
      complianceTier: json['compliance_tier'] as String? ?? 'internal',
      validations: List.unmodifiable(
        (json['validations'] as List<dynamic>? ?? const [])
            .whereType<Map<String, dynamic>>()
            .map(EnvironmentNamespaceValidation.fromJson),
      ),
    );
  }

  final String id;
  final String name;
  final String tier;
  final String owner;
  final String escalation;
  final List<String> delegates;
  final List<String> regions;
  final String description;
  final List<String> secretRefs;
  final String complianceTier;
  final List<EnvironmentNamespaceValidation> validations;
}

@immutable
class EnvironmentNamespaceValidation {
  const EnvironmentNamespaceValidation({
    required this.description,
    required this.command,
    required this.evidencePath,
  });

  factory EnvironmentNamespaceValidation.fromJson(Map<String, dynamic> json) {
    return EnvironmentNamespaceValidation(
      description: json['description'] as String? ?? '',
      command: json['command'] as String? ?? '',
      evidencePath: json['evidence_path'] as String? ?? '',
    );
  }

  final String description;
  final String command;
  final String evidencePath;
}

@immutable
class EnvironmentSecret {
  const EnvironmentSecret({
    required this.id,
    required this.name,
    required this.envKey,
    required this.owner,
    required this.source,
    required this.lastRotatedAt,
    required this.nextRotationDueAt,
    required this.lastValidatedAt,
    required this.rotationIntervalDays,
    required this.criticality,
    required this.systems,
    required this.notes,
    required this.validators,
  });

  factory EnvironmentSecret.fromJson(Map<String, dynamic> json) {
    DateTime? parseTime(String? value) {
      if (value == null || value.isEmpty) return null;
      return DateTime.parse(value).toUtc();
    }

    final validatorsJson = json['validators'] as List<dynamic>? ?? const [];

    return EnvironmentSecret(
      id: json['id'] as String? ?? 'unknown',
      name: json['name'] as String? ?? 'Unknown',
      envKey: json['env_key'] as String? ?? '-',
      owner: json['owner'] as String? ?? 'Unassigned',
      source: json['source'] as String? ?? '-',
      lastRotatedAt: parseTime(json['last_rotated_at'] as String?),
      nextRotationDueAt: parseTime(json['next_rotation_due_at'] as String?),
      lastValidatedAt: parseTime(json['last_validated_at'] as String?),
      rotationIntervalDays:
          (json['rotation_interval_days'] as num?)?.toInt() ?? 30,
      criticality: (json['criticality'] as String? ?? 'tier2').toLowerCase(),
      systems: List.unmodifiable(
        (json['systems'] as List<dynamic>? ?? const [])
            .map((system) => system.toString()),
      ),
      notes: json['notes'] as String? ?? '',
      validators: List.unmodifiable(
        validatorsJson
            .whereType<Map<String, dynamic>>()
            .map(EnvironmentValidator.fromJson),
      ),
    );
  }

  final String id;
  final String name;
  final String envKey;
  final String owner;
  final String source;
  final DateTime? lastRotatedAt;
  final DateTime? nextRotationDueAt;
  final DateTime? lastValidatedAt;
  final int rotationIntervalDays;
  final String criticality;
  final List<String> systems;
  final String notes;
  final List<EnvironmentValidator> validators;

  Duration get rotationInterval => Duration(days: rotationIntervalDays);

  SecretRiskLevel riskLevel(DateTime reference, {int dueSoonWindowDays = 14}) {
    if (isRotationOverdue(reference)) {
      return SecretRiskLevel.overdue;
    }
    if (isRotationDueSoon(reference, dueSoonWindowDays: dueSoonWindowDays)) {
      return SecretRiskLevel.dueSoon;
    }
    return SecretRiskLevel.healthy;
  }

  bool isRotationOverdue(DateTime reference) {
    if (nextRotationDueAt == null) return true;
    return nextRotationDueAt!.isBefore(reference);
  }

  bool isRotationDueSoon(DateTime reference, {int dueSoonWindowDays = 14}) {
    if (nextRotationDueAt == null) return true;
    final threshold = reference.add(Duration(days: dueSoonWindowDays));
    return nextRotationDueAt!.isBefore(threshold) &&
        !nextRotationDueAt!.isBefore(reference);
  }

  bool isValidationStale(DateTime reference) {
    if (lastValidatedAt == null) return true;
    final threshold = lastValidatedAt!.add(rotationInterval);
    return reference.isAfter(threshold);
  }

  int daysUntilRotation(DateTime reference) {
    if (nextRotationDueAt == null) return 0;
    return nextRotationDueAt!
        .difference(reference)
        .inDays;
  }
}

enum SecretRiskLevel { healthy, dueSoon, overdue }

@immutable
class EnvironmentValidator {
  const EnvironmentValidator({
    required this.description,
    required this.command,
  });

  factory EnvironmentValidator.fromJson(Map<String, dynamic> json) {
    return EnvironmentValidator(
      description: json['description'] as String? ?? '',
      command: json['command'] as String? ?? '',
    );
  }

  final String description;
  final String command;
}
