import 'package:flutter/material.dart';

import '../fixit_theme.dart';
import '../tokens/fixit_color_tokens.dart';

enum FixitButtonVariant { primary, secondary, outline, ghost, destructive }

typedef FixitButtonBuilder = Widget Function(BuildContext context, Widget child);

class FixitButton extends StatelessWidget {
  const FixitButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.variant = FixitButtonVariant.primary,
    this.leadingIcon,
    this.trailingIcon,
    this.fullWidth = false,
    this.loading = false,
    this.semanticLabel,
    this.backgroundColorOverride,
    this.foregroundColorOverride,
  });

  final String label;
  final VoidCallback? onPressed;
  final FixitButtonVariant variant;
  final Widget? leadingIcon;
  final Widget? trailingIcon;
  final bool fullWidth;
  final bool loading;
  final String? semanticLabel;
  final Color? backgroundColorOverride;
  final Color? foregroundColorOverride;

  @override
  Widget build(BuildContext context) {
    final theme = context.fixitTheme;
    final colors = theme.colors;
    final spacing = theme.spacing;
    final radius = theme.radius;
    final typography = theme.typography;

    final ButtonStyle style = ButtonStyle(
      minimumSize: MaterialStateProperty.all(Size(fullWidth ? double.infinity : 0, 48)),
      padding: MaterialStateProperty.all(
        EdgeInsets.symmetric(horizontal: spacing.lg, vertical: spacing.sm),
      ),
      shape: MaterialStateProperty.all(
        RoundedRectangleBorder(borderRadius: BorderRadius.circular(radius.md)),
      ),
      foregroundColor: MaterialStateProperty.resolveWith((states) {
        if (foregroundColorOverride != null) {
          return foregroundColorOverride!;
        }
        if (variant == FixitButtonVariant.primary) {
          return colors.brandOnPrimary;
        }
        if (variant == FixitButtonVariant.secondary) {
          return colors.textInverse;
        }
        if (variant == FixitButtonVariant.destructive) {
          return colors.textInverse;
        }
        if (variant == FixitButtonVariant.outline) {
          return colors.textPrimary;
        }
        return colors.textPrimary.withOpacity(states.contains(MaterialState.disabled) ? 0.4 : 0.8);
      }),
      backgroundColor: MaterialStateProperty.resolveWith((states) {
        if (backgroundColorOverride != null) {
          return backgroundColorOverride!;
        }
        final bool disabled = states.contains(MaterialState.disabled);
        switch (variant) {
          case FixitButtonVariant.primary:
            return disabled ? colors.brandPrimary.withOpacity(0.4) : colors.brandPrimary;
          case FixitButtonVariant.secondary:
            return disabled ? colors.brandAccent.withOpacity(0.3) : colors.brandAccent;
          case FixitButtonVariant.destructive:
            return disabled ? colors.statusCritical.withOpacity(0.3) : colors.statusCritical;
          case FixitButtonVariant.outline:
            return Colors.transparent;
          case FixitButtonVariant.ghost:
            return Colors.transparent;
        }
      }),
      overlayColor: MaterialStateProperty.resolveWith((states) {
        if (states.contains(MaterialState.hovered) || states.contains(MaterialState.pressed)) {
          return colors.brandPrimary.withOpacity(0.1);
        }
        return null;
      }),
      side: MaterialStateProperty.resolveWith((states) {
        if (variant == FixitButtonVariant.outline) {
          return BorderSide(color: colors.border, width: 1.2);
        }
        if (variant == FixitButtonVariant.ghost) {
          return BorderSide(color: Colors.transparent);
        }
        return BorderSide.none;
      }),
      elevation: MaterialStateProperty.all(0),
      textStyle: MaterialStateProperty.all(typography.labelLarge),
    );

    final bool isDisabled = onPressed == null;
    final child = _buildContent(context, colors);

    return Semantics(
      button: true,
      enabled: !isDisabled,
      label: semanticLabel ?? label,
      child: ConstrainedBox(
        constraints: BoxConstraints(minWidth: fullWidth ? double.infinity : 0),
        child: ElevatedButton(
          onPressed: loading ? null : onPressed,
          style: style,
          child: child,
        ),
      ),
    );
  }

  Widget _buildContent(BuildContext context, FixitColorTokens colors) {
    final theme = context.fixitTheme;
    final spacing = theme.spacing;
    final textColor = _foregroundColor(theme.colors);

    final textWidget = AnimatedSwitcher(
      duration: theme.motion.sm,
      child: loading
          ? SizedBox(
              key: const ValueKey('loader'),
              height: 20,
              width: 20,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                valueColor: AlwaysStoppedAnimation<Color>(textColor.withOpacity(0.9)),
              ),
            )
          : Text(
              label,
              key: const ValueKey('label'),
              style: theme.typography.labelLarge.copyWith(color: textColor),
            ),
    );

    final hasIcon = leadingIcon != null || trailingIcon != null;

    return Row(
      mainAxisSize: MainAxisSize.min,
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        if (leadingIcon != null) ...[
          IconTheme.merge(
            data: IconThemeData(color: textColor, size: 18),
            child: leadingIcon!,
          ),
          SizedBox(width: spacing.xs),
        ],
        Flexible(child: textWidget),
        if (trailingIcon != null && !loading) ...[
          SizedBox(width: spacing.xs),
          IconTheme.merge(
            data: IconThemeData(color: textColor, size: 18),
            child: trailingIcon!,
          ),
        ] else if (hasIcon && loading)
          SizedBox(width: spacing.xs),
      ],
    );
  }

  Color _foregroundColor(FixitColorTokens colors) {
    if (foregroundColorOverride != null) {
      return foregroundColorOverride!;
    }
    switch (variant) {
      case FixitButtonVariant.primary:
        return colors.brandOnPrimary;
      case FixitButtonVariant.secondary:
        return colors.textInverse;
      case FixitButtonVariant.destructive:
        return colors.textInverse;
      case FixitButtonVariant.outline:
        return colors.textPrimary;
      case FixitButtonVariant.ghost:
        return colors.textPrimary.withOpacity(0.8);
    }
  }
}
