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
import 'pledge_status_x.dart';

class PledgeListScreen extends ConsumerWidget {
  const PledgeListScreen({super.key, required this.occasionId});

  final String occasionId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final pledgesAsync = ref.watch(pledgesProvider(occasionId));

    return Scaffold(
      appBar: AppBar(title: const Text('Pledges')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push(AppRoutes.pledgeCreate(occasionId)),
        icon: const Icon(Icons.add),
        label: const Text('Add Pledge'),
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(pledgesProvider(occasionId).future),
        child: pledgesAsync.when(
          loading: () => const Padding(padding: EdgeInsets.all(PamoraSpacing.md), child: SkeletonList()),
          error: (_, __) => Center(
            child: EmptyState(
              icon: Icons.wifi_off_outlined,
              title: 'Couldn\'t load pledges',
              message: 'Check your connection and try again.',
              actionLabel: 'Retry',
              onAction: () => ref.invalidate(pledgesProvider(occasionId)),
            ),
          ),
          data: (pledges) {
            if (pledges.isEmpty) {
              return ListView(
                padding: const EdgeInsets.only(top: PamoraSpacing.giant),
                children: [
                  EmptyState(
                    icon: Icons.handshake_outlined,
                    title: 'No pledges yet',
                    message: 'Track promised contributions before they\'re received.',
                    actionLabel: 'Add Pledge',
                    onAction: () => context.push(AppRoutes.pledgeCreate(occasionId)),
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
              itemCount: pledges.length,
              separatorBuilder: (_, __) => const SizedBox(height: PamoraSpacing.sm),
              itemBuilder: (context, i) {
                final p = pledges[i];
                return PamoraCard(
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(p.pledgorName, style: Theme.of(context).textTheme.bodyLarge),
                            Text(formatDate(p.pledgedAt), style: Theme.of(context).textTheme.bodySmall),
                          ],
                        ),
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(formatCurrency(p.amount, p.currency), style: Theme.of(context).textTheme.titleLarge),
                          StatusBadge(label: p.status.label, tone: p.status.tone),
                        ],
                      ),
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
