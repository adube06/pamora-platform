import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/app_routes.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/pamora_card.dart';
import '../../../core/widgets/skeleton_loader.dart';
import '../../../core/widgets/status_badge.dart';
import '../application/planning_provider.dart';
import '../data/planning_repository.dart';
import '../models/task.dart';
import 'task_status_x.dart';

class TaskListScreen extends ConsumerWidget {
  const TaskListScreen({super.key, required this.occasionId});

  final String occasionId;

  Future<void> _toggle(WidgetRef ref, Task task) async {
    final repo = ref.read(planningRepositoryProvider);
    if (task.status == TaskStatus.completed) {
      await repo.reopenTask(task.id);
    } else {
      await repo.completeTask(task.id);
    }
    ref.invalidate(tasksProvider(occasionId));
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final tasksAsync = ref.watch(tasksProvider(occasionId));

    return Scaffold(
      appBar: AppBar(title: const Text('Tasks')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push(AppRoutes.taskCreate(occasionId)),
        icon: const Icon(Icons.add),
        label: const Text('Add Task'),
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(tasksProvider(occasionId).future),
        child: tasksAsync.when(
          loading: () => const Padding(padding: EdgeInsets.all(PamoraSpacing.md), child: SkeletonList()),
          error: (_, __) => Center(
            child: EmptyState(
              icon: Icons.wifi_off_outlined,
              title: 'Couldn\'t load tasks',
              message: 'Check your connection and try again.',
              actionLabel: 'Retry',
              onAction: () => ref.invalidate(tasksProvider(occasionId)),
            ),
          ),
          data: (tasks) {
            if (tasks.isEmpty) {
              return ListView(
                padding: const EdgeInsets.only(top: PamoraSpacing.giant),
                children: [
                  EmptyState(
                    icon: Icons.add_task,
                    title: 'No tasks yet',
                    message: 'Break the plan into tasks your committee can act on.',
                    actionLabel: 'Add Task',
                    onAction: () => context.push(AppRoutes.taskCreate(occasionId)),
                  ),
                ],
              );
            }

            return ListView.separated(
              padding: const EdgeInsets.fromLTRB(
                PamoraSpacing.md,
                PamoraSpacing.md,
                PamoraSpacing.md,
                PamoraSpacing.huge,
              ),
              itemCount: tasks.length,
              separatorBuilder: (_, __) => const SizedBox(height: PamoraSpacing.sm),
              itemBuilder: (context, i) {
                final task = tasks[i];
                final done = task.status == TaskStatus.completed;
                return PamoraCard(
                  child: Row(
                    children: [
                      IconButton(
                        onPressed: () => _toggle(ref, task),
                        icon: Icon(
                          done ? Icons.check_circle : Icons.radio_button_unchecked,
                          color: done ? Theme.of(context).colorScheme.primary : null,
                        ),
                      ),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              task.title,
                              style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                                    decoration: done ? TextDecoration.lineThrough : null,
                                  ),
                            ),
                            if (task.dueDate != null)
                              Text('Due ${formatDate(task.dueDate)}', style: Theme.of(context).textTheme.bodySmall),
                          ],
                        ),
                      ),
                      StatusBadge(label: task.priority.label, tone: task.priority.tone),
                    ],
                  ),
                );
              },
            );
          },
        ),
      ),
    );
  }
}
