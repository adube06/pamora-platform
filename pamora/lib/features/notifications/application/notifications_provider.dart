import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/notifications_repository.dart';
import '../models/app_notification.dart';

final notificationsProvider = AsyncNotifierProvider<NotificationsNotifier, List<AppNotification>>(
  NotificationsNotifier.new,
);

class NotificationsNotifier extends AsyncNotifier<List<AppNotification>> {
  @override
  Future<List<AppNotification>> build() => ref.read(notificationsRepositoryProvider).list();

  Future<void> refresh() async {
    state = await AsyncValue.guard(() => ref.read(notificationsRepositoryProvider).list());
  }

  Future<void> markRead(String id) async {
    final updated = await ref.read(notificationsRepositoryProvider).markRead(id);
    final current = state.value ?? [];
    state = AsyncData([
      for (final n in current)
        if (n.id == id) updated else n,
    ]);
  }
}

final unreadNotificationsCountProvider = Provider.autoDispose<int>((ref) {
  final list = ref.watch(notificationsProvider).value ?? const [];
  return list.where((n) => !n.isRead).length;
});
