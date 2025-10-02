import 'package:intl/intl.dart';

import '../../../../common/theme/app_theme.dart';
import '../../../../config.dart';
import '../../../../model/environment_secret_model.dart';
import '../../../../providers/app_pages_provider/environment_checklist_provider.dart';

class EnvironmentSecretSection extends StatelessWidget {
  const EnvironmentSecretSection({super.key, required this.provider});

  final EnvironmentChecklistProvider provider;

  @override
  Widget build(BuildContext context) {
    final theme = appColor(context).appTheme;
    final secrets = provider.secrets;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              language(context, 'secretHealth'),
              style: appCss.dmDenseBold18.textColor(theme.darkText),
            ),
            Container(
              padding:
                  const EdgeInsets.symmetric(horizontal: Insets.i12, vertical: Insets.i6),
              decoration: BoxDecoration(
                color: theme.fieldCardBg,
                borderRadius: BorderRadius.circular(AppRadius.r12),
              ),
              child: Text(
                '${language(context, 'environmentTotalSecrets')} ${secrets.length}',
                style: appCss.dmDenseRegular12.textColor(theme.lightText),
              ),
            )
          ],
        ),
        const VSpace(Sizes.s12),
        Wrap(
          spacing: Insets.i12,
          children: SecretRiskFilter.values
              .map(
                (filter) => ChoiceChip(
                  label: Text(
                    language(context, _filterLabel(filter)),
                    style: appCss.dmDenseRegular12
                        .textColor(provider.filter == filter
                            ? theme.whiteColor
                            : theme.darkText),
                  ),
                  selected: provider.filter == filter,
                  selectedColor: theme.primary,
                  backgroundColor: theme.fieldCardBg,
                  onSelected: (_) => provider.setFilter(filter),
                ),
              )
              .toList(),
        ),
        const VSpace(Sizes.s12),
        if (secrets.isEmpty)
          Text(
            language(context, 'noSecretsFound'),
            style: appCss.dmDenseRegular14.textColor(theme.lightText),
          )
        else
          ...secrets.map(
            (secret) => _SecretCard(
              secret: secret,
              theme: theme,
              provider: provider,
            ).paddingOnly(bottom: Insets.i16),
          ),
      ],
    );
  }

  String _filterLabel(SecretRiskFilter filter) {
    switch (filter) {
      case SecretRiskFilter.all:
        return 'environmentFilterAll';
      case SecretRiskFilter.dueSoon:
        return 'environmentFilterDueSoon';
      case SecretRiskFilter.overdue:
        return 'environmentFilterOverdue';
    }
  }
}

class _SecretCard extends StatelessWidget {
  const _SecretCard({
    required this.secret,
    required this.theme,
    required this.provider,
  });

  final EnvironmentSecret secret;
  final AppTheme theme;
  final EnvironmentChecklistProvider provider;

