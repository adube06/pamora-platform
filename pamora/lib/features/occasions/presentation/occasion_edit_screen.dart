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
import '../application/occasions_provider.dart';
import '../data/occasions_repository.dart';
import '../models/occasion.dart';

class OccasionEditScreen extends ConsumerStatefulWidget {
  const OccasionEditScreen({super.key, required this.occasion});

  final Occasion occasion;

  @override
  ConsumerState<OccasionEditScreen> createState() => _OccasionEditScreenState();
}

class _OccasionEditScreenState extends ConsumerState<OccasionEditScreen> {
  late final _titleController = TextEditingController(text: widget.occasion.title);
  late final _descriptionController = TextEditingController(text: widget.occasion.description ?? '');
  late final _locationController = TextEditingController(text: widget.occasion.location ?? '');
  late DateTime? _primaryDate = widget.occasion.primaryDate;
  late OccasionVisibility _visibility = widget.occasion.visibility;

  bool _submitting = false;
  Map<String, List<String>> _fieldErrors = {};
  String? _generalError;

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    _locationController.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _primaryDate ?? DateTime.now().add(const Duration(days: 30)),
      firstDate: DateTime.now().subtract(const Duration(days: 365 * 5)),
      lastDate: DateTime.now().add(const Duration(days: 365 * 3)),
    );
    if (picked != null) setState(() => _primaryDate = picked);
  }

  Future<void> _submit() async {
    setState(() {
      _submitting = true;
      _fieldErrors = {};
      _generalError = null;
    });

    try {
      await ref.read(occasionsRepositoryProvider).update(
            widget.occasion.slug,
            title: _titleController.text.trim(),
            description: _descriptionController.text.trim(),
            primaryDate: _primaryDate,
            location: _locationController.text.trim(),
            visibility: _visibility,
          );
      ref.invalidate(occasionDetailProvider(widget.occasion.slug));
      ref.invalidate(occasionsListProvider);
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
      appBar: AppBar(title: const Text('Edit Occasion')),
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
                label: 'Title',
                controller: _titleController,
                textInputAction: TextInputAction.next,
                errorText: _fieldErrors['title']?.first,
              ),
              const SizedBox(height: PamoraSpacing.md),
              PamoraTextField(
                label: 'Description (optional)',
                controller: _descriptionController,
                maxLines: 3,
                textInputAction: TextInputAction.newline,
                errorText: _fieldErrors['description']?.first,
              ),
              const SizedBox(height: PamoraSpacing.lg),
              Text('Date', style: textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600)),
              const SizedBox(height: PamoraSpacing.sm),
              OutlinedButton.icon(
                onPressed: _pickDate,
                icon: const Icon(Icons.calendar_today_outlined, size: 18),
                label: Text(_primaryDate == null ? 'Choose a date' : formatDate(_primaryDate)),
              ),
              const SizedBox(height: PamoraSpacing.lg),
              PamoraTextField(
                label: 'Location (optional)',
                controller: _locationController,
                textInputAction: TextInputAction.done,
                errorText: _fieldErrors['location']?.first,
              ),
              const SizedBox(height: PamoraSpacing.lg),
              Text('Who can see this', style: textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600)),
              const SizedBox(height: PamoraSpacing.sm),
              Wrap(
                spacing: PamoraSpacing.sm,
                children: OccasionVisibility.values.map((v) {
                  final selected = v == _visibility;
                  return ChoiceChip(
                    label: Text(v.label),
                    selected: selected,
                    onSelected: (_) => setState(() => _visibility = v),
                    selectedColor: PamoraColors.darkPrimary.withValues(alpha: 0.22),
                    labelStyle: TextStyle(color: selected ? PamoraColors.darkPrimary : null),
                    shape: RoundedRectangleBorder(
                      borderRadius: PamoraRadius.pillRadius,
                      side: BorderSide(color: selected ? PamoraColors.darkPrimary : PamoraColors.darkBorder),
                    ),
                  );
                }).toList(),
              ),
              const SizedBox(height: PamoraSpacing.xl),
              PrimaryButton(label: 'Save Changes', onPressed: _submitting ? null : _submit, loading: _submitting),
            ],
          ),
        ),
      ),
    );
  }
}
