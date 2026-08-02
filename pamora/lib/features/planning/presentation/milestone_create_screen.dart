import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../application/planning_provider.dart';
import '../data/planning_repository.dart';
import 'simple_name_create_screen.dart';

class MilestoneCreateScreen extends ConsumerWidget {
  const MilestoneCreateScreen({super.key, required this.occasionId});

  final String occasionId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return SimpleNameCreateScreen(
      appBarTitle: 'Add Milestone',
      label: 'Milestone name',
      hint: 'e.g. Venue confirmed',
      submitLabel: 'Add Milestone',
      onSubmit: (name) async {
        await ref.read(planningRepositoryProvider).createMilestone(occasionId, name: name);
        ref.invalidate(milestonesProvider(occasionId));
      },
    );
  }
}
