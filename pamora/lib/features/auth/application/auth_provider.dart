import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/network/auth_events.dart';
import '../data/auth_repository.dart';
import '../models/user.dart';

final authProvider = AsyncNotifierProvider<AuthNotifier, User?>(AuthNotifier.new);

/// Holds the current session. `null` data (not loading, not error) means
/// "known to be logged out" — the state go_router's redirect reads.
class AuthNotifier extends AsyncNotifier<User?> {
  @override
  Future<User?> build() async {
    ref.listen<void>(_unauthorizedProvider, (_, __) {
      state = const AsyncData(null);
    });
    return ref.read(authRepositoryProvider).restoreSession();
  }

  Future<void> login({required String email, required String password}) async {
    state = const AsyncLoading();
    try {
      final user = await ref.read(authRepositoryProvider).login(email: email, password: password);
      state = AsyncData(user);
    } on Exception catch (e, st) {
      state = AsyncError(e, st);
      rethrow;
    }
  }

  Future<void> register({required String name, required String email, required String password}) async {
    state = const AsyncLoading();
    try {
      final user = await ref.read(authRepositoryProvider).register(name: name, email: email, password: password);
      state = AsyncData(user);
    } on Exception catch (e, st) {
      state = AsyncError(e, st);
      rethrow;
    }
  }

  Future<void> logout() async {
    await ref.read(authRepositoryProvider).logout();
    state = const AsyncData(null);
  }
}

/// Bridges the plain [AuthEvents] broadcast stream into a provider so
/// [AuthNotifier] can `ref.listen` to it declaratively.
final _unauthorizedProvider = StreamProvider<void>((ref) => AuthEvents.onUnauthorized);

extension AuthExceptionMessage on Object {
  String get authErrorMessage => this is ApiException ? (this as ApiException).message : 'Something went wrong.';
}
