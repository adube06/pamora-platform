import 'package:flutter/material.dart';

import '../theme/colors.dart';
import '../theme/radius.dart';
import '../theme/spacing.dart';

/// A general (non-field-specific) error message banner for forms.
class ErrorBanner extends StatelessWidget {
  const ErrorBanner({super.key, required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(PamoraSpacing.sm),
      decoration: BoxDecoration(
        color: PamoraColors.darkError.withValues(alpha: 0.12),
        borderRadius: PamoraRadius.mediumRadius,
      ),
      child: Row(
        children: [
          const Icon(Icons.error_outline, color: PamoraColors.darkError, size: 18),
          const SizedBox(width: PamoraSpacing.sm),
          Expanded(
            child: Text(
              message,
              style: Theme.of(context).textTheme.bodySmall?.copyWith(color: PamoraColors.darkError),
            ),
          ),
        ],
      ),
    );
  }
}
