import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../../../common/theme/app_theme.dart';
import '../../../../config.dart';
import '../../../../model/i18n_currency_checklist_model.dart';
import '../../../../providers/app_pages_provider/i18n_currency_checklist_provider.dart';

class I18nCurrencySection extends StatelessWidget {
  const I18nCurrencySection({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = appColor(context).appTheme;
    return Consumer<I18nCurrencyChecklistProvider>(
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
              language(context, 'localizationAndCurrency'),
              style: appCss.dmDenseBold18.textColor(theme.darkText),
            ),
            const VSpace(Sizes.s12),
            _SummaryCard(provider: provider, theme: theme),
            const VSpace(Sizes.s16),
            _LocaleTable(provider: provider, theme: theme),
            const VSpace(Sizes.s16),
            _CurrencySampleTable(provider: provider, theme: theme),
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
              language(context, 'loadingLocalizationChecklist'),
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
            language(context, 'localizationChecklistError'),
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

class _SummaryCard extends StatelessWidget {
  const _SummaryCard({required this.provider, required this.theme});

  final I18nCurrencyChecklistProvider provider;
  final AppTheme theme;

  @override
  Widget build(BuildContext context) {
    final generatedAt = provider.generatedAt;
    final generatedAtText =
        DateFormat('yyyy-MM-dd HH:mm').format(generatedAt.toLocal());
    final isStale = provider.isChecklistStale;
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
              Icon(Icons.public, color: theme.primary),
              const HSpace(Sizes.s8),
              Expanded(
                child: Text(
                  language(context, 'localizationChecklistSummary'),
                  style: appCss.dmDenseMedium16.textColor(theme.darkText),
                ),
              ),
            ],
          ),
          const VSpace(Sizes.s12),
          Wrap(
            spacing: Insets.i16,
            runSpacing: Insets.i8,
            children: [
              _SummaryChip(
                label: language(context, 'generatedAtLabel'),
                value: generatedAtText,
              ),
              _SummaryChip(
                label: language(context, 'averageFlutterCoverage'),
                value:
                    '${(provider.averageFlutterCoverage * 100).toStringAsFixed(1)}%',
              ),
              _SummaryChip(
                label: language(context, 'averageLaravelCoverage'),
                value:
                    '${(provider.averageLaravelCoverage * 100).toStringAsFixed(1)}%',
              ),
              _SummaryChip(
                label: language(context, 'perfectLocalesLabel'),
                value: provider.perfectLocaleCount.toString(),
              ),
            ],
          ),
          if (isStale) ...[
            const VSpace(Sizes.s12),
            Container(
              padding: const EdgeInsets.all(Insets.i12),
              decoration: BoxDecoration(
                color: theme.warning.withOpacity(0.12),
                borderRadius: BorderRadius.circular(AppRadius.r10),
              ),
              child: Row(
                children: [
                  Icon(Icons.warning_amber_rounded, color: theme.warning),
                  const HSpace(Sizes.s8),
                  Expanded(
                    child: Text(
                      language(context, 'staleLocalizationChecklist'),
                      style:
                          appCss.dmDenseRegular12.textColor(theme.warning),
                    ),
                  ),
                ],
              ),
            ),
          ]
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
        color: theme.whiteColor,
        borderRadius: BorderRadius.circular(AppRadius.r20),
        border: Border.all(color: theme.stroke.withOpacity(0.4)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            label,
            style: appCss.dmDenseRegular10.textColor(theme.lightText),
          ),
          Text(
            value,
            style: appCss.dmDenseMedium14.textColor(theme.darkText),
          ),
        ],
      ),
    );
  }
}

class _LocaleTable extends StatelessWidget {
  const _LocaleTable({required this.provider, required this.theme});

  final I18nCurrencyChecklistProvider provider;
  final AppTheme theme;

  @override
  Widget build(BuildContext context) {
    if (provider.locales.isEmpty) {
      return Container(
        width: double.infinity,
        padding: const EdgeInsets.all(Insets.i16),
        decoration: BoxDecoration(
          color: theme.fieldCardBg,
          borderRadius: BorderRadius.circular(AppRadius.r12),
        ),
        child: Text(
          language(context, 'noLocalesConfigured'),
          style: appCss.dmDenseRegular12.textColor(theme.lightText),
        ),
      );
    }

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(Insets.i16),
      decoration: BoxDecoration(
        color: theme.whiteColor,
        borderRadius: BorderRadius.circular(AppRadius.r12),
        border: Border.all(color: theme.stroke.withOpacity(0.4)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            language(context, 'translationCoverage'),
            style: appCss.dmDenseMedium14.textColor(theme.darkText),
          ),
          const VSpace(Sizes.s12),
          ...provider.locales.map(
            (locale) => _LocaleRow(locale: locale),
          ),
        ],
      ),
    );
  }
}

class _LocaleRow extends StatelessWidget {
  const _LocaleRow({required this.locale});

  final I18nLocaleStatus locale;

