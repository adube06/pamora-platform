import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/router/app_routes.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/error_banner.dart';
import '../../../core/widgets/pamora_text_field.dart';
import '../../../core/widgets/primary_button.dart';
import '../application/finance_provider.dart';
import '../data/finance_repository.dart';
import '../models/budget_category.dart';

class ExpenseCreateScreen extends ConsumerStatefulWidget {
  const ExpenseCreateScreen({super.key, required this.occasionId});

  final String occasionId;

  @override
  ConsumerState<ExpenseCreateScreen> createState() => _ExpenseCreateScreenState();
}

class _ExpenseCreateScreenState extends ConsumerState<ExpenseCreateScreen> {
  final _amountController = TextEditingController();
  final _descriptionController = TextEditingController();
  DateTime _spentAt = DateTime.now();
  BudgetCategory? _category;

  bool _submitting = false;
  Map<String, List<String>> _fieldErrors = {};
  String? _generalError;

  @override
  void dispose() {
    _amountController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _spentAt,
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now(),
    );
    if (picked != null) setState(() => _spentAt = picked);
  }

  Future<void> _submit() async {
    final amount = double.tryParse(_amountController.text.trim());
    if (amount == null || _category == null) {
      setState(() => _fieldErrors = {
            if (amount == null) 'amount': ['Enter a valid amount.'],
            if (_category == null) 'budget_category_id': ['Choose a category.'],
          });
      return;
    }

    setState(() {
      _submitting = true;
      _fieldErrors = {};
      _generalError = null;
    });

    try {
      await ref.read(financeRepositoryProvider).createExpense(
            widget.occasionId,
            budgetCategoryId: _category!.id,
            amount: amount,
            spentAt: _spentAt,
            description: _descriptionController.text.trim(),
          );
      ref.invalidate(expensesProvider(widget.occasionId));
      if (!mounted) return;
      context.pop();
    } on ApiException catch (e) {
      setState(() {
        _fieldErrors = e.fieldErrors;
        _generalError = e.isValidationError ? null : e.message;
      });
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final budgetAsync = ref.watch(budgetProvider(widget.occasionId));
    final textTheme = Theme.of(context).textTheme;

    return Scaffold(
      appBar: AppBar(title: const Text('Add Expense')),
      body: SafeArea(
        top: false,
        child: budgetAsync.when(
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (_, __) => const Center(child: Text('Couldn\'t load the budget.')),
          data: (budget) {
            if (budget == null || budget.categories.isEmpty) {
              return Center(
                child: EmptyState(
                  icon: Icons.account_balance_wallet_outlined,
                  title: 'Set up a budget first',
                  message: 'Expenses are logged against budget categories — create a budget to get started.',
                  actionLabel: 'Create Budget',
                  onAction: () => context.pushReplacement(AppRoutes.financeBudgetSetup(widget.occasionId)),
                ),
              );
            }

            _category ??= budget.categories.first;

            return SingleChildScrollView(
              padding: const EdgeInsets.all(PamoraSpacing.lg),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (_generalError != null) ...[
                    ErrorBanner(message: _generalError!),
                    const SizedBox(height: PamoraSpacing.md),
                  ],
                  Text('Category', style: textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600)),
                  const SizedBox(height: PamoraSpacing.sm),
                  DropdownButtonFormField<BudgetCategory>(
                    value: _category,
                    items: budget.categories.map((c) => DropdownMenuItem(value: c, child: Text(c.name))).toList(),
                    onChanged: (c) => setState(() => _category = c),
                  ),
                  const SizedBox(height: PamoraSpacing.md),
                  PamoraTextField(
                    label: 'Amount',
                    controller: _amountController,
                    keyboardType: const TextInputType.numberWithOptions(decimal: true),
                    errorText: _fieldErrors['amount']?.first,
                  ),
                  const SizedBox(height: PamoraSpacing.lg),
                  Text('Date spent', style: textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600)),
                  const SizedBox(height: PamoraSpacing.sm),
                  OutlinedButton.icon(
                    onPressed: _pickDate,
                    icon: const Icon(Icons.calendar_today_outlined, size: 18),
                    label: Text(formatDate(_spentAt)),
                  ),
                  const SizedBox(height: PamoraSpacing.md),
                  PamoraTextField(
                    label: 'Description (optional)',
                    controller: _descriptionController,
                    maxLines: 2,
                    errorText: _fieldErrors['description']?.first,
                  ),
                  const SizedBox(height: PamoraSpacing.xl),
                  PrimaryButton(label: 'Add Expense', onPressed: _submitting ? null : _submit, loading: _submitting),
                ],
              ),
            );
          },
        ),
      ),
    );
  }
}
