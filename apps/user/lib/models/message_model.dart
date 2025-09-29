import 'package:equatable/equatable.dart';

class ThreadMessage extends Equatable {
  const ThreadMessage({
    required this.id,
    required this.threadId,
    required this.body,
    required this.attachments,
    required this.meta,
    required this.isSystem,
    required this.author,
    required this.createdAt,
  });

  factory ThreadMessage.fromJson(Map<String, dynamic> json) {
    return ThreadMessage(
      id: json['id']?.toString() ?? '',
      threadId: json['thread_id']?.toString() ?? '',
      body: json['body'] ?? '',
      attachments: (json['attachments'] as List<dynamic>? ?? [])
          .map((item) => ThreadMessageAttachment.fromJson(
              Map<String, dynamic>.from(item as Map)))
          .toList(),
      meta: Map<String, dynamic>.from(json['meta'] as Map? ?? {}),
      isSystem: json['is_system'] == true,
      author: json['author'] != null
          ? ThreadMessageAuthor.fromJson(
              Map<String, dynamic>.from(json['author'] as Map))
          : const ThreadMessageAuthor(id: 0, name: 'Unknown'),
      createdAt: DateTime.tryParse(json['created_at']?.toString() ?? '') ??
          DateTime.fromMillisecondsSinceEpoch(0),
    );
  }

  final String id;
  final String threadId;
  final String body;
  final List<ThreadMessageAttachment> attachments;
  final Map<String, dynamic> meta;
  final bool isSystem;
  final ThreadMessageAuthor author;
  final DateTime createdAt;

  ThreadMessage copyWith({
    String? body,
    List<ThreadMessageAttachment>? attachments,
    Map<String, dynamic>? meta,
  }) {
    return ThreadMessage(
      id: id,
      threadId: threadId,
      body: body ?? this.body,
      attachments: attachments ?? this.attachments,
      meta: meta ?? this.meta,
      isSystem: isSystem,
      author: author,
      createdAt: createdAt,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'thread_id': threadId,
      'body': body,
      'attachments': attachments.map((e) => e.toJson()).toList(),
      'meta': meta,
      'is_system': isSystem,
      'author': author.toJson(),
      'created_at': createdAt.toIso8601String(),
    };
  }

  @override
  List<Object?> get props => [id, threadId, body, attachments, meta, isSystem, author, createdAt];
}

class ThreadMessageAuthor extends Equatable {
  const ThreadMessageAuthor({
    required this.id,
    required this.name,
    this.avatar,
  });

  factory ThreadMessageAuthor.fromJson(Map<String, dynamic> json) {
    return ThreadMessageAuthor(
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

class ThreadMessageAttachment extends Equatable {
  const ThreadMessageAttachment({
    required this.mediaId,
    required this.name,
    required this.mimeType,
    required this.url,
    this.thumbnailUrl,
    this.size,
  });

  factory ThreadMessageAttachment.fromJson(Map<String, dynamic> json) {
    return ThreadMessageAttachment(
      mediaId: int.tryParse(json['media_id']?.toString() ?? '') ?? 0,
      name: json['name']?.toString() ?? '',
      mimeType: json['mime_type']?.toString() ?? '',
      url: json['url']?.toString() ?? '',
      thumbnailUrl: json['thumbnail_url']?.toString(),
      size: int.tryParse(json['size']?.toString() ?? ''),
    );
  }

  final int mediaId;
  final String name;
  final String mimeType;
  final String url;
  final String? thumbnailUrl;
  final int? size;

  Map<String, dynamic> toJson() => {
        'media_id': mediaId,
        'name': name,
        'mime_type': mimeType,
        'url': url,
        if (thumbnailUrl != null) 'thumbnail_url': thumbnailUrl,
        if (size != null) 'size': size,
      };

  @override
  List<Object?> get props => [mediaId, name, mimeType, url, thumbnailUrl, size];
}

class ComposeMessageRequest {
  const ComposeMessageRequest({
    this.body,
    this.attachmentMediaIds = const [],
  });

  final String? body;
  final List<int> attachmentMediaIds;

  Map<String, dynamic> toJson() {
    return {
      if (body != null) 'body': body,
      if (attachmentMediaIds.isNotEmpty)
        'attachments': attachmentMediaIds
            .map((id) => {
                  'media_id': id,
                })
            .toList(),
    };
  }
}
