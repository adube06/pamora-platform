import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../application/planning_provider.dart';
import '../data/planning_repository.dart';
import 'simple_name_create_screen.dart';

class ChecklistCreateScreen extends ConsumerWidget {
  const ChecklistCreateScreen({super.key, required this.occasionId});

  final String occasionId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return SimpleNameCreateScreen(
      appBarTitle: 'Add Checklist',
      label: 'Checklist name',
      hint: 'e.g. Vendor bookings',
      submitLabel: 'Add Checklist',
      onSubmit: (name) async {
        await ref.read(planningRepositoryProvider).createChecklist(occasionId, name: name);
        ref.invalidate(checklistsProvider(occasionId));
      },
    );
  }
}
