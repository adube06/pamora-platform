import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/router/app_routes.dart';
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
import 'occasion_status_x.dart';

class OccasionCreateScreen extends ConsumerStatefulWidget {
  const OccasionCreateScreen({super.key});

  @override
  ConsumerState<OccasionCreateScreen> createState() => _OccasionCreateScreenState();
}

class _OccasionCreateScreenState extends ConsumerState<OccasionCreateScreen> {
  final _titleController = TextEditingController();
  final _locationController = TextEditingController();

  OccasionType _type = OccasionType.wedding;
  OccasionVisibility _visibility = OccasionVisibility.private_;
  DateTime? _primaryDate;

  bool _submitting = false;
  Map<String, List<String>> _fieldErrors = {};
  String? _generalError;

  @override
  void initState() {
    super.initState();
    _titleController.addListener(_onTitleChanged);
  }

  void _onTitleChanged() => setState(() {});

  @override
  void dispose() {
    _titleController.dispose();
    _locationController.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now().add(const Duration(days: 30)),
      firstDate: DateTime.now(),
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
      final occasion = await ref.read(occasionsRepositoryProvider).create(
            title: _titleController.text.trim(),
            type: _type,
            primaryDate: _primaryDate,
            location: _locationController.text.trim(),
            visibility: _visibility,
          );
      ref.invalidate(occasionsListProvider);
      if (!mounted) return;
      context.pushReplacement(AppRoutes.occasionDetail(occasion.slug));
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
      appBar: AppBar(title: const Text('New Occasion')),
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
                hint: 'e.g. Amara & Kwame\'s Wedding',
                textInputAction: TextInputAction.next,
                errorText: _fieldErrors['title']?.first,
              ),
              const SizedBox(height: PamoraSpacing.lg),
              Text('Type', style: textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600)),
              const SizedBox(height: PamoraSpacing.sm),
              Wrap(
                spacing: PamoraSpacing.sm,
                children: OccasionType.values.map((type) {
                  final selected = type == _type;
                  return ChoiceChip(
                    label: Text('${type.emoji} ${type.label}'),
                    selected: selected,
                    onSelected: (_) => setState(() => _type = type),
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
              Text('Date (optional)', style: textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600)),
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
                hint: 'e.g. Nairobi, Kenya',
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
              PrimaryButton(
                label: 'Create Occasion',
                onPressed: _titleController.text.trim().isEmpty || _submitting ? null : _submit,
                loading: _submitting,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
