import 'package:intl/intl.dart';

import '../../../../config.dart';

class ProviderTaxAuditSection extends StatelessWidget {
  final ProviderTaxComplianceProvider controller;

  const ProviderTaxAuditSection({super.key, required this.controller});

  @override
  Widget build(BuildContext context) {
    final locale = Localizations.localeOf(context).toLanguageTag();
    final formatter = DateFormat.yMMMd(locale).add_jm();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          translations!.auditTrail,
          style: appCss.dmDenseBold18
              .textColor(appColor(context).appTheme.darkText),
        ),
        const VSpace(Sizes.s12),
        Container(
          width: double.infinity,
          decoration: ShapeDecoration(
            color: appColor(context).appTheme.whiteBg,
            shadows: [
              BoxShadow(
                color: appColor(context).appTheme.fieldCardBg,
                blurRadius: 12,
                offset: const Offset(0, 8),
              ),
            ],
            shape: SmoothRectangleBorder(
              borderRadius: SmoothBorderRadius(
                cornerRadius: AppRadius.r16,
                cornerSmoothing: 1,
              ),
            ),
          ),
          padding: const EdgeInsets.symmetric(
            horizontal: Insets.i16,
            vertical: Insets.i20,
          ),
          child: controller.isFetchingAudits
              ? Center(
                  child: CircularProgressIndicator(
                    valueColor: AlwaysStoppedAnimation<Color>(
                      appColor(context).appTheme.primary,
                    ),
                  ),
                )
              : controller.audits.isEmpty
                  ? Text(
                      translations!.noAuditEvents,
                      style: appCss.dmDenseMedium14.textColor(
                        appColor(context).appTheme.lightText,
                      ),
                    )
                  : ListView.separated(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemBuilder: (context, index) {
                        final audit = controller.audits[index];
                        return Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Expanded(
                                  child: Text(
                                    '${translations!.action}: ${audit.action ?? translations!.notAvailable ?? '-'}',
                                    style: appCss.dmDenseSemiBold14.textColor(
                                      appColor(context).appTheme.darkText,
                                    ),
                                  ),
                                ),
                                const HSpace(Sizes.s12),
                                Text(
                                  _formatDate(formatter, audit.recordedAt ?? audit.createdAt),
                                  style: appCss.dmDenseRegular12.textColor(
                                    appColor(context).appTheme.lightText,
                                  ),
                                  textAlign: TextAlign.right,
                                ),
                              ],
                            ),
                            const VSpace(Sizes.s6),
                            if (audit.performedBy != null)
                              Text(
                                '${translations!.performedBy}: #${audit.performedBy}',
                                style: appCss.dmDenseRegular12.textColor(
                                  appColor(context).appTheme.lightText,
                                ),
                              ),
                            const VSpace(Sizes.s6),
                            if ((audit.payload ?? {}).isNotEmpty)
                              _PayloadView(payload: audit.payload!),
                          ],
                        );
                      },
                      separatorBuilder: (_, __) => const Divider(),
                      itemCount: controller.audits.length,
                    ),
        ),
      ],
    );
  }

  String _formatDate(DateFormat formatter, String? value) {
    final parsed = value == null ? null : DateTime.tryParse(value);
    if (parsed == null) return translations!.notAvailable ?? '-';
    return formatter.format(parsed);
  }
}

class _PayloadView extends StatelessWidget {
  final Map<String, dynamic> payload;

  const _PayloadView({required this.payload});

  @override
  Widget build(BuildContext context) {
    final entries = payload.entries.toList()
      ..sort((a, b) => a.key.compareTo(b.key));

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          translations!.payload,
          style: appCss.dmDenseSemiBold13
              .textColor(appColor(context).appTheme.darkText),
        ),
        const VSpace(Sizes.s4),
        ...entries.map((entry) {
          return Container(
            margin: const EdgeInsets.only(bottom: Sizes.s4),
            padding: const EdgeInsets.symmetric(
              horizontal: Insets.i12,
              vertical: Insets.i10,
            ),
            decoration: ShapeDecoration(
              color: appColor(context).appTheme.fieldCardBg,
              shape: SmoothRectangleBorder(
                borderRadius: SmoothBorderRadius(
                  cornerRadius: AppRadius.r12,
                  cornerSmoothing: 1,
                ),
              ),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '${entry.key}: ',
                  style: appCss.dmDenseMedium12
                      .textColor(appColor(context).appTheme.lightText),
                ),
                Expanded(
                  child: Text(
                    _stringify(entry.value),
                    style: appCss.dmDenseRegular12
                        .textColor(appColor(context).appTheme.darkText),
                  ),
                ),
              ],
            ),
          );
        })
      ],
    );
  }

  String _stringify(dynamic value) {
    if (value is Map) {
      return value.entries
          .map((e) => '${e.key}: ${_stringify(e.value)}')
          .join(', ');
    }
    if (value is List) {
      return value.map(_stringify).join(', ');
    }
    return value?.toString() ?? '-';
  }
}
