import 'package:flutter_test/flutter_test.dart';
import 'package:pamora/features/people/models/invitation.dart';
import 'package:pamora/features/people/models/members_overview.dart';
import 'package:pamora/features/people/models/occasion_member.dart';
import 'package:pamora/features/people/models/responsibility.dart';
import 'package:pamora/features/people/models/role.dart';

void main() {
  group('OccasionMember.fromJson', () {
    test('parses a full member matching OccasionMemberResource', () {
      final member = OccasionMember.fromJson({
        'id': 'member-uuid',
        'user': {'id': 7, 'name': 'Amina', 'email': 'amina@example.com'},
        'role': 'treasurer',
        'status': 'active',
        'responsibilities': ['treasurer', 'catering_lead'],
        'rsvp_status': 'attending',
        'guest_count': 2,
        'rsvp_message': 'Looking forward to it!',
        'joined_at': '2026-07-01T10:00:00Z',
      });

      expect(member.id, 'member-uuid');
      expect(member.user.name, 'Amina');
      expect(member.role, Role.treasurer);
      expect(member.status, OccasionMemberStatus.active);
      expect(member.responsibilities, [Responsibility.treasurer, Responsibility.cateringLead]);
      expect(member.rsvpStatus, RsvpStatus.attending);
      expect(member.guestCount, 2);
    });

    test('parses a member who has not yet responded to RSVP', () {
      final member = OccasionMember.fromJson({
        'id': 'member-uuid',
        'user': {'id': 7, 'name': 'Amina', 'email': 'amina@example.com'},
        'role': 'guest',
        'status': 'active',
        'responsibilities': [],
        'rsvp_status': null,
        'guest_count': null,
        'rsvp_message': null,
        'joined_at': '2026-07-01T10:00:00Z',
      });

      expect(member.rsvpStatus, isNull);
      expect(member.responsibilities, isEmpty);
    });
  });

  group('Invitation.fromJson', () {
    test('parses without expecting a token field, matching InvitationResource', () {
      final invitation = Invitation.fromJson({
        'id': 'invitation-uuid',
        'email': 'pending@example.com',
        'role': 'member',
        'status': 'pending',
        'notes': null,
        'invited_by': {'id': 1, 'name': 'Host Name'},
        'expires_at': '2026-07-08T10:00:00Z',
      });

      expect(invitation.email, 'pending@example.com');
      expect(invitation.role, Role.member);
      expect(invitation.status, InvitationStatus.pending);
      expect(invitation.invitedBy.name, 'Host Name');
    });
  });

  group('MembersOverview.fromJson', () {
    test('parses members, pending_invitations, and permissions matching MemberController::index()', () {
      final overview = MembersOverview.fromJson({
        'members': [
          {
            'id': 'm1',
            'user': {'id': 1, 'name': 'Host', 'email': 'host@example.com'},
            'role': 'host',
            'status': 'active',
            'responsibilities': [],
            'rsvp_status': null,
            'guest_count': null,
            'rsvp_message': null,
            'joined_at': '2026-07-01T10:00:00Z',
          },
        ],
        'pending_invitations': [
          {
            'id': 'i1',
            'email': 'x@example.com',
            'role': 'guest',
            'status': 'pending',
            'notes': null,
            'invited_by': {'id': 1, 'name': 'Host'},
            'expires_at': '2026-07-08T10:00:00Z',
          },
        ],
        'permissions': {
          'can_invite': true,
          'can_remove_member': true,
          'can_assign_responsibilities': false,
          'can_manage_permissions': false,
          'can_transfer_ownership': true,
          'can_reopen_rsvp': true,
        },
      });

      expect(overview.members, hasLength(1));
      expect(overview.pendingInvitations, hasLength(1));
      expect(overview.permissions.canInvite, isTrue);
      expect(overview.permissions.canAssignResponsibilities, isFalse);
    });
  });
}
