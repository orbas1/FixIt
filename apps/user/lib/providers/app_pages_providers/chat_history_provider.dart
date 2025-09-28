import 'dart:convert';
import 'dart:developer';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../config.dart';

class ChatHistoryProvider with ChangeNotifier {
  List<QueryDocumentSnapshot> chatHistory = [];
  bool isLoading = true; // Track loading state
  UserModel? userModel;

  /* Future<void> onReady(BuildContext context) async {
    try {
      isLoading = true;
      notifyListeners();

      SharedPreferences preferences = await SharedPreferences.getInstance();
      String? userJson = preferences.getString(session.user);
      if (userJson == null) {
        log("ChatHistoryProvider: No user data in SharedPreferences");
        isLoading = false;
        notifyListeners();
        return;
      }

      userModel = UserModel.fromJson(json.decode(userJson));
      final snapshot = await FirebaseFirestore.instance
          .collection(collectionName.users)
          .doc(userModel!.id.toString())
          .collection(collectionName.chats)
          .get();

      chatHistory = snapshot.docs;
      log("ChatHistoryProvider: Fetched ${chatHistory.length} chats");
    } catch (e, stackTrace) {
      log("ChatHistoryProvider: Error in onReady: $e",
          error: e, stackTrace: stackTrace);
    } finally {
      isLoading = false;
      notifyListeners();
    }
  } */
  Future<void> onReady(BuildContext context) async {
    try {
      isLoading = true;
      notifyListeners();

      SharedPreferences preferences = await SharedPreferences.getInstance();
      String? userJson = preferences.getString(session.user);
      if (userJson == null) {
        log("ChatHistoryProvider: No user data in SharedPreferences");

        notifyListeners();
        return;
      }

      userModel = UserModel.fromJson(json.decode(userJson));

      final snapshot = await FirebaseFirestore.instance
          .collection(collectionName.users)
          .doc(userModel!.id.toString())
          .collection(collectionName.chats)
          .orderBy('updateStamp', descending: true)
          .get();

      chatHistory = snapshot.docs;
      isLoading = false;
      // log("ChatHistoryProvider: Fetched ${chatHistory[0]['bookingNumber']} ");
    } catch (e, stackTrace) {
      log("ChatHistoryProvider: Error in onReady: $e",
          error: e, stackTrace: stackTrace);
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> onClearChat(BuildContext context, TickerProvider sync) async {
    final value = Provider.of<DeleteDialogProvider>(context, listen: false);
    await value.onDeleteDialog(sync, context, eImageAssets.clearChat,
        translations!.clearChat, translations!.areYouClearChat, () async {
      showLoading(context);
      try {
        final snapshot = await FirebaseFirestore.instance
            .collection(collectionName.users)
            .doc(userModel!.id.toString())
            .collection(collectionName.chats)
            .get();

        if (snapshot.docs.isNotEmpty) {
          for (var doc in snapshot.docs) {
            final data = doc.data();
            log("data:::$data");
            final chatWithId =
                userModel!.id.toString() == data['senderId'].toString()
                    ? data['receiverId'].toString()
                    : data['senderId'].toString();
            final bookingId = data['bookingId'].toString();
            log("bookingId:::$bookingId");
            // Delete chat messages
            final chatSnapshot = await FirebaseFirestore.instance
                .collection(collectionName.users)
                .doc(userModel!.id.toString())
                .collection(collectionName.chatWith)
                .doc(chatWithId)
                .collection(collectionName.booking)
                .doc(bookingId)
                .collection(collectionName.chat)
                .get();

            for (var chatDoc in chatSnapshot.docs) {
              await chatDoc.reference.delete();
            }

            // Delete chat document
            await doc.reference.delete();
          }
        }

        chatHistory = [];
        hideLoading(context);
        notifyListeners();
        await onReady(context);
        route.pop(context);
        value.onResetPass(context, language(context, appFonts.hurrayChatDelete),
            language(context, appFonts.okay), () => route.pop(context));
      } catch (e, stackTrace) {
        log("ChatHistoryProvider: Error in onClearChat: $e",
            error: e, stackTrace: stackTrace);
        hideLoading(context);
        notifyListeners();
      }
    });
    value.notifyListeners();
  }

  void onTapOption(int index, BuildContext context, TickerProvider sync) {
    if (index == 1) {
      onClearChat(context, sync);
    } else {
      onReady(context);
      Fluttertoast.showToast(msg: "Refresh...");
    }
    notifyListeners();
  }

  void onRefresh(BuildContext context) {
    onReady(context);
    Fluttertoast.showToast(msg: "${language(context, appFonts.refresh)}...");
  }

  void onChatClick(BuildContext context, QueryDocumentSnapshot data) {
    final mapData = data.data() as Map<String, dynamic>;

    final bool isSender =
        mapData['senderId'].toString() == userModel!.id.toString();

    final chatArgs = {
      "image": mapData['receiverImage'],
      "name": mapData['receiverName'],
      "chatId": mapData['chatId'] ?? "", // fallback if bookingId absent
      "role": mapData['role'],
      "userId": isSender ? mapData["receiverId"] : mapData['senderId'],
      "token": isSender ? mapData["receiverToken"] : mapData['senderToken'],
      "bookingId": mapData['bookingId'] ?? "",
      "bookingNumber": mapData['bookingNumber'] ?? "",
      "phone": isSender
          ? (mapData['receiverPhone'] ?? "")
          : (mapData['senderPhone'] ?? ""),
    };

    if (chatArgs['bookingId'] != "") {
      route
          .pushNamed(context, routeName.chatScreen, arg: chatArgs)
          .then((_) => onReady(context));
    } else {
      route
          .pushNamed(context, routeName.providerChatScreen, arg: chatArgs)
          .then((_) => onReady(context));
    }
  }

// void onChatClick(BuildContext context, QueryDocumentSnapshot data) {
//   final mapData = data.data() as Map<String, dynamic>;
//   log("mapData.containsKey('bookingId')::${mapData.containsKey('bookingId')}");
//   if (mapData.containsKey('bookingId')) {
//     log("mapData:::${mapData["bookingNumber"]}");
//     route.pushNamed(context, routeName.chatScreen, arg: {
//       "image": mapData['receiverImage'],
//       "name": mapData['receiverName'],
//       "role": mapData['role'],
//       "userId": mapData['senderId'].toString() == userModel!.id.toString()
//           ? mapData["receiverId"]
//           : mapData['senderId'],
//       "token": mapData['senderId'].toString() == userModel!.id.toString()
//           ? mapData["receiverToken"]
//           : mapData['senderToken'],
//       "bookingId": mapData['bookingId'] ?? "",
//       "bookingNumber": mapData['bookingNumber'] ?? "",
//       "phone": mapData['senderId'].toString() == userModel!.id.toString()
//           ? mapData.containsKey('receiverPhone')
//               ? mapData["receiverPhone"]
//               : ""
//           : mapData.containsKey('senderPhone')
//               ? mapData['senderPhone']
//               : "",
//     }).then((_) => onReady(context));
//   } else {
//     log("mapData:::${mapData["receiverToken"]}");
//     route.pushNamed(context, routeName.providerChatScreen, arg: {
//       "image": mapData['receiverImage'],
//       "name": mapData['receiverName'],
//       "chatId": mapData['chatId'],
//       "role": mapData['role'],
//       "userId": mapData['senderId'].toString() == userModel!.id.toString()
//           ? mapData["receiverId"]
//           : mapData['senderId'],
//       "token": mapData['senderId'].toString() == userModel!.id.toString()
//           ? mapData["receiverToken"]
//           : mapData['senderToken'],
//       "phone": mapData['senderId'].toString() == userModel!.id.toString()
//           ? mapData.containsKey('receiverPhone')
//               ? mapData["receiverPhone"]
//               : ""
//           : mapData.containsKey('senderPhone')
//               ? mapData['senderPhone']
//               : "",
//     }).then((_) => onReady(context));
//   }
// }
}
