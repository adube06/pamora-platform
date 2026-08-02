import '../../../core/widgets/status_badge.dart';
import '../models/invitation.dart';
import '../models/occasion_member.dart';

extension RsvpStatusTone on RsvpStatus {
  BadgeTone get tone {
    switch (this) {
      case RsvpStatus.attending:
        return BadgeTone.success;
      case RsvpStatus.notAttending:
        return BadgeTone.error;
      case RsvpStatus.maybe:
        return BadgeTone.warning;
    }
  }
}

extension InvitationStatusTone on InvitationStatus {
  BadgeTone get tone {
    switch (this) {
      case InvitationStatus.pending:
        return BadgeTone.warning;
      case InvitationStatus.accepted:
        return BadgeTone.success;
      case InvitationStatus.declined:
      case InvitationStatus.revoked:
        return BadgeTone.error;
      case InvitationStatus.expired:
        return BadgeTone.neutral;
    }
  }
}
