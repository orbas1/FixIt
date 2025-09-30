import 'dart:convert';
import 'dart:developer';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:dio/dio.dart';
import 'package:fixit_user/config.dart';
import 'package:get_it/get_it.dart';
import 'package:http/http.dart' as http;

import '../screens/app_pages_screens/server_error_screen/server_error.dart';
import 'auth/auth_token_store.dart';
import 'environment.dart';
import 'error/exceptions.dart';

class ApiServices {
  factory ApiServices() => GetIt.I<ApiServices>();

  ApiServices.withDependencies({
    required this.dio,
    required EnvironmentStore environmentStore,
    required AuthTokenStore tokenStore,
  })  : _environmentStore = environmentStore,
        _tokenStore = tokenStore;

  final Dio dio;
  final EnvironmentStore _environmentStore;
  final AuthTokenStore _tokenStore;

  static final http.Client client = http.Client();
  static List<Map<String, String>> conversationHistory = [];

  //to get full path with paramiters
  static Future<String> getFullUrl(String apiName, List params) async {
    String url0 = "";
    if (params.isNotEmpty) {
      url0 = "$apiName?";
      for (int i = 0; i < params.length; i++) {
        url0 = '$url0${params[i]["key"]}=${params[i]["value"]}';
        if (i + 1 != params.length) url0 = "$url0&";
      }
    } else {
      url0 = apiName;
    }

    String url = '$apiUrl$url0';

    return url;
  }

  Future<APIDataClass> dioException(e) async {
    APIDataClass apiData =
        APIDataClass(message: 'No data', isSuccess: false, data: '0');
    if (e is DioException) {
      if (e.type == DioExceptionType.badResponse) {
        final response = e.response;
        if (response!.statusCode == 403) {
          apiData.message = response.data.toString();
          apiData.isSuccess = false;
          apiData.data = response.statusCode;

          return apiData;
        } else {
          if (response.data != null) {
            apiData.message = response.data['message'];
            apiData.isSuccess = false;
            apiData.data = 0;
            return apiData;
          } else {
            log("EROROROROROR :$response");
            apiData.message = response.data.toString();
            apiData.isSuccess = false;
            apiData.data = 0;
            return apiData;
          }
        }
      } else {
        final response = e.response;
        if (response != null && response.data != null) {
          final Map responseData = json.decode(response.data as String) as Map;
          apiData.message = responseData['message'] as String;
          apiData.isSuccess = false;
          apiData.data = 0;
          return apiData;
        } else {
          log("EROROROROROR :${response!.statusCode}");
          apiData.message = response!.statusCode.toString();
          apiData.isSuccess = false;
          apiData.data = 0;
          return apiData;
        }
      }
    } else {
      log("EROROROROROR :$apiData.message");
      apiData.message = "";
      apiData.isSuccess = false;
      apiData.data = 0;
      return apiData;
    }
  }

  Future<APIDataClass> getApi(
    String apiName,
    dynamic params, {
    isToken = false,
    isData = false,
    isMessage = true,
  }) async {
    //default data to class
    APIDataClass apiData = APIDataClass(
      message: 'No data',
      isSuccess: false,
      data: '0',
    );
    //Check For Internet
    bool isInternet = await isNetworkConnection();
    if (!isInternet) {
      apiData.message = "No Internet Access";
      apiData.isSuccess = false;
      apiData.data = 0;
      return apiData;
    } else {
      log("URL Name For Call: $apiName");

      try {
        final token = isToken ? await _tokenStore.read() : null;
        log("token : $token");
        Response? response;
        response = await dio.get(
          apiName,
          data: params,
          options: Options(headers: isToken ? headersToken(token) : headers),
        );
        log("STATUSS : ${response.statusCode}");
        if (response.statusCode == 500) {
          navigatorKey.currentState?.pushReplacement(
            MaterialPageRoute(builder: (_) => const ServerErrorScreen()),
          );
          return apiData;
        }
        if (response.statusCode == 200 || response.statusCode == 201) {
          //get response
          var responseData = response.data;
          // log("$apiName Response: $responseData");
          //set data to class
          if (isData) {
            apiData.message = isMessage
                ? apiName.contains("highest-ratings")
                    ? ""
                    : responseData["message"] ?? ""
                : "";
            apiData.isSuccess = true;
            log("dskyghvjryb//${apiData.isSuccess}");
            apiData.data = responseData;
            return apiData;
          } else {
            apiData.message = responseData["message"] ?? "";
            apiData.isSuccess = true;
            apiData.data = apiName.contains("self")
                ? responseData['user']
                : responseData["data"];
            return apiData;
          }
        } else {
          log("EEEEEEEEERRRRRROOORRR");
          apiData.message = "No Internet Access";
          apiData.isSuccess = false;
          apiData.data = 0;
          return apiData;
        }
      } catch (e) {
        apiData = await dioException(e);
        log("DDDD :$apiName :: ${apiData.message}");
        return apiData;
      }
    }
  }

