import '../../../../common/theme/app_theme.dart';
import '../../../../config.dart';
import '../../../../model/environment_secret_model.dart';
import '../../../../providers/app_pages_provider/environment_checklist_provider.dart';

class EnvironmentNamespaceSection extends StatelessWidget {
  const EnvironmentNamespaceSection({super.key, required this.provider});

  final EnvironmentChecklistProvider provider;

  @override
  Widget build(BuildContext context) {
    final namespaces = provider.checklist?.namespaces ?? const [];
    final theme = appColor(context).appTheme;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          language(context, 'environmentNamespaces'),
          style: appCss.dmDenseBold18.textColor(theme.darkText),
        ),
        const VSpace(Sizes.s12),
        if (namespaces.isEmpty)
          Text(
            language(context, 'environmentNoNamespaces'),
            style: appCss.dmDenseRegular14.textColor(theme.lightText),
          )
        else
          ...namespaces.map(
            (namespace) => _NamespaceCard(
              namespace: namespace,
              provider: provider,
              theme: theme,
            ).paddingOnly(bottom: Insets.i16),
          ),
      ],
    );
  }
}

class _NamespaceCard extends StatelessWidget {
  const _NamespaceCard({
    required this.namespace,
    required this.provider,
    required this.theme,
  });

  final EnvironmentNamespace namespace;
  final EnvironmentChecklistProvider provider;
  final AppTheme theme;

