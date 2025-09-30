import 'dart:developer';
import 'dart:io';

import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:http/http.dart' as http;
import 'package:path_provider/path_provider.dart';

import '../config.dart';
import '../services/environment.dart';
import '../services/notifications/notification_preferences.dart';

Future<String> downloadAndSaveFile(String url, String fileName) async {
  final Directory directory = await getApplicationDocumentsDirectory();
  final String filePath = '${directory.path}/$fileName';
  final http.Response response = await http.get(Uri.parse(url));
  final File file = File(filePath);
  await file.writeAsBytes(response.bodyBytes);
  return filePath;
}

enum NotificationType {
  createBookingEvent,
  updateBookingStatusEvent,
  assignBooking,
  createProvider,
  extraChargeEvent,
  createBid,
  updateBidEvent,
  createServicemanWithdraw,
  createWithdrawRequest,
  createServiceRequest
}

extension NotificationTypeExtension on NotificationType {
  String get value => switch (this) {
        NotificationType.createBookingEvent => 'createBookingEvent',
        NotificationType.updateBookingStatusEvent => 'updateBookingStatusEvent',
        NotificationType.assignBooking => 'assingBooking',
        NotificationType.createProvider => 'createProvider',
        NotificationType.extraChargeEvent => 'extraChargeEvent',
        NotificationType.createBid => 'createBid',
        NotificationType.updateBidEvent => 'updateBidEvent',
        NotificationType.createServicemanWithdraw => 'createServicemanWithdraw',
        NotificationType.createWithdrawRequest => 'createWithdrawRequest',
        NotificationType.createServiceRequest => 'createServiceRequest',
      };
}

Future<void> createBookingNotification(NotificationType type) async {
  try {
    final response = await apiServices.getApi('${api.notification}?type=${type.value}', [], isToken: true);
    if (response.isSuccess != true) {
      log('Notification trigger failed for ${type.value}');
    }
  } catch (error, stackTrace) {
    log('Notification trigger error: $error', stackTrace: stackTrace);
  }
}

FlutterLocalNotificationsPlugin? flutterLocalNotificationsPlugin;
AndroidNotificationChannel? channel;

class PushNotificationService {
  PushNotificationService._internal();

  static final PushNotificationService instance = PushNotificationService._internal();

  final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _localNotifications = FlutterLocalNotificationsPlugin();

  NotificationPreferenceStore? _preferenceStore;
  bool _localInitialised = false;

  NotificationPreferenceStore get preferenceStore {
    return _preferenceStore ??= NotificationPreferenceStore(preferences: environmentStore.preferences);
  }

  Future<void> initFirebaseMessaging({BuildContext? context}) async {
    final settings = await _messaging.requestPermission(alert: true, badge: true, sound: true);
    if (settings.authorizationStatus == AuthorizationStatus.denied) {
      log('Push notifications denied by user');
      return;
    }

    FirebaseMessaging.onMessage.listen((message) async {
      await _ensureLocalNotifications();
      if (!_shouldDeliver(message)) {
        log('Notification suppressed due to preferences or quiet hours');
        return;
      }
      final lifecycle = WidgetsBinding.instance.lifecycleState;
      if (lifecycle == AppLifecycleState.resumed && navigatorKey.currentContext != null) {
        _showInAppBanner(navigatorKey.currentContext!, message);
      } else {
        await _showSystemNotification(message);
      }
    });

    FirebaseMessaging.onMessageOpenedApp.listen((message) async {
      await _ensureLocalNotifications();
      if (!_shouldDeliver(message)) {
        return;
      }
      _navigateFromMessage(message);
    });

    final initialMessage = await _messaging.getInitialMessage();
    if (initialMessage != null && _shouldDeliver(initialMessage)) {
      _navigateFromMessage(initialMessage);
    }
  }

  Future<void> initNotification(BuildContext context) async {
    await _ensureLocalNotifications();
    final initializationSettings = InitializationSettings(
      android: const AndroidInitializationSettings('@mipmap/ic_launcher'),
      iOS: const DarwinInitializationSettings(),
    );

    await _localNotifications.initialize(
      initializationSettings,
      onDidReceiveNotificationResponse: (response) {
        if (response.payload == null) return;
        final message = RemoteMessage.fromMap({'data': {'deep_link': response.payload!}});
        _navigateFromMessage(message);
      },
    );
  }

  Future<void> showRemoteNotification(RemoteMessage message, {AndroidNotificationChannel? overrideChannel}) async {
    await _ensureLocalNotifications(channelOverride: overrideChannel);
    if (!_shouldDeliver(message)) {
      return;
    }
    await _showSystemNotification(message, channelOverride: overrideChannel);
  }

