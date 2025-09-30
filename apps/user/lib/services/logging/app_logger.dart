import 'dart:developer' as developer;

class AppLogger {
  AppLogger._();

  static final AppLogger instance = AppLogger._();

  void info(String message, {String category = 'App', Object? error, StackTrace? stackTrace}) {
    developer.log(message, name: 'FixIt.$category', error: error, stackTrace: stackTrace);
  }

  void warning(String message, {Object? error, StackTrace? stackTrace}) {
    developer.log(message, name: 'FixIt.Warning', level: 900, error: error, stackTrace: stackTrace);
  }

  void error(String message, {Object? error, StackTrace? stackTrace}) {
    developer.log(message, name: 'FixIt.Error', level: 1000, error: error, stackTrace: stackTrace);
  }

  void network(Object message) {
    developer.log(message.toString(), name: 'FixIt.Network');
  }
}
