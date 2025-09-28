import 'dart:developer';

import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:intl/intl.dart';

import '../../../../config.dart';

class ChatHistoryLayout extends StatelessWidget {
  final dynamic data;
  final List? list;
  final int? index;
  final GestureTapCallback? onTap;

  const ChatHistoryLayout(
      {super.key, this.data, this.list, this.index, this.onTap});

  @override
  Widget build(BuildContext context) {
    log("data:::$data");
    return Column(children: [
      Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(children: [
              ((data['senderId'].toString() == userModel!.id.toString())
                          ? "${data['receiverName']}"
                          : "${data['senderName']}") !=
                      userModel!.id.toString()
                  ? Container(
                      height: Sizes.s45,
                      width: Sizes.s45,
                      decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          image: data['senderImage'] != null
                              ? DecorationImage(
                                  image: NetworkImage(data['senderImage']),
                                  fit: BoxFit.cover)
                              : DecorationImage(
                                  image: AssetImage(eImageAssets.noImageFound3),
                                  fit: BoxFit.cover)))
                  : Container(
                      height: Sizes.s45,
                      width: Sizes.s45,
                      decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          image: data['receiverImage'] != null
                              ? DecorationImage(
                                  image: NetworkImage(data['receiverImage']),
                                  fit: BoxFit.cover)
                              : DecorationImage(
                                  image: AssetImage(eImageAssets.noImageFound3),
                                  fit: BoxFit.cover))),
              const HSpace(Sizes.s10),
              Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(
                    (data as Map<String, dynamic>).containsKey('isOffer')
                        ? (data['senderId'].toString() ==
                                userModel!.id.toString())
                            ? (data['receiverName'] ?? "")
                            : "${data['senderName']} "
                        : (data['senderId'].toString() ==
                                userModel!.id.toString())
                            ? "${data['receiverName']} #${data['bookingNumber']}"
                            : "${data['senderName']} #${data['bookingNumber'] ?? ''}",
                    style: appCss.dmDenseMedium14
                        .textColor(appColor(context).appTheme.darkText)),
                const VSpace(Sizes.s2),
                Text(
                    (data as Map<String, dynamic>).containsKey('isOffer')
                        ? data['messageType'] == MessageType.offer.name
                            ? data['senderId'].toString() ==
                                    userModel!.id.toString()
                                ? "You send the offer"
                                : data['lastMessage']
                            : data['lastMessage']
                        : data['messageType'] == MessageType.offer
                            ? data['senderId'] == userModel!.id
                                ? "You send the offer"
                                : data["lastMessage"]
                            : data['messageType'] == "image"
                                ? data['senderId'] == userModel!.id
                                    ? "\u{1F4F8} You send the image"
                                    : "\u{1F4F8} ${data['senderName']} send you the image"
                                : data['messageType'] == "video"
                                    ? data['senderId'] == userModel!.id
                                        ? "\u{1F4F8} You send the Video"
                                        : "\u{1F4F8} ${data['senderName']} send you the Video"
                                    : data["lastMessage"],
                    style: appCss.dmDenseMedium12
                        .textColor(appColor(context).appTheme.lightText))
              ])
            ]),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                    DateFormat('HH:mm').format(
                        DateTime.fromMillisecondsSinceEpoch(
                            int.parse(data["updateStamp"].toString()))),
                    style: appCss.dmDenseRegular12
                        .textColor(appColor(context).appTheme.lightText)),
                if ((data as Map<String, dynamic>).containsKey('isOffer') &&
                    data['role'] != "serviceman")
                  SvgPicture.asset(eSvgAssets.offer)
              ],
            )
          ]).inkWell(onTap: onTap),
      if (index != list!.length - 1)
        const DividerCommon().paddingSymmetric(vertical: Insets.i15)
    ]);
  }
}
