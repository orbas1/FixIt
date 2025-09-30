import 'package:flutter/material.dart';
import 'package:flutter_stripe/flutter_stripe.dart';

import '../../models/escrow_payment_intent.dart';

class StripePaymentSheetService {
  StripePaymentSheetService({Stripe? stripe})
      : _stripe = stripe ?? Stripe.instance;

  final Stripe _stripe;

  Future<void> present(EscrowPaymentIntent intent) async {
    Stripe.publishableKey = intent.publishableKey;
    await _stripe.applySettings();

    final parameters = SetupPaymentSheetParameters(
      paymentIntentClientSecret: intent.clientSecret,
      merchantDisplayName: 'FixIt Marketplace',
      customerId: intent.customerId,
      customerEphemeralKeySecret: intent.ephemeralKey,
      style: ThemeMode.system,
    );

    await _stripe.initPaymentSheet(
      paymentSheetParameters: parameters,
    );

    await _stripe.presentPaymentSheet();
  }
}
