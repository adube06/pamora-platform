import 'package:flutter/material.dart';

import '../theme/radius.dart';
import '../theme/spacing.dart';

/// A consistent elevated-by-color (not shadow) surface, matching the
/// design system's card component across every feature screen.
class PamoraCard extends StatelessWidget {
  const PamoraCard({
    super.key,
    required this.child,
    this.onTap,
    this.padding = const EdgeInsets.all(PamoraSpacing.md),
  });

  final Widget child;
  final VoidCallback? onTap;
  final EdgeInsetsGeometry padding;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;

    return Material(
      color: scheme.surfaceContainerHighest,
      borderRadius: PamoraRadius.largeRadius,
      child: InkWell(
        borderRadius: PamoraRadius.largeRadius,
        onTap: onTap,
        child: Container(
          padding: padding,
          decoration: BoxDecoration(
            borderRadius: PamoraRadius.largeRadius,
            border: Border.all(color: scheme.outline.withValues(alpha: 0.6)),
          ),
          child: child,
        ),
      ),
    );
  }
}
