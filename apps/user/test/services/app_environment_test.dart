import 'package:flutter_test/flutter_test.dart';
import 'package:fixit_user/services/configuration/app_environment.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('AppEnvironment', () {
    test('loads development configuration successfully', () async {
      final environment = await AppEnvironment.load(flavor: 'dev');

      expect(environment.app.name, 'FixIt User');
      expect(environment.api.baseUrl, isNotEmpty);
      expect(environment.store.playStoreUrl, contains('play.google.com'));
      expect(environment.firebase.apiKey, isNotEmpty);
      expect(environment.telemetry.analyticsEnabled, isTrue);
    });
  });
}
