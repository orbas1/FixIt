class FixitSpacingTokens {
  const FixitSpacingTokens({
    required this.xxxs,
    required this.xxs,
    required this.xs,
    required this.sm,
    required this.md,
    required this.lg,
    required this.xl,
    required this.xxl,
    required this.xxxl,
  });

  final double xxxs;
  final double xxs;
  final double xs;
  final double sm;
  final double md;
  final double lg;
  final double xl;
  final double xxl;
  final double xxxl;

  const FixitSpacingTokens.base()
      : xxxs = 2,
        xxs = 4,
        xs = 8,
        sm = 12,
        md = 16,
        lg = 20,
        xl = 24,
        xxl = 32,
        xxxl = 40;
}
