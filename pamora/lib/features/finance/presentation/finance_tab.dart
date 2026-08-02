import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/router/app_routes.dart';
import '../../../core/theme/colors.dart';
import '../../../core/theme/radius.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/widgets/pamora_card.dart';
import '../../../core/widgets/primary_button.dart';
import '../../../core/widgets/section_header.dart';
import '../../../core/widgets/skeleton_loader.dart';
import '../../../core/widgets/status_badge.dart';
import '../application/finance_provider.dart';
import 'pledge_status_x.dart';

/// The Finance tab's landing view: Budget summary, plus previews of
/// Contributions/Expenses/Pledges each linking to their own list + add flow.
class FinanceTab extends ConsumerWidget {
  const FinanceTab({super.key, required this.occasionId});

  final String occasionId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final budgetAsync = ref.watch(budgetProvider(occasionId));
    final contributionsAsync = ref.watch(contributionsProvider(occasionId));
    final expensesAsync = ref.watch(expensesProvider(occasionId));
    final pledgesAsync = ref.watch(pledgesProvider(occasionId));

    return ListView(
      padding: const EdgeInsets.fromLTRB(PamoraSpacing.md, PamoraSpacing.md, PamoraSpacing.md, PamoraSpacing.huge),
      children: [
        budgetAsync.when(
          loading: () => const SkeletonList(itemCount: 1, itemHeight: 96),
          error: (_, __) => const SizedBox.shrink(),
          data: (budget) {
            if (budget == null) {
              return Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'No budget set up yet. Create one to start tracking spend against a plan.',
                    style: Theme.of(context)
                        .textTheme
                        .bodyMedium
                        ?.copyWith(color: Theme.of(context).colorScheme.onSurfaceVariant),
                  ),
                  const SizedBox(height: PamoraSpacing.md),
                  PrimaryButton(
                    label: 'Create Budget',
                    icon: Icons.account_balance_wallet_outlined,
                    onPressed: () => context.push(AppRoutes.financeBudgetSetup(occasionId)),
                  ),
                ],
              );
            }

            return DecoratedBox(
              decoration: BoxDecoration(gradient: PamoraColors.heroGradient, borderRadius: PamoraRadius.largeRadius),
              child: Padding(
                padding: const EdgeInsets.all(PamoraSpacing.md),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(budget.name, style: Theme.of(context).textTheme.titleLarge?.copyWith(color: Colors.white)),
                    const SizedBox(height: 4),
                    Text(
                      'Planned budget: ${formatCurrency(budget.plannedAmount, budget.currency)}',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.white),
                    ),
                  ],
                ),
              ),
            );
          },
        ),
        const SizedBox(height: PamoraSpacing.xl),
        SectionHeader(
          title: 'Contributions',
          actionLabel: 'View all',
          onAction: () => context.push(AppRoutes.financeContributions(occasionId)),
        ),
        contributionsAsync.when(
          loading: () => const SkeletonList(itemCount: 2, itemHeight: 56),
          error: (_, __) => const SizedBox.shrink(),
          data: (result) {
            final (contributions, summary) = result;
            if (contributions.isEmpty) {
              return _EmptyRow(
                text: 'No contributions recorded yet.',
                onAdd: () => context.push(AppRoutes.contributionCreate(occasionId)),
              );
            }
            return Column(
              children: [
                PamoraCard(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Total received', style: Theme.of(context).textTheme.bodyMedium),
                      Text(
                        formatCurrency(summary.totalReceived, contributions.first.currency),
                        style: Theme.of(context).textTheme.titleLarge,
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: PamoraSpacing.sm),
                ...contributions.take(2).map(
                      (c) => Padding(
                        padding: const EdgeInsets.only(bottom: PamoraSpacing.sm),
                        child: PamoraCard(
                          child: Row(
                            children: [
                              Expanded(child: Text(c.contributorName, overflow: TextOverflow.ellipsis)),
                              Text(formatCurrency(c.amount, c.currency)),
                            ],
                          ),
                        ),
                      ),
                    ),
              ],
            );
          },
        ),
        const SizedBox(height: PamoraSpacing.xl),
        SectionHeader(
          title: 'Expenses',
          actionLabel: 'View all',
          onAction: () => context.push(AppRoutes.financeExpenses(occasionId)),
        ),
        expensesAsync.when(
          loading: () => const SkeletonList(itemCount: 2, itemHeight: 56),
          error: (_, __) => const SizedBox.shrink(),
          data: (expenses) {
            if (expenses.isEmpty) {
              return _EmptyRow(
                text: 'No expenses recorded yet.',
                onAdd: () => context.push(AppRoutes.expenseCreate(occasionId)),
              );
            }
            return Column(
              children: expenses
                  .take(3)
                  .map((e) => Padding(
                        padding: const EdgeInsets.only(bottom: PamoraSpacing.sm),
                        child: PamoraCard(
                          child: Row(
                            children: [
                              Expanded(child: Text(e.category.name, overflow: TextOverflow.ellipsis)),
                              Text(formatCurrency(e.amount, e.currency)),
                            ],
                          ),
                        ),
                      ))
                  .toList(),
            );
          },
        ),
        const SizedBox(height: PamoraSpacing.xl),
        SectionHeader(
          title: 'Pledges',
          actionLabel: 'View all',
          onAction: () => context.push(AppRoutes.financePledges(occasionId)),
        ),
        pledgesAsync.when(
          loading: () => const SkeletonList(itemCount: 2, itemHeight: 56),
          error: (_, __) => const SizedBox.shrink(),
          data: (pledges) {
            if (pledges.isEmpty) {
              return _EmptyRow(
                text: 'No pledges recorded yet.',
                onAdd: () => context.push(AppRoutes.pledgeCreate(occasionId)),
              );
            }
            return Column(
              children: pledges
                  .take(3)
                  .map((p) => Padding(
                        padding: const EdgeInsets.only(bottom: PamoraSpacing.sm),
                        child: PamoraCard(
                          child: Row(
                            children: [
                              Expanded(child: Text(p.pledgorName, overflow: TextOverflow.ellipsis)),
                              StatusBadge(label: p.status.label, tone: p.status.tone),
                            ],
                          ),
                        ),
                      ))
                  .toList(),
            );
          },
        ),
      ],
    );
  }
}

class _EmptyRow extends StatelessWidget {
  const _EmptyRow({required this.text, required this.onAdd});

  final String text;
  final VoidCallback onAdd;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: Text(
            text,
            style:
                Theme.of(context).textTheme.bodySmall?.copyWith(color: Theme.of(context).colorScheme.onSurfaceVariant),
          ),
        ),
        TextButton(onPressed: onAdd, child: const Text('Add')),
      ],
    );
  }
}
