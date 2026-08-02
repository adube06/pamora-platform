import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/colors.dart';
import '../../../core/theme/radius.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/widgets/error_banner.dart';
import '../../../core/widgets/pamora_text_field.dart';
import '../../../core/widgets/primary_button.dart';
import '../application/finance_provider.dart';
import '../data/finance_repository.dart';
import '../models/pledge.dart';

class PledgeCreateScreen extends ConsumerStatefulWidget {
  const PledgeCreateScreen({super.key, required this.occasionId});

  final String occasionId;

  @override
  ConsumerState<PledgeCreateScreen> createState() => _PledgeCreateScreenState();
}

class _PledgeCreateScreenState extends ConsumerState<PledgeCreateScreen> {
  final _nameController = TextEditingController();
  final _amountController = TextEditingController();
  final _phoneController = TextEditingController();
  final _messageController = TextEditingController();
  PledgeStatus _status = PledgeStatus.pending;
  DateTime _pledgedAt = DateTime.now();

  bool _submitting = false;
  Map<String, List<String>> _fieldErrors = {};
  String? _generalError;

  @override
  void dispose() {
    _nameController.dispose();
    _amountController.dispose();
    _phoneController.dispose();
    _messageController.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _pledgedAt,
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now(),
    );
    if (picked != null) setState(() => _pledgedAt = picked);
  }

  Future<void> _submit() async {
    final amount = double.tryParse(_amountController.text.trim());
    if (amount == null) {
      setState(() => _fieldErrors = {
            'amount': ['Enter a valid amount.'],
          });
      return;
    }

    setState(() {
      _submitting = true;
      _fieldErrors = {};
      _generalError = null;
    });

    try {
      await ref.read(financeRepositoryProvider).createPledge(
            widget.occasionId,
            pledgorName: _nameController.text.trim(),
            amount: amount,
            pledgedAt: _pledgedAt,
            pledgorPhone: _phoneController.text.trim(),
            message: _messageController.text.trim(),
            status: _status,
          );
      ref.invalidate(pledgesProvider(widget.occasionId));
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
    final textTheme = Theme.of(context).textTheme;

    return Scaffold(
      appBar: AppBar(title: const Text('Add Pledge')),
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
                label: 'Pledgor name',
                controller: _nameController,
                errorText: _fieldErrors['pledgor_name']?.first,
              ),
              const SizedBox(height: PamoraSpacing.md),
              PamoraTextField(
                label: 'Phone (optional)',
                controller: _phoneController,
                keyboardType: TextInputType.phone,
                errorText: _fieldErrors['pledgor_phone']?.first,
              ),
              const SizedBox(height: PamoraSpacing.md),
              PamoraTextField(
                label: 'Amount',
                controller: _amountController,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                errorText: _fieldErrors['amount']?.first,
              ),
              const SizedBox(height: PamoraSpacing.lg),
              Text('Status', style: textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600)),
              const SizedBox(height: PamoraSpacing.sm),
              Wrap(
                spacing: PamoraSpacing.sm,
                children: [PledgeStatus.pending, PledgeStatus.confirmed].map((s) {
                  final selected = s == _status;
                  return ChoiceChip(
                    label: Text(s.label),
                    selected: selected,
                    onSelected: (_) => setState(() => _status = s),
                    selectedColor: PamoraColors.darkPrimary.withValues(alpha: 0.22),
                    labelStyle: TextStyle(color: selected ? PamoraColors.darkPrimary : null),
                    shape: RoundedRectangleBorder(
                      borderRadius: PamoraRadius.pillRadius,
                      side: BorderSide(color: selected ? PamoraColors.darkPrimary : PamoraColors.darkBorder),
                    ),
                  );
                }).toList(),
              ),
              const SizedBox(height: PamoraSpacing.lg),
              Text('Date pledged', style: textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600)),
              const SizedBox(height: PamoraSpacing.sm),
              OutlinedButton.icon(
                onPressed: _pickDate,
                icon: const Icon(Icons.calendar_today_outlined, size: 18),
                label: Text(formatDate(_pledgedAt)),
              ),
              const SizedBox(height: PamoraSpacing.md),
              PamoraTextField(
                label: 'Message (optional)',
                controller: _messageController,
                maxLines: 2,
                errorText: _fieldErrors['message']?.first,
              ),
              const SizedBox(height: PamoraSpacing.xl),
              PrimaryButton(label: 'Add Pledge', onPressed: _submitting ? null : _submit, loading: _submitting),
            ],
          ),
        ),
      ),
    );
  }
}
