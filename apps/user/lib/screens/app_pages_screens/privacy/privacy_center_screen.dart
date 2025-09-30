import 'package:intl/intl.dart';

import '../../../config.dart';

class PrivacyCenterScreen extends StatefulWidget {
  const PrivacyCenterScreen({super.key});

  @override
  State<PrivacyCenterScreen> createState() => _PrivacyCenterScreenState();
}

class _PrivacyCenterScreenState extends State<PrivacyCenterScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      context.read<PrivacyPreferenceProvider>().fetchPreferences();
    });
  }

  @override
  Widget build(BuildContext context) {
    final textColor = appColor(context).darkText;

    return Scaffold(
      appBar: AppBar(
        title: Text(
          language(context, translations!.privacyPolicy),
          style: appCss.dmDenseBold18.textColor(textColor),
        ),
        centerTitle: true,
        leading: CommonArrow(
          arrow: rtl(context) ? eSvgAssets.arrowRight : eSvgAssets.arrowLeft1,
          onTap: () => route.pop(context),
        ).paddingAll(Insets.i8),
      ),
      body: SafeArea(
        child: Consumer<PrivacyPreferenceProvider>(
          builder: (_, provider, __) {
            if (provider.isLoading && provider.preferences.isEmpty) {
              return const Center(child: CircularProgressIndicator());
            }

            return RefreshIndicator(
              onRefresh: provider.fetchPreferences,
              child: ListView(
                padding: const EdgeInsets.symmetric(horizontal: Insets.i16),
                children: [
                  Text(
                    language(context, translations!.privacyPolicyDescription),
                    style: appCss.dmDenseRegular14.textColor(appColor(context).lightText),
                  ).paddingSymmetric(vertical: Insets.i12),
                  ...provider.preferences.map(
                    (preference) => _PreferenceTile(
                      preference: preference,
                      onChanged: provider.isSubmitting
                          ? null
                          : (value) => provider.updatePreference(preference.key, value),
                    ),
                  ),
                  const VSpace(Sizes.s24),
                  FilledButton.icon(
                    onPressed: provider.isSubmitting
                        ? null
                        : () async {
                            try {
                              final token = await provider.requestExport();
                              if (!context.mounted) return;
                              snackBarMessengers(
                                context,
                                message: '${language(context, translations!.download)} request queued ($token)',
                                color: appColor(context).primary,
                              );
                            } catch (error) {
                              if (!context.mounted) return;
                              snackBarMessengers(
                                context,
                                message: error.toString(),
                                color: appColor(context).red,
                              );
                            }
                          },
                    icon: const Icon(Icons.download_for_offline_outlined),
                    label: Text(language(context, translations!.downloadData)),
                  ).paddingSymmetric(vertical: Insets.i8),
                  OutlinedButton.icon(
                    onPressed: provider.isSubmitting
                        ? null
                        : () async {
                            try {
                              await provider.requestDeletion();
                              if (!context.mounted) return;
                              snackBarMessengers(
                                context,
                                message: language(context, translations!.accountDeletionQueued),
                                color: appColor(context).primary,
                              );
                            } catch (error) {
                              if (!context.mounted) return;
                              snackBarMessengers(
                                context,
                                message: error.toString(),
                                color: appColor(context).red,
                              );
                            }
                          },
                    icon: const Icon(Icons.delete_outline),
                    label: Text(language(context, translations!.requestAccountDeletion)),
                  ).paddingSymmetric(vertical: Insets.i8),
                  const VSpace(Sizes.s40),
                ],
              ),
            );
          },
        ),
      ),
    );
  }
}

class _PreferenceTile extends StatelessWidget {
  const _PreferenceTile({
    required this.preference,
    required this.onChanged,
  });

  final PrivacyPreference preference;
  final ValueChanged<bool>? onChanged;

  @override
  Widget build(BuildContext context) {
    final subtitle = preference.expiresAt != null
        ? '${language(context, translations!.expiresOn)} ${DateFormat.yMMMd().format(preference.expiresAt!)}'
        : language(context, translations!.canUpdateAnytime);

    return Card(
      child: SwitchListTile(
        title: Text(
          _labelFor(context, preference.key),
          style: appCss.dmDenseMedium16.textColor(appColor(context).darkText),
        ),
        subtitle: Text(
          subtitle,
          style: appCss.dmDenseRegular12.textColor(appColor(context).lightText),
        ),
        value: preference.granted,
        onChanged: onChanged,
      ),
    ).paddingSymmetric(vertical: Insets.i6);
  }

  String _labelFor(BuildContext context, String key) {
    switch (key) {
      case 'marketing':
        return language(context, translations!.marketingPreference);
      case 'analytics':
        return language(context, translations!.analyticsPreference);
      default:
        return language(context, translations!.essentialPreference);
    }
  }
}
