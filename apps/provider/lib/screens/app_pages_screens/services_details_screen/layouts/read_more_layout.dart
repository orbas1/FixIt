import '../../../../config.dart';

class ReadMoreLayout extends StatelessWidget {
  final String? text;
  final Color? color;
  const ReadMoreLayout({super.key, this.text, this.color});

  @override
  Widget build(BuildContext context) {
    return ReadMoreText(text ?? '',
        trimLines: 2,
        style: TextStyle(
            color: color ?? appColor(context).appTheme.darkText,
            fontFamily: GoogleFonts.dmSans().fontFamily,
            fontWeight: FontWeight.w500),
        colorClickableText: appColor(context).appTheme.darkText,
        trimMode: TrimMode.Line,
        lessStyle: TextStyle(
            color: appColor(context).appTheme.darkText,
            fontFamily: GoogleFonts.dmSans().fontFamily,
            fontWeight: FontWeight.w700),
        moreStyle: TextStyle(
            color: appColor(context).appTheme.darkText,
            fontFamily: GoogleFonts.dmSans().fontFamily,
            fontWeight: FontWeight.w700),
        trimCollapsedText: language(context, translations!.readMore),
        trimExpandedText: language(context, translations!.readLess));
  }
}
