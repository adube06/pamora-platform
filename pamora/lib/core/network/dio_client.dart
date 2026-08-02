import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../storage/token_storage.dart';
import 'api_config.dart';
import 'api_exception.dart';
import 'auth_interceptor.dart';
import 'host_header_interceptor.dart';

final tokenStorageProvider = Provider<TokenStorage>((ref) => TokenStorage());

final dioProvider = Provider<Dio>((ref) {
  final dio = Dio(
    BaseOptions(
      baseUrl: ApiConfig.baseUrl,
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 15),
      headers: {'Accept': 'application/json'},
    ),
  );

  dio.interceptors.addAll([
    HostHeaderInterceptor(),
    AuthInterceptor(ref.read(tokenStorageProvider)),
    if (kDebugMode) LogInterceptor(requestBody: true, responseBody: true, logPrint: (o) => debugPrint(o.toString())),
  ]);

  return dio;
});

/// Unwraps the `{success, data}` envelope every Pamora endpoint returns.
dynamic unwrapData(Response response) => response.data is Map ? response.data['data'] : response.data;

/// Runs a Dio call and rethrows any [DioException] as our own
/// [ApiException], so repositories never leak raw Dio errors to the UI.
Future<T> guarded<T>(Future<T> Function() action) async {
  try {
    return await action();
  } on DioException catch (e) {
    throw mapDioError(e);
  }
}

/// Maps a [DioException] onto our own [ApiException], pulling the API's
/// `{success: false, error: {code, message, fields}}` envelope
/// (pamora-foundation/04-engineering/07-api-specification.md) into a
/// user-facing message plus per-field validation errors where present.
ApiException mapDioError(DioException error) {
  final response = error.response;
  if (response != null && response.data is Map) {
    final body = response.data as Map;
    final errorBody = body['error'] is Map ? body['error'] as Map : null;
    final message = errorBody?['message']?.toString() ?? 'Something went wrong. Please try again.';
    final rawFields = errorBody?['fields'];
    final fieldErrors = <String, List<String>>{};
    if (rawFields is Map) {
      rawFields.forEach((key, value) {
        if (value is List) {
          fieldErrors[key.toString()] = value.map((e) => e.toString()).toList();
        }
      });
    }
    return ApiException(message: message, statusCode: response.statusCode, fieldErrors: fieldErrors);
  }

  if (error.type == DioExceptionType.connectionTimeout ||
      error.type == DioExceptionType.receiveTimeout ||
      error.type == DioExceptionType.connectionError) {
    return ApiException(message: 'Can\'t reach Pamora. Check your connection and try again.');
  }

  return ApiException(message: 'Something went wrong. Please try again.', statusCode: response?.statusCode);
}
