import 'package:intl/intl.dart';

class AffiliateDashboardModel {
  AffiliateDashboardModel({
    required this.totalEarnings,
    required this.pendingPayout,
    required this.paidPayout,
    required this.totalClicks,
    required this.totalConversions,
    required this.conversionRate,
    required this.topChannels,
    required this.performance,
  });

  final double totalEarnings;
  final double pendingPayout;
  final double paidPayout;
  final int totalClicks;
  final int totalConversions;
  final double conversionRate;
  final List<AffiliateChannelPerformance> topChannels;
  final List<AffiliatePerformanceMetric> performance;

  String formatCurrency({String symbol = '\$'}) {
    final formatter = NumberFormat.simpleCurrency(name: '', decimalDigits: 2);
    return '$symbol${formatter.format(totalEarnings)}';
  }

  factory AffiliateDashboardModel.fromJson(Map<String, dynamic> json) {
    final List<dynamic> channelJson = json['channels'] as List<dynamic>? ?? [];
    final List<dynamic> performanceJson =
        json['performance'] as List<dynamic>? ?? [];
    return AffiliateDashboardModel(
      totalEarnings: _double(json['total_earnings']),
      pendingPayout: _double(json['pending_payouts']),
      paidPayout: _double(json['paid_payouts']),
      totalClicks: _int(json['total_clicks']),
      totalConversions: _int(json['total_conversions']),
      conversionRate: _double(json['conversion_rate']),
      topChannels: channelJson
          .map((entry) => AffiliateChannelPerformance.fromJson(
              Map<String, dynamic>.from(entry as Map)))
          .toList(),
      performance: performanceJson
          .map((entry) => AffiliatePerformanceMetric.fromJson(
              Map<String, dynamic>.from(entry as Map)))
          .toList(),
    );
  }

  static AffiliateDashboardModel sample() {
    return AffiliateDashboardModel(
      totalEarnings: 18240.75,
      pendingPayout: 3420.50,
      paidPayout: 14820.25,
      totalClicks: 18450,
      totalConversions: 1260,
      conversionRate: 6.83,
      topChannels: const [
        AffiliateChannelPerformance(
          channel: 'YouTube Reviews',
          clicks: 8200,
          conversions: 540,
          revenue: 9450.40,
        ),
        AffiliateChannelPerformance(
          channel: 'Instagram Stories',
          clicks: 5600,
          conversions: 420,
          revenue: 6120.00,
        ),
        AffiliateChannelPerformance(
          channel: 'Newsletter CTA',
          clicks: 2650,
          conversions: 185,
          revenue: 2215.90,
        ),
      ],
      performance: List.generate(
        7,
        (index) => AffiliatePerformanceMetric(
          date: DateTime.now().subtract(Duration(days: 6 - index)),
          clicks: 2200 + (index * 120),
          conversions: 150 + (index * 10),
          revenue: 1450.0 + (index * 90),
        ),
      ),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'total_earnings': totalEarnings,
      'pending_payouts': pendingPayout,
      'paid_payouts': paidPayout,
      'total_clicks': totalClicks,
      'total_conversions': totalConversions,
      'conversion_rate': conversionRate,
      'channels': topChannels.map((channel) => channel.toJson()).toList(),
      'performance': performance.map((metric) => metric.toJson()).toList(),
    };
  }

  static double _double(dynamic value) {
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0;
    return 0;
  }

  static int _int(dynamic value) {
    if (value is int) return value;
    if (value is num) return value.toInt();
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }
}

class AffiliateChannelPerformance {
  const AffiliateChannelPerformance({
    required this.channel,
    required this.clicks,
    required this.conversions,
    required this.revenue,
  });

  final String channel;
  final int clicks;
  final int conversions;
  final double revenue;

  factory AffiliateChannelPerformance.fromJson(Map<String, dynamic> json) {
    return AffiliateChannelPerformance(
      channel: json['channel'] as String? ?? 'Unknown',
      clicks: AffiliateDashboardModel._int(json['clicks']),
      conversions: AffiliateDashboardModel._int(json['conversions']),
      revenue: AffiliateDashboardModel._double(json['revenue']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'channel': channel,
      'clicks': clicks,
      'conversions': conversions,
      'revenue': revenue,
    };
  }
}

class AffiliatePerformanceMetric {
  AffiliatePerformanceMetric({
    required this.date,
    required this.clicks,
    required this.conversions,
    required this.revenue,
  });

  final DateTime date;
  final int clicks;
  final int conversions;
  final double revenue;

  factory AffiliatePerformanceMetric.fromJson(Map<String, dynamic> json) {
    return AffiliatePerformanceMetric(
      date: DateTime.tryParse(json['date'] as String? ?? '') ?? DateTime.now(),
      clicks: AffiliateDashboardModel._int(json['clicks']),
      conversions: AffiliateDashboardModel._int(json['conversions']),
      revenue: AffiliateDashboardModel._double(json['revenue']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'date': date.toIso8601String(),
      'clicks': clicks,
      'conversions': conversions,
      'revenue': revenue,
    };
  }
}
