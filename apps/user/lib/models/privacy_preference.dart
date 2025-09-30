class PrivacyPreference {
  PrivacyPreference({
    required this.key,
    required this.granted,
    this.grantedAt,
    this.expiresAt,
  });

  factory PrivacyPreference.fromJson(String key, Map<String, dynamic> json) {
    return PrivacyPreference(
      key: key,
      granted: json['granted'] as bool? ?? false,
      grantedAt: json['granted_at'] != null
          ? DateTime.tryParse(json['granted_at'] as String)
          : null,
      expiresAt: json['expires_at'] != null
          ? DateTime.tryParse(json['expires_at'] as String)
          : null,
    );
  }

  final String key;
  final bool granted;
  final DateTime? grantedAt;
  final DateTime? expiresAt;

  PrivacyPreference copyWith({
    bool? granted,
    DateTime? grantedAt,
    DateTime? expiresAt,
  }) {
    return PrivacyPreference(
      key: key,
      granted: granted ?? this.granted,
      grantedAt: grantedAt ?? this.grantedAt,
      expiresAt: expiresAt ?? this.expiresAt,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'granted': granted,
      'granted_at': grantedAt?.toIso8601String(),
      'expires_at': expiresAt?.toIso8601String(),
    };
  }
}
