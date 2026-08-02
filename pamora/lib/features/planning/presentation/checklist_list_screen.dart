import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/app_routes.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/pamora_card.dart';
import '../../../core/widgets/skeleton_loader.dart';
import '../application/planning_provider.dart';

class ChecklistListScreen extends ConsumerWidget {
  const ChecklistListScreen({super.key, required this.occasionId});

  final String occasionId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final checklistsAsync = ref.watch(checklistsProvider(occasionId));

    return Scaffold(
      appBar: AppBar(title: const Text('Checklists')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push(AppRoutes.checklistCreate(occasionId)),
        icon: const Icon(Icons.add),
        label: const Text('Add Checklist'),
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(checklistsProvider(occasionId).future),
        child: checklistsAsync.when(
          loading: () => const Padding(padding: EdgeInsets.all(PamoraSpacing.md), child: SkeletonList()),
          error: (_, __) => Center(
            child: EmptyState(
              icon: Icons.wifi_off_outlined,
              title: 'Couldn\'t load checklists',
              message: 'Check your connection and try again.',
              actionLabel: 'Retry',
              onAction: () => ref.invalidate(checklistsProvider(occasionId)),
            ),
          ),
          data: (checklists) {
            if (checklists.isEmpty) {
              return ListView(
                padding: const EdgeInsets.only(top: PamoraSpacing.giant),
                children: [
                  EmptyState(
                    icon: Icons.checklist_outlined,
                    title: 'No checklists yet',
                    message: 'Group related to-dos into a checklist.',
                    actionLabel: 'Add Checklist',
                    onAction: () => context.push(AppRoutes.checklistCreate(occasionId)),
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
              itemCount: checklists.length,
              separatorBuilder: (_, __) => const SizedBox(height: PamoraSpacing.sm),
              itemBuilder: (context, i) => PamoraCard(
                child: Row(
                  children: [
                    const Icon(Icons.checklist_outlined),
                    const SizedBox(width: PamoraSpacing.sm),
                    Expanded(child: Text(checklists[i].name, style: Theme.of(context).textTheme.bodyLarge)),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}
