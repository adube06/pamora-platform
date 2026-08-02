import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/planning_repository.dart';
import '../models/checklist.dart';
import '../models/milestone.dart';
import '../models/task.dart';
import '../models/timeline_event.dart';

final tasksProvider = FutureProvider.autoDispose.family<List<Task>, String>((ref, occasionId) {
  return ref.watch(planningRepositoryProvider).tasks(occasionId);
});

final checklistsProvider = FutureProvider.autoDispose.family<List<Checklist>, String>((ref, occasionId) {
  return ref.watch(planningRepositoryProvider).checklists(occasionId);
});

final milestonesProvider = FutureProvider.autoDispose.family<List<Milestone>, String>((ref, occasionId) {
  return ref.watch(planningRepositoryProvider).milestones(occasionId);
});

final timelineEventsProvider = FutureProvider.autoDispose.family<List<TimelineEvent>, String>((ref, occasionId) {
  return ref.watch(planningRepositoryProvider).timelineEvents(occasionId);
});
