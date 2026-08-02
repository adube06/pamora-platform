import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import '../models/members_overview.dart';
import '../models/occasion_member.dart';
import '../models/role.dart';

final peopleRepositoryProvider = Provider<PeopleRepository>((ref) {
  return PeopleRepository(ref.read(dioProvider));
});

class PeopleRepository {
  PeopleRepository(this._dio);

  final Dio _dio;

  Future<MembersOverview> members(String occasionId) {
    return guarded(() async {
      final response = await _dio.get('/occasions/$occasionId/members');
      return MembersOverview.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }

  Future<void> inviteMember(
    String occasionId, {
    required String email,
    required Role role,
    String? notes,
  }) {
    return guarded(() => _dio.post('/occasions/$occasionId/invitations', data: {
          'email': email,
          'role': role.value,
          if (notes != null && notes.isNotEmpty) 'notes': notes,
        }));
  }

  Future<void> removeMember(String memberId) {
    return guarded(() => _dio.delete('/occasion-members/$memberId'));
  }

  Future<OccasionMember> submitRsvp(
    String occasionId, {
    required RsvpStatus status,
    int? guestCount,
    String? message,
  }) {
    return guarded(() async {
      final response = await _dio.post('/occasions/$occasionId/rsvp', data: {
        'rsvp_status': status.value,
        if (guestCount != null) 'guest_count': guestCount,
        if (message != null && message.isNotEmpty) 'rsvp_message': message,
      });
      return OccasionMember.fromJson(unwrapData(response) as Map<String, dynamic>);
    });
  }
}
