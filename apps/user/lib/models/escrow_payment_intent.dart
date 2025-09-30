class EscrowPaymentIntent {
  const EscrowPaymentIntent({
    required this.intentId,
    required this.clientSecret,
    required this.publishableKey,
    this.customerId,
    this.ephemeralKey,
    required this.amount,
    required this.currency,
  });

  final String intentId;
  final String clientSecret;
  final String publishableKey;
  final String? customerId;
  final String? ephemeralKey;
  final double amount;
  final String currency;

  factory EscrowPaymentIntent.fromJson(Map<String, dynamic> json) {
    return EscrowPaymentIntent(
      intentId: json['payment_intent_id'] as String,
      clientSecret: json['client_secret'] as String,
      publishableKey: json['publishable_key'] as String,
      customerId: json['customer_id'] as String?,
      ephemeralKey: json['ephemeral_key'] as String?,
      amount: (json['amount'] as num).toDouble(),
      currency: json['currency'] as String,
    );
  }
}
