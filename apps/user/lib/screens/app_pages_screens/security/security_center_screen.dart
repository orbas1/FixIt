import 'package:url_launcher/url_launcher.dart';

import '../../../config.dart';
import '../../../providers/security/security_incident_provider.dart';
import 'layouts/security_incident_card.dart';

class SecurityCenterScreen extends StatefulWidget {
  const SecurityCenterScreen({super.key});

  @override
  State<SecurityCenterScreen> createState() => _SecurityCenterScreenState();
}

class _SecurityCenterScreenState extends State<SecurityCenterScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      if (!mounted) return;
      await context.read<SecurityIncidentProvider>().loadIncidents();
    });
  }

  @override
  Widget build(BuildContext context) {
    final theme = appColor(context);

    return Scaffold(
      appBar: AppBar(
        title: Text(
          language(context, translations!.securityCenter),
          style: appCss.dmDenseBold18.textColor(theme.darkText),
        ),
        centerTitle: true,
        leading: CommonArrow(
          arrow: rtl(context) ? eSvgAssets.arrowRight : eSvgAssets.arrowLeft1,
          onTap: () => route.pop(context),
        ).paddingAll(Insets.i8),
      ),
      body: SafeArea(
        child: Consumer<SecurityIncidentProvider>(
          builder: (context, provider, _) {
            if (provider.isLoading && provider.incidents.isEmpty) {
              return const Center(child: CircularProgressIndicator());
            }

            final incidents = provider.incidents;

            return RefreshIndicator(
              onRefresh: () => provider.loadIncidents(forceRefresh: true),
              child: ListView(
                padding: const EdgeInsets.symmetric(
                  horizontal: Insets.i16,
                  vertical: Insets.i20,
                ),
                children: [
                  Text(
                    language(context, translations!.securityCenterHeadline),
                    style: appCss.dmDenseMedium14.textColor(theme.lightText),
                  ),
                  const VSpace(Sizes.s16),
                  if (provider.error != null)
                    Container(
                      padding: const EdgeInsets.all(Insets.i12),
                      decoration: BoxDecoration(
                        color: theme.red.withOpacity(0.08),
                        borderRadius: BorderRadius.circular(AppRadius.r12),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Icon(Icons.error_outline, color: theme.red),
                          const HSpace(Sizes.s12),
                          Expanded(
                            child: Text(
                              provider.error!,
                              style: appCss.dmDenseRegular12.textColor(theme.red),
                            ),
                          ),
                        ],
                      ),
                    ).paddingOnly(bottom: Insets.i16),
                  if (incidents.isEmpty)
                    Column(
                      children: [
                        Icon(Icons.verified_user_outlined,
                            size: Sizes.s64, color: theme.lightText),
                        const VSpace(Sizes.s12),
                        Text(
                          language(context, translations!.noSecurityIncidents),
                          style: appCss.dmDenseMedium14.textColor(theme.lightText),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    )
                  else ...[
                    Text(
                      language(context, translations!.openIncidentsLabel),
                      style: appCss.dmDenseMedium14.textColor(theme.darkText),
                    ).paddingOnly(bottom: Insets.i12),
                    ...incidents
                        .map((incident) => SecurityIncidentCard(incident: incident))
                        .toList(),
                  ],
                  const VSpace(Sizes.s24),
                  Text(
                    language(context, translations!.securityRunbookCta),
                    style: appCss.dmDenseMedium13.textColor(theme.darkText),
                  ),
                  const VSpace(Sizes.s8),
                  OutlinedButton.icon(
                    onPressed: () => launchUrl(
                      Uri.parse(
                        '${appSettingModel?.general?.baseUrl ?? apiUrl}/docs/security/incident-response',
                      ),
                      mode: LaunchMode.externalApplication,
                    ),
                    icon: const Icon(Icons.menu_book_outlined),
                    label: Text(language(context, translations!.viewRunbook)),
                  ),
                ],
              ),
            );
          },
        ),
      ),
    );
  }
}
