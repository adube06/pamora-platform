import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/app_routes.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/pamora_card.dart';
import '../../../core/widgets/skeleton_loader.dart';
import '../application/planning_provider.dart';

class TimelineScreen extends ConsumerWidget {
  const TimelineScreen({super.key, required this.occasionId});

  final String occasionId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final eventsAsync = ref.watch(timelineEventsProvider(occasionId));

    return Scaffold(
      appBar: AppBar(title: const Text('Timeline')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push(AppRoutes.timelineEventCreate(occasionId)),
        icon: const Icon(Icons.add),
        label: const Text('Add Event'),
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(timelineEventsProvider(occasionId).future),
        child: eventsAsync.when(
          loading: () => const Padding(padding: EdgeInsets.all(PamoraSpacing.md), child: SkeletonList()),
          error: (_, __) => Center(
            child: EmptyState(
              icon: Icons.wifi_off_outlined,
              title: 'Couldn\'t load the timeline',
              message: 'Check your connection and try again.',
              actionLabel: 'Retry',
              onAction: () => ref.invalidate(timelineEventsProvider(occasionId)),
            ),
          ),
          data: (events) {
            if (events.isEmpty) {
              return ListView(
                padding: const EdgeInsets.only(top: PamoraSpacing.giant),
                children: [
                  EmptyState(
                    icon: Icons.schedule_outlined,
                    title: 'No timeline events yet',
                    message: 'Add key moments so the day runs on schedule.',
                    actionLabel: 'Add Event',
                    onAction: () => context.push(AppRoutes.timelineEventCreate(occasionId)),
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
              itemCount: events.length,
              separatorBuilder: (_, __) => const SizedBox(height: PamoraSpacing.sm),
              itemBuilder: (context, i) {
                final e = events[i];
                return PamoraCard(
                  child: Row(
                    children: [
                      const Icon(Icons.schedule_outlined),
                      const SizedBox(width: PamoraSpacing.sm),
                      Expanded(child: Text(e.name, style: Theme.of(context).textTheme.bodyLarge)),
                      Text(formatDateTime(e.scheduledAt), style: Theme.of(context).textTheme.bodySmall),
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
