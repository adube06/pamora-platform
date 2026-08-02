import 'responsibility.dart';
import 'role.dart';

enum OccasionMemberStatus {
  active('active', 'Active'),
  completed('completed', 'Completed');

  const OccasionMemberStatus(this.value, this.label);
  final String value;
  final String label;

  static OccasionMemberStatus fromValue(String value) =>
      OccasionMemberStatus.values.firstWhere((e) => e.value == value, orElse: () => OccasionMemberStatus.active);
}

enum RsvpStatus {
  attending('attending', 'Attending'),
  notAttending('not_attending', 'Not Attending'),
  maybe('maybe', 'Maybe');

  const RsvpStatus(this.value, this.label);
  final String value;
  final String label;

  static RsvpStatus? fromValue(String? value) {
    if (value == null) return null;
    for (final s in RsvpStatus.values) {
      if (s.value == value) return s;
    }
    return null;
  }
}

class MemberUser {
  const MemberUser({required this.id, required this.name, required this.email});

  final int id;
  final String name;
  final String email;

  factory MemberUser.fromJson(Map<String, dynamic> json) {
    return MemberUser(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
    );
  }
}

class OccasionMember {
  const OccasionMember({
    required this.id,
    required this.user,
    required this.role,
    required this.status,
    required this.responsibilities,
    required this.joinedAt,
    this.rsvpStatus,
    this.guestCount,
    this.rsvpMessage,
  });

  final String id;
  final MemberUser user;
  final Role role;
  final OccasionMemberStatus status;
  final List<Responsibility> responsibilities;
  final DateTime joinedAt;
  final RsvpStatus? rsvpStatus;
  final int? guestCount;
  final String? rsvpMessage;

  factory OccasionMember.fromJson(Map<String, dynamic> json) {
    return OccasionMember(
      id: json['id'].toString(),
      user: MemberUser.fromJson(json['user'] as Map<String, dynamic>),
      role: Role.fromValue(json['role'] as String? ?? 'guest'),
      status: OccasionMemberStatus.fromValue(json['status'] as String? ?? 'active'),
      responsibilities: (json['responsibilities'] as List? ?? [])
          .map((r) => Responsibility.fromValue(r as String))
          .whereType<Responsibility>()
          .toList(),
      joinedAt: DateTime.tryParse(json['joined_at'] as String? ?? '') ?? DateTime.now(),
      rsvpStatus: RsvpStatus.fromValue(json['rsvp_status'] as String?),
      guestCount: json['guest_count'] as int?,
      rsvpMessage: json['rsvp_message'] as String?,
    );
  }
}
