import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/app_routes.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/pamora_card.dart';
import '../../../core/widgets/skeleton_loader.dart';
import '../application/finance_provider.dart';

class ExpenseListScreen extends ConsumerWidget {
  const ExpenseListScreen({super.key, required this.occasionId});

  final String occasionId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final expensesAsync = ref.watch(expensesProvider(occasionId));

    return Scaffold(
      appBar: AppBar(title: const Text('Expenses')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push(AppRoutes.expenseCreate(occasionId)),
        icon: const Icon(Icons.add),
        label: const Text('Add Expense'),
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(expensesProvider(occasionId).future),
        child: expensesAsync.when(
          loading: () => const Padding(padding: EdgeInsets.all(PamoraSpacing.md), child: SkeletonList()),
          error: (_, __) => Center(
            child: EmptyState(
              icon: Icons.wifi_off_outlined,
              title: 'Couldn\'t load expenses',
              message: 'Check your connection and try again.',
              actionLabel: 'Retry',
              onAction: () => ref.invalidate(expensesProvider(occasionId)),
            ),
          ),
          data: (expenses) {
            if (expenses.isEmpty) {
              return ListView(
                padding: const EdgeInsets.only(top: PamoraSpacing.giant),
                children: [
                  EmptyState(
                    icon: Icons.receipt_long_outlined,
                    title: 'No expenses yet',
                    message: 'Log spend against your budget categories.',
                    actionLabel: 'Add Expense',
                    onAction: () => context.push(AppRoutes.expenseCreate(occasionId)),
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
              itemCount: expenses.length,
              separatorBuilder: (_, __) => const SizedBox(height: PamoraSpacing.sm),
              itemBuilder: (context, i) {
                final e = expenses[i];
                return PamoraCard(
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(e.category.name, style: Theme.of(context).textTheme.bodyLarge),
                            Text(formatDate(e.spentAt), style: Theme.of(context).textTheme.bodySmall),
                          ],
                        ),
                      ),
                      Text(formatCurrency(e.amount, e.currency), style: Theme.of(context).textTheme.titleLarge),
                    ],
                  ),
                );
              },
            );
          },
        ),
      ),
    );
  }
}
