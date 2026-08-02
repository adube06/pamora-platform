import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/app_routes.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/pamora_card.dart';
import '../../../core/widgets/primary_button.dart';
import '../../../core/widgets/section_header.dart';
import '../../../core/widgets/skeleton_loader.dart';
import '../../../core/widgets/status_badge.dart';
import '../application/planning_provider.dart';
import 'task_status_x.dart';

/// The Planning tab's landing view: a preview of Tasks/Milestones/Timeline/
/// Checklists, each linking out to its own full list + single add action.
class PlanningTab extends ConsumerWidget {
  const PlanningTab({super.key, required this.occasionId});

  final String occasionId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final tasksAsync = ref.watch(tasksProvider(occasionId));
    final milestonesAsync = ref.watch(milestonesProvider(occasionId));
    final timelineAsync = ref.watch(timelineEventsProvider(occasionId));

    return ListView(
      padding: const EdgeInsets.fromLTRB(PamoraSpacing.md, PamoraSpacing.md, PamoraSpacing.md, PamoraSpacing.huge),
      children: [
        PrimaryButton(
          label: 'Add Task',
          icon: Icons.add_task,
          onPressed: () => context.push(AppRoutes.taskCreate(occasionId)),
        ),
        const SizedBox(height: PamoraSpacing.xl),
        SectionHeader(
          title: 'Tasks',
          actionLabel: 'View all',
          onAction: () => context.push(AppRoutes.planningTasks(occasionId)),
        ),
        tasksAsync.when(
          loading: () => const SkeletonList(itemCount: 2, itemHeight: 56),
          error: (_, __) => const _InlineError(),
          data: (tasks) {
            if (tasks.isEmpty) {
              return const _EmptyHint(text: 'No tasks yet — add one to get planning moving.');
            }
            return Column(
              children: tasks
                  .take(3)
                  .map((t) => Padding(
                        padding: const EdgeInsets.only(bottom: PamoraSpacing.sm),
                        child: PamoraCard(
                          child: Row(
                            children: [
                              Expanded(
                                child: Text(
                                  t.title,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: Theme.of(context).textTheme.bodyLarge,
                                ),
                              ),
                              const SizedBox(width: PamoraSpacing.sm),
                              StatusBadge(label: t.status.label, tone: t.status.tone),
                            ],
                          ),
                        ),
                      ))
                  .toList(),
            );
          },
        ),
        const SizedBox(height: PamoraSpacing.xl),
        SectionHeader(
          title: 'Milestones',
          actionLabel: 'View all',
          onAction: () => context.push(AppRoutes.planningMilestones(occasionId)),
        ),
        milestonesAsync.when(
          loading: () => const SkeletonList(itemCount: 2, itemHeight: 56),
          error: (_, __) => const _InlineError(),
          data: (milestones) {
            if (milestones.isEmpty) {
              return const _EmptyHint(text: 'No milestones yet.');
            }
            return Column(
              children: milestones
                  .take(3)
                  .map((m) => Padding(
                        padding: const EdgeInsets.only(bottom: PamoraSpacing.sm),
                        child: PamoraCard(
                          child: Row(
                            children: [
                              Icon(
                                m.isAchieved ? Icons.flag : Icons.outlined_flag,
                                size: 18,
                                color: m.isAchieved
                                    ? Theme.of(context).colorScheme.primary
                                    : Theme.of(context).colorScheme.onSurfaceVariant,
                              ),
                              const SizedBox(width: PamoraSpacing.sm),
                              Expanded(
                                child: Text(m.name, maxLines: 1, overflow: TextOverflow.ellipsis),
                              ),
                            ],
                          ),
                        ),
                      ))
                  .toList(),
            );
          },
        ),
        const SizedBox(height: PamoraSpacing.xl),
        SectionHeader(
          title: 'Timeline',
          actionLabel: 'View all',
          onAction: () => context.push(AppRoutes.planningTimeline(occasionId)),
        ),
        timelineAsync.when(
          loading: () => const SkeletonList(itemCount: 2, itemHeight: 56),
          error: (_, __) => const _InlineError(),
          data: (events) {
            if (events.isEmpty) {
              return const _EmptyHint(text: 'No timeline events yet.');
            }
            return Column(
              children: events
                  .take(3)
                  .map((e) => Padding(
                        padding: const EdgeInsets.only(bottom: PamoraSpacing.sm),
                        child: PamoraCard(
                          child: Row(
                            children: [
                              Expanded(
                                child: Text(e.name, maxLines: 1, overflow: TextOverflow.ellipsis),
                              ),
                              Text(
                                formatDate(e.scheduledAt),
                                style: Theme.of(context).textTheme.bodySmall,
                              ),
                            ],
                          ),
                        ),
                      ))
                  .toList(),
            );
          },
        ),
        const SizedBox(height: PamoraSpacing.xl),
        SectionHeader(
          title: 'Checklists',
          actionLabel: 'View all',
          onAction: () => context.push(AppRoutes.planningChecklists(occasionId)),
        ),
        const _EmptyHint(text: 'Group related to-dos into a checklist.'),
      ],
    );
  }
}

class _EmptyHint extends StatelessWidget {
  const _EmptyHint({required this.text});

  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: PamoraSpacing.sm),
      child: Text(
        text,
        style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Theme.of(context).colorScheme.onSurfaceVariant),
      ),
    );
  }
}

class _InlineError extends StatelessWidget {
  const _InlineError();

  @override
  Widget build(BuildContext context) {
    return const EmptyState(icon: Icons.wifi_off_outlined, title: '', message: 'Couldn\'t load this section.');
  }
}
