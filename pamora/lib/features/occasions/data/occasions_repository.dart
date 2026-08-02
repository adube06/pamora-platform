import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import '../models/occasion.dart';

final occasionsRepositoryProvider = Provider<OccasionsRepository>((ref) {
  return OccasionsRepository(ref.read(dioProvider));
});

class OccasionsRepository {
  OccasionsRepository(this._dio);

  final Dio _dio;

  Future<List<Occasion>> list() {
    return guarded(() async {
      final response = await _dio.get('/occasions');
      final data = unwrapData(response) as List;
      return data.map((e) => Occasion.fromJson(e as Map<String, dynamic>)).toList();
    });
  }

  Future<Occasion> show(String id) {
    return guarded(() async {
      final response = await _dio.get('/occasions/$id');
      return Occasion.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }

  Future<Occasion> create({
    required String title,
    required OccasionType type,
    String? description,
    DateTime? primaryDate,
    String? timezone,
    String? location,
    OccasionVisibility? visibility,
  }) {
    return guarded(() async {
      final response = await _dio.post('/occasions', data: {
        'title': title,
        'type': type.value,
        if (description != null && description.isNotEmpty) 'description': description,
        if (primaryDate != null) 'primary_date': primaryDate.toIso8601String().split('T').first,
        if (timezone != null && timezone.isNotEmpty) 'timezone': timezone,
        if (location != null && location.isNotEmpty) 'location': location,
        if (visibility != null) 'visibility': visibility.value,
      });
      return Occasion.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }

  Future<Occasion> update(
    String id, {
    String? title,
    String? description,
    DateTime? primaryDate,
    String? location,
    OccasionVisibility? visibility,
  }) {
    return guarded(() async {
      final response = await _dio.patch('/occasions/$id', data: {
        if (title != null) 'title': title,
        if (description != null) 'description': description,
        if (primaryDate != null) 'primary_date': primaryDate.toIso8601String().split('T').first,
        if (location != null) 'location': location,
        if (visibility != null) 'visibility': visibility.value,
      });
      return Occasion.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }

  Future<Occasion> archive(String id) {
    return guarded(() async {
      final response = await _dio.post('/occasions/$id/archive');
      return Occasion.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }

  Future<Occasion> cancel(String id) {
    return guarded(() async {
      final response = await _dio.post('/occasions/$id/cancel');
      return Occasion.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }
}
