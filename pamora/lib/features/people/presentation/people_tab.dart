import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/app_routes.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/pamora_card.dart';
import '../../../core/widgets/primary_button.dart';
import '../../../core/widgets/section_header.dart';
import '../../../core/widgets/skeleton_loader.dart';
import '../../../core/widgets/status_badge.dart';
import '../../auth/application/auth_provider.dart';
import '../application/people_provider.dart';
import 'member_status_x.dart';

/// The Guests tab's landing view: a members preview, pending invitations,
/// and an RSVP prompt if the current user hasn't responded yet.
class PeopleTab extends ConsumerWidget {
  const PeopleTab({super.key, required this.occasionId});

  final String occasionId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final overviewAsync = ref.watch(membersProvider(occasionId));
    final currentUserId = ref.watch(authProvider).value?.id;

    return overviewAsync.when(
      loading: () => const Padding(padding: EdgeInsets.all(PamoraSpacing.md), child: SkeletonList(itemCount: 3)),
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
        final me = currentUserId == null ? null : overview.members.where((m) => m.user.id.toString() == currentUserId);
        final myMembership = (me != null && me.isNotEmpty) ? me.first : null;

        return ListView(
          padding: const EdgeInsets.fromLTRB(PamoraSpacing.md, PamoraSpacing.md, PamoraSpacing.md, PamoraSpacing.huge),
          children: [
            if (myMembership != null && myMembership.rsvpStatus == null) ...[
              PamoraCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Are you attending?', style: Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: PamoraSpacing.xs),
                    Text(
                      'Let the host know if you\'ll be there.',
                      style: Theme.of(context)
                          .textTheme
                          .bodySmall
                          ?.copyWith(color: Theme.of(context).colorScheme.onSurfaceVariant),
                    ),
                    const SizedBox(height: PamoraSpacing.md),
                    PrimaryButton(
                      label: 'Respond',
                      icon: Icons.event_available_outlined,
                      onPressed: () => context.push(AppRoutes.rsvp(occasionId)),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: PamoraSpacing.xl),
            ],
            if (overview.permissions.canInvite)
              PrimaryButton(
                label: 'Invite Someone',
                icon: Icons.person_add_alt_1_outlined,
                onPressed: () => context.push(AppRoutes.inviteMember(occasionId)),
              ),
            const SizedBox(height: PamoraSpacing.xl),
            SectionHeader(
              title: 'Committee & Guests',
              actionLabel: 'View all',
              onAction: () => context.push(AppRoutes.members(occasionId)),
            ),
            if (overview.members.isEmpty)
              Text(
                'No one has joined yet.',
                style: Theme.of(context)
                    .textTheme
                    .bodySmall
                    ?.copyWith(color: Theme.of(context).colorScheme.onSurfaceVariant),
              )
            else
              Column(
                children: overview.members
                    .take(3)
                    .map((m) => Padding(
                          padding: const EdgeInsets.only(bottom: PamoraSpacing.sm),
                          child: PamoraCard(
                            child: Row(
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(m.user.name, style: Theme.of(context).textTheme.bodyLarge),
                                      Text(m.role.label, style: Theme.of(context).textTheme.bodySmall),
                                    ],
                                  ),
                                ),
                                if (m.rsvpStatus != null)
                                  StatusBadge(label: m.rsvpStatus!.label, tone: m.rsvpStatus!.tone),
                              ],
                            ),
                          ),
                        ))
                    .toList(),
              ),
            if (overview.pendingInvitations.isNotEmpty) ...[
              const SizedBox(height: PamoraSpacing.xl),
              SectionHeader(title: 'Pending Invitations'),
              Column(
                children: overview.pendingInvitations
                    .take(3)
                    .map((i) => Padding(
                          padding: const EdgeInsets.only(bottom: PamoraSpacing.sm),
                          child: PamoraCard(
                            child: Row(
                              children: [
                                Expanded(child: Text(i.email, overflow: TextOverflow.ellipsis)),
                                StatusBadge(label: i.status.label, tone: i.status.tone),
                              ],
                            ),
                          ),
                        ))
                    .toList(),
              ),
            ],
          ],
        );
      },
    );
  }
}
