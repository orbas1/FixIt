class FeedJobBidModel {
  FeedJobBidModel({
    required this.id,
    required this.amount,
    required this.status,
    this.providerId,
    this.providerName,
    this.createdAt,
  });

  final int id;
  final double amount;
  final String status;
  final int? providerId;
  final String? providerName;
  final DateTime? createdAt;

  factory FeedJobBidModel.fromJson(Map<String, dynamic> json) {
    return FeedJobBidModel(
      id: json['id'] ?? 0,
      amount: (json['amount'] ?? 0).toDouble(),
      status: json['status'] ?? '',
      providerId: json['provider'] != null ? json['provider']['id'] as int? : null,
      providerName: json['provider'] != null ? json['provider']['name'] as String? : null,
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at']) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'amount': amount,
      'status': status,
      'provider': {
        'id': providerId,
        'name': providerName,
      },
      'created_at': createdAt?.toIso8601String(),
    };
  }
}

class FeedJobModel {
  FeedJobModel({
    required this.id,
    required this.title,
    required this.description,
    required this.status,
    required this.initialBudget,
    this.finalBudget,
    this.currency,
    this.durationValue,
    this.durationUnit,
    this.requiredServicemen,
    this.attachments,
    this.location,
    this.distanceKm,
    this.bidsSummary,
    this.createdAt,
    this.bookingDate,
  });

  final int id;
  final String title;
  final String? description;
  final String status;
  final double? initialBudget;
  final double? finalBudget;
  final String? currency;
  final String? durationValue;
  final String? durationUnit;
  final int? requiredServicemen;
  final List<dynamic>? attachments;
  final Map<String, dynamic>? location;
  final double? distanceKm;
  final Map<String, dynamic>? bidsSummary;
  final DateTime? createdAt;
  final DateTime? bookingDate;

  factory FeedJobModel.fromJson(Map<String, dynamic> json) {
    return FeedJobModel(
      id: json['id'] ?? 0,
      title: json['title'] ?? '',
      description: json['description'],
      status: json['status'] ?? '',
      initialBudget: json['budget'] != null ? (json['budget']['initial'] ?? 0).toDouble() : null,
      finalBudget: json['budget'] != null && json['budget']['final'] != null
          ? (json['budget']['final']).toDouble()
          : null,
      currency: json['budget'] != null ? json['budget']['currency'] as String? : null,
      durationValue: json['duration'] != null ? json['duration']['value']?.toString() : null,
      durationUnit: json['duration'] != null ? json['duration']['unit'] as String? : null,
      requiredServicemen: json['required_servicemen'],
      attachments: (json['attachments'] as List?)?.toList(),
      location: json['location'] as Map<String, dynamic>?,
      distanceKm: json['distance_km'] != null ? (json['distance_km']).toDouble() : null,
      bidsSummary: json['bids'] as Map<String, dynamic>?,
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at']) : null,
      bookingDate: json['booking_date'] != null ? DateTime.tryParse(json['booking_date']) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'status': status,
      'budget': {
        'initial': initialBudget,
        'final': finalBudget,
        'currency': currency,
      },
      'duration': {
        'value': durationValue,
        'unit': durationUnit,
      },
      'required_servicemen': requiredServicemen,
      'attachments': attachments,
      'location': location,
      'distance_km': distanceKm,
      'bids': bidsSummary,
      'created_at': createdAt?.toIso8601String(),
      'booking_date': bookingDate?.toIso8601String(),
    };
  }
}
