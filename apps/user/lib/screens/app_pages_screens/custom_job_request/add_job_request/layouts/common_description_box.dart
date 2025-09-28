import '../../../../../config.dart';

class CommonDescriptionBox extends StatelessWidget {
  final FocusNode? focusNode;
  final TextEditingController? description;
  final Color? color;
  const CommonDescriptionBox(
      {super.key, this.focusNode, this.description, this.color});

  @override
  Widget build(BuildContext context) {
    return Stack(children: [
      TextFieldCommon(
              focusNode: focusNode,
              isNumber: true,
              validator: (name) => validation.commonValidation(
                  context, translations!.pleaseEnterDesc),
              controller: description,
              hintText: translations!.enterDetails ??
                  language(context, appFonts.enterDetails),
              maxLines: 3,
              fillColor: color,
              minLines: 3,
              isMaxLine: true)
          .paddingSymmetric(horizontal: Insets.i20),
      SvgPicture.asset(eSvgAssets.details,
              fit: BoxFit.scaleDown,
              colorFilter: ColorFilter.mode(
                  !focusNode!.hasFocus
                      ? description!.text.isNotEmpty
                          ? appColor(context).darkText
                          : appColor(context).lightText
                      : appColor(context).darkText,
                  BlendMode.srcIn))
          .paddingOnly(
              left: rtl(context) ? 0 : Insets.i35,
              right: rtl(context) ? Insets.i35 : 0,
              top: Insets.i13)
    ]);
  }
}
