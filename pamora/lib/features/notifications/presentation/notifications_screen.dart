import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/colors.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/pamora_card.dart';
import '../../../core/widgets/skeleton_loader.dart';
import '../application/notifications_provider.dart';
import '../models/app_notification.dart';

class NotificationsScreen extends ConsumerWidget {
  const NotificationsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final notificationsAsync = ref.watch(notificationsProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Notifications')),
      body: RefreshIndicator(
        onRefresh: () => ref.read(notificationsProvider.notifier).refresh(),
        child: notificationsAsync.when(
          loading: () => const Padding(padding: EdgeInsets.all(PamoraSpacing.md), child: SkeletonList()),
          error: (_, __) => Center(
            child: EmptyState(
              icon: Icons.wifi_off_outlined,
              title: 'Couldn\'t load notifications',
              message: 'Check your connection and try again.',
              actionLabel: 'Retry',
              onAction: () => ref.read(notificationsProvider.notifier).refresh(),
            ),
          ),
          data: (notifications) {
            if (notifications.isEmpty) {
              return ListView(
                padding: const EdgeInsets.only(top: PamoraSpacing.giant),
                children: const [
                  EmptyState(
                    icon: Icons.notifications_none_outlined,
                    title: 'You\'re all caught up',
                    message: 'Updates about your occasions will show up here.',
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
              itemCount: notifications.length,
              separatorBuilder: (_, __) => const SizedBox(height: PamoraSpacing.sm),
              itemBuilder: (context, i) => _NotificationCard(notification: notifications[i]),
            );
          },
        ),
      ),
    );
  }
}

class _NotificationCard extends ConsumerWidget {
  const _NotificationCard({required this.notification});

  final AppNotification notification;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final unread = !notification.isRead;
    final textTheme = Theme.of(context).textTheme;

    return PamoraCard(
      onTap: unread ? () => ref.read(notificationsProvider.notifier).markRead(notification.id) : null,
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(top: 6),
            child: Container(
              width: 8,
              height: 8,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: unread ? PamoraColors.darkPrimary : Colors.transparent,
              ),
            ),
          ),
          const SizedBox(width: PamoraSpacing.sm),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  notification.title,
                  style: textTheme.bodyLarge?.copyWith(fontWeight: unread ? FontWeight.w700 : FontWeight.w400),
                ),
                const SizedBox(height: 2),
                Text(notification.body, style: textTheme.bodyMedium),
                const SizedBox(height: 4),
                Text(formatRelative(notification.createdAt), style: textTheme.bodySmall),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
