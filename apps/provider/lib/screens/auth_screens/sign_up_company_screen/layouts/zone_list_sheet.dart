import 'package:fixit_provider/screens/auth_screens/sign_up_company_screen/layouts/zone_list_layout.dart';
import 'package:flutter/cupertino.dart';

import '../../../../config.dart';

class ZoneBottomSheet extends StatelessWidget {
  final bool isAddLocation;
  const ZoneBottomSheet({super.key, this.isAddLocation = false});

  @override
  Widget build(BuildContext context) {
    return StatefulBuilder(builder: (context, setState) {
      return Consumer2<SignUpCompanyProvider, CompanyDetailProvider>(
          builder: (context1, value, company, child) {
        return StatefulBuilder(builder: (context1, setState) {
          return Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                  padding: EdgeInsets.only(
                      bottom: MediaQuery.of(context).viewInsets.bottom),
                  child: Stack(alignment: Alignment.bottomCenter, children: [
                    Column(children: [
                      Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(language(context, translations!.selectZone),
                                style: appCss.dmDenseMedium18.textColor(
                                    appColor(context).appTheme.darkText)),
                            const Icon(CupertinoIcons.multiply)
                                .inkWell(onTap: () => route.pop(context))
                          ]).paddingSymmetric(horizontal: Insets.i20),
                      const VSpace(Sizes.s20),
                      if (!isAddLocation)
                        if (value.zonesList.isEmpty) const CommonEmpty(),
                      if (!isAddLocation)
                        if (value.zonesList.isNotEmpty)
                          ...value.zonesList.asMap().entries.map((e) =>
                              ZoneListTileLayout(
                                  data: e.value,
                                  isContain: value.zoneSelect.contains(e.value),
                                  onTap: () =>
                                      value.onZoneSelect(e.value)).inkWell(
                                  onTap: () => value.onZoneSelect(e.value))),
                      if (isAddLocation)
                        if (company.zonesList.isEmpty) const CommonEmpty(),
                      if (isAddLocation)
                        if (company.zonesList.isNotEmpty)
                          ...company.zonesList.asMap().entries.map((e) =>
                              ZoneListTileLayout(
                                      data: e.value,
                                      isContain: company.zoneSelect
                                          .where((element) =>
                                              element.id == e.value.id)
                                          .isNotEmpty,
                                      onTap: () =>
                                          company.onZoneSelect(e.value))
                                  /* .backgroundColor(Colors.red) */
                                  .inkWell(
                                      onTap: () =>
                                          company.onZoneSelect(e.value)))
                    ]).paddingSymmetric(vertical: Insets.i20).marginOnly(
                          bottom: Insets.i150,
                        ),
                    Align(
                        alignment: Alignment.bottomCenter,
                        child: BottomSheetButtonCommon(
                                textOne: translations!.clearAll,
                                textTwo: translations!.apply,
                                applyTap: () {
                                  route.pop(context);
                                  //  value.searchService(context, isPop: true);
                                },
                                clearTap: () {
                                  value.zoneSelect = [];
                                  value.notifyListeners();
                                  route.pop(context);
                                })
                            .padding(horizontal: Sizes.s20, bottom: Sizes.s20))
                  ]).bottomSheetExtension(context)),
            ],
          );
        });
      });
    });
  }
}
