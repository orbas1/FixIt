import 'package:flutter/foundation.dart';

@immutable
class ArchitectureGovernanceChecklist {
  const ArchitectureGovernanceChecklist({
    required this.generatedAt,
    required this.board,
    required this.cadences,
    required this.decisions,
    required this.capabilities,
    required this.risks,
    required this.qualityGates,
  });

  factory ArchitectureGovernanceChecklist.fromJson(Map<String, dynamic> json) {
    final generatedAtRaw = json['generated_at'] as String?;
    final generatedAt = generatedAtRaw != null
        ? DateTime.parse(generatedAtRaw).toUtc()
        : DateTime.now().toUtc();
    final boardJson = json['board'] as Map<String, dynamic>? ?? const {};
    final cadencesJson = json['cadences'] as Map<String, dynamic>? ?? const {};
    final decisionsJson = json['decisions'] as List<dynamic>? ?? const [];
    final capabilitiesJson = json['capabilities'] as List<dynamic>? ?? const [];
    final riskJson = json['risk_register'] as List<dynamic>? ?? const [];
    final qualityJson = json['quality_gates'] as Map<String, dynamic>? ?? const {};

    return ArchitectureGovernanceChecklist(
      generatedAt: generatedAt,
      board: GovernanceBoard.fromJson(boardJson),
      cadences: List.unmodifiable(
        cadencesJson.entries.map((entry) {
          final value = entry.value;
          if (value is Map<String, dynamic>) {
            return GovernanceCadence.fromJson(entry.key, value);
          }
          if (value is Map) {
            final normalized = Map<String, dynamic>.fromEntries(
              value.entries.map(
                (mapEntry) => MapEntry(mapEntry.key.toString(), mapEntry.value),
              ),
            );
            return GovernanceCadence.fromJson(entry.key, normalized);
          }
          return GovernanceCadence.fromJson(entry.key, const {});
        }).toList(),
      ),
      decisions: List.unmodifiable(
        decisionsJson
            .whereType<Map<String, dynamic>>()
            .map(GovernanceDecision.fromJson)
            .toList(),
      ),
      capabilities: List.unmodifiable(
        capabilitiesJson
            .whereType<Map<String, dynamic>>()
            .map(GovernanceCapability.fromJson)
            .toList(),
      ),
      risks: List.unmodifiable(
        riskJson
            .whereType<Map<String, dynamic>>()
            .map(GovernanceRisk.fromJson)
            .toList(),
      ),
      qualityGates: GovernanceQualityGates.fromJson(qualityJson),
    );
  }

  final DateTime generatedAt;
  final GovernanceBoard board;
  final List<GovernanceCadence> cadences;
  final List<GovernanceDecision> decisions;
  final List<GovernanceCapability> capabilities;
  final List<GovernanceRisk> risks;
  final GovernanceQualityGates qualityGates;

  double get dependencyCoverage {
    var covered = 0;
    var total = 0;
    final capabilityIds = capabilities.map((cap) => cap.id).toSet();
    for (final capability in capabilities) {
      for (final dependency in capability.dependencies) {
        total += 1;
        if (capabilityIds.contains(dependency)) {
          covered += 1;
        }
      }
    }
    if (total == 0) {
      return 1.0;
    }
    return covered / total;
  }

  Map<String, List<GovernanceCapability>> get capabilitiesByQuarter {
    final Map<String, List<GovernanceCapability>> grouped = {};
    for (final capability in capabilities) {
      grouped.putIfAbsent(capability.roadmapQuarter, () => <GovernanceCapability>[]);
      grouped[capability.roadmapQuarter]!.add(capability);
    }
    for (final entry in grouped.entries) {
      entry.value.sort((a, b) => a.title.compareTo(b.title));
    }
    return grouped;
  }

