import 'package:fluttertoast/fluttertoast.dart';

import '../../config.dart';

class ProviderTaxComplianceProvider with ChangeNotifier {
  ProviderTaxProfileModel? profile;
  List<ProviderTaxRateModel> rates = [];
  List<ProviderTaxAuditModel> audits = [];
  List<ZoneModel> zones = [];
  List<TaxModel> taxes = [];

  bool isLoading = false;
  bool isSavingProfile = false;
  bool isSavingRate = false;
  bool isDeletingRate = false;
  bool isFetchingAudits = false;
  String? errorMessage;

  // Profile form controllers
  final TextEditingController legalNameCtrl = TextEditingController();
  final TextEditingController taxIdentifierCtrl = TextEditingController();
  final TextEditingController countryCtrl = TextEditingController();
  final TextEditingController regionCtrl = TextEditingController();
  final TextEditingController metadataNotesCtrl = TextEditingController();

  bool privacyConsent = true;
  DateTime? effectiveFrom;
  DateTime? effectiveTo;

  // Rate form state
  final TextEditingController rateCtrl = TextEditingController();
  final TextEditingController registrationNumberCtrl = TextEditingController();
  final TextEditingController complianceFlagsCtrl = TextEditingController();
  final TextEditingController jurisdictionCountryCtrl = TextEditingController();
  final TextEditingController jurisdictionRegionCtrl = TextEditingController();

  ProviderTaxRateModel? editingRate;
  ZoneModel? selectedZone;
  TaxModel? selectedTax;
  bool rateIsDefault = true;
  String calculationType = 'exclusive';
  DateTime? rateEffectiveFrom;
  DateTime? rateEffectiveTo;

