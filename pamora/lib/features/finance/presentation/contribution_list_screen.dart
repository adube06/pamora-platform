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
import '../application/finance_provider.dart';

class ContributionListScreen extends ConsumerWidget {
  const ContributionListScreen({super.key, required this.occasionId});

  final String occasionId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final contributionsAsync = ref.watch(contributionsProvider(occasionId));

    return Scaffold(
      appBar: AppBar(title: const Text('Contributions')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push(AppRoutes.contributionCreate(occasionId)),
        icon: const Icon(Icons.add),
        label: const Text('Add Contribution'),
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(contributionsProvider(occasionId).future),
        child: contributionsAsync.when(
          loading: () => const Padding(padding: EdgeInsets.all(PamoraSpacing.md), child: SkeletonList()),
          error: (_, __) => Center(
            child: EmptyState(
              icon: Icons.wifi_off_outlined,
              title: 'Couldn\'t load contributions',
              message: 'Check your connection and try again.',
              actionLabel: 'Retry',
              onAction: () => ref.invalidate(contributionsProvider(occasionId)),
            ),
          ),
          data: (result) {
            final (contributions, summary) = result;
            if (contributions.isEmpty) {
              return ListView(
                padding: const EdgeInsets.only(top: PamoraSpacing.giant),
                children: [
                  EmptyState(
                    icon: Icons.volunteer_activism_outlined,
                    title: 'No contributions yet',
                    message: 'Record contributions as they come in.',
                    actionLabel: 'Add Contribution',
                    onAction: () => context.push(AppRoutes.contributionCreate(occasionId)),
                  ),
                ],
              );
            }

            return ListView(
              padding: const EdgeInsets.fromLTRB(
                PamoraSpacing.md,
                PamoraSpacing.md,
                PamoraSpacing.md,
                PamoraSpacing.huge,
              ),
              children: [
                PamoraCard(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Total received', style: Theme.of(context).textTheme.bodyMedium),
                          Text('${summary.count} contribution${summary.count == 1 ? '' : 's'}',
                              style: Theme.of(context).textTheme.bodySmall),
                        ],
                      ),
                      Text(
                        formatCurrency(summary.totalReceived, contributions.first.currency),
                        style: Theme.of(context).textTheme.headlineSmall,
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: PamoraSpacing.md),
                ...contributions.map((c) => Padding(
                      padding: const EdgeInsets.only(bottom: PamoraSpacing.sm),
                      child: PamoraCard(
                        child: Row(
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(c.contributorName, style: Theme.of(context).textTheme.bodyLarge),
                                  Text(formatDate(c.contributedAt), style: Theme.of(context).textTheme.bodySmall),
                                ],
                              ),
                            ),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                Text(formatCurrency(c.amount, c.currency),
                                    style: Theme.of(context).textTheme.titleLarge),
                                StatusBadge(label: c.method.label, tone: BadgeTone.neutral),
                              ],
                            ),
                          ],
                        ),
                      ),
                    )),
              ],
            );
          },
        ),
      ),
    );
  }
}
