import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../../../common/theme/app_theme.dart';
import '../../../../config.dart';
import '../../../../model/architecture_governance_model.dart';
import '../../../../providers/app_pages_provider/architecture_governance_provider.dart';

class ArchitectureGovernanceSection extends StatelessWidget {
  const ArchitectureGovernanceSection({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = appColor(context).appTheme;
    return Consumer<ArchitectureGovernanceProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading && provider.checklist == null) {
          return _LoadingCard(theme: theme);
        }
        if (provider.errorMessage != null && provider.checklist == null) {
          return _ErrorCard(
            theme: theme,
            message: provider.errorMessage!,
            onRetry: provider.refresh,
          );
        }
        if (provider.checklist == null) {
          return const SizedBox.shrink();
        }
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              language(context, 'architectureGovernanceTitle'),
              style: appCss.dmDenseBold18.textColor(theme.darkText),
            ),
            const VSpace(Sizes.s12),
            _BoardSummaryCard(provider: provider, theme: theme),
            const VSpace(Sizes.s16),
            _CadenceList(provider: provider, theme: theme),
            const VSpace(Sizes.s16),
            _DecisionList(provider: provider, theme: theme),
            const VSpace(Sizes.s16),
            _CapabilityRoadmap(provider: provider, theme: theme),
            const VSpace(Sizes.s16),
            _RiskRegister(provider: provider, theme: theme),
          ],
        );
      },
    );
  }
}

class _LoadingCard extends StatelessWidget {
  const _LoadingCard({required this.theme});

  final AppTheme theme;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(Insets.i16),
      decoration: BoxDecoration(
        color: theme.fieldCardBg,
        borderRadius: BorderRadius.circular(AppRadius.r12),
      ),
      child: Row(
        children: [
          CircularProgressIndicator(color: theme.primary),
          const HSpace(Sizes.s12),
          Expanded(
            child: Text(
              language(context, 'loadingArchitectureGovernance'),
              style: appCss.dmDenseMedium14.textColor(theme.darkText),
            ),
          )
        ],
      ),
    );
  }
}

class _ErrorCard extends StatelessWidget {
  const _ErrorCard({
    required this.theme,
    required this.message,
    required this.onRetry,
  });

  final AppTheme theme;
  final String message;
  final Future<void> Function() onRetry;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(Insets.i16),
      decoration: BoxDecoration(
        color: theme.red.withOpacity(0.08),
        borderRadius: BorderRadius.circular(AppRadius.r12),
        border: Border.all(color: theme.red.withOpacity(0.4)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            language(context, 'architectureGovernanceErrorTitle'),
            style: appCss.dmDenseBold16.textColor(theme.red),
          ),
          const VSpace(Sizes.s8),
          Text(
            message,
            style: appCss.dmDenseRegular12.textColor(theme.red),
          ),
          const VSpace(Sizes.s12),
          Align(
            alignment: Alignment.centerRight,
            child: TextButton(
              onPressed: onRetry,
              child: Text(language(context, 'retry')),
            ),
          )
        ],
      ),
    );
  }
}

class _BoardSummaryCard extends StatelessWidget {
  const _BoardSummaryCard({required this.provider, required this.theme});

  final ArchitectureGovernanceProvider provider;
  final AppTheme theme;

