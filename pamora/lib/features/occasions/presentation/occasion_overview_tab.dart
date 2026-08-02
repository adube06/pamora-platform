import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/spacing.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/widgets/pamora_card.dart';
import '../../../core/widgets/secondary_button.dart';
import '../../../core/widgets/status_badge.dart';
import '../application/occasions_provider.dart';
import '../data/occasions_repository.dart';
import '../models/occasion.dart';
import 'occasion_status_x.dart';

class OccasionOverviewTab extends ConsumerWidget {
  const OccasionOverviewTab({super.key, required this.occasion});

  final Occasion occasion;

  Future<void> _confirmAndRun(
    BuildContext context,
    WidgetRef ref, {
    required String title,
    required String message,
    required Future<void> Function() action,
  }) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title),
        content: Text(message),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text('Confirm', style: TextStyle(color: Theme.of(context).colorScheme.error)),
          ),
        ],
      ),
    );
    if (confirmed != true) return;
    await action();
    ref.invalidate(occasionDetailProvider(occasion.slug));
    ref.invalidate(occasionsListProvider);
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final textTheme = Theme.of(context).textTheme;
    final repo = ref.read(occasionsRepositoryProvider);
    final canAct = occasion.status != OccasionStatus.archived && occasion.status != OccasionStatus.cancelled;

    return ListView(
      padding: const EdgeInsets.all(PamoraSpacing.md),
      children: [
        PamoraCard(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(occasion.type.label, style: textTheme.bodyMedium),
                  StatusBadge(label: occasion.status.label, tone: occasion.status.tone),
                ],
              ),
              const SizedBox(height: PamoraSpacing.md),
              _InfoRow(icon: Icons.event_outlined, label: 'Date', value: formatDate(occasion.primaryDate)),
              _InfoRow(icon: Icons.place_outlined, label: 'Location', value: occasion.location ?? '—'),
              _InfoRow(icon: Icons.visibility_outlined, label: 'Visibility', value: occasion.visibility.label),
            ],
          ),
        ),
        if (occasion.description != null && occasion.description!.isNotEmpty) ...[
          const SizedBox(height: PamoraSpacing.md),
          PamoraCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('About', style: textTheme.titleLarge),
                const SizedBox(height: PamoraSpacing.xs),
                Text(occasion.description!, style: textTheme.bodyMedium),
              ],
            ),
          ),
        ],
        if (canAct) ...[
          const SizedBox(height: PamoraSpacing.xl),
          SecondaryButton(
            label: 'Edit Occasion',
            icon: Icons.edit_outlined,
            onPressed: () => context.push('/occasions/${occasion.slug}/edit'),
          ),
          const SizedBox(height: PamoraSpacing.sm),
          SecondaryButton(
            label: 'Archive Occasion',
            icon: Icons.archive_outlined,
            onPressed: () => _confirmAndRun(
              context,
              ref,
              title: 'Archive this occasion?',
              message: 'You can\'t undo this from the app.',
              action: () => repo.archive(occasion.slug),
            ),
          ),
          const SizedBox(height: PamoraSpacing.sm),
          SecondaryButton(
            label: 'Cancel Occasion',
            icon: Icons.cancel_outlined,
            destructive: true,
            onPressed: () => _confirmAndRun(
              context,
              ref,
              title: 'Cancel this occasion?',
              message: 'Everyone involved will see it as cancelled.',
              action: () => repo.cancel(occasion.slug),
            ),
          ),
        ],
      ],
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({required this.icon, required this.label, required this.value});

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        children: [
          Icon(icon, size: 18, color: scheme.onSurfaceVariant),
          const SizedBox(width: PamoraSpacing.sm),
          Text(label, style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: scheme.onSurfaceVariant)),
          const Spacer(),
          Text(value, style: Theme.of(context).textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}
