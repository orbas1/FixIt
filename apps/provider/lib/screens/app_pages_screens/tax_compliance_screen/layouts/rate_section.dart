import 'package:flutter/services.dart';
import 'package:intl/intl.dart';

import '../../../../config.dart';

class ProviderTaxRateSection extends StatelessWidget {
  final GlobalKey<FormState> formKey;
  final ProviderTaxComplianceProvider controller;

  const ProviderTaxRateSection({
    super.key,
    required this.formKey,
    required this.controller,
  });

  @override
  Widget build(BuildContext context) {
    final locale = Localizations.localeOf(context).toLanguageTag();
    final dateFormatter = DateFormat.yMMMd(locale);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          translations!.taxRates,
          style: appCss.dmDenseBold18
              .textColor(appColor(context).appTheme.darkText),
        ),
        const VSpace(Sizes.s12),
        Container(
          decoration: _cardDecoration(context),
          padding: const EdgeInsets.symmetric(
            horizontal: Insets.i16,
            vertical: Insets.i20,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    controller.editingRate == null
                        ? translations!.addTaxRate
                        : translations!.editRate,
                    style: appCss.dmDenseSemiBold16
                        .textColor(appColor(context).appTheme.darkText),
                  ),
                  if (controller.editingRate != null)
                    TextButton(
                      onPressed: () {
                        controller.resetRateForm();
                        controller.notifyListeners();
                        FocusScope.of(context).unfocus();
                      },
                      child: Text(translations!.cancel!),
                    ),
                ],
              ),
              const VSpace(Sizes.s16),
              Form(
                key: formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _DropdownField<TaxModel>(
                      label: translations!.tax,
                      value: controller.selectedTax,
                      hint: translations!.selectTax!,
                      items: controller.taxes
                          .map((tax) => DropdownMenuItem<TaxModel>(
                                value: tax,
                                child: Text(tax.name ?? '-'),
                              ))
                          .toList(),
                      onChanged: (value) {
                        controller.selectedTax = value;
                        controller.notifyListeners();
                      },
                      validator: (value) {
                        if (value == null) {
                          return translations!.selectTax;
                        }
                        return null;
                      },
                    ),
                    const VSpace(Sizes.s16),
                    _DropdownField<ZoneModel>(
                      label: translations!.zone,
                      value: controller.selectedZone,
                      hint: translations!.selectZone!,
                      items: controller.zones
                          .map((zone) => DropdownMenuItem<ZoneModel>(
                                value: zone,
                                child: Text(zone.name ?? '-'),
                              ))
                          .toList(),
                      onChanged: (value) {
                        controller.selectedZone = value;
                        controller.notifyListeners();
                      },
                    ),
                    const VSpace(Sizes.s16),
                    ContainerWithTextLayout(
                      title: translations!.rateValue,
                    ),
                    const VSpace(Sizes.s8),
                    TextFieldCommon(
                      controller: controller.rateCtrl,
                      hintText: translations!.rateValue!,
                      prefixIcon: eSvgAssets.receipt,
                      keyboardType: const TextInputType.numberWithOptions(
                        decimal: true,
                      ),
                      inputFormatters: [
                        FilteringTextInputFormatter.allow(
                          RegExp(r'^[0-9]*[\.]?[0-9]{0,4}'),
                        ),
                      ],
                      validator: (value) {
                        final rate = double.tryParse(value ?? '');
                        if (rate == null || rate <= 0) {
                          return translations!.pleaseEnterValidNumber;
                        }
                        return null;
                      },
                    ),
                    const VSpace(Sizes.s16),
                    _DropdownField<String>(
                      label: translations!.calculationType,
                      value: controller.calculationType,
                      items: [
                        DropdownMenuItem(
                          value: 'exclusive',
                          child: Text(translations!.exclusive!),
                        ),
                        DropdownMenuItem(
                          value: 'inclusive',
                          child: Text(translations!.inclusive!),
                        ),
                      ],
                      onChanged: (value) {
                        controller.calculationType = value ?? 'exclusive';
                        controller.notifyListeners();
                      },
                    ),
                    const VSpace(Sizes.s12),
                    Row(
                      children: [
                        Switch(
                          value: controller.rateIsDefault,
                          activeColor: appColor(context).appTheme.primary,
                          onChanged: (value) {
                            controller.rateIsDefault = value;
                            controller.notifyListeners();
                          },
                        ),
                        const HSpace(Sizes.s8),
                        Expanded(
                          child: Text(
                            translations!.defaultRate,
                            style: appCss.dmDenseMedium14
                                .textColor(appColor(context).appTheme.darkText),
                          ),
                        ),
                      ],
                    ),
                    const VSpace(Sizes.s12),
                    Row(
                      children: [
                        Expanded(
                          child: _DateField(
                            label: translations!.effectiveFrom,
                            value: controller.rateEffectiveFrom,
                            onTap: () => _selectDate(
                              context,
                              initial: controller.rateEffectiveFrom,
                              onSelected: controller.updateRateEffectiveFrom,
                            ),
                          ),
                        ),
                        const HSpace(Sizes.s12),
                        Expanded(
                          child: _DateField(
                            label: translations!.effectiveTo,
                            value: controller.rateEffectiveTo,
                            allowClear: true,
                            onTap: () => _selectDate(
                              context,
                              initial: controller.rateEffectiveTo,
                              onSelected: controller.updateRateEffectiveTo,
                            ),
                            onClear: () => controller.updateRateEffectiveTo(null),
                          ),
                        ),
                      ],
                    ),
                    const VSpace(Sizes.s16),
                    ContainerWithTextLayout(
                      title: translations!.registrationNumber,
                    ),
                    const VSpace(Sizes.s8),
                    TextFieldCommon(
                      controller: controller.registrationNumberCtrl,
                      hintText: translations!.enterRegistrationNumber!,
                      prefixIcon: eSvgAssets.profile,
                    ),
                    const VSpace(Sizes.s16),
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              ContainerWithTextLayout(
                                title: translations!.jurisdictionCountry,
                              ),
                              const VSpace(Sizes.s8),
                              TextFieldCommon(
                                controller: controller.jurisdictionCountryCtrl,
                                hintText: translations!.enterCountryCode!,
                                prefixIcon: eSvgAssets.country,
                                textCapitalization:
                                    TextCapitalization.characters,
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
                                title: translations!.jurisdictionRegion,
                              ),
                              const VSpace(Sizes.s8),
                              TextFieldCommon(
                                controller: controller.jurisdictionRegionCtrl,
                                hintText: translations!.enterRegionCode!,
                                prefixIcon: eSvgAssets.state,
                                textCapitalization:
                                    TextCapitalization.characters,
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const VSpace(Sizes.s16),
                    ContainerWithTextLayout(
                      title: translations!.complianceFlags,
                    ),
                    const VSpace(Sizes.s8),
                    TextFieldCommon(
                      controller: controller.complianceFlagsCtrl,
                      hintText: translations!.enterComplianceFlags!,
                      prefixIcon: eSvgAssets.tag,
                    ),
                    const VSpace(Sizes.s20),
                    ButtonCommon(
                      title: controller.editingRate == null
                          ? translations!.saveChanges
                          : translations!.update,
                      isLoading: controller.isSavingRate,
                      onTap: () {
                        FocusScope.of(context).unfocus();
                        if (formKey.currentState?.validate() ?? false) {
                          controller.submitRate(context);
                        }
                      },
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        const VSpace(Sizes.s20),
        if (controller.rates.isEmpty)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(Insets.i16),
            decoration: _cardDecoration(context),
            child: Text(
              translations!.noTaxRates,
              style: appCss.dmDenseMedium14
                  .textColor(appColor(context).appTheme.lightText),
            ),
          )
        else
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemBuilder: (context, index) {
              final rate = controller.rates[index];
              return Container(
                decoration: _cardDecoration(context),
                padding: const EdgeInsets.symmetric(
                  horizontal: Insets.i16,
                  vertical: Insets.i18,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          rate.taxName ?? translations!.tax,
                          style: appCss.dmDenseSemiBold16.textColor(
                              appColor(context).appTheme.darkText),
                        ),
                        Row(
                          children: [
                            TextButton(
                              onPressed: () {
                                controller.resetRateForm(rate);
                                controller.notifyListeners();
                              },
                              child: Text(translations!.editRate),
                            ),
                            TextButton(
                              onPressed: () => _confirmDelete(context, rate),
                              child: Text(
                                translations!.delete!,
                                style: TextStyle(
                                  color: appColor(context).appTheme.redBg,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    const VSpace(Sizes.s8),
                    Text(
                      '${translations!.rateValue}: ${(rate.rate ?? 0).toStringAsFixed(2)}%',
                      style: appCss.dmDenseMedium14
                          .textColor(appColor(context).appTheme.darkText),
                    ),
                    const VSpace(Sizes.s6),
                    Text(
                      '${translations!.calculationType}: ${rate.calculationType ?? controller.calculationType}',
                      style: appCss.dmDenseRegular12
                          .textColor(appColor(context).appTheme.lightText),
                    ),
                    if (rate.zoneName != null)
                      Text(
                        '${translations!.zone}: ${rate.zoneName}',
                        style: appCss.dmDenseRegular12
                            .textColor(appColor(context).appTheme.lightText),
                      ),
                    if (rate.registrationNumber?.isNotEmpty ?? false)
                      Text(
                        '${translations!.registrationNumber}: ${rate.registrationNumber}',
                        style: appCss.dmDenseRegular12
                            .textColor(appColor(context).appTheme.lightText),
                      ),
                    const VSpace(Sizes.s6),
                    Text(
                      '${translations!.effectiveFrom}: ${_format(dateFormatter, rate.effectiveFrom)}',
                      style: appCss.dmDenseRegular12
                          .textColor(appColor(context).appTheme.lightText),
                    ),
                    Text(
                      '${translations!.effectiveTo}: ${_format(dateFormatter, rate.effectiveTo)}',
                      style: appCss.dmDenseRegular12
                          .textColor(appColor(context).appTheme.lightText),
                    ),
                    const VSpace(Sizes.s6),
                    if (rate.complianceFlags.isNotEmpty)
                      Wrap(
                        spacing: Sizes.s6,
                        runSpacing: Sizes.s6,
                        children: rate.complianceFlags
                            .map((flag) => Chip(
                                  backgroundColor:
                                      appColor(context).appTheme.fieldCardBg,
                                  label: Text(flag,
                                      style: appCss.dmDenseRegular12.textColor(
                                          appColor(context)
                                              .appTheme
                                              .darkText)),
                                ))
                            .toList(),
                      ),
                    if ((rate.jurisdiction ?? {}).isNotEmpty)
                      Text(
                        '${translations!.jurisdictionCountry}: ${rate.jurisdiction?['country_code'] ?? '-'} / ${rate.jurisdiction?['region_code'] ?? '-'}',
                        style: appCss.dmDenseRegular12
                            .textColor(appColor(context).appTheme.lightText),
                      ),
                    const VSpace(Sizes.s6),
                    Text(
                      '${translations!.updatedAt}: ${_format(dateFormatter, rate.updatedAt ?? rate.createdAt)}',
                      style: appCss.dmDenseRegular12
                          .textColor(appColor(context).appTheme.lightText),
                    ),
                  ],
                ),
              );
            },
            separatorBuilder: (_, __) => const VSpace(Sizes.s14),
            itemCount: controller.rates.length,
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

  String _format(DateFormat formatter, String? value) {
    final parsed = value == null ? null : DateTime.tryParse(value);
    if (parsed == null) return translations!.notAvailable ?? '-';
    return formatter.format(parsed);
  }

  Future<void> _confirmDelete(
    BuildContext context,
    ProviderTaxRateModel rate,
  ) async {
    final shouldDelete = await showDialog<bool>(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: Text(translations!.confirmDeleteRate),
          content: Text(translations!.confirmDeleteRateDescription),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              child: Text(translations!.cancel!),
            ),
            TextButton(
              onPressed: () => Navigator.of(context).pop(true),
              child: Text(
                translations!.delete!,
                style: TextStyle(color: appColor(context).appTheme.redBg),
              ),
            ),
          ],
        );
      },
    );
    if (shouldDelete == true) {
      controller.deleteRate(context, rate);
    }
  }
}

class _DropdownField<T> extends StatelessWidget {
  final String label;
  final T? value;
  final List<DropdownMenuItem<T>> items;
  final ValueChanged<T?>? onChanged;
  final String? hint;
  final String? Function(T?)? validator;

  const _DropdownField({
    required this.label,
    required this.value,
    required this.items,
    this.onChanged,
    this.hint,
    this.validator,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        ContainerWithTextLayout(title: label),
        const VSpace(Sizes.s8),
        DropdownButtonFormField<T>(
          value: value,
          items: items,
          validator: validator,
          onChanged: onChanged,
          decoration: InputDecoration(
            filled: true,
            fillColor: appColor(context).appTheme.fieldCardBg,
            contentPadding: const EdgeInsets.symmetric(
              horizontal: Insets.i16,
              vertical: Insets.i14,
            ),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(AppRadius.r12),
              borderSide: BorderSide.none,
            ),
          ),
          hint: Text(hint ?? ''),
        ),
      ],
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