  @override
  Widget build(BuildContext context) {
    final board = provider.board;
    if (board == null) {
      return const SizedBox.shrink();
    }
    final dateFormatter = DateFormat('yyyy-MM-dd HH:mm');
    final nextSession = board.nextSession != null
        ? dateFormatter.format(board.nextSession!.toLocal())
        : language(context, 'notScheduled');
    final charterReview = board.charterReviewDate != null
        ? DateFormat('yyyy-MM-dd').format(board.charterReviewDate!.toLocal())
        : language(context, 'notScheduled');
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(Insets.i16),
      decoration: BoxDecoration(
        color: theme.fieldCardBg,
        borderRadius: BorderRadius.circular(AppRadius.r12),
        border: Border.all(color: theme.stroke.withOpacity(0.6)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.account_tree_outlined, color: theme.primary),
              const HSpace(Sizes.s8),
              Expanded(
                child: Text(
                  language(context, 'architectureBoardSummary'),
                  style: appCss.dmDenseMedium16.textColor(theme.darkText),
                ),
              ),
              _StatusPill(
                label: provider.quorumSatisfied
                    ? language(context, 'quorumMet')
                    : language(context, 'quorumNotMet'),
                color: provider.quorumSatisfied ? theme.green : theme.red,
              )
            ],
          ),
          const VSpace(Sizes.s12),
          Wrap(
            spacing: Insets.i16,
            runSpacing: Insets.i8,
            children: [
              _SummaryChip(
                label: language(context, 'generatedAtLabel'),
                value: dateFormatter.format(provider.generatedAt.toLocal()),
              ),
              _SummaryChip(
                label: language(context, 'boardCharterReview'),
                value: charterReview,
              ),
              _SummaryChip(
                label: language(context, 'nextArbSession'),
                value: nextSession,
              ),
              _SummaryChip(
                label: language(context, 'votingMembers'),
                value: board.votingMemberCount.toString(),
              ),
              _SummaryChip(
                label: language(context, 'boardQuorum'),
                value: board.quorum.toString(),
              ),
              _SummaryChip(
                label: language(context, 'dependencyCoverageLabel'),
                value:
                    '${(provider.dependencyCoverage * 100).toStringAsFixed(1)}%',
              ),
            ],
          ),
          const VSpace(Sizes.s12),
          Text(
            language(context, 'escalationPolicyLabel'),
            style: appCss.dmDenseMedium14.textColor(theme.darkText),
          ),
          const VSpace(Sizes.s4),
          Text(
            board.escalationPolicy,
            style: appCss.dmDenseRegular12.textColor(theme.lightText),
          ),
          const VSpace(Sizes.s12),
          Text(
            language(context, 'boardMembersLabel'),
            style: appCss.dmDenseMedium14.textColor(theme.darkText),
          ),
          const VSpace(Sizes.s8),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: board.members
                .map(
                  (member) => Padding(
                    padding: const EdgeInsets.only(bottom: Insets.i8),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(
                          member.votingMember
                              ? Icons.how_to_vote
                              : Icons.remove_circle_outline,
                          size: 18,
                          color: member.votingMember
                              ? theme.primary
                              : theme.lightText,
                        ),
                        const HSpace(Sizes.s8),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '${member.name} · ${member.role}',
                                style: appCss.dmDenseMedium13
                                    .textColor(theme.darkText),
                              ),
                              const VSpace(Sizes.s4),
                              Text(
                                member.responsibilities.join(', '),
                                style: appCss.dmDenseRegular11
                                    .textColor(theme.lightText),
                              ),
                            ],
                          ),
                        )
                      ],
                    ),
                  ),
                )
                .toList(),
          )
        ],
      ),
    );
  }
}

class _CadenceList extends StatelessWidget {
  const _CadenceList({required this.provider, required this.theme});

  final ArchitectureGovernanceProvider provider;
  final AppTheme theme;

  @override
  Widget build(BuildContext context) {
    if (provider.cadences.isEmpty) {
      return const SizedBox.shrink();
    }
    final dateFormatter = DateFormat('yyyy-MM-dd HH:mm');
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(Insets.i16),
      decoration: BoxDecoration(
        color: theme.fieldCardBg,
        borderRadius: BorderRadius.circular(AppRadius.r12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.schedule, color: theme.primary),
              const HSpace(Sizes.s8),
              Text(
                language(context, 'governanceCadences'),
                style: appCss.dmDenseMedium16.textColor(theme.darkText),
              ),
            ],
          ),
          const VSpace(Sizes.s12),
          Column(
            children: provider.cadences
                .map(
                  (cadence) => Container(
                    width: double.infinity,
                    margin: const EdgeInsets.only(bottom: Insets.i12),
                    padding: const EdgeInsets.all(Insets.i12),
                    decoration: BoxDecoration(
                      color: theme.white,
                      borderRadius: BorderRadius.circular(AppRadius.r10),
                      border: Border.all(color: theme.stroke.withOpacity(0.4)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                cadence.description,
                                style: appCss.dmDenseMedium14
                                    .textColor(theme.darkText),
                              ),
                            ),
                            _StatusPill(
                              label: '${cadence.frequencyDays} ${language(context, 'days')}',
                              color: cadence.isStale(provider.generatedAt)
                                  ? theme.red
                                  : theme.green,
                            )
                          ],
                        ),
                        const VSpace(Sizes.s8),
                        Text(
                          '${language(context, 'lastHeldLabel')}: ${cadence.lastHeld != null ? dateFormatter.format(cadence.lastHeld!.toLocal()) : language(context, 'notRecorded')}',
                          style:
                              appCss.dmDenseRegular12.textColor(theme.lightText),
                        ),
                        const VSpace(Sizes.s6),
                        if (cadence.artifacts.isNotEmpty)
                          Text(
                            '${language(context, 'artifactsLabel')}: ${cadence.artifacts.join(', ')}',
                            style: appCss.dmDenseRegular11
                                .textColor(theme.lightText),
                          ),
                      ],
                    ),
                  ),
                )
                .toList(),
          )
        ],
      ),
    );
  }
}

