import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../../config.dart';
import '../../../models/affiliate_dashboard_model.dart';
import '../../../services/affiliate/affiliate_repository.dart';

class AffiliateHubScreen extends StatefulWidget {
  const AffiliateHubScreen({super.key});

  @override
  State<AffiliateHubScreen> createState() => _AffiliateHubScreenState();
}

class _AffiliateHubScreenState extends State<AffiliateHubScreen> {
  final AffiliateRepository _repository = AffiliateRepository();
  Future<AffiliateDashboardModel>? _future;

  @override
  void initState() {
    super.initState();
    _future = _repository.fetchDashboard();
  }

  Future<void> _refresh() async {
    setState(() {
      _future = _repository.fetchDashboard(preferCache: false);
    });
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: Text(translations?.affiliate ?? 'Affiliate overview'),
        actions: [
          IconButton(
            tooltip: translations?.refresh ?? 'Refresh',
            onPressed: _refresh,
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: SafeArea(
        child: FutureBuilder<AffiliateDashboardModel>(
          future: _future,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snapshot.hasError) {
              return EmptyLayout(
                title: translations?.error ?? 'Unable to load dashboard',
                subtitle: snapshot.error.toString(),
                buttonText: translations?.retry ?? 'Retry',
                onTap: _refresh,
              );
            }
            final dashboard = snapshot.data ?? AffiliateDashboardModel.sample();
            return RefreshIndicator(
              onRefresh: _refresh,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  _buildSummaryGrid(context, theme, dashboard),
                  const SizedBox(height: 16),
                  _buildPerformanceSection(theme, dashboard),
                  const SizedBox(height: 16),
                  _buildChannelSection(theme, dashboard),
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildSummaryGrid(
      BuildContext context, ThemeData theme, AffiliateDashboardModel dashboard) {
    final tiles = [
      _SummaryTile(
        label: translations?.totalEarnings ?? 'Total earnings',
        value: dashboard.formatCurrency(symbol: r'$'),
        icon: Icons.payments_outlined,
      ),
      _SummaryTile(
        label: translations?.pending ?? 'Pending payout',
        value: NumberFormat.simpleCurrency(name: '').format(dashboard.pendingPayout),
        icon: Icons.schedule_outlined,
      ),
      _SummaryTile(
        label: translations?.paid ?? 'Paid out',
        value: NumberFormat.simpleCurrency(name: '').format(dashboard.paidPayout),
        icon: Icons.account_balance_wallet_outlined,
      ),
      _SummaryTile(
        label: translations?.clicks ?? 'Clicks',
        value: NumberFormat.decimalPattern().format(dashboard.totalClicks),
        icon: Icons.mouse_outlined,
      ),
      _SummaryTile(
        label: translations?.conversions ?? 'Conversions',
        value: NumberFormat.decimalPattern().format(dashboard.totalConversions),
        icon: Icons.check_circle_outline,
      ),
      _SummaryTile(
        label: translations?.conversionRate ?? 'Conversion rate',
        value: '${dashboard.conversionRate.toStringAsFixed(2)}%',
        icon: Icons.trending_up_outlined,
      ),
    ];

    final width = MediaQuery.of(context).size.width;
    final columns = width > 680 ? 3 : 2;
    final tileWidth = (width - ((columns + 1) * 12)) / columns;
    return Wrap(
      spacing: 12,
      runSpacing: 12,
      children: tiles
          .map(
            (tile) => ConstrainedBox(
              constraints: BoxConstraints(maxWidth: tileWidth),
              child: tile,
            ),
          )
          .toList(),
    );
  }

  Widget _buildPerformanceSection(
    ThemeData theme,
    AffiliateDashboardModel dashboard,
  ) {
    final dateFormatter = DateFormat.MMMd();
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              translations?.performance ?? 'Performance (7 days)',
              style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 12),
            DataTable(
              headingTextStyle: theme.textTheme.labelMedium?.copyWith(fontWeight: FontWeight.bold),
              columns: const [
                DataColumn(label: Text('Date')),
                DataColumn(label: Text('Clicks')),
                DataColumn(label: Text('Conversions')),
                DataColumn(label: Text('Revenue')),
              ],
              rows: dashboard.performance
                  .map(
                    (metric) => DataRow(
                      cells: [
                        DataCell(Text(dateFormatter.format(metric.date))),
                        DataCell(Text(NumberFormat.decimalPattern().format(metric.clicks))),
                        DataCell(Text(NumberFormat.decimalPattern().format(metric.conversions))),
                        DataCell(Text(NumberFormat.simpleCurrency(name: '').format(metric.revenue))),
                      ],
                    ),
                  )
                  .toList(),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildChannelSection(ThemeData theme, AffiliateDashboardModel dashboard) {
    return Card(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Text(
              translations?.topChannels ?? 'Top channels',
              style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600),
            ),
          ),
          const Divider(height: 1),
          for (final channel in dashboard.topChannels)
            ListTile(
              leading: const Icon(Icons.campaign_outlined),
              title: Text(channel.channel),
              subtitle: Text(
                  '${NumberFormat.decimalPattern().format(channel.clicks)} clicks • ${NumberFormat.decimalPattern().format(channel.conversions)} conversions'),
              trailing: Text(NumberFormat.simpleCurrency(name: '').format(channel.revenue)),
            ),
        ],
      ),
    );
  }
}

class _SummaryTile extends StatelessWidget {
  const _SummaryTile({
    required this.label,
    required this.value,
    required this.icon,
  });

  final String label;
  final String value;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Card(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, color: theme.colorScheme.primary),
            const SizedBox(height: 12),
            Text(
              value,
              style: theme.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: theme.textTheme.bodySmall,
            ),
          ],
        ),
      ),
    );
  }
}
