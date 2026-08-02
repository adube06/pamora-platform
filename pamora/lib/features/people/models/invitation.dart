import 'role.dart';

enum InvitationStatus {
  pending('pending', 'Pending'),
  accepted('accepted', 'Accepted'),
  declined('declined', 'Declined'),
  expired('expired', 'Expired'),
  revoked('revoked', 'Revoked');

  const InvitationStatus(this.value, this.label);
  final String value;
  final String label;

  static InvitationStatus fromValue(String value) =>
      InvitationStatus.values.firstWhere((e) => e.value == value, orElse: () => InvitationStatus.pending);
}

class InvitedBy {
  const InvitedBy({required this.id, required this.name});

  final int id;
  final String name;

  factory InvitedBy.fromJson(Map<String, dynamic> json) {
    return InvitedBy(id: json['id'] as int, name: json['name'] as String? ?? '');
  }
}

/// Never carries the invitation token — the API deliberately omits it from
/// this list shape (InvitationResource), since it's a bearer credential
/// only ever returned directly to the inviter at creation time.
class Invitation {
  const Invitation({
    required this.id,
    required this.email,
    required this.role,
    required this.status,
    required this.invitedBy,
    this.notes,
    this.expiresAt,
  });

  final String id;
  final String email;
  final Role role;
  final InvitationStatus status;
  final InvitedBy invitedBy;
  final String? notes;
  final DateTime? expiresAt;

  factory Invitation.fromJson(Map<String, dynamic> json) {
    return Invitation(
      id: json['id'].toString(),
      email: json['email'] as String? ?? '',
      role: Role.fromValue(json['role'] as String? ?? 'guest'),
      status: InvitationStatus.fromValue(json['status'] as String? ?? 'pending'),
      invitedBy: InvitedBy.fromJson(json['invited_by'] as Map<String, dynamic>),
      notes: json['notes'] as String?,
      expiresAt: json['expires_at'] != null ? DateTime.tryParse(json['expires_at'] as String) : null,
    );
  }
}