class _DecisionList extends StatelessWidget {
  const _DecisionList({required this.provider, required this.theme});

  final ArchitectureGovernanceProvider provider;
  final AppTheme theme;

  @override
  Widget build(BuildContext context) {
    final attentionDecisions = provider.decisionsRequiringAttention;
    final allDecisions = provider.decisions;
    final dateFormatter = DateFormat('yyyy-MM-dd');
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(Insets.i16),
      decoration: BoxDecoration(
        color: theme.fieldCardBg,
        borderRadius: BorderRadius.circular(AppRadius.r12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.gavel_outlined, color: theme.primary),
              const HSpace(Sizes.s8),
              Expanded(
                child: Text(
                  language(context, 'architectureDecisionsTitle'),
                  style: appCss.dmDenseMedium16.textColor(theme.darkText),
                ),
              ),
              if (attentionDecisions.isNotEmpty)
                _StatusPill(
                  label: language(context, 'actionsRequired'),
                  color: theme.warning,
                )
            ],
          ),
          const VSpace(Sizes.s12),
          if (attentionDecisions.isNotEmpty) ...[
            Text(
              language(context, 'decisionsNeedingRenewal'),
              style: appCss.dmDenseMedium14.textColor(theme.darkText),
            ),
            const VSpace(Sizes.s8),
            Column(
              children: attentionDecisions
                  .map(
                    (decision) => _DecisionTile(
                      decision: decision,
                      theme: theme,
                      dateFormatter: dateFormatter,
                    ),
                  )
                  .toList(),
            ),
            const VSpace(Sizes.s16),
          ],
          Text(
            language(context, 'allGovernanceDecisions'),
            style: appCss.dmDenseMedium14.textColor(theme.darkText),
          ),
          const VSpace(Sizes.s8),
          Column(
            children: allDecisions
                .map(
                  (decision) => _DecisionTile(
                    decision: decision,
                    theme: theme,
                    dateFormatter: dateFormatter,
                  ),
                )
                .toList(),
          )
        ],
      ),
    );
  }
}

class _DecisionTile extends StatelessWidget {
  const _DecisionTile({
    required this.decision,
    required this.theme,
    required this.dateFormatter,
  });

  final GovernanceDecision decision;
  final AppTheme theme;
  final DateFormat dateFormatter;

  @override
  Widget build(BuildContext context) {
    final renewal = decision.renewalDate != null
        ? dateFormatter.format(decision.renewalDate!.toLocal())
        : language(context, 'notScheduled');
    final decisionDate = decision.decisionDate != null
        ? dateFormatter.format(decision.decisionDate!.toLocal())
        : language(context, 'notRecorded');
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: Insets.i8),
      padding: const EdgeInsets.all(Insets.i12),
      decoration: BoxDecoration(
        color: theme.white,
        borderRadius: BorderRadius.circular(AppRadius.r10),
        border: Border.all(color: theme.stroke.withOpacity(0.4)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  decision.title,
                  style:
                      appCss.dmDenseMedium13.textColor(theme.darkText),
                ),
              ),
              _StatusPill(
                label: decision.status.toUpperCase(),
                color: decision.status == 'accepted'
                    ? theme.green
                    : theme.warning,
              )
            ],
          ),
          const VSpace(Sizes.s6),
          Text(
            '${language(context, 'decisionOwnerLabel')}: ${decision.ownerId}',
            style: appCss.dmDenseRegular11.textColor(theme.lightText),
          ),
          const VSpace(Sizes.s4),
          Text(
            '${language(context, 'decisionDateLabel')}: $decisionDate',
            style: appCss.dmDenseRegular11.textColor(theme.lightText),
          ),
          const VSpace(Sizes.s4),
          Text(
            '${language(context, 'renewalDateLabel')}: $renewal',
            style: appCss.dmDenseRegular11.textColor(theme.lightText),
          ),
          if (decision.linkedCapabilities.isNotEmpty) ...[
            const VSpace(Sizes.s4),
            Text(
              '${language(context, 'linkedCapabilitiesLabel')}: ${decision.linkedCapabilities.join(', ')}',
              style: appCss.dmDenseRegular11.textColor(theme.lightText),
            ),
          ],
          if (!decision.hasEvidence) ...[
            const VSpace(Sizes.s6),
            Row(
              children: [
                Icon(Icons.warning_amber_rounded, color: theme.warning, size: 16),
                const HSpace(Sizes.s4),
                Expanded(
                  child: Text(
                    language(context, 'decisionMissingEvidence'),
                    style: appCss.dmDenseRegular11.textColor(theme.warning),
                  ),
                ),
              ],
            ),
          ]
        ],
      ),
    );
  }
}

