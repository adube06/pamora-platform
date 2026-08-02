import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/app_routes.dart';
import '../../../core/theme/colors.dart';
import '../../../core/theme/radius.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/pamora_card.dart';
import '../../../core/widgets/primary_button.dart';
import '../../../core/widgets/section_header.dart';
import '../../../core/widgets/skeleton_loader.dart';
import '../../auth/application/auth_provider.dart';
import '../../notifications/application/notifications_provider.dart';
import '../../occasions/application/occasions_provider.dart';
import '../../occasions/presentation/occasion_status_x.dart';
import '../application/home_provider.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final userAsync = ref.watch(authProvider);
    final occasionsAsync = ref.watch(occasionsListProvider);
    final unreadCount = ref.watch(unreadNotificationsCountProvider);
    final textTheme = Theme.of(context).textTheme;

    final firstName = userAsync.value?.name.split(' ').first ?? 'there';

    return Scaffold(
      appBar: AppBar(
        title: const Text('Pamora'),
        actions: [
          IconButton(
            onPressed: () => context.go(AppRoutes.notifications),
            icon: Badge(
              isLabelVisible: unreadCount > 0,
              label: Text('$unreadCount'),
              child: const Icon(Icons.notifications_outlined),
            ),
          ),
          const SizedBox(width: PamoraSpacing.xs),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(occasionsListProvider);
          ref.invalidate(upcomingTasksProvider);
        },
        child: ListView(
          padding: const EdgeInsets.fromLTRB(PamoraSpacing.md, PamoraSpacing.md, PamoraSpacing.md, PamoraSpacing.huge),
          children: [
            Text('Hi, $firstName 👋', style: textTheme.headlineMedium),
            const SizedBox(height: PamoraSpacing.xs),
            Text(
              'Here\'s what\'s happening across your occasions.',
              style: textTheme.bodyMedium?.copyWith(color: Theme.of(context).colorScheme.onSurfaceVariant),
            ),
            const SizedBox(height: PamoraSpacing.xl),
            occasionsAsync.when(
              loading: () => const SkeletonList(itemCount: 2, itemHeight: 84),
              error: (_, __) => const SizedBox.shrink(),
              data: (occasions) {
                if (occasions.isEmpty) {
                  return DecoratedBox(
                    decoration:
                        BoxDecoration(gradient: PamoraColors.heroGradient, borderRadius: PamoraRadius.xlargeRadius),
                    child: Padding(
                      padding: const EdgeInsets.all(PamoraSpacing.lg),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Plan your first occasion',
                            style: textTheme.headlineSmall?.copyWith(color: Colors.white),
                          ),
                          const SizedBox(height: PamoraSpacing.xs),
                          Text(
                            'Weddings, birthdays, funerals — start planning in minutes.',
                            style: textTheme.bodyMedium?.copyWith(color: Colors.white.withValues(alpha: 0.9)),
                          ),
                          const SizedBox(height: PamoraSpacing.md),
                          PrimaryButton(
                            label: 'Create Occasion',
                            fullWidth: false,
                            onPressed: () => context.push(AppRoutes.occasionCreate),
                          ),
                        ],
                      ),
                    ),
                  );
                }

                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SectionHeader(
                      title: 'Your Occasions',
                      actionLabel: 'View all',
                      onAction: () => context.go(AppRoutes.occasions),
                    ),
                    ...occasions.take(3).map((o) => Padding(
                          padding: const EdgeInsets.only(bottom: PamoraSpacing.sm),
                          child: PamoraCard(
                            onTap: () => context.push(AppRoutes.occasionDetail(o.slug)),
                            child: Row(
                              children: [
                                Text(o.type.emoji, style: const TextStyle(fontSize: 24)),
                                const SizedBox(width: PamoraSpacing.sm),
                                Expanded(
                                  child: Text(o.title, maxLines: 1, overflow: TextOverflow.ellipsis),
                                ),
                                Text(formatDate(o.primaryDate), style: textTheme.bodySmall),
                              ],
                            ),
                          ),
                        )),
                  ],
                );
              },
            ),
            const SizedBox(height: PamoraSpacing.xl),
            SectionHeader(title: 'Upcoming Tasks'),
            Consumer(
              builder: (context, ref, _) {
                final tasksAsync = ref.watch(upcomingTasksProvider);
                return tasksAsync.when(
                  loading: () => const SkeletonList(itemCount: 2, itemHeight: 60),
                  error: (_, __) => const SizedBox.shrink(),
                  data: (tasks) {
                    if (tasks.isEmpty) {
                      return const EmptyState(
                        icon: Icons.task_alt_outlined,
                        title: '',
                        message: 'No upcoming tasks. Enjoy the calm.',
                      );
                    }
                    return Column(
                      children: tasks
                          .map((t) => Padding(
                                padding: const EdgeInsets.only(bottom: PamoraSpacing.sm),
                                child: PamoraCard(
                                  onTap: () => context.push(AppRoutes.occasionDetail(t.occasionId)),
                                  child: Row(
                                    children: [
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(t.task.title, maxLines: 1, overflow: TextOverflow.ellipsis),
                                            Text(
                                              t.occasionTitle,
                                              style: textTheme.bodySmall,
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                          ],
                                        ),
                                      ),
                                      if (t.task.dueDate != null)
                                        Text(formatDate(t.task.dueDate), style: textTheme.bodySmall),
                                    ],
                                  ),
                                ),
                              ))
                          .toList(),
                    );
                  },
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}