  Future<void> _ensureLocalNotifications({AndroidNotificationChannel? channelOverride}) async {
    if (flutterLocalNotificationsPlugin == null) {
      flutterLocalNotificationsPlugin = _localNotifications;
    }

    if (channel == null || channelOverride != null) {
      channel = channelOverride ?? const AndroidNotificationChannel(
        'high_importance_channel',
        'High Importance Notifications',
        description: 'Fixit alerts for jobs, bids, disputes, and payouts.',
        importance: Importance.high,
        playSound: true,
      );
      await _localNotifications.resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()?.createNotificationChannel(channel!);
    }
    _localInitialised = true;
  }

  NotificationCategory _mapCategory(RemoteMessage message) {
    final type = message.data['type']?.toString();
    switch (type) {
      case 'feed.new_nearby':
        return NotificationCategory.feedNearby;
      case 'bid.placed':
        return NotificationCategory.bidPlaced;
      case 'dispute.deadline':
        return NotificationCategory.disputeDeadline;
      case 'payout.sent':
        return NotificationCategory.payoutSent;
      case 'order.status':
      default:
        return NotificationCategory.orderStatus;
    }
  }

  bool _shouldDeliver(RemoteMessage message) {
    final category = _mapCategory(message);
    return preferenceStore.shouldDeliver(category, DateTime.now());
  }

  Future<void> _showSystemNotification(RemoteMessage message, {AndroidNotificationChannel? channelOverride}) async {
    final notification = message.notification;
    final android = notification?.android;
    final activeChannel = channelOverride ?? channel;
    if (notification == null || activeChannel == null) {
      return;
    }

    final details = NotificationDetails(
      android: AndroidNotificationDetails(
        activeChannel.id,
        activeChannel.name,
        channelDescription: activeChannel.description,
        icon: android?.smallIcon ?? '@mipmap/ic_launcher',
        channelShowBadge: true,
        importance: Importance.high,
        priority: Priority.high,
      ),
      iOS: const DarwinNotificationDetails(presentSound: true, presentAlert: true),
    );

    await _localNotifications.show(
      notification.hashCode,
      notification.title,
      notification.body,
      details,
      payload: message.data['deep_link']?.toString(),
    );
  }

  void _showInAppBanner(BuildContext context, RemoteMessage message) {
    final messenger = ScaffoldMessenger.maybeOf(context);
    if (messenger == null) return;

    final notification = message.notification;
    final snackBar = SnackBar(
      content: Text(notification?.title ?? 'New update'),
      action: SnackBarAction(
        label: 'View',
        onPressed: () => _navigateFromMessage(message),
      ),
    );

    messenger.showSnackBar(snackBar);
  }

  void _navigateFromMessage(RemoteMessage message) {
    final deepLink = message.data['deep_link']?.toString();
    if (deepLink != null && deepLink.isNotEmpty) {
      route.pushNamed(navigatorKey.currentContext!, deepLink);
      return;
    }

    switch (_mapCategory(message)) {
      case NotificationCategory.feedNearby:
        route.pushNamed(navigatorKey.currentContext!, routeName.feedScreen);
        break;
      case NotificationCategory.bidPlaced:
        route.pushNamed(navigatorKey.currentContext!, routeName.booking);
        break;
      case NotificationCategory.disputeDeadline:
        route.pushNamed(navigatorKey.currentContext!, routeName.disputeScreen);
        break;
      case NotificationCategory.orderStatus:
        route.pushNamed(navigatorKey.currentContext!, routeName.booking);
        break;
      case NotificationCategory.payoutSent:
        route.pushNamed(navigatorKey.currentContext!, routeName.wallet);
        break;
    }
  }
}

class CustomNotificationController {
  CustomNotificationController() : _service = PushNotificationService.instance;

  final PushNotificationService _service;

  Future<void> initFirebaseMessaging({BuildContext? context}) => _service.initFirebaseMessaging(context: context);

  Future<void> initNotification(BuildContext context) => _service.initNotification(context);

  Future<void> showRemoteNotification(RemoteMessage message, {AndroidNotificationChannel? channel}) =>
      _service.showRemoteNotification(message, overrideChannel: channel);
}

Future<void> showNotification(RemoteMessage remote, AndroidNotificationChannel channel) async {
  await PushNotificationService.instance.showRemoteNotification(remote, overrideChannel: channel);
}