  List<GovernanceDecision> decisionsRequiringAttention(DateTime reference) {
    final warningWindow = qualityGates.decisionRenewalWarningDays;
    return decisions
        .where(
          (decision) => decision.daysUntilRenewal(reference) <= warningWindow,
        )
        .toList()
      ..sort((a, b) => a.daysUntilRenewal(reference).compareTo(b.daysUntilRenewal(reference)));
  }

  List<GovernanceCapability> staleCapabilities(DateTime reference) {
    final threshold = qualityGates.readinessStaleDays;
    return capabilities
        .where(
          (capability) => capability.isReadinessStale(reference, thresholdDays: threshold),
        )
        .toList()
      ..sort(
        (a, b) => a.readiness.lastUpdated.compareTo(b.readiness.lastUpdated),
      );
  }
}

@immutable
class GovernanceBoard {
  const GovernanceBoard({
    required this.charterReviewDate,
    required this.nextSession,
    required this.quorum,
    required this.escalationPolicy,
    required this.members,
  });

  factory GovernanceBoard.fromJson(Map<String, dynamic> json) {
    final membersJson = json['members'] as List<dynamic>? ?? const [];
    return GovernanceBoard(
      charterReviewDate: _parseDate(json['charter_review_date'] as String?),
      nextSession: _parseDateTime(json['next_session'] as String?),
      quorum: (json['quorum'] as num?)?.toInt() ?? 0,
      escalationPolicy: json['escalation_policy'] as String? ?? '',
      members: List.unmodifiable(
        membersJson
            .whereType<Map<String, dynamic>>()
            .map(GovernanceBoardMember.fromJson)
            .toList(),
      ),
    );
  }

  final DateTime? charterReviewDate;
  final DateTime? nextSession;
  final int quorum;
  final String escalationPolicy;
  final List<GovernanceBoardMember> members;

  int get votingMemberCount =>
      members.where((member) => member.votingMember).length;

  bool get quorumSatisfied =>
      quorum > 0 && votingMemberCount >= quorum;

  GovernanceBoardMember? memberById(String id) {
    return members.firstWhere(
      (member) => member.id == id,
      orElse: () => const GovernanceBoardMember.empty(),
    ).nullable;
  }
}

@immutable
class GovernanceBoardMember {
  const GovernanceBoardMember({
    required this.id,
    required this.name,
    required this.role,
    required this.tenureStart,
    required this.votingMember,
    required this.responsibilities,
  });

  const GovernanceBoardMember.empty()
      : id = '',
        name = '',
        role = '',
        tenureStart = null,
        votingMember = false,
        responsibilities = const [];

  factory GovernanceBoardMember.fromJson(Map<String, dynamic> json) {
    return GovernanceBoardMember(
      id: json['id'] as String? ?? '',
      name: json['name'] as String? ?? '',
      role: json['role'] as String? ?? '',
      tenureStart: _parseDate(json['tenure_start'] as String?),
      votingMember: json['voting_member'] as bool? ?? false,
      responsibilities: List.unmodifiable(
        (json['responsibilities'] as List<dynamic>? ?? const [])
            .map((entry) => entry.toString())
            .toList(),
      ),
    );
  }

  final String id;
  final String name;
  final String role;
  final DateTime? tenureStart;
  final bool votingMember;
  final List<String> responsibilities;

  GovernanceBoardMember? get nullable => id.isEmpty ? null : this;
}

@immutable
class GovernanceCadence {
  const GovernanceCadence({
    required this.id,
    required this.description,
    required this.frequencyDays,
    required this.lastHeld,
    required this.facilitatorId,
    required this.artifacts,
  });

  factory GovernanceCadence.fromJson(String id, Map<String, dynamic> json) {
    return GovernanceCadence(
      id: id,
      description: json['description'] as String? ?? '',
      frequencyDays: (json['frequency_days'] as num?)?.toInt() ?? 0,
      lastHeld: _parseDateTime(json['last_held'] as String?),
      facilitatorId: json['facilitator'] as String? ?? '',
      artifacts: List.unmodifiable(
        (json['artifacts'] as List<dynamic>? ?? const [])
            .map((entry) => entry.toString())
            .toList(),
      ),
    );
  }

