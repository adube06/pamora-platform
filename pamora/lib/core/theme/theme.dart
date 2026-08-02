import 'package:flutter/material.dart';

import 'colors.dart';
import 'radius.dart';
import 'typography.dart';

/// Builds the app's Material 3 themes from Pamora design tokens.
///
/// Dark is the forced default (see main.dart); light is fully built so a
/// theme toggle can be added later without redesigning anything.
class PamoraTheme {
  PamoraTheme._();

  static ThemeData get dark {
    final colorScheme = const ColorScheme.dark(
      primary: PamoraColors.darkPrimary,
      onPrimary: Colors.white,
      secondary: PamoraColors.darkSecondary,
      onSecondary: Colors.black,
      error: PamoraColors.darkError,
      onError: Colors.black,
      surface: PamoraColors.darkSurface,
      onSurface: PamoraColors.darkTextPrimary,
      surfaceContainerHighest: PamoraColors.darkSurfaceRaised,
      outline: PamoraColors.darkBorder,
    );

    final textTheme = PamoraTypography.buildTextTheme(
      textPrimary: PamoraColors.darkTextPrimary,
      textSecondary: PamoraColors.darkTextSecondary,
    );

    return _build(colorScheme: colorScheme, textTheme: textTheme, background: PamoraColors.darkBackground);
  }

  static ThemeData get light {
    final colorScheme = const ColorScheme.light(
      primary: PamoraColors.lightPrimary,
      onPrimary: Colors.white,
      secondary: PamoraColors.lightSecondary,
      onSecondary: Colors.white,
      error: PamoraColors.lightError,
      onError: Colors.white,
      surface: PamoraColors.lightSurface,
      onSurface: PamoraColors.lightTextPrimary,
      surfaceContainerHighest: PamoraColors.lightBackground,
      outline: PamoraColors.lightBorder,
    );

    final textTheme = PamoraTypography.buildTextTheme(
      textPrimary: PamoraColors.lightTextPrimary,
      textSecondary: PamoraColors.lightTextSecondary,
    );

    return _build(colorScheme: colorScheme, textTheme: textTheme, background: PamoraColors.lightBackground);
  }

  static ThemeData _build({
    required ColorScheme colorScheme,
    required TextTheme textTheme,
    required Color background,
  }) {
    final isDark = colorScheme.brightness == Brightness.dark;
    final borderColor = colorScheme.outline;

    return ThemeData(
      useMaterial3: true,
      brightness: colorScheme.brightness,
      colorScheme: colorScheme,
      scaffoldBackgroundColor: background,
      canvasColor: background,
      textTheme: textTheme,
      splashFactory: InkSparkle.splashFactory,
      dividerTheme: DividerThemeData(color: borderColor, thickness: 1, space: 1),
      appBarTheme: AppBarTheme(
        backgroundColor: background,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        centerTitle: false,
        titleTextStyle: textTheme.headlineSmall,
        iconTheme: IconThemeData(color: colorScheme.onSurface),
      ),
      cardTheme: CardThemeData(
        color: colorScheme.surfaceContainerHighest,
        elevation: 0,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(
          borderRadius: PamoraRadius.largeRadius,
          side: BorderSide(color: borderColor.withValues(alpha: isDark ? 0.6 : 1)),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: colorScheme.primary,
          foregroundColor: colorScheme.onPrimary,
          disabledBackgroundColor: isDark ? PamoraColors.darkDisabled : PamoraColors.lightDisabled,
          minimumSize: const Size.fromHeight(52),
          textStyle: textTheme.labelLarge?.copyWith(color: colorScheme.onPrimary, fontSize: 16),
          shape: RoundedRectangleBorder(borderRadius: PamoraRadius.mediumRadius),
          elevation: 0,
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: colorScheme.onSurface,
          minimumSize: const Size.fromHeight(52),
          side: BorderSide(color: borderColor),
          textStyle: textTheme.labelLarge?.copyWith(fontSize: 16),
          shape: RoundedRectangleBorder(borderRadius: PamoraRadius.mediumRadius),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: colorScheme.primary,
          textStyle: textTheme.labelLarge,
          shape: RoundedRectangleBorder(borderRadius: PamoraRadius.mediumRadius),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: colorScheme.surfaceContainerHighest,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        hintStyle: textTheme.bodyLarge?.copyWith(color: colorScheme.onSurfaceVariant),
        labelStyle: textTheme.bodyMedium,
        errorStyle: textTheme.bodySmall?.copyWith(color: colorScheme.error),
        border: OutlineInputBorder(
          borderRadius: PamoraRadius.mediumRadius,
          borderSide: BorderSide(color: borderColor),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: PamoraRadius.mediumRadius,
          borderSide: BorderSide(color: borderColor),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: PamoraRadius.mediumRadius,
          borderSide: BorderSide(color: colorScheme.primary, width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: PamoraRadius.mediumRadius,
          borderSide: BorderSide(color: colorScheme.error),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: PamoraRadius.mediumRadius,
          borderSide: BorderSide(color: colorScheme.error, width: 1.5),
        ),
      ),
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: colorScheme.surface,
        indicatorColor: colorScheme.primary.withValues(alpha: 0.16),
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        height: 68,
        labelTextStyle: WidgetStateProperty.resolveWith((states) {
          final selected = states.contains(WidgetState.selected);
          return textTheme.labelMedium?.copyWith(
            color: selected ? colorScheme.primary : colorScheme.onSurfaceVariant,
            fontWeight: selected ? FontWeight.w600 : FontWeight.w500,
          );
        }),
        iconTheme: WidgetStateProperty.resolveWith((states) {
          final selected = states.contains(WidgetState.selected);
          return IconThemeData(color: selected ? colorScheme.primary : colorScheme.onSurfaceVariant);
        }),
      ),
      snackBarTheme: SnackBarThemeData(
        backgroundColor: colorScheme.surfaceContainerHighest,
        contentTextStyle: textTheme.bodyMedium,
        shape: RoundedRectangleBorder(borderRadius: PamoraRadius.mediumRadius),
        behavior: SnackBarBehavior.floating,
      ),
      bottomSheetTheme: BottomSheetThemeData(
        backgroundColor: colorScheme.surface,
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(PamoraRadius.xlarge)),
        ),
      ),
      progressIndicatorTheme: ProgressIndicatorThemeData(color: colorScheme.primary),
      dialogTheme: DialogThemeData(
        backgroundColor: colorScheme.surfaceContainerHighest,
        shape: RoundedRectangleBorder(borderRadius: PamoraRadius.largeRadius),
      ),
    );
  }
}
