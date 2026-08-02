import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/skeleton_loader.dart';
import '../../../core/theme/spacing.dart';
import '../../finance/presentation/finance_tab.dart';
import '../../people/presentation/people_tab.dart';
import '../../planning/presentation/planning_tab.dart';
import '../application/occasions_provider.dart';
import 'occasion_overview_tab.dart';

class OccasionDetailScreen extends ConsumerWidget {
  const OccasionDetailScreen({super.key, required this.occasionId});

  final String occasionId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final occasionAsync = ref.watch(occasionDetailProvider(occasionId));

    return occasionAsync.when(
      loading: () => Scaffold(
        appBar: AppBar(),
        body: const Padding(padding: EdgeInsets.all(PamoraSpacing.md), child: SkeletonList(itemCount: 3)),
      ),
      error: (_, __) => Scaffold(
        appBar: AppBar(),
        body: Center(
          child: EmptyState(
            icon: Icons.error_outline,
            title: 'Couldn\'t load this occasion',
            message: 'Check your connection and try again.',
            actionLabel: 'Retry',
            onAction: () => ref.invalidate(occasionDetailProvider(occasionId)),
          ),
        ),
      ),
      data: (occasion) {
        return DefaultTabController(
          length: 4,
          child: Scaffold(
            appBar: AppBar(
              title: Text(occasion.title, maxLines: 1, overflow: TextOverflow.ellipsis),
              bottom: const TabBar(
                isScrollable: true,
                tabAlignment: TabAlignment.start,
                tabs: [Tab(text: 'Overview'), Tab(text: 'Planning'), Tab(text: 'Finance'), Tab(text: 'Guests')],
              ),
            ),
            body: TabBarView(
              children: [
                OccasionOverviewTab(occasion: occasion),
                PlanningTab(occasionId: occasion.slug),
                FinanceTab(occasionId: occasion.slug),
                PeopleTab(occasionId: occasion.slug),
              ],
            ),
          ),
        );
      },
    );
  }
}
