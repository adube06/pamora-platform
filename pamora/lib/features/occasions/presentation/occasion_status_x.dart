import '../../../core/widgets/status_badge.dart';
import '../models/occasion.dart';

extension OccasionStatusTone on OccasionStatus {
  BadgeTone get tone {
    switch (this) {
      case OccasionStatus.draft:
        return BadgeTone.neutral;
      case OccasionStatus.planning:
        return BadgeTone.info;
      case OccasionStatus.active:
        return BadgeTone.primary;
      case OccasionStatus.completed:
        return BadgeTone.success;
      case OccasionStatus.archived:
        return BadgeTone.neutral;
      case OccasionStatus.cancelled:
        return BadgeTone.error;
    }
  }
}

extension OccasionTypeIcon on OccasionType {
  String get emoji {
    switch (this) {
      case OccasionType.wedding:
        return '💍';
      case OccasionType.funeral:
        return '🕊️';
      case OccasionType.birthday:
        return '🎂';
    }
  }
}
