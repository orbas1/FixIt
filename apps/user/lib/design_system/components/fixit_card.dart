import 'package:flutter/material.dart';

import '../fixit_theme.dart';

class FixitCard extends StatelessWidget {
  const FixitCard({
    super.key,
    required this.child,
    this.title,
    this.subtitle,
    this.leading,
    this.actions,
    this.padding,
    this.crossAxisAlignment = CrossAxisAlignment.start,
    this.onTap,
  });

  final Widget child;
  final String? title;
  final String? subtitle;
  final Widget? leading;
  final List<Widget>? actions;
  final EdgeInsetsGeometry? padding;
  final CrossAxisAlignment crossAxisAlignment;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final theme = context.fixitTheme;
    final colors = theme.colors;
    final spacing = theme.spacing;
    final radius = theme.radius;
    final typography = theme.typography;

    final EdgeInsets resolvedPadding = padding ??
        EdgeInsets.symmetric(
          horizontal: spacing.lg,
          vertical: spacing.md,
        );

    final content = Column(
      crossAxisAlignment: crossAxisAlignment,
      mainAxisSize: MainAxisSize.min,
      children: [
        if (title != null || subtitle != null || leading != null || (actions?.isNotEmpty ?? false))
          Padding(
            padding: EdgeInsets.only(bottom: spacing.md),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                if (leading != null) ...[
                  Container(
                    decoration: BoxDecoration(
                      color: colors.brandPrimary.withOpacity(0.08),
                      borderRadius: BorderRadius.circular(radius.sm),
                    ),
                    padding: EdgeInsets.all(spacing.xs),
                    margin: EdgeInsets.only(right: spacing.sm),
                    child: leading,
                  ),
                ],
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (title != null)
                        Text(
                          title!,
                          style: typography.titleMedium.copyWith(color: colors.textPrimary),
                        ),
                      if (subtitle != null) ...[
                        SizedBox(height: spacing.xxs),
                        Text(
                          subtitle!,
                          style: typography.bodySmall.copyWith(color: colors.textTertiary),
                        ),
                      ],
                    ],
                  ),
                ),
                if (actions != null && actions!.isNotEmpty)
                  Wrap(
                    spacing: spacing.xs,
                    children: actions!,
                  ),
              ],
            ),
          ),
        child,
      ],
    );

    final decoratedCard = AnimatedContainer(
      duration: theme.motion.sm,
      decoration: BoxDecoration(
        color: colors.surface,
        borderRadius: BorderRadius.circular(radius.lg),
        boxShadow: [
          BoxShadow(
            color: colors.shadow,
            blurRadius: 12,
            offset: const Offset(0, 8),
          ),
        ],
        border: Border.all(color: colors.border),
      ),
      padding: resolvedPadding,
      child: content,
    );

    if (onTap != null) {
      return Semantics(
        button: true,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(radius.lg),
          child: decoratedCard,
        ),
      );
    }

    return decoratedCard;
  }
}
