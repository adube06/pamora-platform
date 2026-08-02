import 'package:flutter/material.dart';

import '../theme/colors.dart';
import '../theme/radius.dart';
import '../theme/spacing.dart';

enum BadgeTone { neutral, success, warning, error, info, primary }

/// A small icon+label pill. Color always ships paired with an icon and
/// text, never conveying meaning by hue alone (Accessibility doc).
class StatusBadge extends StatelessWidget {
  const StatusBadge({super.key, required this.label, required this.tone, this.icon});

  final String label;
  final BadgeTone tone;
  final IconData? icon;

  ({Color fg, Color bg, IconData icon}) _style() {
    switch (tone) {
      case BadgeTone.success:
        return (
          fg: PamoraColors.darkSuccess,
          bg: PamoraColors.darkSuccess.withValues(alpha: 0.14),
          icon: icon ?? Icons.check_circle_outline
        );
      case BadgeTone.warning:
        return (
          fg: PamoraColors.darkWarning,
          bg: PamoraColors.darkWarning.withValues(alpha: 0.14),
          icon: icon ?? Icons.error_outline
        );
      case BadgeTone.error:
        return (
          fg: PamoraColors.darkError,
          bg: PamoraColors.darkError.withValues(alpha: 0.14),
          icon: icon ?? Icons.cancel_outlined
        );
      case BadgeTone.info:
        return (
          fg: PamoraColors.darkInfo,
          bg: PamoraColors.darkInfo.withValues(alpha: 0.14),
          icon: icon ?? Icons.info_outline
        );
      case BadgeTone.primary:
        return (
          fg: PamoraColors.darkPrimary,
          bg: PamoraColors.darkPrimary.withValues(alpha: 0.14),
          icon: icon ?? Icons.circle
        );
      case BadgeTone.neutral:
        return (
          fg: PamoraColors.darkTextSecondary,
          bg: PamoraColors.darkTextSecondary.withValues(alpha: 0.14),
          icon: icon ?? Icons.circle_outlined
        );
    }
  }

  @override
  Widget build(BuildContext context) {
    final style = _style();

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: PamoraSpacing.sm, vertical: 4),
      decoration: BoxDecoration(color: style.bg, borderRadius: PamoraRadius.pillRadius),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(style.icon, size: 13, color: style.fg),
          const SizedBox(width: 4),
          Text(
            label,
            style: Theme.of(context).textTheme.labelMedium?.copyWith(color: style.fg, fontWeight: FontWeight.w600),
          ),
        ],
      ),
    );
  }
}
