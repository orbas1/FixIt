class FixitMotionTokens {
  const FixitMotionTokens({
    required this.xs,
    required this.sm,
    required this.md,
    required this.lg,
  });

  final Duration xs;
  final Duration sm;
  final Duration md;
  final Duration lg;

  const FixitMotionTokens.base()
      : xs = const Duration(milliseconds: 80),
        sm = const Duration(milliseconds: 150),
        md = const Duration(milliseconds: 220),
        lg = const Duration(milliseconds: 320);
}
