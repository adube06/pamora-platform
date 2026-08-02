import 'package:flutter/material.dart';

/// Pamora Design System v1 color tokens.
///
/// Values must stay in sync with `resources/css/app.css` in the Laravel app
/// (ADR-018: tokens before components; ADR-022: one brand, many platforms).
class PamoraColors {
  PamoraColors._();

  // Dark palette (`:root.dark` in app.css) — the app's default theme.
  static const Color darkPrimary = Color(0xFF6366F1);
  static const Color darkPrimaryHover = Color(0xFF818CF8);
  static const Color darkSecondary = Color(0xFF2DD4BF);
  static const Color darkSecondaryHover = Color(0xFF5EEAD4);
  static const Color darkSuccess = Color(0xFF4ADE80);
  static const Color darkWarning = Color(0xFFFBBF24);
  static const Color darkError = Color(0xFFF87171);
  static const Color darkInfo = Color(0xFF60A5FA);
  static const Color darkSurface = Color(0xFF18181B);
  static const Color darkBackground = Color(0xFF09090B);
  static const Color darkBorder = Color(0xFF27272A);
  static const Color darkTextPrimary = Color(0xFFF4F4F5);
  static const Color darkTextSecondary = Color(0xFFA1A1AA);
  static const Color darkDisabled = Color(0xFF52525B);

  // A step above `darkSurface`, used for cards floating on top of the
  // background so elevation reads as a lighter layer rather than a shadow
  // (shadows barely show on near-black backgrounds).
  static const Color darkSurfaceRaised = Color(0xFF222226);

  // Light palette (`:root.light` in app.css) — built for a future theme
  // toggle; not exposed as the app default per the user's explicit dark ask.
  static const Color lightPrimary = Color(0xFF4F46E5);
  static const Color lightPrimaryHover = Color(0xFF4338CA);
  static const Color lightSecondary = Color(0xFF0D9488);
  static const Color lightSecondaryHover = Color(0xFF0F766E);
  static const Color lightSuccess = Color(0xFF16A34A);
  static const Color lightWarning = Color(0xFFD97706);
  static const Color lightError = Color(0xFFDC2626);
  static const Color lightInfo = Color(0xFF2563EB);
  static const Color lightSurface = Color(0xFFFFFFFF);
  static const Color lightBackground = Color(0xFFF9FAFB);
  static const Color lightBorder = Color(0xFFE5E7EB);
  static const Color lightTextPrimary = Color(0xFF111827);
  static const Color lightTextSecondary = Color(0xFF6B7280);
  static const Color lightDisabled = Color(0xFF9CA3AF);

  /// Primary → secondary accent gradient used sparingly on hero surfaces,
  /// primary buttons, and progress indicators.
  static const LinearGradient heroGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [darkPrimary, darkSecondary],
  );
}
