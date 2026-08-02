import 'invitation.dart';
import 'occasion_member.dart';

class MemberPermissions {
  const MemberPermissions({
    required this.canInvite,
    required this.canRemoveMember,
    required this.canAssignResponsibilities,
    required this.canManagePermissions,
    required this.canTransferOwnership,
    required this.canReopenRsvp,
  });

  final bool canInvite;
  final bool canRemoveMember;
  final bool canAssignResponsibilities;
  final bool canManagePermissions;
  final bool canTransferOwnership;
  final bool canReopenRsvp;

  factory MemberPermissions.fromJson(Map<String, dynamic> json) {
    return MemberPermissions(
      canInvite: json['can_invite'] == true,
      canRemoveMember: json['can_remove_member'] == true,
      canAssignResponsibilities: json['can_assign_responsibilities'] == true,
      canManagePermissions: json['can_manage_permissions'] == true,
      canTransferOwnership: json['can_transfer_ownership'] == true,
      canReopenRsvp: json['can_reopen_rsvp'] == true,
    );
  }
}

/// Maps GET /occasions/{occasion}/members exactly — members, pending
/// invitations, and the caller's own permission flags for this occasion.
class MembersOverview {
  const MembersOverview({required this.members, required this.pendingInvitations, required this.permissions});

  final List<OccasionMember> members;
  final List<Invitation> pendingInvitations;
  final MemberPermissions permissions;

  factory MembersOverview.fromJson(Map<String, dynamic> json) {
    return MembersOverview(
      members: (json['members'] as List? ?? []).map((m) => OccasionMember.fromJson(m as Map<String, dynamic>)).toList(),
      pendingInvitations: (json['pending_invitations'] as List? ?? [])
          .map((i) => Invitation.fromJson(i as Map<String, dynamic>))
          .toList(),
      permissions: MemberPermissions.fromJson((json['permissions'] as Map?)?.cast<String, dynamic>() ?? {}),
    );
  }
}
