import 'package:flutter/material.dart';

import 'tokens/fixit_color_tokens.dart';
import 'tokens/fixit_motion_tokens.dart';
import 'tokens/fixit_radius_tokens.dart';
import 'tokens/fixit_spacing_tokens.dart';
import 'tokens/fixit_typography_tokens.dart';

class FixitDesignSystem {
  const FixitDesignSystem({
    required this.brightness,
    required this.colors,
    required this.typography,
    required this.spacing,
    required this.radius,
    required this.motion,
  });

  final Brightness brightness;
  final FixitColorTokens colors;
  final FixitTypographyTokens typography;
  final FixitSpacingTokens spacing;
  final FixitRadiusTokens radius;
  final FixitMotionTokens motion;

  ThemeData toThemeData() {
    final colorScheme = colors.toColorScheme(brightness);
    final textTheme = typography.toTextTheme();
    final extension = FixitThemeExtension(
      colors: colors,
      spacing: spacing,
      radius: radius,
      motion: motion,
      typography: typography,
    );

    final baseTheme = ThemeData(
      useMaterial3: true,
      brightness: brightness,
      colorScheme: colorScheme,
      textTheme: textTheme,
      scaffoldBackgroundColor: colors.backgroundPrimary,
      canvasColor: colors.surface,
      extensions: [extension],
      fontFamily: textTheme.bodyMedium?.fontFamily,
    );

    final buttonStyle = ElevatedButton.styleFrom(
      elevation: 0,
      textStyle: textTheme.labelLarge,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(radius.md),
      ),
      padding: EdgeInsets.symmetric(
        vertical: spacing.sm,
        horizontal: spacing.lg,
      ),
    );

    final outlinedButtonStyle = OutlinedButton.styleFrom(
      textStyle: textTheme.labelLarge,
      side: BorderSide(color: colors.border),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(radius.md),
      ),
      padding: EdgeInsets.symmetric(
        vertical: spacing.sm,
        horizontal: spacing.lg,
      ),
    );

    final textButtonStyle = TextButton.styleFrom(
      textStyle: textTheme.labelLarge,
      foregroundColor: colors.brandPrimary,
      padding: EdgeInsets.symmetric(
        vertical: spacing.sm,
        horizontal: spacing.xs,
      ),
    );

    return baseTheme.copyWith(
      elevatedButtonTheme: ElevatedButtonThemeData(style: buttonStyle),
      outlinedButtonTheme: OutlinedButtonThemeData(style: outlinedButtonStyle),
      textButtonTheme: TextButtonThemeData(style: textButtonStyle),
      appBarTheme: AppBarTheme(
        backgroundColor: colors.surface,
        foregroundColor: colors.textPrimary,
        elevation: 0,
        titleTextStyle: textTheme.titleMedium,
        centerTitle: false,
      ),
      cardTheme: CardTheme(
        color: colors.surface,
        shadowColor: colors.shadow,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(radius.md),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: colors.surfaceHigh,
        contentPadding: EdgeInsets.all(spacing.md),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(radius.md),
          borderSide: BorderSide(color: colors.border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(radius.md),
          borderSide: BorderSide(color: colors.brandPrimary, width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(radius.md),
          borderSide: BorderSide(color: colors.statusCritical),
        ),
      ),
      chipTheme: ChipThemeData(
        backgroundColor: colors.surfaceHigh,
        selectedColor: colors.brandPrimary.withOpacity(0.12),
        disabledColor: colors.surfaceDisabled,
        padding: EdgeInsets.symmetric(
          horizontal: spacing.sm,
          vertical: spacing.xs,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(radius.lg),
        ),
        labelStyle: textTheme.labelLarge!,
        secondaryLabelStyle: textTheme.labelLarge!,
      ),
      dialogTheme: DialogTheme(
        backgroundColor: colors.surface,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(radius.lg),
        ),
      ),
      dividerTheme: DividerThemeData(
        color: colors.border,
        space: spacing.md,
        thickness: 1,
      ),
      scrollbarTheme: ScrollbarThemeData(
        radius: Radius.circular(radius.sm),
        thumbVisibility: MaterialStateProperty.all(true),
        thickness: MaterialStateProperty.all(4),
      ),
    );
  }

  static FixitDesignSystem light() {
    return FixitDesignSystem(
      brightness: Brightness.light,
      colors: fixitLightColors,
      typography: buildTypographyTokens(brightness: Brightness.light),
      spacing: const FixitSpacingTokens.base(),
      radius: const FixitRadiusTokens.base(),
      motion: const FixitMotionTokens.base(),
    );
  }

  static FixitDesignSystem dark() {
    return FixitDesignSystem(
      brightness: Brightness.dark,
      colors: fixitDarkColors,
      typography: buildTypographyTokens(brightness: Brightness.dark),
      spacing: const FixitSpacingTokens.base(),
      radius: const FixitRadiusTokens.base(),
      motion: const FixitMotionTokens.base(),
    );
  }
}

