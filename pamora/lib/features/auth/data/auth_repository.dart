import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import '../../../core/storage/token_storage.dart';
import '../models/user.dart';
import 'user_cache.dart';

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(ref.read(dioProvider), ref.read(tokenStorageProvider), ref.read(userCacheProvider));
});

class AuthRepository {
  AuthRepository(this._dio, this._tokenStorage, this._userCache);

  final Dio _dio;
  final TokenStorage _tokenStorage;
  final UserCache _userCache;

  Future<User> register({
    required String name,
    required String email,
    required String password,
  }) {
    return guarded(() async {
      final response = await _dio.post('/auth/register', data: {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': password,
      });
      return _persistSession(unwrapData(response) as Map<String, dynamic>);
    });
  }

  Future<User> login({required String email, required String password}) {
    return guarded(() async {
      final response = await _dio.post('/auth/login', data: {'email': email, 'password': password});
      return _persistSession(unwrapData(response) as Map<String, dynamic>);
    });
  }

  Future<void> logout() async {
    try {
      await _dio.post('/auth/logout');
    } catch (_) {
      // Best-effort: the token may already be dead server-side. The local
      // session is what actually matters for getting the user logged out.
    } finally {
      await _tokenStorage.clear();
      await _userCache.clear();
    }
  }

  Future<User?> restoreSession() async {
    final token = await _tokenStorage.read();
    if (token == null) return null;
    return _userCache.read();
  }

  Future<void> forgotPassword(String email) {
    return guarded(() => _dio.post('/auth/forgot-password', data: {'email': email}));
  }

  Future<void> resetPassword({
    required String token,
    required String email,
    required String password,
  }) {
    return guarded(() => _dio.post('/auth/reset-password', data: {
          'token': token,
          'email': email,
          'password': password,
          'password_confirmation': password,
        }));
  }

  Future<User> _persistSession(Map<String, dynamic> data) async {
    final token = data['token'] as String;
    final userJson = data['user'] as Map<String, dynamic>;
    await _tokenStorage.save(token);
    await _userCache.save(userJson);
    return User.fromJson(userJson);
  }
}
