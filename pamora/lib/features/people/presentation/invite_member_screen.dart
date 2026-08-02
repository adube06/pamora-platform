import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/colors.dart';
import '../../../core/theme/radius.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/widgets/error_banner.dart';
import '../../../core/widgets/pamora_text_field.dart';
import '../../../core/widgets/primary_button.dart';
import '../application/people_provider.dart';
import '../data/people_repository.dart';
import '../models/role.dart';

class InviteMemberScreen extends ConsumerStatefulWidget {
  const InviteMemberScreen({super.key, required this.occasionId});

  final String occasionId;

  @override
  ConsumerState<InviteMemberScreen> createState() => _InviteMemberScreenState();
}

class _InviteMemberScreenState extends ConsumerState<InviteMemberScreen> {
  final _emailController = TextEditingController();
  final _notesController = TextEditingController();
  Role _role = Role.member;

  bool _submitting = false;
  Map<String, List<String>> _fieldErrors = {};
  String? _generalError;

  @override
  void dispose() {
    _emailController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() {
      _submitting = true;
      _fieldErrors = {};
      _generalError = null;
    });

    try {
      await ref.read(peopleRepositoryProvider).inviteMember(
            widget.occasionId,
            email: _emailController.text.trim(),
            role: _role,
            notes: _notesController.text.trim(),
          );
      ref.invalidate(membersProvider(widget.occasionId));
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
      appBar: AppBar(title: const Text('Invite Someone')),
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
                label: 'Email',
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                errorText: _fieldErrors['email']?.first,
              ),
              const SizedBox(height: PamoraSpacing.lg),
              Text('Role', style: textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600)),
              const SizedBox(height: PamoraSpacing.sm),
              Wrap(
                spacing: PamoraSpacing.sm,
                runSpacing: PamoraSpacing.sm,
                children: Role.invitable.map((r) {
                  final selected = r == _role;
                  return ChoiceChip(
                    label: Text(r.label),
                    selected: selected,
                    onSelected: (_) => setState(() => _role = r),
                    selectedColor: PamoraColors.darkPrimary.withValues(alpha: 0.22),
                    labelStyle: TextStyle(color: selected ? PamoraColors.darkPrimary : null),
                    shape: RoundedRectangleBorder(
                      borderRadius: PamoraRadius.pillRadius,
                      side: BorderSide(color: selected ? PamoraColors.darkPrimary : PamoraColors.darkBorder),
                    ),
                  );
                }).toList(),
              ),
              if (_fieldErrors['role'] != null) ...[
                const SizedBox(height: PamoraSpacing.xs),
                Text(_fieldErrors['role']!.first, style: TextStyle(color: Theme.of(context).colorScheme.error)),
              ],
              const SizedBox(height: PamoraSpacing.md),
              PamoraTextField(
                label: 'Note (optional)',
                controller: _notesController,
                maxLines: 2,
                errorText: _fieldErrors['notes']?.first,
              ),
              const SizedBox(height: PamoraSpacing.xl),
              PrimaryButton(label: 'Send Invitation', onPressed: _submitting ? null : _submit, loading: _submitting),
            ],
          ),
        ),
      ),
    );
  }
}
