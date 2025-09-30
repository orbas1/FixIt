import 'dart:async';
import 'dart:io';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:dio/dio.dart';

class ConnectivityInterceptor extends Interceptor {
  ConnectivityInterceptor({required Connectivity connectivity})
      : _connectivity = connectivity;

  final Connectivity _connectivity;

  @override
  Future<void> onRequest(RequestOptions options, RequestInterceptorHandler handler) async {
    final results = await _connectivity.checkConnectivity();
    if (results.contains(ConnectivityResult.none)) {
      handler.reject(
        DioException.connectionError(
          requestOptions: options,
          reason: 'No internet connectivity',
          error: const SocketException('No internet connectivity'),
        ),
      );
      return;
    }
    handler.next(options);
  }
}
