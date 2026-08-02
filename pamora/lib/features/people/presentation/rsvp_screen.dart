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
import '../models/occasion_member.dart';

class RsvpScreen extends ConsumerStatefulWidget {
  const RsvpScreen({super.key, required this.occasionId});

  final String occasionId;

  @override
  ConsumerState<RsvpScreen> createState() => _RsvpScreenState();
}

class _RsvpScreenState extends ConsumerState<RsvpScreen> {
  final _guestCountController = TextEditingController();
  final _messageController = TextEditingController();
  RsvpStatus _status = RsvpStatus.attending;

  bool _submitting = false;
  String? _generalError;

  @override
  void dispose() {
    _guestCountController.dispose();
    _messageController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() {
      _submitting = true;
      _generalError = null;
    });

    try {
      await ref.read(peopleRepositoryProvider).submitRsvp(
            widget.occasionId,
            status: _status,
            guestCount: _status == RsvpStatus.attending ? int.tryParse(_guestCountController.text.trim()) : null,
            message: _messageController.text.trim(),
          );
      ref.invalidate(membersProvider(widget.occasionId));
      if (!mounted) return;
      context.pop();
    } on ApiException catch (e) {
      setState(() => _generalError = e.message);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return Scaffold(
      appBar: AppBar(title: const Text('RSVP')),
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
              Text('Will you be attending?', style: textTheme.titleMedium),
              const SizedBox(height: PamoraSpacing.sm),
              Wrap(
                spacing: PamoraSpacing.sm,
                children: RsvpStatus.values.map((s) {
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
              if (_status == RsvpStatus.attending) ...[
                const SizedBox(height: PamoraSpacing.md),
                PamoraTextField(
                  label: 'Number of guests (optional)',
                  controller: _guestCountController,
                  keyboardType: TextInputType.number,
                ),
              ],
              const SizedBox(height: PamoraSpacing.md),
              PamoraTextField(
                label: 'Message (optional)',
                controller: _messageController,
                maxLines: 2,
              ),
              const SizedBox(height: PamoraSpacing.xl),
              PrimaryButton(label: 'Submit RSVP', onPressed: _submitting ? null : _submit, loading: _submitting),
            ],
          ),
        ),
      ),
    );
  }
}
