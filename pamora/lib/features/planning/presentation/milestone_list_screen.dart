import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/app_routes.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/pamora_card.dart';
import '../../../core/widgets/skeleton_loader.dart';
import '../application/planning_provider.dart';

class MilestoneListScreen extends ConsumerWidget {
  const MilestoneListScreen({super.key, required this.occasionId});

  final String occasionId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final milestonesAsync = ref.watch(milestonesProvider(occasionId));

    return Scaffold(
      appBar: AppBar(title: const Text('Milestones')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push(AppRoutes.milestoneCreate(occasionId)),
        icon: const Icon(Icons.add),
        label: const Text('Add Milestone'),
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(milestonesProvider(occasionId).future),
        child: milestonesAsync.when(
          loading: () => const Padding(padding: EdgeInsets.all(PamoraSpacing.md), child: SkeletonList()),
          error: (_, __) => Center(
            child: EmptyState(
              icon: Icons.wifi_off_outlined,
              title: 'Couldn\'t load milestones',
              message: 'Check your connection and try again.',
              actionLabel: 'Retry',
              onAction: () => ref.invalidate(milestonesProvider(occasionId)),
            ),
          ),
          data: (milestones) {
            if (milestones.isEmpty) {
              return ListView(
                padding: const EdgeInsets.only(top: PamoraSpacing.giant),
                children: [
                  EmptyState(
                    icon: Icons.flag_outlined,
                    title: 'No milestones yet',
                    message: 'Mark the big moments worth tracking toward.',
                    actionLabel: 'Add Milestone',
                    onAction: () => context.push(AppRoutes.milestoneCreate(occasionId)),
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
              itemCount: milestones.length,
              separatorBuilder: (_, __) => const SizedBox(height: PamoraSpacing.sm),
              itemBuilder: (context, i) {
                final m = milestones[i];
                return PamoraCard(
                  child: Row(
                    children: [
                      Icon(
                        m.isAchieved ? Icons.flag : Icons.outlined_flag,
                        color: m.isAchieved ? Theme.of(context).colorScheme.primary : null,
                      ),
                      const SizedBox(width: PamoraSpacing.sm),
                      Expanded(child: Text(m.name, style: Theme.of(context).textTheme.bodyLarge)),
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
