import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/spacing.dart';
import '../../../core/widgets/pamora_card.dart';
import '../../../core/widgets/section_header.dart';
import '../../auth/application/auth_provider.dart';
import '../data/preferences_repository.dart';

class _PreferenceItem {
  const _PreferenceItem(this.key, this.label, this.description);
  final String key;
  final String label;
  final String description;
}

class _PreferenceGroup {
  const _PreferenceGroup(this.title, this.items);
  final String title;
  final List<_PreferenceItem> items;
}

/// Keys and labels match App\Domains\Communication\Domain\Models\Notification::TYPES
/// exactly — that enum is the backend's own documented single source of
/// truth for these preference keys, grouped here by owning domain for
/// readability rather than shown as one flat list of eleven toggles.
const _groups = [
  _PreferenceGroup('Planning', [
    _PreferenceItem('task_assigned', 'Task Assigned', 'When a task is assigned to you.'),
    _PreferenceItem('task_completed', 'Task Completed', 'When a task you\'re involved with is completed.'),
    _PreferenceItem('reminder_triggered', 'Reminders', 'Scheduled reminders for upcoming timeline events.'),
  ]),
  _PreferenceGroup('People', [
    _PreferenceItem('member_joined', 'Member Joined', 'When someone joins an occasion you\'re part of.'),
  ]),
  _PreferenceGroup('Finance', [
    _PreferenceItem('contribution_received', 'Contribution Received', 'When a contribution or pledge is recorded.'),
  ]),
  _PreferenceGroup('Marketplace', [
    _PreferenceItem(
        'quotation_submitted', 'Quotation Submitted', 'When a vendor submits a quotation for your request.'),
    _PreferenceItem('quotation_accepted', 'Quotation Accepted', 'When your quotation is accepted.'),
    _PreferenceItem('quotation_rejected', 'Quotation Rejected', 'When your quotation is rejected.'),
    _PreferenceItem('booking_confirmed', 'Booking Confirmed', 'When a booking is confirmed.'),
    _PreferenceItem('booking_completed', 'Booking Completed', 'When a booking is marked complete.'),
    _PreferenceItem('review_published', 'Review Published', 'When a review is published for your service.'),
  ]),
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
      final updated = await ref.read(preferencesRepositoryProvider).update({key: value});
      if (mounted) setState(() => _prefs = updated);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Notification Preferences')),
      body: ListView(
        padding: const EdgeInsets.all(PamoraSpacing.md),
        children: [
          for (final group in _groups) ...[
            SectionHeader(title: group.title),
            for (final item in group.items) ...[
              PamoraCard(
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
                      value: _prefs[item.key] != false,
                      onChanged: _saving ? null : (v) => _toggle(item.key, v),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: PamoraSpacing.sm),
            ],
            const SizedBox(height: PamoraSpacing.md),
          ],
        ],
      ),
    );
  }
}
