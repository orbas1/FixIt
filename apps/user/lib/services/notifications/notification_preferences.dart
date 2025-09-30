import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

enum NotificationCategory {
  feedNearby,
  bidPlaced,
  disputeDeadline,
  orderStatus,
  payoutSent,
}

extension NotificationCategoryX on NotificationCategory {
  String get key => switch (this) {
        NotificationCategory.feedNearby => 'feed.new_nearby',
        NotificationCategory.bidPlaced => 'bid.placed',
        NotificationCategory.disputeDeadline => 'dispute.deadline',
        NotificationCategory.orderStatus => 'order.status',
        NotificationCategory.payoutSent => 'payout.sent',
      };

  String get label => switch (this) {
        NotificationCategory.feedNearby => 'New jobs near you',
        NotificationCategory.bidPlaced => 'Bid updates',
        NotificationCategory.disputeDeadline => 'Dispute reminders',
        NotificationCategory.orderStatus => 'Order status',
        NotificationCategory.payoutSent => 'Payout notifications',
      };
}

class QuietHoursSettings {
  const QuietHoursSettings({required this.enabled, required this.startMinute, required this.endMinute});

  final bool enabled;
  final int startMinute;
  final int endMinute;

  bool isActive(DateTime time) {
    if (!enabled) return false;
    final minutes = time.hour * 60 + time.minute;
    if (startMinute <= endMinute) {
      return minutes >= startMinute && minutes <= endMinute;
    }
    return minutes >= startMinute || minutes <= endMinute;
  }

  QuietHoursSettings copyWith({bool? enabled, int? startMinute, int? endMinute}) {
    return QuietHoursSettings(
      enabled: enabled ?? this.enabled,
      startMinute: startMinute ?? this.startMinute,
      endMinute: endMinute ?? this.endMinute,
    );
  }

  Map<String, dynamic> toJson() => {
        'enabled': enabled,
        'start': startMinute,
        'end': endMinute,
      };

  factory QuietHoursSettings.fromJson(Map<String, dynamic> json) {
    return QuietHoursSettings(
      enabled: json['enabled'] as bool? ?? false,
      startMinute: json['start'] as int? ?? 22 * 60,
      endMinute: json['end'] as int? ?? 7 * 60,
    );
  }
}

class NotificationPreferenceStore {
  NotificationPreferenceStore({required SharedPreferences preferences}) : _preferences = preferences;

  static const _categoryPrefix = 'notification.category.';
  static const _quietHoursKey = 'notification.quiet_hours';

  final SharedPreferences _preferences;

  Future<Map<NotificationCategory, bool>> loadCategories() async {
    final result = <NotificationCategory, bool>{};
    for (final category in NotificationCategory.values) {
      result[category] = _preferences.getBool('$_categoryPrefix${category.key}') ?? true;
    }
    return result;
  }

  Future<void> setCategory(NotificationCategory category, bool enabled) async {
    await _preferences.setBool('$_categoryPrefix${category.key}', enabled);
  }

  QuietHoursSettings loadQuietHours() {
    final raw = _preferences.getString(_quietHoursKey);
    if (raw == null) {
      return const QuietHoursSettings(enabled: false, startMinute: 22 * 60, endMinute: 7 * 60);
    }
    try {
      final decoded = jsonDecode(raw) as Map<String, dynamic>;
      return QuietHoursSettings.fromJson(decoded);
    } catch (_) {
      return const QuietHoursSettings(enabled: false, startMinute: 22 * 60, endMinute: 7 * 60);
    }
  }

  Future<void> saveQuietHours(QuietHoursSettings settings) async {
    await _preferences.setString(_quietHoursKey, jsonEncode(settings.toJson()));
  }

  bool shouldDeliver(NotificationCategory category, DateTime timestamp) {
    final enabled = _preferences.getBool('$_categoryPrefix${category.key}') ?? true;
    if (!enabled) return false;
    final quietHours = loadQuietHours();
    if (quietHours.isActive(timestamp)) {
      return false;
    }
    return true;
  }
}
