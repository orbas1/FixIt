class SecurityIncident {
  SecurityIncident({
    required this.id,
    required this.title,
    required this.severity,
    required this.status,
    required this.detectedAt,
    required this.timeline,
    this.detectionSource,
    this.impactSummary,
    this.mitigationSteps,
  });

  factory SecurityIncident.fromJson(Map<String, dynamic> json) {
    final timeline = (json['timeline'] as List<dynamic>? ?? [])
        .whereType<Map>()
        .map((entry) => SecurityIncidentTimelineEntry.fromJson(
            Map<String, dynamic>.from(entry)))
        .toList();

    return SecurityIncident(
      id: json['id'] as String,
      title: json['title'] as String? ?? '',
      severity: json['severity'] as String? ?? 'medium',
      status: json['status'] as String? ?? 'open',
      detectionSource: json['detection_source'] as String?,
      impactSummary: json['impact_summary'] as String?,
      mitigationSteps: json['mitigation_steps'] as String?,
      detectedAt: DateTime.tryParse(json['detected_at'] as String? ?? '') ?? DateTime.now(),
      timeline: timeline,
    );
  }

  final String id;
  final String title;
  final String severity;
  final String status;
  final DateTime detectedAt;
  final String? detectionSource;
  final String? impactSummary;
  final String? mitigationSteps;
  final List<SecurityIncidentTimelineEntry> timeline;

  String get severityLabel => severity.toUpperCase();

  String get statusLabel =>
      status.isEmpty ? '' : status[0].toUpperCase() + status.substring(1);
}

class SecurityIncidentTimelineEntry {
  SecurityIncidentTimelineEntry({
    required this.event,
    required this.occurredAt,
    this.actorId,
    this.note,
  });

  factory SecurityIncidentTimelineEntry.fromJson(Map<String, dynamic> json) {
    final context = json['context'] is Map
        ? Map<String, dynamic>.from(json['context'] as Map)
        : const <String, dynamic>{};

    return SecurityIncidentTimelineEntry(
      event: json['event'] as String? ?? '',
      occurredAt: DateTime.tryParse(json['occurred_at'] as String? ?? '') ?? DateTime.now(),
      actorId: context['actor_id'] as int?,
      note: context['note'] as String?,
    );
  }

  final String event;
  final DateTime occurredAt;
  final int? actorId;
  final String? note;
}
