import 'package:dio/dio.dart';

import '../../models/ads/placement_models.dart';

class PlacementApiClient {
  PlacementApiClient({required this.httpClient});

  final Dio httpClient;

  Future<PlacementApiResponse> fetchPlacement(
    String slot, {
    required List<String> zones,
    String? locale,
    String? etag,
  }) async {
    final query = <String, dynamic>{};
    if (zones.isNotEmpty) {
      query['zones[]'] = zones;
    }
    if (locale != null) {
      query['locale'] = locale;
    }

    final response = await httpClient.get<Map<String, dynamic>>(
      '/api/v1/placements/$slot',
      queryParameters: query,
      options: Options(
        headers: {
          if (etag != null) 'If-None-Match': etag,
        },
        responseType: ResponseType.json,
        validateStatus: (status) => status != null && status < 500,
      ),
    );

    if (response.statusCode == 304) {
      return PlacementApiResponse.notModified();
    }

    if (response.statusCode != null && response.statusCode! >= 200 && response.statusCode! < 300) {
      final data = response.data?['data'] as Map<String, dynamic>? ?? {};
      final payload = PlacementPayload.fromJson(data);
      final etagHeader = response.headers.value('etag');
      return PlacementApiResponse(payload: payload, etag: etagHeader);
    }

    throw DioException(
      requestOptions: response.requestOptions,
      response: response,
      type: DioExceptionType.badResponse,
    );
  }
}

class PlacementApiResponse {
  PlacementApiResponse({this.payload, this.etag, this.notModified = false});

  factory PlacementApiResponse.notModified() => PlacementApiResponse(notModified: true);

  final PlacementPayload? payload;
  final String? etag;
  final bool notModified;

  bool get hasPayload => payload != null;
}