class _CapabilityRoadmap extends StatelessWidget {
  const _CapabilityRoadmap({required this.provider, required this.theme});

  final ArchitectureGovernanceProvider provider;
  final AppTheme theme;

  @override
  Widget build(BuildContext context) {
    if (provider.capabilities.isEmpty) {
      return const SizedBox.shrink();
    }
    final grouped = provider.capabilitiesByQuarter;
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(Insets.i16),
      decoration: BoxDecoration(
        color: theme.fieldCardBg,
        borderRadius: BorderRadius.circular(AppRadius.r12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.timeline_outlined, color: theme.primary),
              const HSpace(Sizes.s8),
              Expanded(
                child: Text(
                  language(context, 'capabilityRoadmapTitle'),
                  style: appCss.dmDenseMedium16.textColor(theme.darkText),
                ),
              ),
              if (provider.staleCapabilities.isNotEmpty)
                _StatusPill(
                  label: language(context, 'readinessAttention'),
                  color: theme.warning,
                )
            ],
          ),
          const VSpace(Sizes.s12),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: grouped.entries
                .map(
                  (entry) => Padding(
                    padding: const EdgeInsets.only(bottom: Insets.i12),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          entry.key,
                          style: appCss.dmDenseMedium14.textColor(theme.darkText),
                        ),
                        const VSpace(Sizes.s8),
                        Column(
                          children: entry.value
                              .map(
                                (capability) => _CapabilityTile(
                                  capability: capability,
                                  theme: theme,
                                  isStale: provider.staleCapabilities
                                      .any((item) => item.id == capability.id),
                                ),
                              )
                              .toList(),
                        )
                      ],
                    ),
                  ),
                )
                .toList(),
          )
        ],
      ),
    );
  }
}

class _CapabilityTile extends StatelessWidget {
  const _CapabilityTile({
    required this.capability,
    required this.theme,
    required this.isStale,
  });

  final GovernanceCapability capability;
  final AppTheme theme;
  final bool isStale;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: Insets.i8),
      padding: const EdgeInsets.all(Insets.i12),
      decoration: BoxDecoration(
        color: theme.white,
        borderRadius: BorderRadius.circular(AppRadius.r10),
        border: Border.all(
          color: isStale ? theme.warning : theme.stroke.withOpacity(0.4),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  capability.title,
                  style:
                      appCss.dmDenseMedium13.textColor(theme.darkText),
                ),
              ),
              _StatusPill(
                label: capability.readiness.architectureReview,
                color: theme.primary,
              )
            ],
          ),
          const VSpace(Sizes.s6),
          Text(
            capability.summary,
            style: appCss.dmDenseRegular11.textColor(theme.lightText),
          ),
          const VSpace(Sizes.s6),
          Wrap(
            spacing: Insets.i12,
            runSpacing: Insets.i6,
            children: [
              _SummaryChip(
                label: language(context, 'ownersLabel'),
                value: capability.owners.join(', '),
              ),
              if (capability.dependencies.isNotEmpty)
                _SummaryChip(
                  label: language(context, 'dependenciesLabel'),
                  value: capability.dependencies.join(', '),
                ),
              _SummaryChip(
                label: language(context, 'securityStatusLabel'),
                value: capability.readiness.security,
              ),
              _SummaryChip(
                label: language(context, 'operationsStatusLabel'),
                value: capability.readiness.operations,
              ),
            ],
          ),
          const VSpace(Sizes.s6),
          if (capability.metrics.isNotEmpty)
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: capability.metrics
                  .map(
                    (metric) => Padding(
                      padding: const EdgeInsets.only(bottom: Insets.i4),
                      child: Text(
                        '${metric.name}: ${metric.baseline.toStringAsFixed(2)} → ${metric.target.toStringAsFixed(2)} ${metric.unit}',
                        style: appCss.dmDenseRegular11
                            .textColor(theme.lightText),
                      ),
                    ),
                  )
                  .toList(),
            )
        ],
      ),
    );
  }
}

