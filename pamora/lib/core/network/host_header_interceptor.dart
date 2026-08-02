import 'package:dio/dio.dart';

import 'api_config.dart';

/// Forces the `Host` header to [ApiConfig.apiHost] regardless of the literal
/// address Dio connects to, so Herd's reverse proxy routes the request to
/// the right local site even when we dial an IP (emulator/physical device)
/// instead of the `.test` hostname.
class HostHeaderInterceptor extends Interceptor {
  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    options.headers['Host'] = ApiConfig.apiHost;
    handler.next(options);
  }
}
