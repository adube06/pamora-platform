import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final onboardingStorageProvider = Provider<OnboardingStorage>((ref) => OnboardingStorage());

/// Tracks whether the short "what is Pamora" onboarding has been shown,
/// so returning users skip straight to login.
class OnboardingStorage {
  OnboardingStorage({FlutterSecureStorage? storage}) : _storage = storage ?? const FlutterSecureStorage();

  static const _key = 'pamora_onboarding_seen';

  final FlutterSecureStorage _storage;

  Future<bool> hasSeenOnboarding() async => (await _storage.read(key: _key)) == 'true';

  Future<void> markSeen() => _storage.write(key: _key, value: 'true');
}
