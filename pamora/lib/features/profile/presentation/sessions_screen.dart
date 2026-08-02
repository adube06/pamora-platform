import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/spacing.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/pamora_card.dart';
import '../../../core/widgets/skeleton_loader.dart';
import '../../../core/widgets/status_badge.dart';
import '../application/sessions_provider.dart';
import '../data/sessions_repository.dart';

class SessionsScreen extends ConsumerWidget {
  const SessionsScreen({super.key});

  Future<void> _revoke(BuildContext context, WidgetRef ref, String sessionId) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Sign out this device?'),
        content: const Text('That device will be signed out of Pamora immediately.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text('Sign Out', style: TextStyle(color: Theme.of(context).colorScheme.error)),
          ),
        ],
      ),
    );
    if (confirmed != true) return;
    await ref.read(sessionsRepositoryProvider).revoke(sessionId);
    ref.invalidate(sessionsProvider);
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final sessionsAsync = ref.watch(sessionsProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Active Sessions')),
      body: sessionsAsync.when(
        loading: () => const Padding(padding: EdgeInsets.all(PamoraSpacing.md), child: SkeletonList()),
        error: (_, __) => Center(
          child: EmptyState(
            icon: Icons.wifi_off_outlined,
            title: 'Couldn\'t load sessions',
            message: 'Check your connection and try again.',
            actionLabel: 'Retry',
            onAction: () => ref.invalidate(sessionsProvider),
          ),
        ),
        data: (sessions) {
          return ListView.separated(
            padding: const EdgeInsets.all(PamoraSpacing.md),
            itemCount: sessions.length,
            separatorBuilder: (_, __) => const SizedBox(height: PamoraSpacing.sm),
            itemBuilder: (context, i) {
              final s = sessions[i];
              return PamoraCard(
                child: Row(
                  children: [
                    Icon(Icons.devices_outlined, color: Theme.of(context).colorScheme.onSurfaceVariant),
                    const SizedBox(width: PamoraSpacing.sm),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(s.userAgent ?? 'Unknown device', maxLines: 1, overflow: TextOverflow.ellipsis),
                          Text(
                            '${s.ipAddress ?? ''}  •  ${formatRelative(s.lastActiveAt ?? DateTime.now())}',
                            style: Theme.of(context).textTheme.bodySmall,
                          ),
                        ],
                      ),
                    ),
                    if (s.isCurrentDevice)
                      const StatusBadge(label: 'This device', tone: BadgeTone.primary)
                    else
                      IconButton(
                        onPressed: () => _revoke(context, ref, s.id),
                        icon: Icon(Icons.logout, color: Theme.of(context).colorScheme.error),
                      ),
                  ],
                ),
              );
            },
          );
        },
      ),
    );
  }
}