class FixitThemeExtension extends ThemeExtension<FixitThemeExtension> {
  const FixitThemeExtension({
    required this.colors,
    required this.spacing,
    required this.radius,
    required this.motion,
    required this.typography,
  });

  final FixitColorTokens colors;
  final FixitSpacingTokens spacing;
  final FixitRadiusTokens radius;
  final FixitMotionTokens motion;
  final FixitTypographyTokens typography;

  @override
  FixitThemeExtension copyWith({
    FixitColorTokens? colors,
    FixitSpacingTokens? spacing,
    FixitRadiusTokens? radius,
    FixitMotionTokens? motion,
    FixitTypographyTokens? typography,
  }) {
    return FixitThemeExtension(
      colors: colors ?? this.colors,
      spacing: spacing ?? this.spacing,
      radius: radius ?? this.radius,
      motion: motion ?? this.motion,
      typography: typography ?? this.typography,
    );
  }

  @override
  ThemeExtension<FixitThemeExtension> lerp(ThemeExtension<FixitThemeExtension>? other, double t) {
    if (other is! FixitThemeExtension) {
      return this;
    }

    Color lerpColor(Color a, Color b) => Color.lerp(a, b, t)!;

    FixitColorTokens lerpColors(FixitColorTokens a, FixitColorTokens b) {
      return FixitColorTokens(
        brandPrimary: lerpColor(a.brandPrimary, b.brandPrimary),
        brandOnPrimary: lerpColor(a.brandOnPrimary, b.brandOnPrimary),
        brandSecondary: lerpColor(a.brandSecondary, b.brandSecondary),
        brandOnSecondary: lerpColor(a.brandOnSecondary, b.brandOnSecondary),
        brandAccent: lerpColor(a.brandAccent, b.brandAccent),
        brandMuted: lerpColor(a.brandMuted, b.brandMuted),
        backgroundPrimary: lerpColor(a.backgroundPrimary, b.backgroundPrimary),
        backgroundSecondary: lerpColor(a.backgroundSecondary, b.backgroundSecondary),
        surface: lerpColor(a.surface, b.surface),
        surfaceHigh: lerpColor(a.surfaceHigh, b.surfaceHigh),
        surfaceInverse: lerpColor(a.surfaceInverse, b.surfaceInverse),
        surfaceDisabled: lerpColor(a.surfaceDisabled, b.surfaceDisabled),
        border: lerpColor(a.border, b.border),
        shadow: lerpColor(a.shadow, b.shadow),
        textPrimary: lerpColor(a.textPrimary, b.textPrimary),
        textSecondary: lerpColor(a.textSecondary, b.textSecondary),
        textTertiary: lerpColor(a.textTertiary, b.textTertiary),
        textInverse: lerpColor(a.textInverse, b.textInverse),
        iconPrimary: lerpColor(a.iconPrimary, b.iconPrimary),
        iconSecondary: lerpColor(a.iconSecondary, b.iconSecondary),
        statusSuccess: lerpColor(a.statusSuccess, b.statusSuccess),
        statusWarning: lerpColor(a.statusWarning, b.statusWarning),
        statusCritical: lerpColor(a.statusCritical, b.statusCritical),
        statusInfo: lerpColor(a.statusInfo, b.statusInfo),
        statusPending: lerpColor(a.statusPending, b.statusPending),
        statusAccepted: lerpColor(a.statusAccepted, b.statusAccepted),
        statusOngoing: lerpColor(a.statusOngoing, b.statusOngoing),
        rating: lerpColor(a.rating, b.rating),
        skeletonBase: lerpColor(a.skeletonBase, b.skeletonBase),
        skeletonHighlight: lerpColor(a.skeletonHighlight, b.skeletonHighlight),
      );
    }

    return FixitThemeExtension(
      colors: lerpColors(colors, other.colors),
      spacing: spacing,
      radius: radius,
      motion: motion,
      typography: typography,
    );
  }
}

extension BuildContextDesignSystem on BuildContext {
  FixitThemeExtension get fixitTheme =>
      Theme.of(this).extension<FixitThemeExtension>() ??
      FixitThemeExtension(
        colors: fixitLightColors,
        spacing: const FixitSpacingTokens.base(),
        radius: const FixitRadiusTokens.base(),
        motion: const FixitMotionTokens.base(),
        typography: buildTypographyTokens(brightness: Brightness.light),
      );
}
