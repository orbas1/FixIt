import 'package:flutter/material.dart';

import '../../design_system/fixit_theme.dart';
import '../../design_system/tokens/fixit_color_tokens.dart';

enum ThemeType { light, dark }

class AppTheme {
  AppTheme._(this._designSystem);

  final FixitDesignSystem _designSystem;

  static ThemeType defaultTheme = ThemeType.light;

  bool get isDark => _designSystem.brightness == Brightness.dark;
  FixitColorTokens get _colors => _designSystem.colors;

  Color get primary => _colors.brandPrimary;
  Color get darkText => _colors.textPrimary;
  Color get lightText => _colors.textTertiary;
  Color get whiteBg => _colors.backgroundSecondary;
  Color get stroke => _colors.border;
  Color get fieldCardBg => _colors.surfaceHigh;
  Color get trans => Colors.transparent;
  Color get green => _colors.statusSuccess;
  Color get greenColor => _colors.brandAccent;
  Color get online => _colors.statusSuccess;
  Color get red => _colors.statusCritical;
  Color get whiteColor => _colors.textInverse;
  Color get rateColor => _colors.rating;
  Color get pending => _colors.statusPending;
  Color get accepted => _colors.statusAccepted;
  Color get ongoing => _colors.statusOngoing;
  Color get cartBottomBg => _colors.surfaceHigh;
  Color get skeletonColor => _colors.skeletonBase;

  ThemeData get themeData => _designSystem.toThemeData();

  FixitThemeExtension get tokens => FixitThemeExtension(
        colors: _designSystem.colors,
        spacing: _designSystem.spacing,
        radius: _designSystem.radius,
        motion: _designSystem.motion,
        typography: _designSystem.typography,
      );

  factory AppTheme.fromType(ThemeType type) {
    switch (type) {
      case ThemeType.light:
        return AppTheme._(FixitDesignSystem.light());
      case ThemeType.dark:
        return AppTheme._(FixitDesignSystem.dark());
    }
  }
}