  Future<APIDataClass> postApi(String apiName, body,
      {isToken = false, isData = false}) async {
    //default data to class
    APIDataClass apiData = APIDataClass(
      message: 'No data',
      isSuccess: false,
      data: '0',
    );
    //Check For Internet
    var connectivityResult = await (Connectivity().checkConnectivity());
    if (connectivityResult == ConnectivityResult.none) {
      apiData.message = "No Internet Access";
      apiData.isSuccess = false;
      apiData.data = 0;
      return apiData;
    } else {
      log("URL: $apiName");

      final token = isToken ? await _tokenStore.read() : null;
      log("AUTH : $token");
      log("AUTH : ${headersToken(token)}");
      try {
        final response = await dio.post(
          apiName,
          data: body,
          options: Options(headers: isToken ? headersToken(token) : headers),
        );

        var responseData = response.data;
        log("response 1: ${response.statusCode}");
        final statusCode = response.statusCode ?? 0;
        if (statusCode == 202) {
          apiData.message = responseData["message"] ?? "";
          apiData.isSuccess = false;
          apiData.data = responseData;
          return apiData;
        }

        if (statusCode == 200 || statusCode == 201) {
          if (responseData is Map<String, dynamic> &&
              responseData['access_token'] != null) {
            final String accessToken = responseData['access_token'] as String;
            final refreshToken = responseData['refresh_token'];
            if (refreshToken is String && refreshToken.isNotEmpty) {
              await _tokenStore.writeTokens(
                  accessToken: accessToken, refreshToken: refreshToken);
            } else {
              await _tokenStore.write(accessToken);
            }

            final bool isLoginEndpoint =
                apiName.contains("/login") || apiName.contains("social/login");
            final bool isRegisterEndpoint = apiName.contains("register");

            apiData.message = responseData["message"] ??
                (isLoginEndpoint
                    ? "Login Successfully"
                    : (isRegisterEndpoint ? "Register Successfully" : "Success"));
            apiData.isSuccess = true;
            apiData.data = responseData;
            return apiData;
          }

          if (isData) {
            apiData.message = responseData["message"] ?? "";
            apiData.isSuccess = true;
            apiData.data = responseData;
            return apiData;
          }

          apiData.message = responseData["message"] ?? "";
          apiData.isSuccess = true;
          apiData.data = responseData["data"] ?? responseData;
          return apiData;
        }

        log("RESPONJSE 1: ${response.data}");
        apiData.message = responseData["message"] ?? "";
        apiData.isSuccess = false;
        apiData.data = responseData;
        return apiData;
      } catch (e) {
        if (e is DioException) {
          if (e.type == DioExceptionType.badResponse) {
            final response = e.response;
            log("EEEEE : $response");

            if (response!.data != null) {
              apiData.message = response.data['message'];
              apiData.isSuccess = false;
              apiData.data = 0;
              return apiData;
            } else {
              apiData.message = response.data.toString();
              apiData.isSuccess = false;
              apiData.data = 0;
              return apiData;
            }
          } else {
            final response = e.response;
            if (response != null && response.data != null) {
              final Map responseData =
                  json.decode(response.data as String) as Map;
              apiData.message = responseData['message'] as String;
              apiData.isSuccess = false;
              apiData.data = 0;
              return apiData;
            } else {
              apiData.message = response!.statusCode.toString();
              apiData.isSuccess = false;
              apiData.data = 0;
              return apiData;
            }
          }
        } else {
          apiData.message = "";
          apiData.isSuccess = false;
          apiData.data = 0;
          return apiData;
        }
      }
    }
  }

