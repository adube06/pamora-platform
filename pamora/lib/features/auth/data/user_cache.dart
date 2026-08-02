import 'dart:convert';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/user.dart';

final userCacheProvider = Provider<UserCache>((ref) => UserCache());

/// The backend has no `GET /me` endpoint — register/login are the only
/// calls that return the user object. We cache it locally alongside the
/// token so the splash screen can rehydrate a session without an API call.
class UserCache {
  UserCache({FlutterSecureStorage? storage}) : _storage = storage ?? const FlutterSecureStorage();

  static const _key = 'pamora_cached_user';

  final FlutterSecureStorage _storage;

  Future<void> save(Map<String, dynamic> json) => _storage.write(key: _key, value: jsonEncode(json));

  Future<User?> read() async {
    final raw = await _storage.read(key: _key);
    if (raw == null) return null;
    return User.fromJson(jsonDecode(raw) as Map<String, dynamic>);
  }

  Future<void> clear() => _storage.delete(key: _key);
}
