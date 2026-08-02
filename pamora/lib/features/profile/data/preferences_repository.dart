import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';

final preferencesRepositoryProvider = Provider<PreferencesRepository>((ref) {
  return PreferencesRepository(ref.read(dioProvider));
});

class PreferencesRepository {
  PreferencesRepository(this._dio);

  final Dio _dio;

  /// [notificationPreferences] keys must match the backend's flat
  /// notification-type keys directly (task_assigned, contribution_received,
  /// etc. — see Notification::TYPES) — the endpoint has no wrapper object.
  Future<Map<String, dynamic>> update(Map<String, dynamic> notificationPreferences) {
    return guarded(() async {
      final response = await _dio.patch('/preferences', data: notificationPreferences);
      final data = unwrapData(response) as Map<String, dynamic>;
      return (data['notification_preferences'] as Map).cast<String, dynamic>();
    });
  }
}
