import 'package:dio/dio.dart';

class IpLocationClient {
  IpLocationClient({required Dio httpClient, String? endpoint})
      : _httpClient = httpClient,
        _endpoint = endpoint;

  final Dio _httpClient;
  final String? _endpoint;

  Future<Map<String, dynamic>?> resolve() async {
    try {
      final response = await _httpClient.get<Map<String, dynamic>>(
        _endpoint ?? '/location/estimate',
        options: Options(headers: {'Accept': 'application/json'}),
      );
      final data = response.data;
      if (data == null) return null;
      final payload = data['data'];
      if (payload is Map<String, dynamic>) {
        return Map<String, dynamic>.from(payload);
      }
      return null;
    } on DioException catch (error) {
      if (error.response?.statusCode == 404) {
        return null;
      }
      rethrow;
    }
  }
}
