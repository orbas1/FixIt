import 'package:intl/intl.dart';

import '../../../../common/theme/app_theme.dart';
import '../../../../config.dart';
import '../../../../providers/app_pages_provider/environment_checklist_provider.dart';

class EnvironmentSummaryHeader extends StatelessWidget {
  const EnvironmentSummaryHeader({super.key, required this.provider});

  final EnvironmentChecklistProvider provider;

  @override
  Widget build(BuildContext context) {
    final theme = appColor(context).appTheme;
    final formatter = DateFormat.yMMMd(Localizations.localeOf(context).toString())
        .add_jm();
    final metrics = <_SummaryMetric>[
      _SummaryMetric(
        label: language(context, 'namespacesCount'),
        value: provider.namespaceCount.toString(),
        subtitle: language(context, 'environmentNamespacesSubtitle'),
        color: theme.primary,
      ),
      _SummaryMetric(
        label: language(context, 'secretsCount'),
        value: provider.secretCount.toString(),
        subtitle: language(context, 'environmentSecretsSubtitle'),
        color: theme.green,
      ),
      _SummaryMetric(
        label: language(context, 'secretsDueSoon'),
        value: provider.dueSoonCount.toString(),
        subtitle: language(context, 'environmentDueSoonSubtitle'),
        color: theme.pending,
      ),
      _SummaryMetric(
        label: language(context, 'secretsOverdue'),
        value: provider.overdueCount.toString(),
        subtitle: language(context, 'environmentOverdueSubtitle'),
        color: theme.red,
      ),
      _SummaryMetric(
        label: language(context, 'environmentValidationAlerts'),
        value: provider.validationAlertsCount.toString(),
        subtitle: language(context, 'environmentValidationSubtitle'),
        color: theme.pendingApproval,
      ),
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          language(context, 'environmentSummary'),
          style:
              appCss.dmDenseBold18.textColor(theme.darkText),
        ),
        const VSpace(Sizes.s12),
        Wrap(
          spacing: Insets.i12,
          runSpacing: Insets.i12,
          children: metrics
              .map((metric) => _SummaryCard(metric: metric, theme: theme))
              .toList(),
        ),
        const VSpace(Sizes.s12),
        Row(
          children: [
            Icon(Icons.schedule, size: Sizes.s16, color: theme.lightText),
            const HSpace(Sizes.s8),
            Expanded(
              child: Text(
                '${language(context, 'environmentChecklistUpdated')} '
                '${formatter.format(provider.generatedAt.toLocal())}',
                style: appCss.dmDenseRegular12.textColor(theme.lightText),
              ),
            ),
          ],
        ),
      ],
    );
  }
}

class _SummaryMetric {
  const _SummaryMetric({
    required this.label,
    required this.value,
    required this.subtitle,
    required this.color,
  });

  final String label;
  final String value;
  final String subtitle;
  final Color color;
}

class _SummaryCard extends StatelessWidget {
  const _SummaryCard({required this.metric, required this.theme});

  final _SummaryMetric metric;
  final AppTheme theme;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: MediaQuery.of(context).size.width < 420
          ? double.infinity
          : (MediaQuery.of(context).size.width - Insets.i80) / 2,
      padding: const EdgeInsets.all(Insets.i16),
      decoration: ShapeDecoration(
        color: theme.whiteBg,
        shape: SmoothRectangleBorder(
          borderRadius: SmoothBorderRadius(
            cornerRadius: AppRadius.r16,
            cornerSmoothing: 1,
          ),
        ),
        shadows: [
          BoxShadow(
            color: theme.fieldCardBg,
            blurRadius: 12,
            spreadRadius: 2,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            metric.label,
            style: appCss.dmDenseMedium12.textColor(theme.lightText),
          ),
          const VSpace(Sizes.s8),
          Text(
            metric.value,
            style: appCss.dmDenseBold24.textColor(metric.color),
          ),
          const VSpace(Sizes.s6),
          Text(
            metric.subtitle,
            style: appCss.dmDenseRegular12.textColor(theme.lightText),
          ),
        ],
      ),
    );
  }
}
