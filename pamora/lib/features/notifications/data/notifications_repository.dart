import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import '../models/app_notification.dart';

final notificationsRepositoryProvider = Provider<NotificationsRepository>((ref) {
  return NotificationsRepository(ref.read(dioProvider));
});

class NotificationsRepository {
  NotificationsRepository(this._dio);

  final Dio _dio;

  Future<List<AppNotification>> list() {
    return guarded(() async {
      final response = await _dio.get('/notifications');
      final data = unwrapData(response) as List;
      return data.map((e) => AppNotification.fromJson(e as Map<String, dynamic>)).toList();
    });
  }

  Future<AppNotification> markRead(String id) {
    return guarded(() async {
      final response = await _dio.post('/notifications/$id/read');
      return AppNotification.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }
}
