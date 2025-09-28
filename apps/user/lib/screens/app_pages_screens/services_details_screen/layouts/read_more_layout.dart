import 'package:flutter_html/flutter_html.dart';

import '../../../../config.dart';

class ReadMoreLayout extends StatefulWidget {
  final String? text;

  const ReadMoreLayout({super.key, this.text});

  @override
  State<ReadMoreLayout> createState() => _ReadMoreLayoutState();
}

class _ReadMoreLayoutState extends State<ReadMoreLayout> {
  bool isExpanded = false;

  @override
  Widget build(BuildContext context) {
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Html(data: widget.text ?? "", style: {
        "body": Style(
            margin: Margins.zero,
            fontFamily: GoogleFonts.dmSans().fontFamily,
            fontWeight: FontWeight.w500,
            color: appColor(context).darkText,
            maxLines: isExpanded ? null : 2,
            textOverflow: TextOverflow.ellipsis)
      }),
      InkWell(
          onTap: () => setState(() => isExpanded = !isExpanded),
          child: Text(
              isExpanded
                  ? language(context, translations!.readLess)
                  : language(context, translations!.readMore),
              style: TextStyle(
                  color: appColor(context).darkText,
                  fontWeight: FontWeight.w700)))
    ]);
  }
}
