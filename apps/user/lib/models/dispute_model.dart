import 'dart:convert';

class DisputeModel {
  DisputeModel({
    required this.id,
    required this.jobId,
    required this.stage,
    required this.status,
    required this.reasonCode,
    this.summary,
    this.deadlineAt,
    this.closedAt,
    this.resolution,
    this.metadata,
    this.events = const [],
    this.messages = const [],
  });

  final int id;
  final int jobId;
  final String stage;
  final String status;
  final String? reasonCode;
  final String? summary;
  final DateTime? deadlineAt;
  final DateTime? closedAt;
  final Map<String, dynamic>? resolution;
  final Map<String, dynamic>? metadata;
  final List<DisputeEventModel> events;
  final List<DisputeMessageModel> messages;

  bool get isResolved => status == 'resolved';
  bool get isOpen => closedAt == null;

  factory DisputeModel.fromJson(Map<String, dynamic> json) {
    return DisputeModel(
      id: json['id'] as int,
      jobId: json['service_request_id'] as int,
      stage: json['stage'] as String,
      status: json['status'] as String,
      reasonCode: json['reason_code'] as String?,
      summary: json['summary'] as String?,
      deadlineAt: _parseDate(json['deadline_at'] as String?),
      closedAt: _parseDate(json['closed_at'] as String?),
      resolution: json['resolution'] != null
          ? Map<String, dynamic>.from(json['resolution'] as Map<dynamic, dynamic>)
          : null,
      metadata: json['metadata'] != null
          ? Map<String, dynamic>.from(json['metadata'] as Map<dynamic, dynamic>)
          : null,
      events: (json['events'] as List<dynamic>? ?? [])
          .map((event) => DisputeEventModel.fromJson(
              Map<String, dynamic>.from(event as Map<dynamic, dynamic>)))
          .toList(),
      messages: (json['messages'] as List<dynamic>? ?? [])
          .map((message) => DisputeMessageModel.fromJson(
              Map<String, dynamic>.from(message as Map<dynamic, dynamic>)))
          .toList(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'service_request_id': jobId,
      'stage': stage,
      'status': status,
      'reason_code': reasonCode,
      'summary': summary,
      'deadline_at': deadlineAt?.toIso8601String(),
      'closed_at': closedAt?.toIso8601String(),
      'resolution': resolution,
      'metadata': metadata,
      'events': events.map((event) => event.toJson()).toList(),
      'messages': messages.map((message) => message.toJson()).toList(),
    };
  }

  static DateTime? _parseDate(String? value) =>
      value != null ? DateTime.tryParse(value) : null;
}

class DisputeEventModel {
  DisputeEventModel({
    required this.action,
    required this.createdAt,
    this.actorId,
    this.fromStage,
    this.toStage,
    this.note,
    this.metadata,
  });

  final String action;
  final DateTime createdAt;
  final int? actorId;
  final String? fromStage;
  final String? toStage;
  final String? note;
  final Map<String, dynamic>? metadata;

  factory DisputeEventModel.fromJson(Map<String, dynamic> json) {
    return DisputeEventModel(
      action: json['action'] as String,
      createdAt: DateTime.parse(json['created_at'] as String),
      actorId: json['actor_id'] as int?,
      fromStage: json['from_stage'] as String?,
      toStage: json['to_stage'] as String?,
      note: json['notes'] as String?,
      metadata: json['metadata'] != null
          ? Map<String, dynamic>.from(json['metadata'] as Map<dynamic, dynamic>)
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'action': action,
      'created_at': createdAt.toIso8601String(),
      'actor_id': actorId,
      'from_stage': fromStage,
      'to_stage': toStage,
      'notes': note,
      'metadata': metadata,
    };
  }
}

class DisputeMessageModel {
  DisputeMessageModel({
    required this.id,
    required this.body,
    required this.visibility,
    required this.createdAt,
    this.authorId,
    this.attachments,
  });

  final int id;
  final String body;
  final String visibility;
  final DateTime createdAt;
  final int? authorId;
  final List<dynamic>? attachments;

  factory DisputeMessageModel.fromJson(Map<String, dynamic> json) {
    return DisputeMessageModel(
      id: json['id'] as int,
      body: json['body'] as String,
      visibility: json['visibility'] as String,
      createdAt: DateTime.parse(json['created_at'] as String),
      authorId: json['author_id'] as int?,
      attachments: json['attachments'] != null
          ? List<dynamic>.from(json['attachments'] as List<dynamic>)
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'body': body,
      'visibility': visibility,
      'created_at': createdAt.toIso8601String(),
      'author_id': authorId,
      'attachments': attachments,
    };
  }
}

class DisputeCacheEntry {
  DisputeCacheEntry({required this.dispute, required this.cachedAt});

  final DisputeModel dispute;
  final DateTime cachedAt;

  Map<String, dynamic> toJson() => {
        'dispute': dispute.toJson(),
        'cached_at': cachedAt.toIso8601String(),
      };

  factory DisputeCacheEntry.fromJson(Map<String, dynamic> json) {
    return DisputeCacheEntry(
      dispute: DisputeModel.fromJson(
          Map<String, dynamic>.from(json['dispute'] as Map<dynamic, dynamic>)),
      cachedAt: DateTime.parse(json['cached_at'] as String),
    );
  }

  static String encodeList(List<DisputeCacheEntry> entries) =>
      jsonEncode(entries.map((entry) => entry.toJson()).toList());

  static List<DisputeCacheEntry> decodeList(String raw) {
    final payload = jsonDecode(raw) as List<dynamic>;
    return payload
        .map((item) => DisputeCacheEntry.fromJson(
            Map<String, dynamic>.from(item as Map<dynamic, dynamic>)))
        .toList();
  }
}
