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
import '../application/occasions_provider.dart';
import '../models/occasion.dart';
import 'occasion_status_x.dart';

class OccasionListScreen extends ConsumerWidget {
  const OccasionListScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final occasionsAsync = ref.watch(occasionsListProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Occasions')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push(AppRoutes.occasionCreate),
        icon: const Icon(Icons.add),
        label: const Text('New Occasion'),
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(occasionsListProvider.future),
        child: occasionsAsync.when(
          loading: () => const _LoadingList(),
          error: (error, _) => _ErrorView(onRetry: () => ref.invalidate(occasionsListProvider)),
          data: (occasions) {
            if (occasions.isEmpty) {
              return ListView(
                padding: const EdgeInsets.only(top: PamoraSpacing.giant),
                children: [
                  EmptyState(
                    icon: Icons.celebration_outlined,
                    title: 'No occasions yet',
                    message: 'Create your first Occasion to start planning.',
                    actionLabel: 'Create Occasion',
                    onAction: () => context.push(AppRoutes.occasionCreate),
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
              itemCount: occasions.length,
              separatorBuilder: (_, __) => const SizedBox(height: PamoraSpacing.sm),
              itemBuilder: (context, i) => _OccasionCard(occasion: occasions[i]),
            );
          },
        ),
      ),
    );
  }
}

class _OccasionCard extends StatelessWidget {
  const _OccasionCard({required this.occasion});

  final Occasion occasion;

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return PamoraCard(
      onTap: () => context.push(AppRoutes.occasionDetail(occasion.slug)),
      child: Row(
        children: [
          Text(occasion.type.emoji, style: const TextStyle(fontSize: 28)),
          const SizedBox(width: PamoraSpacing.md),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(occasion.title, style: textTheme.titleLarge, maxLines: 1, overflow: TextOverflow.ellipsis),
                const SizedBox(height: 4),
                Text(
                  occasion.primaryDate != null ? formatDate(occasion.primaryDate) : occasion.type.label,
                  style: textTheme.bodySmall,
                ),
              ],
            ),
          ),
          const SizedBox(width: PamoraSpacing.sm),
          StatusBadge(label: occasion.status.label, tone: occasion.status.tone),
        ],
      ),
    );
  }
}

class _LoadingList extends StatelessWidget {
  const _LoadingList();

  @override
  Widget build(BuildContext context) {
    return const Padding(
      padding: EdgeInsets.all(PamoraSpacing.md),
      child: SkeletonList(itemCount: 4, itemHeight: 76),
    );
  }
}

class _ErrorView extends StatelessWidget {
  const _ErrorView({required this.onRetry});

  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.only(top: PamoraSpacing.giant),
      children: [
        EmptyState(
          icon: Icons.wifi_off_outlined,
          title: 'Couldn\'t load your occasions',
          message: 'Check your connection and try again.',
          actionLabel: 'Retry',
          onAction: onRetry,
        ),
      ],
    );
  }
}