  Future<void> bootstrap(BuildContext context) async {
    if (isLoading) return;

    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      await loadReferenceData();
      await fetchProfile();
      await fetchRates();
      await fetchAudits();
    } catch (e) {
      errorMessage = e.toString();
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> refreshAll() async {
    await Future.wait([
      fetchProfile(),
      fetchRates(),
      fetchAudits(),
    ]);
    notifyListeners();
  }

  Future<void> loadReferenceData() async {
    try {
      final zoneResponse = await apiServices.getApi(api.zone, [], isToken: true);
      if (zoneResponse.isSuccess!) {
        zones = (zoneResponse.data as List<dynamic>)
            .map((data) => ZoneModel.fromJson(Map<String, dynamic>.from(data)))
            .toList();
      }

      final taxResponse = await apiServices.getApi(api.tax, [], isToken: true);
      if (taxResponse.isSuccess!) {
        taxes = (taxResponse.data as List<dynamic>)
            .map((data) => TaxModel.fromJson(Map<String, dynamic>.from(data)))
            .toList();
      }

      if (selectedTax == null && taxes.isNotEmpty) {
        selectedTax = taxes.first;
      }
    } catch (e) {
      errorMessage = e.toString();
    }
  }

  Future<void> fetchProfile() async {
    final response = await apiServices.getApi(api.providerTaxProfile, [], isToken: true);
    if (response.isSuccess!) {
      if (response.data == null) {
        profile = null;
        clearProfileForm();
      } else {
        profile = ProviderTaxProfileModel.fromJson(
            Map<String, dynamic>.from(response.data));
        populateProfileForm();
      }
    } else {
      errorMessage = response.message;
    }
    notifyListeners();
  }

  Future<void> fetchRates() async {
    final response = await apiServices.getApi(api.providerTaxRates, [], isToken: true);
    if (response.isSuccess!) {
      rates = (response.data as List<dynamic>)
          .map((data) => ProviderTaxRateModel.fromJson(
              Map<String, dynamic>.from(data)))
          .toList();
    } else {
      errorMessage = response.message;
    }
    notifyListeners();
  }

  Future<void> fetchAudits() async {
    isFetchingAudits = true;
    notifyListeners();
    final response = await apiServices.getApi(api.providerTaxAudits, [], isToken: true);
    if (response.isSuccess!) {
      audits = (response.data as List<dynamic>)
          .map((data) => ProviderTaxAuditModel.fromJson(
              Map<String, dynamic>.from(data)))
          .toList();
    }
    isFetchingAudits = false;
    notifyListeners();
  }

  void populateProfileForm() {
    legalNameCtrl.text = profile?.legalName ?? '';
    taxIdentifierCtrl.text = '';
    countryCtrl.text = profile?.countryCode ?? '';
    regionCtrl.text = profile?.regionCode ?? '';
    metadataNotesCtrl.text = profile?.metadata?['notes']?.toString() ?? '';
    privacyConsent = profile?.privacyConsentAt != null;
    effectiveFrom = profile?.effectiveFrom != null
        ? DateTime.tryParse(profile!.effectiveFrom!)
        : null;
    effectiveTo = profile?.effectiveTo != null
        ? DateTime.tryParse(profile!.effectiveTo!)
        : null;
  }

  void clearProfileForm() {
    legalNameCtrl.clear();
    taxIdentifierCtrl.clear();
    countryCtrl.clear();
    regionCtrl.clear();
    metadataNotesCtrl.clear();
    privacyConsent = true;
    effectiveFrom = null;
    effectiveTo = null;
  }

  Future<void> submitProfile(BuildContext context) async {
    isSavingProfile = true;
    notifyListeners();

    final body = {
      'legal_name': legalNameCtrl.text.trim(),
      'tax_identifier': taxIdentifierCtrl.text.trim(),
      'country_code': countryCtrl.text.trim().toUpperCase(),
      'region_code': regionCtrl.text.trim().isEmpty
          ? null
          : regionCtrl.text.trim().toUpperCase(),
      'effective_from': (effectiveFrom ?? DateTime.now())
          .toIso8601String()
          .split('T')
          .first,
      'effective_to': effectiveTo != null
          ? effectiveTo!.toIso8601String().split('T').first
          : null,
      'metadata': metadataNotesCtrl.text.trim().isEmpty
          ? {}
          : {
              'notes': metadataNotesCtrl.text.trim(),
            },
      'privacy_consent': privacyConsent,
    };

    try {
      final hasProfile = profile != null;
      final response = hasProfile
          ? await apiServices.putApi(api.providerTaxProfile, body, isToken: true)
          : await apiServices.postApi(api.providerTaxProfile, body,
              isToken: true);

      if (response.isSuccess!) {
        profile = ProviderTaxProfileModel.fromJson(
            Map<String, dynamic>.from(response.data));
        Fluttertoast.showToast(
            msg: translations?.update ?? 'Profile updated',
            backgroundColor: appColor(context).appTheme.primary);
        await fetchRates();
        await fetchAudits();
        populateProfileForm();
      } else {
        Fluttertoast.showToast(msg: response.message);
      }
    } catch (e) {
      Fluttertoast.showToast(msg: e.toString());
    } finally {
      isSavingProfile = false;
      notifyListeners();
    }
  }

  void resetRateForm([ProviderTaxRateModel? rate]) {
    editingRate = rate;
    rateCtrl.text = rate?.rate?.toString() ?? '';
    registrationNumberCtrl.text = rate?.registrationNumber ?? '';
    complianceFlagsCtrl.text =
        rate == null ? '' : rate.complianceFlags.join(', ');
    jurisdictionCountryCtrl.text =
        rate?.jurisdiction?['country_code']?.toString() ?? '';
    jurisdictionRegionCtrl.text =
        rate?.jurisdiction?['region_code']?.toString() ?? '';
    selectedZone = null;
    if (rate?.zoneId != null) {
      try {
        selectedZone = zones.firstWhere((z) => z.id == rate!.zoneId);
      } catch (_) {
        selectedZone = null;
      }
    }

    selectedTax = null;
    if (rate?.taxId != null) {
      try {
        selectedTax = taxes.firstWhere((t) => t.id == rate!.taxId);
      } catch (_) {
        selectedTax = null;
      }
    }
    selectedTax ??= taxes.isNotEmpty ? taxes.first : null;
    rateIsDefault = rate?.isDefault ?? true;
    calculationType = rate?.calculationType ?? 'exclusive';
    rateEffectiveFrom = rate?.effectiveFrom != null
        ? DateTime.tryParse(rate!.effectiveFrom!)
        : DateTime.now();
    rateEffectiveTo = rate?.effectiveTo != null
        ? DateTime.tryParse(rate!.effectiveTo!)
        : null;
  }

  Future<void> submitRate(BuildContext context) async {
    if (profile == null) {
      Fluttertoast.showToast(
          msg: translations?.createTaxProfileFirst ?? 'Create profile first');
      return;
    }

    final parsedRate = double.tryParse(rateCtrl.text.trim());
    if (parsedRate == null) {
      Fluttertoast.showToast(
          msg: translations?.pleaseEnterValid ?? 'Enter a valid rate');
      return;
    }

    isSavingRate = true;
    notifyListeners();

    final payload = {
      'profile_id': profile!.id,
      'tax_id': selectedTax?.id ?? editingRate?.taxId,
      'zone_id': selectedZone?.id,
      'rate': parsedRate,
      'calculation_type': calculationType,
      'is_default': rateIsDefault,
      'effective_from': (rateEffectiveFrom ?? DateTime.now())
          .toIso8601String()
          .split('T')
          .first,
      'effective_to': rateEffectiveTo != null
          ? rateEffectiveTo!.toIso8601String().split('T').first
          : null,
      'registration_number': registrationNumberCtrl.text.trim().isEmpty
          ? null
          : registrationNumberCtrl.text.trim(),
      'jurisdiction': {
        if (jurisdictionCountryCtrl.text.trim().isNotEmpty)
          'country_code': jurisdictionCountryCtrl.text.trim().toUpperCase(),
        if (jurisdictionRegionCtrl.text.trim().isNotEmpty)
          'region_code': jurisdictionRegionCtrl.text.trim().toUpperCase(),
      },
      'compliance_flags': complianceFlagsCtrl.text
              .split(',')
              .map((flag) => flag.trim())
              .where((flag) => flag.isNotEmpty)
              .toList(),
    };

    try {
      final response = editingRate == null
          ? await apiServices.postApi(api.providerTaxRates, payload,
              isToken: true)
          : await apiServices.putApi(
              '${api.providerTaxRates}/${editingRate!.id}', payload,
              isToken: true);

      if (response.isSuccess!) {
        Fluttertoast.showToast(
            msg: translations?.update ?? 'Saved',
            backgroundColor: appColor(context).appTheme.primary);
        await fetchRates();
        await fetchAudits();
        resetRateForm();
        notifyListeners();
      } else {
        Fluttertoast.showToast(msg: response.message);
      }
    } catch (e) {
      Fluttertoast.showToast(msg: e.toString());
    } finally {
      isSavingRate = false;
      notifyListeners();
    }
  }

  Future<void> deleteRate(BuildContext context, ProviderTaxRateModel rate) async {
    isDeletingRate = true;
    notifyListeners();
    try {
      final response = await apiServices.deleteApi(
          '${api.providerTaxRates}/${rate.id}', {},
          isToken: true);
      if (response.isSuccess!) {
        Fluttertoast.showToast(
            msg: translations?.deleteAccount ?? 'Deleted',
            backgroundColor: appColor(context).appTheme.primary);
        await fetchRates();
        await fetchAudits();
        resetRateForm();
        notifyListeners();
      } else {
        Fluttertoast.showToast(msg: response.message);
      }
    } catch (e) {
      Fluttertoast.showToast(msg: e.toString());
    } finally {
      isDeletingRate = false;
      notifyListeners();
    }
  }

  void updateRateEffectiveFrom(DateTime date) {
    rateEffectiveFrom = date;
    notifyListeners();
  }

  void updateRateEffectiveTo(DateTime? date) {
    rateEffectiveTo = date;
    notifyListeners();
  }

  void updateProfileEffectiveFrom(DateTime date) {
    effectiveFrom = date;
    notifyListeners();
  }

  void updateProfileEffectiveTo(DateTime? date) {
    effectiveTo = date;
    notifyListeners();
  }

  @override
  void dispose() {
    legalNameCtrl.dispose();
    taxIdentifierCtrl.dispose();
    countryCtrl.dispose();
    regionCtrl.dispose();
    metadataNotesCtrl.dispose();
    rateCtrl.dispose();
    registrationNumberCtrl.dispose();
    complianceFlagsCtrl.dispose();
    jurisdictionCountryCtrl.dispose();
    jurisdictionRegionCtrl.dispose();
    super.dispose();
  }
}
