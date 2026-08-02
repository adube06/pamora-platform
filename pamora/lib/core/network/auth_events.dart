import 'dart:async';

/// A tiny broadcast channel the network layer uses to tell the auth layer
/// "the token is dead" without core/network importing features/auth (which
/// would create a provider dependency cycle: auth needs Dio, Dio's 401
/// handling needs to force auth into a logged-out state).
class AuthEvents {
  AuthEvents._();

  static final StreamController<void> _unauthorized = StreamController<void>.broadcast();

  static Stream<void> get onUnauthorized => _unauthorized.stream;

  static void notifyUnauthorized() => _unauthorized.add(null);
}
