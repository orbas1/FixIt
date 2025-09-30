import '../../config.dart';
import '../../models/affiliate_dashboard_model.dart';
import '../logging/app_logger.dart';

class AffiliateRepository {
  AffiliateRepository({ApiServices? apiServices})
      : _apiServices = apiServices ?? apiServices;

  final ApiServices _apiServices;

  Future<AffiliateDashboardModel> fetchDashboard({bool preferCache = true}) async {
    try {
      final response = await _apiServices.getApi(
        api.affiliateDashboard,
        const [],
        isToken: true,
        isData: true,
      );

      if (response.isSuccess == true && response.data != null) {
        if (response.data is Map<String, dynamic>) {
          return AffiliateDashboardModel.fromJson(
            Map<String, dynamic>.from(response.data as Map),
          );
        }

        if (response.data is List && (response.data as List).isNotEmpty) {
          final first = (response.data as List).first;
          if (first is Map) {
            return AffiliateDashboardModel.fromJson(
              Map<String, dynamic>.from(first as Map),
            );
          }
        }
      }
    } catch (error, stackTrace) {
      AppLogger.instance.error(
        'AffiliateRepository: dashboard fetch failed',
        error: error,
        stackTrace: stackTrace,
      );
    }

    return AffiliateDashboardModel.sample();
  }
}
