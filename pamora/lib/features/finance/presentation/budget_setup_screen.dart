import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/widgets/error_banner.dart';
import '../../../core/widgets/pamora_text_field.dart';
import '../../../core/widgets/primary_button.dart';
import '../application/finance_provider.dart';
import '../data/finance_repository.dart';

class BudgetSetupScreen extends ConsumerStatefulWidget {
  const BudgetSetupScreen({super.key, required this.occasionId});

  final String occasionId;

  @override
  ConsumerState<BudgetSetupScreen> createState() => _BudgetSetupScreenState();
}

class _BudgetSetupScreenState extends ConsumerState<BudgetSetupScreen> {
  final _nameController = TextEditingController(text: 'Main Budget');
  final _amountController = TextEditingController();
  final _currencyController = TextEditingController(text: 'KES');

  bool _submitting = false;
  Map<String, List<String>> _fieldErrors = {};
  String? _generalError;

  @override
  void dispose() {
    _nameController.dispose();
    _amountController.dispose();
    _currencyController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    final amount = double.tryParse(_amountController.text.trim());
    if (amount == null) {
      setState(() => _fieldErrors = {
            'planned_amount': ['Enter a valid amount.'],
          });
      return;
    }

    setState(() {
      _submitting = true;
      _fieldErrors = {};
      _generalError = null;
    });

    try {
      await ref.read(financeRepositoryProvider).createBudget(
            widget.occasionId,
            name: _nameController.text.trim(),
            plannedAmount: amount,
            currency: _currencyController.text.trim(),
          );
      ref.invalidate(budgetProvider(widget.occasionId));
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
    return Scaffold(
      appBar: AppBar(title: const Text('Create Budget')),
      body: SafeArea(
        top: false,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(PamoraSpacing.lg),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (_generalError != null) ...[
                ErrorBanner(message: _generalError!),
                const SizedBox(height: PamoraSpacing.md),
              ],
              PamoraTextField(
                label: 'Budget name',
                controller: _nameController,
                errorText: _fieldErrors['name']?.first,
              ),
              const SizedBox(height: PamoraSpacing.md),
              PamoraTextField(
                label: 'Planned amount',
                controller: _amountController,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                errorText: _fieldErrors['planned_amount']?.first,
              ),
              const SizedBox(height: PamoraSpacing.md),
              PamoraTextField(
                label: 'Currency (optional)',
                controller: _currencyController,
                hint: 'e.g. KES, USD',
                errorText: _fieldErrors['currency']?.first,
              ),
              const SizedBox(height: PamoraSpacing.xl),
              PrimaryButton(label: 'Create Budget', onPressed: _submitting ? null : _submit, loading: _submitting),
            ],
          ),
        ),
      ),
    );
  }
}