  @override
  Widget build(BuildContext context) {
    final severity = secret.riskLevel(provider.referenceTime);
    final severityColor = _severityColor(severity, theme);
    final formatter = DateFormat.yMMMd(Localizations.localeOf(context).toString())
        .add_jm();

    return Container(
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
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      secret.name,
                      style: appCss.dmDenseBold16.textColor(theme.darkText),
                    ),
                    const VSpace(Sizes.s4),
                    Text(
                      secret.envKey,
                      style: appCss.dmDenseRegular12
                          .textColor(theme.lightText)
                          .copyWith(fontFamily: 'monospace'),
                    ),
                  ],
                ),
              ),
              _SeverityBadge(
                severity: severity,
                color: severityColor,
              ),
            ],
          ),
          const VSpace(Sizes.s12),
          Wrap(
            spacing: Insets.i12,
            runSpacing: Insets.i8,
            children: [
              _SecretMetaRow(
                icon: Icons.account_circle_outlined,
                label: language(context, 'namespaceOwner'),
                value: secret.owner,
              ),
              _SecretMetaRow(
                icon: Icons.source_outlined,
                label: language(context, 'sourceOfTruth'),
                value: secret.source,
              ),
              if (secret.systems.isNotEmpty)
                _SecretMetaRow(
                  icon: Icons.hub_outlined,
                  label: language(context, 'environmentSystems'),
                  value: secret.systems.join(', '),
                ),
            ],
          ),
          const VSpace(Sizes.s12),
          Row(
            children: [
              Expanded(
                child: _SecretDateColumn(
                  title: language(context, 'rotationDue'),
                  date: secret.nextRotationDueAt,
                  formatter: formatter,
                  theme: theme,
                ),
              ),
              const HSpace(Sizes.s16),
              Expanded(
                child: _SecretDateColumn(
                  title: language(context, 'lastValidated'),
                  date: secret.lastValidatedAt,
                  formatter: formatter,
                  theme: theme,
                  highlight: secret.isValidationStale(provider.referenceTime),
                ),
              ),
            ],
          ),
          if (secret.notes.isNotEmpty) ...[
            const VSpace(Sizes.s12),
            Text(
              secret.notes,
              style: appCss.dmDenseRegular12.textColor(theme.lightText),
            ),
          ],
          if (secret.validators.isNotEmpty) ...[
            const VSpace(Sizes.s12),
            Text(
              language(context, 'validationMethod'),
              style: appCss.dmDenseMedium12.textColor(theme.darkText),
            ),
            const VSpace(Sizes.s6),
            ...secret.validators.take(2).map(
                  (validator) => Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Icon(Icons.check_circle_outline,
                          size: Sizes.s16, color: theme.primary),
                      const HSpace(Sizes.s8),
                      Expanded(
                        child: Text(
                          '${validator.description}\n${validator.command}',
                          style: appCss.dmDenseRegular12
                              .textColor(theme.lightText)
                              .copyWith(fontFamily: 'monospace'),
                        ),
                      ),
                    ],
                  ),
                ),
          ],
        ],
      ),
    );
  }

  Color _severityColor(SecretRiskLevel level, AppTheme theme) {
    switch (level) {
      case SecretRiskLevel.overdue:
        return theme.red;
      case SecretRiskLevel.dueSoon:
        return theme.pending;
      case SecretRiskLevel.healthy:
        return theme.green;
    }
  }
}

class _SeverityBadge extends StatelessWidget {
  const _SeverityBadge({required this.severity, required this.color});

  final SecretRiskLevel severity;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(
          horizontal: Insets.i12, vertical: Insets.i6),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(AppRadius.r12),
        border: Border.all(color: color.withOpacity(0.4)),
      ),
      child: Text(
        language(context, _severityLabel(severity)),
        style: appCss.dmDenseMedium12.textColor(color),
      ),
    );
  }

  String _severityLabel(SecretRiskLevel severity) {
    switch (severity) {
      case SecretRiskLevel.overdue:
        return 'secretsOverdue';
      case SecretRiskLevel.dueSoon:
        return 'secretsDueSoon';
      case SecretRiskLevel.healthy:
        return 'secretsHealthy';
    }
  }
}

class _SecretMetaRow extends StatelessWidget {
  const _SecretMetaRow({
    required this.icon,
    required this.label,
    required this.value,
  });

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    final theme = appColor(context).appTheme;
    return Container(
      constraints: const BoxConstraints(minWidth: 160),
      padding: const EdgeInsets.all(Insets.i12),
      decoration: BoxDecoration(
        color: theme.fieldCardBg,
        borderRadius: BorderRadius.circular(AppRadius.r12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: Sizes.s16, color: theme.primary),
              const HSpace(Sizes.s8),
              Expanded(
                child: Text(
                  label,
                  style: appCss.dmDenseMedium12.textColor(theme.darkText),
                ),
              ),
            ],
          ),
          const VSpace(Sizes.s6),
          Text(
            value,
            style: appCss.dmDenseRegular12.textColor(theme.lightText),
          ),
        ],
      ),
    );
  }
}

class _SecretDateColumn extends StatelessWidget {
  const _SecretDateColumn({
    required this.title,
    required this.date,
    required this.formatter,
    required this.theme,
    this.highlight = false,
  });

  final String title;
  final DateTime? date;
  final DateFormat formatter;
  final AppTheme theme;
  final bool highlight;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: appCss.dmDenseMedium12.textColor(theme.darkText),
        ),
        const VSpace(Sizes.s4),
        Text(
          date == null ? language(context, 'notAvailable') : formatter.format(date!.toLocal()),
          style: (highlight ? appCss.dmDenseBold12 : appCss.dmDenseRegular12)
              .textColor(highlight ? theme.red : theme.lightText),
        ),
      ],
    );
  }
}
