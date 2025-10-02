import '../../../config.dart';
import '../../../providers/app_pages_provider/architecture_governance_provider.dart';
import '../../../providers/app_pages_provider/environment_checklist_provider.dart';
import '../../../providers/app_pages_provider/i18n_currency_checklist_provider.dart';
import 'layouts/environment_namespace_section.dart';
import 'layouts/environment_secret_section.dart';
import 'layouts/environment_summary_header.dart';
import 'layouts/i18n_currency_section.dart';
import 'layouts/architecture_governance_section.dart';

class EnvironmentChecklistScreen extends StatefulWidget {
  const EnvironmentChecklistScreen({super.key});

  @override
  State<EnvironmentChecklistScreen> createState() =>
      _EnvironmentChecklistScreenState();
}

class _EnvironmentChecklistScreenState
    extends State<EnvironmentChecklistScreen> {
  bool _bootstrapped = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (!_bootstrapped) {
      Future.microtask(
        () => context.read<EnvironmentChecklistProvider>().bootstrap(),
      );
      Future.microtask(
        () => context.read<I18nCurrencyChecklistProvider>().bootstrap(),
      );
      Future.microtask(
        () => context.read<ArchitectureGovernanceProvider>().bootstrap(),
      );
      _bootstrapped = true;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<EnvironmentChecklistProvider>(
      builder: (context, provider, child) {
        final theme = appColor(context).appTheme;
        return Scaffold(
          appBar: AppBarCommon(
            title: 'environmentAndSecrets',
            onTap: () => route.pop(context),
          ),
          body: RefreshIndicator(
            onRefresh: provider.refresh,
            color: theme.primary,
            child: provider.isLoading && provider.checklist == null
                ? const _LoadingState()
                : SafeArea(
                    child: LayoutBuilder(
                      builder: (context, constraints) {
                        return SingleChildScrollView(
                          physics: const AlwaysScrollableScrollPhysics(),
                          child: Padding(
                            padding: const EdgeInsets.all(Insets.i20),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                if (provider.errorMessage != null)
                                  _ErrorBanner(
                                    message: provider.errorMessage!,
                                    onRetry: provider.bootstrap,
                                  ),
                                EnvironmentSummaryHeader(provider: provider),
                                const VSpace(Sizes.s24),
                                EnvironmentNamespaceSection(provider: provider),
                                const VSpace(Sizes.s24),
                                EnvironmentSecretSection(provider: provider),
                                const VSpace(Sizes.s24),
                                const I18nCurrencySection(),
                                const VSpace(Sizes.s24),
                                const ArchitectureGovernanceSection(),
                                const VSpace(Sizes.s24),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  ),
          ),
        );
      },
    );
  }
}

class _ErrorBanner extends StatelessWidget {
  const _ErrorBanner({required this.message, required this.onRetry});

  final String message;
  final Future<void> Function() onRetry;

  @override
  Widget build(BuildContext context) {
    final theme = appColor(context).appTheme;
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: Insets.i16),
      padding: const EdgeInsets.all(Insets.i12),
      decoration: BoxDecoration(
        color: theme.red.withOpacity(0.12),
        borderRadius: BorderRadius.circular(AppRadius.r12),
        border: Border.all(color: theme.red.withOpacity(0.5)),
      ),
      child: Row(
        children: [
          Icon(Icons.error_outline, color: theme.red),
          const HSpace(Sizes.s12),
          Expanded(
            child: Text(
              message,
              style: appCss.dmDenseRegular12.textColor(theme.red),
            ),
          ),
          TextButton(
            onPressed: onRetry,
            child: Text(language(context, 'refresh')),
          )
        ],
      ),
    );
  }
}

class _LoadingState extends StatelessWidget {
  const _LoadingState();

  @override
  Widget build(BuildContext context) {
    return const Center(child: CircularProgressIndicator());
  }
}
