import 'package:flutter/material.dart';

/// Core color tokens describing the Fixit design language.
///
/// The palette is deliberately verbose so that both the declarative theming
/// layer (Material [ThemeData]) and the legacy `AppTheme` compatibility layer
/// can reference a single source of truth.
class FixitColorTokens {
  const FixitColorTokens({
    required this.brandPrimary,
    required this.brandOnPrimary,
    required this.brandSecondary,
    required this.brandOnSecondary,
    required this.brandAccent,
    required this.brandMuted,
    required this.backgroundPrimary,
    required this.backgroundSecondary,
    required this.surface,
    required this.surfaceHigh,
    required this.surfaceInverse,
    required this.surfaceDisabled,
    required this.border,
    required this.shadow,
    required this.textPrimary,
    required this.textSecondary,
    required this.textTertiary,
    required this.textInverse,
    required this.iconPrimary,
    required this.iconSecondary,
    required this.statusSuccess,
    required this.statusWarning,
    required this.statusCritical,
    required this.statusInfo,
    required this.statusPending,
    required this.statusAccepted,
    required this.statusOngoing,
    required this.rating,
    required this.skeletonBase,
    required this.skeletonHighlight,
  });

  final Color brandPrimary;
  final Color brandOnPrimary;
  final Color brandSecondary;
  final Color brandOnSecondary;
  final Color brandAccent;
  final Color brandMuted;
  final Color backgroundPrimary;
  final Color backgroundSecondary;
  final Color surface;
  final Color surfaceHigh;
  final Color surfaceInverse;
  final Color surfaceDisabled;
  final Color border;
  final Color shadow;
  final Color textPrimary;
  final Color textSecondary;
  final Color textTertiary;
  final Color textInverse;
  final Color iconPrimary;
  final Color iconSecondary;
  final Color statusSuccess;
  final Color statusWarning;
  final Color statusCritical;
  final Color statusInfo;
  final Color statusPending;
  final Color statusAccepted;
  final Color statusOngoing;
  final Color rating;
  final Color skeletonBase;
  final Color skeletonHighlight;

  /// Creates a Material color scheme for the active brightness.
  ColorScheme toColorScheme(Brightness brightness) {
    return ColorScheme(
      brightness: brightness,
      primary: brandPrimary,
      onPrimary: brandOnPrimary,
      secondary: brandSecondary,
      onSecondary: brandOnSecondary,
      tertiary: brandAccent,
      onTertiary: brandOnSecondary,
      error: statusCritical,
      onError: textInverse,
      background: backgroundPrimary,
      onBackground: textPrimary,
      surface: surface,
      onSurface: textPrimary,
      surfaceTint: brandPrimary,
      inverseSurface: surfaceInverse,
      onInverseSurface: textInverse,
      outline: border,
      shadow: shadow,
      surfaceContainerHighest: surfaceHigh,
      surfaceVariant: surface,
      inversePrimary: brandOnPrimary,
      primaryContainer: surfaceHigh,
      onPrimaryContainer: textPrimary,
      secondaryContainer: surfaceHigh,
      onSecondaryContainer: textPrimary,
      tertiaryContainer: surfaceHigh,
      onTertiaryContainer: textPrimary,
      errorContainer: statusCritical.withOpacity(0.12),
      onErrorContainer: statusCritical,
      outlineVariant: border,
      scrim: Colors.black.withOpacity(0.45),
    );
  }
}

const FixitColorTokens fixitLightColors = FixitColorTokens(
  brandPrimary: Color(0xFF5465FF),
  brandOnPrimary: Colors.white,
  brandSecondary: Color(0xFF2D3A8C),
  brandOnSecondary: Colors.white,
  brandAccent: Color(0xFF00B9AE),
  brandMuted: Color(0xFFB4C1FF),
  backgroundPrimary: Color(0xFFF8F9FB),
  backgroundSecondary: Color(0xFFFFFFFF),
  surface: Color(0xFFFFFFFF),
  surfaceHigh: Color(0xFFF1F3FF),
  surfaceInverse: Color(0xFF1C1F2A),
  surfaceDisabled: Color(0xFFE0E3EB),
  border: Color(0xFFE5E8EA),
  shadow: Color(0x19000000),
  textPrimary: Color(0xFF00162E),
  textSecondary: Color(0xFF44566C),
  textTertiary: Color(0xFF808B97),
  textInverse: Colors.white,
  iconPrimary: Color(0xFF00162E),
  iconSecondary: Color(0xFF5D6B82),
  statusSuccess: Color(0xFF27AF4D),
  statusWarning: Color(0xFFFDB448),
  statusCritical: Color(0xFFFF4B4B),
  statusInfo: Color(0xFF48BFFD),
  statusPending: Color(0xFFFDB448),
  statusAccepted: Color(0xFF48BFFD),
  statusOngoing: Color(0xFFFF7456),
  rating: Color(0xFFFFC412),
  skeletonBase: Color(0xFFEDEDED),
  skeletonHighlight: Color(0xFFF6F7F8),
);

const FixitColorTokens fixitDarkColors = FixitColorTokens(
  brandPrimary: Color(0xFF6F7DFF),
  brandOnPrimary: Colors.white,
  brandSecondary: Color(0xFF7B89FF),
  brandOnSecondary: Colors.white,
  brandAccent: Color(0xFF00D3C4),
  brandMuted: Color(0xFF363F6B),
  backgroundPrimary: Color(0xFF12141F),
  backgroundSecondary: Color(0xFF1A1C28),
  surface: Color(0xFF1A1C28),
  surfaceHigh: Color(0xFF2A2D3A),
  surfaceInverse: Color(0xFFF8F9FB),
  surfaceDisabled: Color(0xFF2F3240),
  border: Color(0xFF3A3D48),
  shadow: Color(0x66000000),
  textPrimary: Color(0xFFF1F1F1),
  textSecondary: Color(0xFFB5B9C3),
  textTertiary: Color(0xFF8C94A5),
  textInverse: Color(0xFF12141F),
  iconPrimary: Color(0xFFF1F1F1),
  iconSecondary: Color(0xFFA5ABBD),
  statusSuccess: Color(0xFF39C46D),
  statusWarning: Color(0xFFFFC866),
  statusCritical: Color(0xFFFF6A6A),
  statusInfo: Color(0xFF62CFFF),
  statusPending: Color(0xFFFFC866),
  statusAccepted: Color(0xFF62CFFF),
  statusOngoing: Color(0xFFFF8B6E),
  rating: Color(0xFFFFC412),
  skeletonBase: Color(0xFF2A2D3A),
  skeletonHighlight: Color(0xFF3A3D48),
);
