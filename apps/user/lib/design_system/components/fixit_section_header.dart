import 'package:flutter/material.dart';

import '../fixit_theme.dart';

class FixitSectionHeader extends StatelessWidget {
  const FixitSectionHeader({
    super.key,
    required this.title,
    this.subtitle,
    this.trailing,
    this.padding,
  });

  final String title;
  final String? subtitle;
  final Widget? trailing;
  final EdgeInsetsGeometry? padding;

  @override
  Widget build(BuildContext context) {
    final theme = context.fixitTheme;
    final colors = theme.colors;
    final spacing = theme.spacing;
    final typography = theme.typography;

    return Padding(
      padding: padding ?? EdgeInsets.symmetric(horizontal: spacing.lg, vertical: spacing.md),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: typography.titleLarge.copyWith(color: colors.textPrimary),
                ),
                if (subtitle != null) ...[
                  SizedBox(height: spacing.xs),
                  Text(
                    subtitle!,
                    style: typography.bodyMedium.copyWith(color: colors.textSecondary),
                  ),
                ],
              ],
            ),
          ),
          if (trailing != null) ...[
            SizedBox(width: spacing.sm),
            DefaultTextStyle(
              style: typography.labelLarge.copyWith(color: colors.brandPrimary),
              child: trailing!,
            ),
          ],
        ],
      ),
    );
  }
}