  final String id;
  final String description;
  final int frequencyDays;
  final DateTime? lastHeld;
  final String facilitatorId;
  final List<String> artifacts;

  bool isStale(DateTime reference) {
    if (lastHeld == null || frequencyDays <= 0) {
      return true;
    }
    final delta = reference.difference(lastHeld!).inDays;
    return delta > frequencyDays + 1;
  }
}

@immutable
class GovernanceDecision {
  const GovernanceDecision({
    required this.id,
    required this.title,
    required this.status,
    required this.ownerId,
    required this.enforcementTier,
    required this.decisionDate,
    required this.renewalDate,
    required this.linkedCapabilities,
    required this.evidence,
  });

  factory GovernanceDecision.fromJson(Map<String, dynamic> json) {
    return GovernanceDecision(
      id: json['id'] as String? ?? '',
      title: json['title'] as String? ?? '',
      status: json['status'] as String? ?? 'proposed',
      ownerId: json['owner'] as String? ?? '',
      enforcementTier: json['enforcement_tier'] as String? ?? 'advisory',
      decisionDate: _parseDate(json['decision_date'] as String?),
      renewalDate: _parseDate(json['renewal_date'] as String?),
      linkedCapabilities: List.unmodifiable(
        (json['linked_capabilities'] as List<dynamic>? ?? const [])
            .map((entry) => entry.toString())
            .toList(),
      ),
      evidence: GovernanceDecisionEvidence.fromJson(
        json['evidence'] as Map<String, dynamic>? ?? const {},
      ),
    );
  }

  final String id;
  final String title;
  final String status;
  final String ownerId;
  final String enforcementTier;
  final DateTime? decisionDate;
  final DateTime? renewalDate;
  final List<String> linkedCapabilities;
  final GovernanceDecisionEvidence evidence;

  int daysUntilRenewal(DateTime reference) {
    if (renewalDate == null) {
      return 0;
    }
    return renewalDate!.difference(reference).inDays;
  }

  bool get hasEvidence => evidence.repository.isNotEmpty;
}

@immutable
class GovernanceDecisionEvidence {
  const GovernanceDecisionEvidence({
    required this.repository,
    required this.artifacts,
  });

  factory GovernanceDecisionEvidence.fromJson(Map<String, dynamic> json) {
    return GovernanceDecisionEvidence(
      repository: json['repository'] as String? ?? '',
      artifacts: List.unmodifiable(
        (json['artifacts'] as List<dynamic>? ?? const [])
            .map((entry) => entry.toString())
            .toList(),
      ),
    );
  }

  final String repository;
  final List<String> artifacts;
}

@immutable
class GovernanceCapability {
  const GovernanceCapability({
    required this.id,
    required this.title,
    required this.summary,
    required this.owners,
    required this.roadmapQuarter,
    required this.dependencies,
    required this.readiness,
    required this.metrics,
  });

  factory GovernanceCapability.fromJson(Map<String, dynamic> json) {
    return GovernanceCapability(
      id: json['id'] as String? ?? '',
      title: json['title'] as String? ?? '',
      summary: json['summary'] as String? ?? '',
      owners: List.unmodifiable(
        (json['owners'] as List<dynamic>? ?? const [])
            .map((entry) => entry.toString())
            .toList(),
      ),
      roadmapQuarter: json['roadmap_quarter'] as String? ?? '',
      dependencies: List.unmodifiable(
        (json['dependencies'] as List<dynamic>? ?? const [])
            .map((entry) => entry.toString())
            .toList(),
      ),
      readiness: CapabilityReadiness.fromJson(
        json['readiness'] as Map<String, dynamic>? ?? const {},
      ),
      metrics: List.unmodifiable(
        (json['metrics'] as List<dynamic>? ?? const [])
            .whereType<Map<String, dynamic>>()
            .map(CapabilityMetric.fromJson)
            .toList(),
      ),
    );
  }

