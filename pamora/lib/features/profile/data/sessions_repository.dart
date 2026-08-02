import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import '../models/session.dart';

final sessionsRepositoryProvider = Provider<SessionsRepository>((ref) {
  return SessionsRepository(ref.read(dioProvider));
});

class SessionsRepository {
  SessionsRepository(this._dio);

  final Dio _dio;

  Future<List<Session>> list() {
    return guarded(() async {
      final response = await _dio.get('/sessions');
      return (unwrapData(response) as List).map((e) => Session.fromJson(e as Map<String, dynamic>)).toList();
    });
  }

  Future<void> revoke(String sessionId) {
    return guarded(() => _dio.delete('/sessions/$sessionId'));
  }
}