  @override
  Widget build(BuildContext context) {
    final secrets = provider.secretsForNamespace(namespace).toList();
    final now = provider.referenceTime;
    final riskGroups = {
      SecretRiskLevel.overdue:
          secrets.where((secret) => secret.isRotationOverdue(now)).length,
      SecretRiskLevel.dueSoon: secrets
          .where((secret) =>
              secret.isRotationDueSoon(now, dueSoonWindowDays: 14) &&
              !secret.isRotationOverdue(now))
          .length,
      SecretRiskLevel.healthy: secrets
          .where((secret) =>
              !secret.isRotationOverdue(now) &&
              !secret.isRotationDueSoon(now, dueSoonWindowDays: 14))
          .length,
    };

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
            children: [
              Expanded(
                child: Text(
                  namespace.name,
                  style: appCss.dmDenseBold16.textColor(theme.darkText),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                    horizontal: Insets.i12, vertical: Insets.i6),
                decoration: BoxDecoration(
                  color: theme.primary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(AppRadius.r12),
                ),
                child: Text(
                  namespace.tier.toUpperCase(),
                  style: appCss.dmDenseMedium12.textColor(theme.primary),
                ),
              ),
            ],
          ),
          const VSpace(Sizes.s12),
          Wrap(
            spacing: Insets.i12,
            runSpacing: Insets.i8,
            children: [
              _NamespaceMetaChip(
                icon: Icons.account_tree_outlined,
                label: language(context, 'namespaceOwner'),
                value: namespace.owner,
                theme: theme,
              ),
              _NamespaceMetaChip(
                icon: Icons.campaign_outlined,
                label: language(context, 'environmentEscalation'),
                value: namespace.escalation,
                theme: theme,
              ),
              if (namespace.delegates.isNotEmpty)
                _NamespaceMetaChip(
                  icon: Icons.group_outlined,
                  label: language(context, 'environmentDelegates'),
                  value: namespace.delegates.join(', '),
                  theme: theme,
                ),
              if (namespace.regions.isNotEmpty)
                _NamespaceMetaChip(
                  icon: Icons.public,
                  label: language(context, 'environmentRegions'),
                  value: namespace.regions.join(', '),
                  theme: theme,
                ),
              _NamespaceMetaChip(
                icon: Icons.verified_user_outlined,
                label: language(context, 'complianceTier'),
                value: namespace.complianceTier,
                theme: theme,
              ),
            ],
          ),
          const VSpace(Sizes.s12),
          if (namespace.description.isNotEmpty)
            Text(
              namespace.description,
              style: appCss.dmDenseRegular12.textColor(theme.lightText),
            ),
          const VSpace(Sizes.s12),
          Wrap(
            spacing: Insets.i12,
            runSpacing: Insets.i8,
            children: [
              _RiskChip(
                label: language(context, 'secretsOverdue'),
                count: riskGroups[SecretRiskLevel.overdue] ?? 0,
                color: theme.red,
              ),
              _RiskChip(
                label: language(context, 'secretsDueSoon'),
                count: riskGroups[SecretRiskLevel.dueSoon] ?? 0,
                color: theme.pending,
              ),
              _RiskChip(
                label: language(context, 'secretsHealthy'),
                count: riskGroups[SecretRiskLevel.healthy] ?? 0,
                color: theme.green,
              ),
            ],
          ),
          if (namespace.validations.isNotEmpty) ...[
            const VSpace(Sizes.s12),
            Text(
              language(context, 'namespaceValidations'),
              style: appCss.dmDenseMedium12.textColor(theme.darkText),
            ),
            const VSpace(Sizes.s6),
            ...namespace.validations.map(
              (validation) => _ValidationRow(
                validation: validation,
                theme: theme,
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _NamespaceMetaChip extends StatelessWidget {
  const _NamespaceMetaChip({
    required this.icon,
    required this.label,
    required this.value,
    required this.theme,
  });

  final IconData icon;
  final String label;
  final String value;
  final AppTheme theme;

  @override
  Widget build(BuildContext context) {
    return Container(
      constraints: const BoxConstraints(minWidth: 160),
      padding: const EdgeInsets.all(Insets.i12),
      decoration: ShapeDecoration(
        color: theme.fieldCardBg,
        shape: SmoothRectangleBorder(
          borderRadius: SmoothBorderRadius(
            cornerRadius: AppRadius.r12,
            cornerSmoothing: 1,
          ),
        ),
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

class _RiskChip extends StatelessWidget {
  const _RiskChip({
    required this.label,
    required this.count,
    required this.color,
  });

  final String label;
  final int count;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final theme = appColor(context).appTheme;
    return Container(
      padding: const EdgeInsets.symmetric(
          horizontal: Insets.i12, vertical: Insets.i8),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(AppRadius.r12),
        border: Border.all(color: color.withOpacity(0.4)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: Sizes.s8,
            height: Sizes.s8,
            decoration: BoxDecoration(
              color: color,
              shape: BoxShape.circle,
            ),
          ),
          const HSpace(Sizes.s8),
          Text(
            '$count',
            style: appCss.dmDenseBold14.textColor(color),
          ),
          const HSpace(Sizes.s6),
          Text(
            label,
            style: appCss.dmDenseRegular12.textColor(theme.darkText),
          ),
        ],
      ),
    );
  }
}

class _ValidationRow extends StatelessWidget {
  const _ValidationRow({required this.validation, required this.theme});

  final EnvironmentNamespaceValidation validation;
  final AppTheme theme;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: Insets.i8),
      padding: const EdgeInsets.all(Insets.i12),
      decoration: BoxDecoration(
        color: theme.fieldCardBg,
        borderRadius: BorderRadius.circular(AppRadius.r12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            validation.description,
            style: appCss.dmDenseMedium12.textColor(theme.darkText),
          ),
          const VSpace(Sizes.s4),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(Icons.terminal, size: Sizes.s14, color: theme.primary),
              const HSpace(Sizes.s6),
              Expanded(
                child: Text(
                  validation.command,
                  style: appCss.dmDenseRegular12
                      .textColor(theme.lightText)
                      .copyWith(fontFamily: 'monospace'),
                ),
              ),
            ],
          ),
          if (validation.evidencePath.isNotEmpty) ...[
            const VSpace(Sizes.s4),
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(Icons.folder_open, size: Sizes.s14, color: theme.primary),
                const HSpace(Sizes.s6),
                Expanded(
                  child: Text(
                    validation.evidencePath,
                    style: appCss.dmDenseRegular12.textColor(theme.lightText),
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }
}