  @override
  Widget build(BuildContext context) {
    final theme = appColor(context).appTheme;
    final coverage = locale.expectation;
    final missingCount =
        coverage.missingFlutterKeys.length + coverage.missingLaravelKeys.length;
    return Container(
      padding: const EdgeInsets.symmetric(vertical: Insets.i8),
      decoration: BoxDecoration(
        border: Border(
          bottom: BorderSide(color: theme.stroke.withOpacity(0.2)),
        ),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  locale.name,
                  style: appCss.dmDenseMedium14.textColor(theme.darkText),
                ),
                Text(
                  locale.code,
                  style: appCss.dmDenseRegular10.textColor(theme.lightText),
                ),
              ],
            ),
          ),
          _CoverageBadge(
            label: language(context, 'flutterLabel'),
            value: coverage.flutterCoverage,
            theme: theme,
          ),
          const HSpace(Sizes.s8),
          _CoverageBadge(
            label: language(context, 'laravelLabel'),
            value: coverage.laravelCoverage,
            theme: theme,
          ),
          const HSpace(Sizes.s8),
          Icon(
            locale.rtl
                ? Icons.format_textdirection_r_to_l
                : Icons.format_textdirection_l_to_r,
            color: locale.rtl ? theme.primary : theme.lightText,
            size: Sizes.s18,
          ),
          const HSpace(Sizes.s8),
          if (missingCount > 0)
            Tooltip(
              message: language(context, 'missingKeysDetected'),
              child: CircleAvatar(
                radius: 10,
                backgroundColor: theme.warning,
                child: Text(
                  missingCount.toString(),
                  style: appCss.dmDenseRegular10.textColor(theme.whiteColor),
                ),
              ),
            )
          else
            Icon(Icons.verified, color: theme.success, size: Sizes.s18),
        ],
      ),
    );
  }
}

class _CoverageBadge extends StatelessWidget {
  const _CoverageBadge({
    required this.label,
    required this.value,
    required this.theme,
  });

  final String label;
  final double value;
  final AppTheme theme;

  @override
  Widget build(BuildContext context) {
    final percentage = (value * 100).clamp(0, 100).toStringAsFixed(0);
    final bool perfect = value >= 1;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: Insets.i10, vertical: Insets.i6),
      decoration: BoxDecoration(
        color: perfect ? theme.success.withOpacity(0.12) : theme.fieldCardBg,
        borderRadius: BorderRadius.circular(AppRadius.r20),
      ),
      child: Text(
        '$label $percentage%',
        style: appCss.dmDenseRegular12.textColor(
          perfect ? theme.success : theme.darkText,
        ),
      ),
    );
  }
}

class _CurrencySampleTable extends StatelessWidget {
  const _CurrencySampleTable({required this.provider, required this.theme});

  final I18nCurrencyChecklistProvider provider;
  final AppTheme theme;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(Insets.i16),
      decoration: BoxDecoration(
        color: theme.fieldCardBg,
        borderRadius: BorderRadius.circular(AppRadius.r12),
        border: Border.all(color: theme.stroke.withOpacity(0.4)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            language(context, 'currencyFormattingSamples'),
            style: appCss.dmDenseMedium14.textColor(theme.darkText),
          ),
          const VSpace(Sizes.s12),
          ...provider.locales.map((locale) {
            final profile = provider.profileForCode(locale.currencyProfileCode);
            if (profile == null) {
              return const SizedBox.shrink();
            }
            return _CurrencyProfileCard(locale: locale, profile: profile);
          })
        ],
      ),
    );
  }
}

class _CurrencyProfileCard extends StatelessWidget {
  const _CurrencyProfileCard({required this.locale, required this.profile});

  final I18nLocaleStatus locale;
  final I18nCurrencyProfile profile;

  @override
  Widget build(BuildContext context) {
    final theme = appColor(context).appTheme;
    return Container(
      margin: const EdgeInsets.only(bottom: Insets.i12),
      padding: const EdgeInsets.all(Insets.i12),
      decoration: BoxDecoration(
        color: theme.whiteColor,
        borderRadius: BorderRadius.circular(AppRadius.r10),
        border: Border.all(color: theme.stroke.withOpacity(0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(
                '${locale.name} · ${profile.code}',
                style: appCss.dmDenseMedium13.textColor(theme.darkText),
              ),
              const Spacer(),
              Text(
                '${language(context, 'decimalDigitsLabel')}: ${profile.decimalDigits}',
                style: appCss.dmDenseRegular10.textColor(theme.lightText),
              ),
            ],
          ),
          const VSpace(Sizes.s8),
          ...profile.samples.map((sample) {
            final actual = _formatSample(context, profile, sample);
            final matches = actual == sample.expected;
            return Padding(
              padding: const EdgeInsets.only(bottom: Insets.i6),
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '${language(context, 'amountLabel')}: ${sample.amount.toStringAsFixed(profile.decimalDigits)}',
                          style:
                              appCss.dmDenseRegular10.textColor(theme.lightText),
                        ),
                        Text(
                          '${language(context, 'expectedLabel')}: ${sample.expected}',
                          style: appCss.dmDenseRegular12.textColor(theme.darkText),
                        ),
                        Text(
                          '${language(context, 'actualLabel')}: $actual',
                          style: appCss.dmDenseRegular12.textColor(matches
                              ? theme.success
                              : theme.red),
                        ),
                      ],
                    ),
                  ),
                  Icon(
                    matches ? Icons.check_circle : Icons.error_outline,
                    color: matches ? theme.success : theme.red,
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }

  String _formatSample(
    BuildContext context,
    I18nCurrencyProfile profile,
    I18nCurrencySample sample,
  ) {
    final locale = sample.locale;
    Locale? localeOverride;
    if (locale != null && locale.isNotEmpty) {
      final segments = locale.split('_');
      if (segments.length == 2) {
        localeOverride = Locale(segments[0], segments[1]);
      } else {
        localeOverride = Locale(locale);
      }
    }
    return formatCurrency(
      context,
      sample.amount,
      convert: false,
      currencyOverride: profile.toCurrencyModel(),
      localeOverride: localeOverride,
    );
  }
}
