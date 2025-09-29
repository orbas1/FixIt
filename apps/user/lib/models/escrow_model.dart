import 'dart:convert';

import 'package:collection/collection.dart';

class EscrowModel {
  EscrowModel({
    required this.id,
    required this.amount,
    required this.amountReleased,
    required this.amountRefunded,
    required this.status,
    required this.currency,
    this.holdReference,
    this.fundedAt,
    this.releasedAt,
    this.refundedAt,
    this.cancelledAt,
    this.expiresAt,
    this.ledger = const [],
  });

  final int id;
  final double amount;
  final double amountReleased;
  final double amountRefunded;
  final String status;
  final String currency;
  final String? holdReference;
  final DateTime? fundedAt;
  final DateTime? releasedAt;
  final DateTime? refundedAt;
  final DateTime? cancelledAt;
  final DateTime? expiresAt;
  final List<EscrowTransactionModel> ledger;

  double get availableAmount =>
      (amount - amountReleased - amountRefunded).clamp(0, double.infinity);

  bool get isClosed =>
      const ['released', 'refunded', 'cancelled'].contains(status);

  factory EscrowModel.fromJson(Map<String, dynamic> json) {
    final ledger = (json['transactions'] as List<dynamic>? ?? [])
        .map((item) => EscrowTransactionModel.fromJson(
            Map<String, dynamic>.from(item as Map<dynamic, dynamic>)))
        .toList();

    DateTime? parseDate(String? value) =>
        value != null ? DateTime.tryParse(value) : null;

    return EscrowModel(
      id: json['id'] as int,
      amount: (json['amount'] as num).toDouble(),
      amountReleased: (json['amount_released'] as num?)?.toDouble() ?? 0,
      amountRefunded: (json['amount_refunded'] as num?)?.toDouble() ?? 0,
      status: json['status'] as String,
      currency: json['currency'] as String,
      holdReference: json['hold_reference'] as String?,
      fundedAt: parseDate(json['funded_at'] as String?),
      releasedAt: parseDate(json['released_at'] as String?),
      refundedAt: parseDate(json['refunded_at'] as String?),
      cancelledAt: parseDate(json['cancelled_at'] as String?),
      expiresAt: parseDate(json['expires_at'] as String?),
      ledger: ledger,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'amount': amount,
      'amount_released': amountReleased,
      'amount_refunded': amountRefunded,
      'status': status,
      'currency': currency,
      'hold_reference': holdReference,
      'funded_at': fundedAt?.toIso8601String(),
      'released_at': releasedAt?.toIso8601String(),
      'refunded_at': refundedAt?.toIso8601String(),
      'cancelled_at': cancelledAt?.toIso8601String(),
      'expires_at': expiresAt?.toIso8601String(),
      'transactions': ledger.map((entry) => entry.toJson()).toList(),
    };
  }

  String cacheKey() =>
      'escrow-$id-${availableAmount.toStringAsFixed(2)}-$status';
}

class EscrowTransactionModel {
  EscrowTransactionModel({
    required this.id,
    required this.type,
    required this.direction,
    required this.amount,
    required this.currency,
    required this.occurredAt,
    this.notes,
    this.reference,
  });

  final int id;
  final String type;
  final String direction;
  final double amount;
  final String currency;
  final DateTime occurredAt;
  final String? notes;
  final String? reference;

  factory EscrowTransactionModel.fromJson(Map<String, dynamic> json) {
    return EscrowTransactionModel(
      id: json['id'] as int,
      type: json['type'] as String,
      direction: json['direction'] as String,
      amount: (json['amount'] as num).toDouble(),
      currency: json['currency'] as String,
      occurredAt: DateTime.parse(json['occurred_at'] as String),
      notes: json['notes'] as String?,
      reference: json['reference'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'type': type,
      'direction': direction,
      'amount': amount,
      'currency': currency,
      'occurred_at': occurredAt.toIso8601String(),
      'notes': notes,
      'reference': reference,
    };
  }
}

class EscrowCacheEntry {
  EscrowCacheEntry({required this.payload, required this.lastUpdated});

  final EscrowModel payload;
  final DateTime lastUpdated;

  factory EscrowCacheEntry.fromJson(Map<String, dynamic> json) {
    return EscrowCacheEntry(
      payload: EscrowModel.fromJson(
          Map<String, dynamic>.from(json['payload'] as Map<dynamic, dynamic>)),
      lastUpdated: DateTime.parse(json['last_updated'] as String),
    );
  }

  Map<String, dynamic> toJson() => {
        'payload': payload.toJson(),
        'last_updated': lastUpdated.toIso8601String(),
      };

  static String encodeList(List<EscrowCacheEntry> entries) =>
      jsonEncode(entries.map((entry) => entry.toJson()).toList());

  static List<EscrowCacheEntry> decodeList(String raw) {
    final data = jsonDecode(raw) as List<dynamic>;
    return data
        .map((item) => EscrowCacheEntry.fromJson(
            Map<String, dynamic>.from(item as Map<dynamic, dynamic>)))
        .toList();
  }

  static EscrowCacheEntry? latest(List<EscrowCacheEntry> entries) {
    return entries.sortedBy<DateTime>((entry) => entry.lastUpdated).lastOrNull;
  }
}
