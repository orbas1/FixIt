class FixitRadiusTokens {
  const FixitRadiusTokens({
    required this.xs,
    required this.sm,
    required this.md,
    required this.lg,
    required this.round,
  });

  final double xs;
  final double sm;
  final double md;
  final double lg;
  final double round;

  const FixitRadiusTokens.base()
      : xs = 4,
        sm = 8,
        md = 12,
        lg = 20,
        round = 999;
}
