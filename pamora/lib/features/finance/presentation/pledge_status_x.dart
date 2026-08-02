import '../../../core/widgets/status_badge.dart';
import '../models/pledge.dart';

extension PledgeStatusTone on PledgeStatus {
  BadgeTone get tone {
    switch (this) {
      case PledgeStatus.pending:
        return BadgeTone.warning;
      case PledgeStatus.confirmed:
        return BadgeTone.success;
      case PledgeStatus.cancelled:
        return BadgeTone.error;
      case PledgeStatus.expired:
        return BadgeTone.neutral;
    }
  }
}
