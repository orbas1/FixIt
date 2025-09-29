import 'package:equatable/equatable.dart';

import 'message_model.dart';

class ThreadSummary extends Equatable {
  const ThreadSummary({
    required this.id,
    required this.type,
    required this.status,
    required this.subject,
    required this.lastMessageAt,
    required this.participants,
    this.latestMessage,
    this.serviceRequestId,
    this.bookingId,
  });

  factory ThreadSummary.fromJson(Map<String, dynamic> json) {
    return ThreadSummary(
      id: json['id']?.toString() ?? '',
      type: json['type']?.toString() ?? 'buyer_provider',
      status: json['status']?.toString() ?? 'open',
      subject: json['subject']?.toString() ?? '',
      lastMessageAt: DateTime.tryParse(json['last_message_at']?.toString() ?? '') ??
          DateTime.fromMillisecondsSinceEpoch(0),
      participants: (json['participants'] as List<dynamic>? ?? [])
          .map((item) => ThreadParticipant.fromJson(
              Map<String, dynamic>.from(item as Map)))
          .toList(),
      latestMessage: json['latest_message'] != null
          ? ThreadMessage.fromJson(
              Map<String, dynamic>.from(json['latest_message'] as Map))
          : null,
      serviceRequestId: json['service_request_id'],
      bookingId: json['booking_id'],
    );
  }

  final String id;
  final String type;
  final String status;
  final String subject;
  final DateTime lastMessageAt;
  final List<ThreadParticipant> participants;
  final ThreadMessage? latestMessage;
  final int? serviceRequestId;
  final int? bookingId;

  Map<String, dynamic> toJson() => {
        'id': id,
        'type': type,
        'status': status,
        'subject': subject,
        'last_message_at': lastMessageAt.toIso8601String(),
        'participants': participants.map((e) => e.toJson()).toList(),
        if (latestMessage != null) 'latest_message': latestMessage!.toJson(),
        'service_request_id': serviceRequestId,
        'booking_id': bookingId,
      };

  @override
  List<Object?> get props => [id, type, status, subject, lastMessageAt, participants, latestMessage, serviceRequestId, bookingId];
}

class ThreadDetail extends ThreadSummary {
  const ThreadDetail({
    required super.id,
    required super.type,
    required super.status,
    required super.subject,
    required super.lastMessageAt,
    required super.participants,
    super.latestMessage,
    super.serviceRequestId,
    super.bookingId,
    required this.messages,
  });

  factory ThreadDetail.fromJson(Map<String, dynamic> json) {
    final summary = ThreadSummary.fromJson(json);
    return ThreadDetail(
      id: summary.id,
      type: summary.type,
      status: summary.status,
      subject: summary.subject,
      lastMessageAt: summary.lastMessageAt,
      participants: summary.participants,
      latestMessage: summary.latestMessage,
      serviceRequestId: summary.serviceRequestId,
      bookingId: summary.bookingId,
      messages: (json['messages'] as List<dynamic>? ?? [])
          .map((item) => ThreadMessage.fromJson(
              Map<String, dynamic>.from(item as Map)))
          .toList(),
    );
  }

  final List<ThreadMessage> messages;

  @override
  Map<String, dynamic> toJson() => super.toJson()
    ..addAll({'messages': messages.map((e) => e.toJson()).toList()});

  @override
  List<Object?> get props => super.props..add(messages);
}

class ThreadParticipant extends Equatable {
  const ThreadParticipant({
    required this.id,
    required this.role,
    required this.isActive,
    required this.user,
    this.lastReadAt,
    this.mutedUntil,
  });

  factory ThreadParticipant.fromJson(Map<String, dynamic> json) {
    return ThreadParticipant(
      id: int.tryParse(json['id']?.toString() ?? '') ?? 0,
      role: json['role']?.toString() ?? 'consumer',
      isActive: json['is_active'] == true,
      user: json['user'] != null
          ? ThreadParticipantUser.fromJson(
              Map<String, dynamic>.from(json['user'] as Map))
          : const ThreadParticipantUser(id: 0, name: 'Unknown'),
      lastReadAt: DateTime.tryParse(json['last_read_at']?.toString() ?? ''),
      mutedUntil: DateTime.tryParse(json['muted_until']?.toString() ?? ''),
    );
  }

  final int id;
  final String role;
  final bool isActive;
  final ThreadParticipantUser user;
  final DateTime? lastReadAt;
  final DateTime? mutedUntil;

  Map<String, dynamic> toJson() => {
        'id': id,
        'role': role,
        'is_active': isActive,
        'user': user.toJson(),
        'last_read_at': lastReadAt?.toIso8601String(),
        'muted_until': mutedUntil?.toIso8601String(),
      };

  @override
  List<Object?> get props => [id, role, isActive, user, lastReadAt, mutedUntil];
}

class ThreadParticipantUser extends Equatable {
  const ThreadParticipantUser({
    required this.id,
    required this.name,
    this.avatar,
  });

  factory ThreadParticipantUser.fromJson(Map<String, dynamic> json) {
    return ThreadParticipantUser(
      id: int.tryParse(json['id']?.toString() ?? '') ?? 0,
      name: json['name']?.toString() ?? '',
      avatar: json['avatar']?.toString(),
    );
  }

  final int id;
  final String name;
  final String? avatar;

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        if (avatar != null) 'avatar': avatar,
      };

  @override
  List<Object?> get props => [id, name, avatar];
}