class _RiskRegister extends StatelessWidget {
  const _RiskRegister({required this.provider, required this.theme});

  final ArchitectureGovernanceProvider provider;
  final AppTheme theme;

  @override
  Widget build(BuildContext context) {
    if (provider.risks.isEmpty) {
      return const SizedBox.shrink();
    }
    final dateFormatter = DateFormat('yyyy-MM-dd');
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(Insets.i16),
      decoration: BoxDecoration(
        color: theme.fieldCardBg,
        borderRadius: BorderRadius.circular(AppRadius.r12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.report_problem_outlined, color: theme.primary),
              const HSpace(Sizes.s8),
              Text(
                language(context, 'governanceRisksTitle'),
                style: appCss.dmDenseMedium16.textColor(theme.darkText),
              ),
            ],
          ),
          const VSpace(Sizes.s12),
          Column(
            children: provider.risks
                .map(
                  (risk) => Container(
                    width: double.infinity,
                    margin: const EdgeInsets.only(bottom: Insets.i8),
                    padding: const EdgeInsets.all(Insets.i12),
                    decoration: BoxDecoration(
                      color: theme.white,
                      borderRadius: BorderRadius.circular(AppRadius.r10),
                      border: Border.all(color: theme.stroke.withOpacity(0.4)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                risk.description,
                                style: appCss.dmDenseMedium13
                                    .textColor(theme.darkText),
                              ),
                            ),
                            _StatusPill(
                              label: risk.severity.toUpperCase(),
                              color: risk.severity == 'high'
                                  ? theme.red
                                  : risk.severity == 'medium'
                                      ? theme.warning
                                      : theme.primary,
                            )
                          ],
                        ),
                        const VSpace(Sizes.s6),
                        Text(
                          '${language(context, 'riskOwnerLabel')}: ${risk.owner}',
                          style: appCss.dmDenseRegular11
                              .textColor(theme.lightText),
                        ),
                        const VSpace(Sizes.s4),
                        Text(
                          '${language(context, 'riskDueDateLabel')}: ${risk.dueDate != null ? dateFormatter.format(risk.dueDate!.toLocal()) : language(context, 'notScheduled')}',
                          style: appCss.dmDenseRegular11
                              .textColor(theme.lightText),
                        ),
                        const VSpace(Sizes.s4),
                        Text(
                          '${language(context, 'mitigationLabel')}: ${risk.mitigation}',
                          style: appCss.dmDenseRegular11
                              .textColor(theme.lightText),
                        ),
                      ],
                    ),
                  ),
                )
                .toList(),
          )
        ],
      ),
    );
  }
}

class _SummaryChip extends StatelessWidget {
  const _SummaryChip({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    final theme = appColor(context).appTheme;
    return Container(
      padding: const EdgeInsets.symmetric(
        horizontal: Insets.i12,
        vertical: Insets.i8,
      ),
      decoration: BoxDecoration(
        color: theme.white,
        borderRadius: BorderRadius.circular(AppRadius.r10),
        border: Border.all(color: theme.stroke.withOpacity(0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            label,
            style: appCss.dmDenseRegular10.textColor(theme.lightText),
          ),
          const VSpace(Sizes.s4),
          Text(
            value,
            style: appCss.dmDenseMedium12.textColor(theme.darkText),
          ),
        ],
      ),
    );
  }
}

class _StatusPill extends StatelessWidget {
  const _StatusPill({required this.label, required this.color});

  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final theme = appColor(context).appTheme;
    return Container(
      padding: const EdgeInsets.symmetric(
        horizontal: Insets.i10,
        vertical: Insets.i6,
      ),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(AppRadius.r20),
        border: Border.all(color: color.withOpacity(0.4)),
      ),
      child: Text(
        label,
        style: appCss.dmDenseMedium11.textColor(
          color.computeLuminance() > 0.5 ? theme.darkText : color,
        ),
      ),
    );
  }
}
