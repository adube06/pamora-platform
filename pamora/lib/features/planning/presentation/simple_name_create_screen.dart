import 'package:flutter/material.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/spacing.dart';
import '../../../core/widgets/error_banner.dart';
import '../../../core/widgets/pamora_text_field.dart';
import '../../../core/widgets/primary_button.dart';

/// Shared shape for the create-only, name-only Planning sub-resources
/// (Milestone, Checklist) — the backend only exposes create+list for these.
class SimpleNameCreateScreen extends StatefulWidget {
  const SimpleNameCreateScreen({
    super.key,
    required this.appBarTitle,
    required this.label,
    required this.hint,
    required this.submitLabel,
    required this.onSubmit,
  });

  final String appBarTitle;
  final String label;
  final String hint;
  final String submitLabel;
  final Future<void> Function(String name) onSubmit;

  @override
  State<SimpleNameCreateScreen> createState() => _SimpleNameCreateScreenState();
}

class _SimpleNameCreateScreenState extends State<SimpleNameCreateScreen> {
  final _controller = TextEditingController();
  bool _submitting = false;
  String? _fieldError;
  String? _generalError;

  @override
  void initState() {
    super.initState();
    _controller.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() {
      _submitting = true;
      _fieldError = null;
      _generalError = null;
    });

    try {
      await widget.onSubmit(_controller.text.trim());
      if (!mounted) return;
      Navigator.of(context).pop();
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
    return Scaffold(
      appBar: AppBar(title: Text(widget.appBarTitle)),
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
                label: widget.label,
                controller: _controller,
                hint: widget.hint,
                textInputAction: TextInputAction.done,
                errorText: _fieldError,
              ),
              const SizedBox(height: PamoraSpacing.xl),
              PrimaryButton(
                label: widget.submitLabel,
                onPressed: _controller.text.trim().isEmpty || _submitting ? null : _submit,
                loading: _submitting,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
