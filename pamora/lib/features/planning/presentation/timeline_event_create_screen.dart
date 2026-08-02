import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/widgets/error_banner.dart';
import '../../../core/widgets/pamora_text_field.dart';
import '../../../core/widgets/primary_button.dart';
import '../application/planning_provider.dart';
import '../data/planning_repository.dart';

class TimelineEventCreateScreen extends ConsumerStatefulWidget {
  const TimelineEventCreateScreen({super.key, required this.occasionId});

  final String occasionId;

  @override
  ConsumerState<TimelineEventCreateScreen> createState() => _TimelineEventCreateScreenState();
}

class _TimelineEventCreateScreenState extends ConsumerState<TimelineEventCreateScreen> {
  final _nameController = TextEditingController();
  DateTime? _scheduledAt;

  bool _submitting = false;
  String? _fieldError;
  String? _generalError;

  @override
  void initState() {
    super.initState();
    _nameController.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    _nameController.dispose();
    super.dispose();
  }

  Future<void> _pickDateTime() async {
    final date = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now().add(const Duration(days: 365 * 3)),
    );
    if (date == null || !mounted) return;
    final time = await showTimePicker(context: context, initialTime: TimeOfDay.now());
    if (time == null) return;
    setState(() => _scheduledAt = DateTime(date.year, date.month, date.day, time.hour, time.minute));
  }

  Future<void> _submit() async {
    setState(() {
      _submitting = true;
      _fieldError = null;
      _generalError = null;
    });

    try {
      await ref.read(planningRepositoryProvider).createTimelineEvent(
            widget.occasionId,
            name: _nameController.text.trim(),
            scheduledAt: _scheduledAt,
          );
      ref.invalidate(timelineEventsProvider(widget.occasionId));
      if (!mounted) return;
      context.pop();
    } on ApiException catch (e) {
      setState(() {
        _fieldError = e.fieldErrors['name']?.first;
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
      appBar: AppBar(title: const Text('Add Timeline Event')),
      body: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.all(PamoraSpacing.lg),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (_generalError != null) ...[
                ErrorBanner(message: _generalError!),
                const SizedBox(height: PamoraSpacing.md),
              ],
              PamoraTextField(
                label: 'Event name',
                controller: _nameController,
                hint: 'e.g. Ceremony begins',
                errorText: _fieldError,
              ),
              const SizedBox(height: PamoraSpacing.lg),
              Text('When (optional)', style: textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600)),
              const SizedBox(height: PamoraSpacing.sm),
              OutlinedButton.icon(
                onPressed: _pickDateTime,
                icon: const Icon(Icons.schedule_outlined, size: 18),
                label: Text(_scheduledAt == null ? 'Choose date & time' : formatDateTime(_scheduledAt)),
              ),
              const SizedBox(height: PamoraSpacing.xl),
              PrimaryButton(
                label: 'Add Event',
                onPressed: _nameController.text.trim().isEmpty || _submitting ? null : _submit,
                loading: _submitting,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
