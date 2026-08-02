import '../../../core/widgets/status_badge.dart';
import '../models/task.dart';

extension TaskStatusTone on TaskStatus {
  BadgeTone get tone {
    switch (this) {
      case TaskStatus.draft:
        return BadgeTone.neutral;
      case TaskStatus.open:
        return BadgeTone.info;
      case TaskStatus.inProgress:
        return BadgeTone.primary;
      case TaskStatus.completed:
        return BadgeTone.success;
      case TaskStatus.cancelled:
        return BadgeTone.error;
      case TaskStatus.deferred:
        return BadgeTone.warning;
    }
  }
}

extension TaskPriorityTone on TaskPriority {
  BadgeTone get tone {
    switch (this) {
      case TaskPriority.critical:
        return BadgeTone.error;
      case TaskPriority.high:
        return BadgeTone.warning;
      case TaskPriority.medium:
        return BadgeTone.info;
      case TaskPriority.low:
        return BadgeTone.neutral;
    }
  }
}
