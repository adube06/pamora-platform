/// API connection config, overridable at build/run time via `--dart-define`.
///
/// Herd resolves `pamora.test` through a host-machine-only DNS trick, so the
/// Android emulator can't resolve that hostname directly. We connect via an
/// IP (the emulator's host alias `10.0.2.2` by default) but force the
/// `Host` header to the real site hostname so Herd's proxy still routes to
/// the correct site — see [HostHeaderInterceptor].
class ApiConfig {
  ApiConfig._();

  /// The site hostname Herd routes on. Override with
  /// `--dart-define=API_HOST=pamora.test`.
  static const String apiHost = String.fromEnvironment('API_HOST', defaultValue: 'pamora.test');

  /// The IP/host actually dialed. Defaults to the Android emulator's alias
  /// for the host machine's loopback interface. For a physical device on
  /// the same network, override with `--dart-define=API_HOST_IP=<lan-ip>`
  /// once Herd's site sharing / network access is enabled for that site.
  static const String apiHostIp = String.fromEnvironment('API_HOST_IP', defaultValue: '10.0.2.2');

  static const String scheme = String.fromEnvironment('API_SCHEME', defaultValue: 'http');

  static String get baseUrl => '$scheme://$apiHostIp/api/v1';
}