  final String id;
  final String title;
  final String summary;
  final List<String> owners;
  final String roadmapQuarter;
  final List<String> dependencies;
  final CapabilityReadiness readiness;
  final List<CapabilityMetric> metrics;

  bool isReadinessStale(DateTime reference, {required int thresholdDays}) {
    final lastUpdated = readiness.lastUpdated;
    if (lastUpdated == null) {
      return true;
    }
    return reference.difference(lastUpdated).inDays > thresholdDays;
  }
}

@immutable
class CapabilityReadiness {
  const CapabilityReadiness({
    required this.architectureReview,
    required this.security,
    required this.operations,
    required this.lastUpdated,
  });

  factory CapabilityReadiness.fromJson(Map<String, dynamic> json) {
    return CapabilityReadiness(
      architectureReview: json['architecture_review'] as String? ?? 'pending',
      security: json['security'] as String? ?? 'unknown',
      operations: json['operations'] as String? ?? 'unknown',
      lastUpdated: _parseDateTime(json['last_updated'] as String?),
    );
  }

  final String architectureReview;
  final String security;
  final String operations;
  final DateTime? lastUpdated;
}

@immutable
class CapabilityMetric {
  const CapabilityMetric({
    required this.id,
    required this.name,
    required this.baseline,
    required this.target,
    required this.unit,
  });

  factory CapabilityMetric.fromJson(Map<String, dynamic> json) {
    return CapabilityMetric(
      id: json['id'] as String? ?? '',
      name: json['name'] as String? ?? '',
      baseline: (json['baseline'] as num?)?.toDouble() ?? 0,
      target: (json['target'] as num?)?.toDouble() ?? 0,
      unit: json['unit'] as String? ?? '',
    );
  }

  final String id;
  final String name;
  final double baseline;
  final double target;
  final String unit;
}

@immutable
class GovernanceRisk {
  const GovernanceRisk({
    required this.id,
    required this.description,
    required this.severity,
    required this.owner,
    required this.mitigation,
    required this.dueDate,
  });

  factory GovernanceRisk.fromJson(Map<String, dynamic> json) {
    return GovernanceRisk(
      id: json['id'] as String? ?? '',
      description: json['description'] as String? ?? '',
      severity: json['severity'] as String? ?? 'low',
      owner: json['owner'] as String? ?? '',
      mitigation: json['mitigation'] as String? ?? '',
      dueDate: _parseDate(json['due_date'] as String?),
    );
  }

  final String id;
  final String description;
  final String severity;
  final String owner;
  final String mitigation;
  final DateTime? dueDate;
}

@immutable
class GovernanceQualityGates {
  const GovernanceQualityGates({
    required this.readinessStaleDays,
    required this.decisionRenewalWarningDays,
  });

  factory GovernanceQualityGates.fromJson(Map<String, dynamic> json) {
    return GovernanceQualityGates(
      readinessStaleDays: (json['readiness_stale_days'] as num?)?.toInt() ?? 7,
      decisionRenewalWarningDays:
          (json['decision_renewal_warning_days'] as num?)?.toInt() ?? 30,
    );
  }

  final int readinessStaleDays;
  final int decisionRenewalWarningDays;
}

DateTime? _parseDateTime(String? raw) {
  if (raw == null || raw.isEmpty) {
    return null;
  }
  final normalised = raw.endsWith('Z') ? raw.replaceFirst('Z', '+00:00') : raw;
  return DateTime.parse(normalised).toUtc();
}

DateTime? _parseDate(String? raw) {
  if (raw == null || raw.isEmpty) {
    return null;
  }
  return DateTime.parse('$rawT00:00:00Z').toUtc();
}
