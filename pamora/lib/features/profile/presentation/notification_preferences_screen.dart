import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/spacing.dart';
import '../../../core/widgets/pamora_card.dart';
import '../../auth/application/auth_provider.dart';
import '../data/preferences_repository.dart';

class _PreferenceItem {
  const _PreferenceItem(this.key, this.label, this.description);
  final String key;
  final String label;
  final String description;
}

const _items = [
  _PreferenceItem('announcements', 'Announcements', 'Updates posted by an occasion\'s organizers.'),
  _PreferenceItem('task_reminders', 'Task Reminders', 'Nudges about tasks assigned to you.'),
  _PreferenceItem('rsvp_updates', 'RSVP Updates', 'When guests respond to an invitation.'),
  _PreferenceItem('contribution_alerts', 'Contribution Alerts', 'When a contribution or pledge comes in.'),
];

class NotificationPreferencesScreen extends ConsumerStatefulWidget {
  const NotificationPreferencesScreen({super.key});

  @override
  ConsumerState<NotificationPreferencesScreen> createState() => _NotificationPreferencesScreenState();
}

class _NotificationPreferencesScreenState extends ConsumerState<NotificationPreferencesScreen> {
  late Map<String, dynamic> _prefs = Map.of(ref.read(authProvider).value?.notificationPreferences ?? {});
  bool _saving = false;

  Future<void> _toggle(String key, bool value) async {
    setState(() {
      _prefs[key] = value;
      _saving = true;
    });
    try {
      final updated = await ref.read(preferencesRepositoryProvider).update(_prefs);
      if (mounted) setState(() => _prefs = updated);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Notification Preferences')),
      body: ListView.separated(
        padding: const EdgeInsets.all(PamoraSpacing.md),
        itemCount: _items.length,
        separatorBuilder: (_, __) => const SizedBox(height: PamoraSpacing.sm),
        itemBuilder: (context, i) {
          final item = _items[i];
          final enabled = _prefs[item.key] != false;
          return PamoraCard(
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(item.label, style: Theme.of(context).textTheme.bodyLarge),
                      Text(item.description, style: Theme.of(context).textTheme.bodySmall),
                    ],
                  ),
                ),
                Switch(
                  value: enabled,
                  onChanged: _saving ? null : (v) => _toggle(item.key, v),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
