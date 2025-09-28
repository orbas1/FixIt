import '../../../config.dart';

class AddNewLocation extends StatelessWidget {
  const AddNewLocation({super.key});
  @override
  Widget build(BuildContext context) {
    return Consumer<NewLocationProvider>(builder: (context1, value, child) {
      return StatefulWrapper(
          onInit: () => Future.delayed(const Duration(milliseconds: 100),
              () => value.getOnInitData(context)),
          child: PopScope(
            canPop: true,
            onPopInvoked: (bool didPop) => value.onBack(context, false),
            child: Scaffold(
                appBar: AppBarCommon(
                    title: value.isEdit
                        ? translations!.editLocation
                        : translations!.addNewLocation,
                    onTap: () => value.onBack(context, true)),
                body: SingleChildScrollView(
                    child: Form(
                  key: value.locationFormKey,
                  child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                        textCommon.dmSensMediumDark14(context,
                            text: translations!.selectCategory),
                        const VSpace(Sizes.s20),
                        Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: value.categoryList
                                .asMap()
                                .entries
                                .map((e) => SelectCategory(
                                    onTap: () => value.onCategory(e.key),
                                    data: e.value,
                                    index: e.key,
                                    selectedIndex: value.selectIndex))
                                .toList()),
                        const LocationTextFieldLayout()
                            .paddingSymmetric(vertical: Insets.i15),
                        const VSpace(Sizes.s35),
                        ButtonCommon(
                            title: value.isEdit
                                ? translations!.updateLocation
                                : translations!.addLocation,
                            onTap: () => value.onAddLocation(context))
                      ])
                      .paddingAll(Insets.i20)
                      .boxShapeExtension(
                          color: appColor(context).appTheme.fieldCardBg,
                          radius: AppRadius.r12)
                      .padding(horizontal: Insets.i20, vertical: Insets.i20),
                ))),
          ));
    });
  }
}
