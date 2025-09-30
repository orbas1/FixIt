import 'package:flutter/widgets.dart';
import 'package:go_router/go_router.dart';

import '../config.dart';

class NavigationClass {
  Future<T?> pushNamed<T>(BuildContext context, String location,
      {Object? arg}) {
    return GoRouter.of(context).push<T>(location, extra: arg);
  }

  Future<T?> push<T>(BuildContext context, Widget page, {Object? arg}) {
    return Navigator.of(context).push<T>(
      MaterialPageRoute(builder: (_) => page, settings: RouteSettings(arguments: arg)),
    );
  }

  void pop(BuildContext context, {Object? arg}) {
    if (GoRouter.of(context).canPop()) {
      GoRouter.of(context).pop(arg);
    } else {
      Navigator.of(context).maybePop(arg);
    }
  }

  Future<T?> popAndPushNamed<T>(BuildContext context, String location,
      {Object? arg, Object? result}) {
    if (GoRouter.of(context).canPop()) {
      GoRouter.of(context).pop(result);
    }
    return GoRouter.of(context).pushReplacement<T>(location, extra: arg);
  }

  Future<T?> pushReplacementNamed<T>(BuildContext context, String location,
      {Object? args}) {
    return GoRouter.of(context).pushReplacement<T>(location, extra: args);
  }

  void pushNamedAndRemoveUntil(BuildContext context, String location,
      {Object? arg}) {
    GoRouter.of(context).go(location, extra: arg);
  }

  void pushAndRemoveUntil(BuildContext context, {String? location, Object? args}) {
    if (location != null) {
      GoRouter.of(context).go(location, extra: args);
    } else {
      GoRouter.of(context).go(routeName.login, extra: args);
    }
  }
}
