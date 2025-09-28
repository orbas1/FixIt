import 'package:intl/intl.dart';

import '../../../config.dart';
import '../../../providers/app_pages_provider/service_add_ons_provider.dart';

class ServiceAddOnList extends StatelessWidget {
  const ServiceAddOnList({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<ServiceAddOnsProvider>(builder: (context, service, child) {
      return Scaffold(
          appBar: ActionAppBar(title: translations?.serviceAddOns, actions: [
            CommonArrow(
                arrow: eSvgAssets.add,
                onTap: () {
                  service.clearData();
                  route.pushNamed(context, routeName.addServiceAddons);
                }).paddingSymmetric(horizontal: Insets.i20)
          ]),
          body: serviceAddOns.isEmpty
              ? const CommonEmpty()
              : ListView.builder(
                  padding: const EdgeInsets.symmetric(horizontal: Insets.i20),
                  itemCount: serviceAddOns.length,
                  itemBuilder: (context, index) {
                    final item = serviceAddOns[index];
                    return Column(children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                SizedBox(
                                  height: Sizes.s60,
                                  width: Sizes.s60,
                                  child: Image.network(
                                    item.media?.first.originalUrl ?? '',
                                    height: Sizes.s60,
                                    width: Sizes.s60,
                                    fit: BoxFit.cover,
                                  ),
                                ),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        item.title ?? '',
                                        overflow: TextOverflow.ellipsis,
                                        style: appCss.dmDenseMedium16.textColor(
                                          appColor(context).appTheme.darkText,
                                        ),
                                      ),
                                      const VSpace(Sizes.s3),
                                      Text(
                                        symbolPosition
                                            ? "${getSymbol(context)}${item.price}"
                                            : "${item.price}${getSymbol(context)}",
                                        style: appCss.dmDenseBold16.textColor(
                                          appColor(context).appTheme.online,
                                        ),
                                      ),
                                    ],
                                  ).padding(
                                      top: Insets.i5, horizontal: Insets.i12),
                                ),
                              ],
                            ),
                          )
                        ],
                      ).paddingOnly(top: Insets.i17, bottom: 15),
                      const DottedLines(),
                      Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              DateFormat("dd-MMM-yyyy").format(
                                DateTime.parse(item.createdAt ?? ""),
                              ),
                              style: appCss.dmDenseRegular16.textColor(
                                appColor(context).appTheme.lightText,
                              ),
                            ),
                            Row(mainAxisSize: MainAxisSize.min, children: [
                              CommonArrow(
                                arrow: eSvgAssets.edit,
                                onTap: () async {
                                  service.isEdit = true;
                                  await service.getServiceAddOnsId(
                                      context, item.id);

                                  route.pushNamed(
                                      context, routeName.addServiceAddons,
                                      arg: {
                                        'isEdit': true,
                                        "data": service.serviceAddOnsGet
                                      }).then((e) => service.notifyListeners());
                                },
                                isThirteen: true,
                              ),
                              const HSpace(Sizes.s10),
                              CommonArrow(
                                  arrow: eSvgAssets.delete,
                                  color: appColor(context)
                                      .appTheme
                                      .red
                                      .withOpacity(0.1),
                                  svgColor: appColor(context).appTheme.red,
                                  onTap: () {
                                    service.onAddOnsDelete(context, item.id);
                                  },
                                  isThirteen: true)
                            ])
                          ]).paddingOnly(top: Insets.i14, bottom: Insets.i17)
                    ])
                        .paddingSymmetric(horizontal: Insets.i15)
                        .boxBorderExtension(context,
                            isShadow: true,
                            bColor: appColor(context).appTheme.stroke)
                        .paddingOnly(bottom: Insets.i15)
                        .inkWell(onTap: () {});
                  }));
    });
  }
}
