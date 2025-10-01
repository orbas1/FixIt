import '../../../config.dart';
import 'layouts/audit_section.dart';
import 'layouts/profile_section.dart';
import 'layouts/rate_section.dart';

class TaxComplianceScreen extends StatefulWidget {
  const TaxComplianceScreen({super.key});

  @override
  State<TaxComplianceScreen> createState() => _TaxComplianceScreenState();
}

class _TaxComplianceScreenState extends State<TaxComplianceScreen> {
  final GlobalKey<FormState> _profileFormKey = GlobalKey<FormState>();
  final GlobalKey<FormState> _rateFormKey = GlobalKey<FormState>();
  bool _initialized = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (!_initialized) {
      Future.microtask(
        () => context
            .read<ProviderTaxComplianceProvider>()
            .bootstrap(context),
      );
      _initialized = true;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<ProviderTaxComplianceProvider>(
        builder: (context, controller, child) {
      return Scaffold(
        appBar: AppBarCommon(
          title: translations!.providerTaxCompliance,
          onTap: () => route.pop(context),
        ),
        body: RefreshIndicator(
          onRefresh: controller.refreshAll,
          color: appColor(context).appTheme.primary,
          child: controller.isLoading
              ? const Center(child: CircularProgressIndicator())
              : SafeArea(
                  child: LayoutBuilder(builder: (context, constraints) {
                    return SingleChildScrollView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (controller.errorMessage != null)
                            _ErrorBanner(message: controller.errorMessage!),
                          ProviderTaxProfileSection(
                            formKey: _profileFormKey,
                            controller: controller,
                          ),
                          const VSpace(Sizes.s24),
                          ProviderTaxRateSection(
                            formKey: _rateFormKey,
                            controller: controller,
                          ),
                          const VSpace(Sizes.s24),
                          ProviderTaxAuditSection(controller: controller),
                          const VSpace(Sizes.s24),
                        ],
                      ).paddingAll(Insets.i20),
                    );
                  }),
                ),
        ),
      );
    });
  }
}

class _ErrorBanner extends StatelessWidget {
  final String message;

  const _ErrorBanner({required this.message});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: Insets.i20),
      padding: const EdgeInsets.all(Insets.i16),
      decoration: ShapeDecoration(
        color: appColor(context).appTheme.redBg,
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
          Icon(Icons.error_outline,
              color: appColor(context).appTheme.whiteColor),
          const HSpace(Sizes.s12),
          Expanded(
            child: Text(
              message,
              style: appCss.dmDenseMedium14
                  .textColor(appColor(context).appTheme.whiteColor),
            ),
          ),
        ],
      ),
    );
  }
}
