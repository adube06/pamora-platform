import 'package:dio/dio.dart';

import '../storage/token_storage.dart';
import 'auth_events.dart';

/// Attaches the stored bearer token to every request, and reacts to a 401
/// by clearing the dead token and broadcasting [AuthEvents.onUnauthorized]
/// so the auth layer can drop the user back to the login screen.
class AuthInterceptor extends Interceptor {
  AuthInterceptor(this._tokenStorage);

  final TokenStorage _tokenStorage;

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) async {
    final token = await _tokenStorage.read();
    if (token != null) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    handler.next(options);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    if (err.response?.statusCode == 401) {
      await _tokenStorage.clear();
      AuthEvents.notifyUnauthorized();
    }
    handler.next(err);
  }
}
