import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../occasions/application/occasions_provider.dart';
import '../../planning/data/planning_repository.dart';
import '../../planning/models/task.dart';

class UpcomingTask {
  const UpcomingTask({required this.task, required this.occasionTitle, required this.occasionId});
  final Task task;
  final String occasionTitle;
  final String occasionId;
}

/// The Occasion API has no embedded task/budget counts, so Home aggregates
/// via a couple of parallel calls against the most recent active occasions
/// rather than one dashboard endpoint (documented backend gap).
final upcomingTasksProvider = FutureProvider.autoDispose<List<UpcomingTask>>((ref) async {
  final occasions = await ref.watch(occasionsListProvider.future);
  final repo = ref.watch(planningRepositoryProvider);

  final relevant = occasions.take(2).toList();
  final results = <UpcomingTask>[];

  for (final occasion in relevant) {
    final tasks = await repo.tasks(occasion.slug);
    for (final task in tasks) {
      if (task.status != TaskStatus.completed && task.status != TaskStatus.cancelled) {
        results.add(UpcomingTask(task: task, occasionTitle: occasion.title, occasionId: occasion.slug));
      }
    }
  }

  results.sort((a, b) {
    if (a.task.dueDate == null) return 1;
    if (b.task.dueDate == null) return -1;
    return a.task.dueDate!.compareTo(b.task.dueDate!);
  });

  return results.take(5).toList();
});