  Future<APIDataClass> deleteApi(String apiName, body,
      {isToken = false}) async {
    //default data to class
    APIDataClass apiData = APIDataClass(
      message: 'No data',
      isSuccess: false,
      data: '0',
    );
    //Check For Internet
    var connectivityResult = await (Connectivity().checkConnectivity());
    if (connectivityResult == ConnectivityResult.none) {
      apiData.message = "No Internet Access";
      apiData.isSuccess = false;
      apiData.data = 0;
      return apiData;
    } else {
      log("URL: $apiName");

      final token = isToken ? await _tokenStore.read() : null;
      log("AUTH : $token");
      log("AUTH : ${headersToken(token)}");
      try {
        final response = await dio.delete(
          apiName,
          data: body,
          options: Options(headers: isToken ? headersToken(token) : headers),
        );
        var responseData = response.data;
        log("response 1: $responseData");
        if (response.statusCode == 200 || response.statusCode == 201) {
          //set data to class
          log("RESPONJSE :2 ${response.data}");
          apiData.message = responseData["message"] ?? "";
          apiData.isSuccess = true;
          apiData.data = responseData["data"];
          return apiData;
        } else {
          log("RESPONJSE 1: ${response.data}");
          apiData.message = responseData["message"];
          apiData.isSuccess = false;
          apiData.data = 0;
          return apiData;
        }
      } catch (e) {
        if (e is DioException) {
          if (e.type == DioExceptionType.badResponse) {
            final response = e.response;
            log("RESPONJSE 1: ${response!.data}");
            if (response.data != null) {
              apiData.message = response.data['message'];
              apiData.isSuccess = false;
              apiData.data = 0;
              return apiData;
            } else {
              apiData.message = response.data.toString();
              apiData.isSuccess = false;
              apiData.data = 0;
              return apiData;
            }
          } else {
            final response = e.response;
            if (response != null && response.data != null) {
              final Map responseData =
                  json.decode(response.data as String) as Map;
              apiData.message = responseData['message'] as String;
              apiData.isSuccess = false;
              apiData.data = 0;
              return apiData;
            } else {
              apiData.message = response!.statusCode.toString();
              apiData.isSuccess = false;
              apiData.data = 0;
              return apiData;
            }
          }
        } else {
          apiData.message = "";
          apiData.isSuccess = false;
          apiData.data = 0;
          return apiData;
        }
      }
    }
  }

  Future<APIDataClass> putApi(String apiName, body,
      {isToken = false, isData = false}) async {
    //default data to class
    APIDataClass apiData = APIDataClass(
      message: 'No data',
      isSuccess: false,
      data: '0',
    );
    //Check For Internet
    var connectivityResult = await (Connectivity().checkConnectivity());
    if (connectivityResult == ConnectivityResult.none) {
      apiData.message = "No Internet Access";
      apiData.isSuccess = false;
      apiData.data = 0;
      return apiData;
    } else {
      log("URL: $apiName");

      final token = isToken ? await _tokenStore.read() : null;
      log("AUTH : $token");
      log("AUTH : ${headersToken(token)}");
      try {
        final response = await dio.put(
          apiName,
          data: jsonEncode(body),
          options: Options(headers: isToken ? headersToken(token) : headers),
        );
        var responseData = response.data;
        log("response : ${response.statusCode}");
        if (response.statusCode == 200 || response.statusCode == 201) {
          //get response

          if (isData) {
            /*  await _tokenStore.write(responseData['access_token']);*/
            //set data to class
            apiData.message = "";
            apiData.isSuccess = true;
            apiData.data = responseData;
            return apiData;
          } else {
            //set data to class
            log("RESPONJSE :2 ${response.data}");
            apiData.message = responseData["message"] ?? "";
            apiData.isSuccess = true;
            apiData.data = responseData["data"];
            return apiData;
          }
        } else {
          log("RESPONJSE 1: ${response.data}");
          apiData.message = responseData["message"];
          apiData.isSuccess = false;
          apiData.data = 0;
          return apiData;
        }
      } catch (e) {
        if (e is DioException) {
          if (e.type == DioExceptionType.badResponse) {
            final response = e.response;
            log("EEEEresponse :$response");
            if (response != null && response.data != null) {
              apiData.message = response.data['message'];
              apiData.isSuccess = false;
              apiData.data = 0;
              return apiData;
            } else {
              apiData.message = response!.data.toString();
              apiData.isSuccess = false;
              apiData.data = 0;
              return apiData;
            }
          } else {
            final response = e.response;
            if (response != null && response.data != null) {
              final Map responseData =
                  json.decode(response.data as String) as Map;
              apiData.message = responseData['message'] as String;
              apiData.isSuccess = false;
              apiData.data = 0;
              return apiData;
            } else {
              apiData.message = response!.statusCode.toString();
              apiData.isSuccess = false;
              apiData.data = 0;
              return apiData;
            }
          }
        } else {
          apiData.message = "";
          apiData.isSuccess = false;
          apiData.data = 0;
          return apiData;
        }
      }
    }
  }
}

Exception handleErrorResponse(http.Response response) {
  var data = jsonDecode(response.body);

  return RemoteException(
    statusCode: response.statusCode,
    message: data['message'] ?? response.statusCode == 422
        ? 'Validation failed'
        : 'Server exception',
  );
}
