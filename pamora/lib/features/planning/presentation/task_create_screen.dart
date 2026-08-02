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
import '../application/planning_provider.dart';
import '../data/planning_repository.dart';
import '../models/task.dart';

class TaskCreateScreen extends ConsumerStatefulWidget {
  const TaskCreateScreen({super.key, required this.occasionId});

  final String occasionId;

  @override
  ConsumerState<TaskCreateScreen> createState() => _TaskCreateScreenState();
}

class _TaskCreateScreenState extends ConsumerState<TaskCreateScreen> {
  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  TaskPriority _priority = TaskPriority.medium;
  DateTime? _dueDate;

  bool _submitting = false;
  Map<String, List<String>> _fieldErrors = {};
  String? _generalError;

  @override
  void initState() {
    super.initState();
    _titleController.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now().add(const Duration(days: 7)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365 * 3)),
    );
    if (picked != null) setState(() => _dueDate = picked);
  }

  Future<void> _submit() async {
    setState(() {
      _submitting = true;
      _fieldErrors = {};
      _generalError = null;
    });

    try {
      await ref.read(planningRepositoryProvider).createTask(
            widget.occasionId,
            title: _titleController.text.trim(),
            description: _descriptionController.text.trim(),
            priority: _priority,
            dueDate: _dueDate,
          );
      ref.invalidate(tasksProvider(widget.occasionId));
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
      appBar: AppBar(title: const Text('Add Task')),
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
                hint: 'e.g. Book the venue',
                textInputAction: TextInputAction.next,
                errorText: _fieldErrors['title']?.first,
              ),
              const SizedBox(height: PamoraSpacing.md),
              PamoraTextField(
                label: 'Description (optional)',
                controller: _descriptionController,
                maxLines: 3,
                errorText: _fieldErrors['description']?.first,
              ),
              const SizedBox(height: PamoraSpacing.lg),
              Text('Priority', style: textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600)),
              const SizedBox(height: PamoraSpacing.sm),
              Wrap(
                spacing: PamoraSpacing.sm,
                children: TaskPriority.values.map((p) {
                  final selected = p == _priority;
                  return ChoiceChip(
                    label: Text(p.label),
                    selected: selected,
                    onSelected: (_) => setState(() => _priority = p),
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
              Text('Due date (optional)', style: textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600)),
              const SizedBox(height: PamoraSpacing.sm),
              OutlinedButton.icon(
                onPressed: _pickDate,
                icon: const Icon(Icons.calendar_today_outlined, size: 18),
                label: Text(_dueDate == null ? 'Choose a date' : formatDate(_dueDate)),
              ),
              const SizedBox(height: PamoraSpacing.xl),
              PrimaryButton(
                label: 'Add Task',
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
