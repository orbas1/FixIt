import 'package:intl/intl.dart';

import '../../../../config.dart';

class ProviderTaxProfileSection extends StatelessWidget {
  final GlobalKey<FormState> formKey;
  final ProviderTaxComplianceProvider controller;

  const ProviderTaxProfileSection({
    super.key,
    required this.formKey,
    required this.controller,
  });

  @override
  Widget build(BuildContext context) {
    final profile = controller.profile;
    final locale = Localizations.localeOf(context).toLanguageTag();
    final dateFormatter = DateFormat.yMMMEd(locale);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          translations!.taxProfile,
          style: appCss.dmDenseBold18
              .textColor(appColor(context).appTheme.darkText),
        ),
        const VSpace(Sizes.s12),
        Container(
          width: double.infinity,
          decoration: _cardDecoration(context),
          padding: const EdgeInsets.symmetric(
            horizontal: Insets.i16,
            vertical: Insets.i20,
          ),
          child: Form(
            key: formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (profile != null) ...[
                  _InfoChip(
                    label: translations!.complianceStatus,
                    value: profile.complianceStatus ?? translations!.pending ?? '',
                  ),
                  const VSpace(Sizes.s8),
                  _InfoChip(
                    label: translations!.maskedTaxIdentifier,
                    value:
                        profile.maskedTaxIdentifier ?? translations!.notAvailable ?? '-',
                  ),
                  if (profile.lastVerifiedAt != null) ...[
                    const VSpace(Sizes.s8),
                    _InfoChip(
                      label: translations!.lastVerifiedAt,
                      value: dateFormatter.format(
                        DateTime.tryParse(profile.lastVerifiedAt!) ??
                            DateTime.now(),
                      ),
                    ),
                  ],
                  const Divider(),
                  const VSpace(Sizes.s12),
                ],
                ContainerWithTextLayout(
                  title: translations!.legalBusinessName,
                ),
                const VSpace(Sizes.s8),
                TextFieldCommon(
                  controller: controller.legalNameCtrl,
                  hintText: translations!.enterLegalBusinessName!,
                  prefixIcon: eSvgAssets.companyName,
                  validator: (value) {
                    if (value == null || value.trim().isEmpty) {
                      return translations!.pleaseEnterName;
                    }
                    return null;
                  },
                ),
                const VSpace(Sizes.s18),
                ContainerWithTextLayout(title: translations!.taxIdentifier)
                    .paddingOnly(bottom: Insets.i8),
                TextFieldCommon(
                  controller: controller.taxIdentifierCtrl,
                  hintText: translations!.enterTaxIdentifier!,
                  prefixIcon: eSvgAssets.idVerification,
                  validator: (value) {
                    if (profile == null && (value == null || value.trim().isEmpty)) {
                      return translations!.pleaseEnterValue;
                    }
                    return null;
                  },
                ),
                const VSpace(Sizes.s18),
                Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          ContainerWithTextLayout(
                            title: translations!.countryCode,
                          ),
                          const VSpace(Sizes.s8),
                          TextFieldCommon(
                            controller: controller.countryCtrl,
                            hintText: translations!.enterCountryCode!,
                            prefixIcon: eSvgAssets.country,
                            textCapitalization: TextCapitalization.characters,
                            validator: (value) {
                              if (value == null || value.trim().length != 2) {
                                return translations!.enterCountryCode;
                              }
                              return null;
                            },
                          ),
                        ],
                      ),
                    ),
                    const HSpace(Sizes.s12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          ContainerWithTextLayout(
                            title: translations!.regionCode,
                          ),
                          const VSpace(Sizes.s8),
                          TextFieldCommon(
                            controller: controller.regionCtrl,
                            hintText: translations!.enterRegionCode!,
                            prefixIcon: eSvgAssets.state,
                            textCapitalization: TextCapitalization.characters,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const VSpace(Sizes.s18),
                Row(
                  children: [
                    Expanded(
                      child: _DateField(
                        label: translations!.effectiveFrom,
                        value: controller.effectiveFrom,
                        onTap: () => _selectDate(
                          context,
                          initial: controller.effectiveFrom,
                          onSelected: controller.updateProfileEffectiveFrom,
                        ),
                      ),
                    ),
                    const HSpace(Sizes.s12),
                    Expanded(
                      child: _DateField(
                        label: translations!.effectiveTo,
                        value: controller.effectiveTo,
                        allowClear: true,
                        onTap: () => _selectDate(
                          context,
                          initial: controller.effectiveTo,
                          onSelected: controller.updateProfileEffectiveTo,
                        ),
                        onClear: () => controller.updateProfileEffectiveTo(null),
                      ),
                    ),
                  ],
                ),
                const VSpace(Sizes.s18),
                ContainerWithTextLayout(
                  title: translations!.metadataNotes,
                ),
                const VSpace(Sizes.s8),
                TextFieldCommon(
                  controller: controller.metadataNotesCtrl,
                  hintText: translations!.enterMetadataNotes!,
                  prefixIcon: eSvgAssets.description,
                  maxLines: 4,
                  minLines: 3,
                  isMaxLine: true,
                ),
                const VSpace(Sizes.s18),
                _ConsentTile(controller: controller),
                const VSpace(Sizes.s24),
                ButtonCommon(
                  title: controller.isSavingProfile
                      ? translations!.update
                      : translations!.saveChanges,
                  isLoading: controller.isSavingProfile,
                  onTap: () {
                    FocusScope.of(context).unfocus();
                    if (formKey.currentState?.validate() ?? false) {
                      controller.submitProfile(context);
                    }
                  },
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  ShapeDecoration _cardDecoration(BuildContext context) {
    return ShapeDecoration(
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
    );
  }

  Future<void> _selectDate(
    BuildContext context, {
    required DateTime? initial,
    required ValueChanged<DateTime> onSelected,
  }) async {
    FocusScope.of(context).unfocus();
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: initial ?? now,
      firstDate: DateTime(now.year - 10),
      lastDate: DateTime(now.year + 10),
    );
    if (picked != null) {
      onSelected(picked);
    }
  }
}

class _ConsentTile extends StatelessWidget {
  final ProviderTaxComplianceProvider controller;

  const _ConsentTile({required this.controller});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(Insets.i14),
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
          Switch(
            value: controller.privacyConsent,
            activeColor: appColor(context).appTheme.primary,
            onChanged: (value) {
              controller.privacyConsent = value;
              controller.notifyListeners();
            },
          ),
          const HSpace(Sizes.s12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  translations!.privacyConsent,
                  style: appCss.dmDenseSemiBold14
                      .textColor(appColor(context).appTheme.darkText),
                ),
                const VSpace(Sizes.s4),
                Text(
                  translations!.privacyConsentDescription,
                  style: appCss.dmDenseRegular12
                      .textColor(appColor(context).appTheme.lightText),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _DateField extends StatelessWidget {
  final String label;
  final DateTime? value;
  final VoidCallback onTap;
  final bool allowClear;
  final VoidCallback? onClear;

  const _DateField({
    required this.label,
    required this.value,
    required this.onTap,
    this.allowClear = false,
    this.onClear,
  });

  @override
  Widget build(BuildContext context) {
    final locale = Localizations.localeOf(context).toLanguageTag();
    final formatter = DateFormat.yMMMd(locale);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        ContainerWithTextLayout(title: label),
        const VSpace(Sizes.s8),
        GestureDetector(
          onTap: onTap,
          child: Container(
            padding: const EdgeInsets.symmetric(
              horizontal: Insets.i16,
              vertical: Insets.i14,
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
              children: [
                Icon(Icons.calendar_today,
                    size: 18, color: appColor(context).appTheme.darkText),
                const HSpace(Sizes.s12),
                Expanded(
                  child: Text(
                    value == null
                        ? translations!.selectDate ?? ''
                        : formatter.format(value!),
                    style: appCss.dmDenseRegular14
                        .textColor(appColor(context).appTheme.darkText),
                  ),
                ),
                if (allowClear && value != null)
                  IconButton(
                    icon: Icon(Icons.close,
                        size: 18,
                        color: appColor(context).appTheme.lightText),
                    onPressed: onClear,
                  )
              ],
            ),
          ),
        ),
      ],
    );
  }
}

class _InfoChip extends StatelessWidget {
  final String label;
  final String value;

  const _InfoChip({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Container(
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
            label,
            style: appCss.dmDenseMedium12
                .textColor(appColor(context).appTheme.lightText),
          ),
          const HSpace(Sizes.s6),
          Expanded(
            child: Text(
              value,
              style: appCss.dmDenseSemiBold13
                  .textColor(appColor(context).appTheme.darkText),
            ),
          )
        ],
      ),
    );
  }
}
