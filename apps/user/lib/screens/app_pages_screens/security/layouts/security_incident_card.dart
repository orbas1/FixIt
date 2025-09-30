import 'package:intl/intl.dart';

import '../../../../config.dart';
import '../../../../models/security_incident.dart';

class SecurityIncidentCard extends StatelessWidget {
  const SecurityIncidentCard({super.key, required this.incident});

  final SecurityIncident incident;

  Color _severityColor(BuildContext context) {
    switch (incident.severity.toLowerCase()) {
      case 'critical':
        return appColor(context).red;
      case 'high':
        return appColor(context).pending;
      case 'medium':
        return appColor(context).primary;
      default:
        return appColor(context).lightText;
    }
  }

  @override
  Widget build(BuildContext context) {
    final dateLabel = DateFormat.yMMMd().add_Hm().format(incident.detectedAt);
    final severityColor = _severityColor(context);

    return Card(
      elevation: 0,
      margin: const EdgeInsets.symmetric(vertical: Insets.i8),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.r12)),
      child: Padding(
        padding: const EdgeInsets.all(Insets.i16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    incident.title,
                    style: appCss.dmDenseBold16.textColor(appColor(context).darkText),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: Insets.i12, vertical: Insets.i6),
                  decoration: BoxDecoration(
                    color: severityColor.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(AppRadius.r20),
                  ),
                  child: Text(
                    incident.severityLabel,
                    style: appCss.dmDenseMedium12.textColor(severityColor),
                  ),
                ),
              ],
            ),
            const VSpace(Sizes.s12),
            Row(
              children: [
                Icon(Icons.verified_outlined, size: Sizes.s18, color: appColor(context).primary),
                const HSpace(Sizes.s8),
                Text(
                  '${language(context, translations!.statusLabel)}: ${incident.statusLabel}',
                  style: appCss.dmDenseRegular12.textColor(appColor(context).lightText),
                ),
              ],
            ),
            const VSpace(Sizes.s8),
            Row(
              children: [
                Icon(Icons.schedule_outlined, size: Sizes.s18, color: appColor(context).lightText),
                const HSpace(Sizes.s8),
                Text(
                  '${language(context, translations!.detectedAt)} $dateLabel',
                  style: appCss.dmDenseRegular12.textColor(appColor(context).lightText),
                ),
              ],
            ),
            if (incident.impactSummary?.isNotEmpty == true) ...[
              const VSpace(Sizes.s12),
              Text(
                incident.impactSummary!,
                style: appCss.dmDenseRegular13.textColor(appColor(context).darkText),
              ),
            ],
            if (incident.mitigationSteps?.isNotEmpty == true) ...[
              const VSpace(Sizes.s12),
              Text(
                language(context, translations!.mitigationHeading),
                style: appCss.dmDenseMedium12.textColor(appColor(context).darkText),
              ),
              Text(
                incident.mitigationSteps!,
                style: appCss.dmDenseRegular12.textColor(appColor(context).lightText),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
