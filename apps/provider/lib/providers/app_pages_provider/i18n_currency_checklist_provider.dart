import 'package:flutter/foundation.dart';

import '../../model/i18n_currency_checklist_model.dart';
import '../../services/i18n_currency_checklist_service.dart';

class I18nCurrencyChecklistProvider with ChangeNotifier {
  I18nCurrencyChecklistProvider({I18nCurrencyChecklistService? service})
      : _service = service ?? const I18nCurrencyChecklistService();

  final I18nCurrencyChecklistService _service;

  I18nCurrencyChecklist? _checklist;
  bool _isLoading = false;
  String? _errorMessage;

  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  I18nCurrencyChecklist? get checklist => _checklist;

  DateTime get generatedAt =>
      _checklist?.generatedAt ?? DateTime.fromMillisecondsSinceEpoch(0, isUtc: true);

  List<I18nLocaleStatus> get locales => _checklist?.locales ?? const [];

  Map<String, I18nCurrencyProfile> get currencyProfiles =>
      _checklist?.currencyProfiles ?? const {};

  double get averageFlutterCoverage {
    if (locales.isEmpty) {
      return 0;
    }
    final total = locales
        .map((locale) => locale.expectation.flutterCoverage)
        .fold<double>(0, (value, element) => value + element);
    return total / locales.length;
  }

  double get averageLaravelCoverage {
    if (locales.isEmpty) {
      return 0;
    }
    final total = locales
        .map((locale) => locale.expectation.laravelCoverage)
        .fold<double>(0, (value, element) => value + element);
    return total / locales.length;
  }

  int get perfectLocaleCount =>
      locales.where((locale) => locale.expectation.isPerfect).length;

  bool get isChecklistStale {
    final difference = DateTime.now().toUtc().difference(generatedAt).inDays;
    return difference > 7;
  }

  Future<void> bootstrap() => _load();

  Future<void> refresh() => _load(force: true);

  Future<void> _load({bool force = false}) async {
    if (_isLoading && !force) {
      return;
    }
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
    try {
      _checklist = await _service.loadChecklist();
    } catch (error, stackTrace) {
      debugPrint('Failed to load i18n checklist: $error');
      debugPrintStack(stackTrace: stackTrace);
      _errorMessage = error.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  I18nCurrencyProfile? profileForCode(String code) {
    return currencyProfiles[code];
  }
}
