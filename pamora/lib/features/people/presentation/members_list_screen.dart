import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/router/app_routes.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/pamora_card.dart';
import '../../../core/widgets/skeleton_loader.dart';
import '../../../core/widgets/status_badge.dart';
import '../application/people_provider.dart';
import '../data/people_repository.dart';
import '../models/role.dart';
import 'member_status_x.dart';

class MembersListScreen extends ConsumerWidget {
  const MembersListScreen({super.key, required this.occasionId});

  final String occasionId;

  Future<void> _confirmRemove(BuildContext context, WidgetRef ref, String memberId, String name) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Remove this member?'),
        content: Text('$name will lose access to this occasion.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text('Remove', style: TextStyle(color: Theme.of(context).colorScheme.error)),
          ),
        ],
      ),
    );
    if (confirmed != true) return;

    try {
      await ref.read(peopleRepositoryProvider).removeMember(memberId);
      ref.invalidate(membersProvider(occasionId));
    } on ApiException catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      }
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final overviewAsync = ref.watch(membersProvider(occasionId));

    return Scaffold(
      appBar: AppBar(title: const Text('Committee & Guests')),
      floatingActionButton: overviewAsync.value?.permissions.canInvite == true
          ? FloatingActionButton.extended(
              onPressed: () => context.push(AppRoutes.inviteMember(occasionId)),
              icon: const Icon(Icons.person_add_alt_1_outlined),
              label: const Text('Invite'),
            )
          : null,
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(membersProvider(occasionId).future),
        child: overviewAsync.when(
          loading: () => const Padding(padding: EdgeInsets.all(PamoraSpacing.md), child: SkeletonList()),
          error: (_, __) => Center(
            child: EmptyState(
              icon: Icons.wifi_off_outlined,
              title: 'Couldn\'t load guests',
              message: 'Check your connection and try again.',
              actionLabel: 'Retry',
              onAction: () => ref.invalidate(membersProvider(occasionId)),
            ),
          ),
          data: (overview) {
            if (overview.members.isEmpty && overview.pendingInvitations.isEmpty) {
              return ListView(
                padding: const EdgeInsets.only(top: PamoraSpacing.giant),
                children: [
                  EmptyState(
                    icon: Icons.people_outline,
                    title: 'No one has joined yet',
                    message: 'Invite committee members and guests to get started.',
                    actionLabel: overview.permissions.canInvite ? 'Invite Someone' : null,
                    onAction:
                        overview.permissions.canInvite ? () => context.push(AppRoutes.inviteMember(occasionId)) : null,
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
                for (final m in overview.members) ...[
                  PamoraCard(
                    child: Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(m.user.name, style: Theme.of(context).textTheme.bodyLarge),
                              Text(m.role.label, style: Theme.of(context).textTheme.bodySmall),
                              if (m.responsibilities.isNotEmpty)
                                Text(
                                  m.responsibilities.map((r) => r.label).join(', '),
                                  style: Theme.of(context)
                                      .textTheme
                                      .bodySmall
                                      ?.copyWith(color: Theme.of(context).colorScheme.onSurfaceVariant),
                                ),
                            ],
                          ),
                        ),
                        if (m.rsvpStatus != null) ...[
                          StatusBadge(label: m.rsvpStatus!.label, tone: m.rsvpStatus!.tone),
                          const SizedBox(width: PamoraSpacing.xs),
                        ],
                        if (overview.permissions.canRemoveMember && m.role != Role.host)
                          IconButton(
                            icon: const Icon(Icons.person_remove_outlined),
                            tooltip: 'Remove',
                            onPressed: () => _confirmRemove(context, ref, m.id, m.user.name),
                          ),
                      ],
                    ),
                  ),
                  const SizedBox(height: PamoraSpacing.sm),
                ],
                if (overview.pendingInvitations.isNotEmpty) ...[
                  const SizedBox(height: PamoraSpacing.md),
                  Text('Pending Invitations', style: Theme.of(context).textTheme.titleMedium),
                  const SizedBox(height: PamoraSpacing.sm),
                  for (final i in overview.pendingInvitations) ...[
                    PamoraCard(
                      child: Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(i.email, overflow: TextOverflow.ellipsis),
                                Text(i.role.label, style: Theme.of(context).textTheme.bodySmall),
                              ],
                            ),
                          ),
                          StatusBadge(label: i.status.label, tone: i.status.tone),
                        ],
                      ),
                    ),
                    const SizedBox(height: PamoraSpacing.sm),
                  ],
                ],
              ],
            );
          },
        ),
      ),
    );
  }
}
