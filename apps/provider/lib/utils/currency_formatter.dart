import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../model/currency_model.dart';
import '../providers/common_providers/currency_provider.dart';

class CurrencyFormatter {
  const CurrencyFormatter._();

  static String format(
    BuildContext context,
    num amount, {
    bool convert = true,
    CurrencyModel? overrideCurrency,
    Locale? localeOverride,
    bool includeCode = false,
  }) {
    final currencyProvider = context.read<CurrencyProvider>();
    final Locale locale = localeOverride ??
        Localizations.maybeLocaleOf(context) ??
        const Locale('en');

    final CurrencyModel? active = overrideCurrency ?? currencyProvider.currency;
    final String symbol = overrideCurrency?.symbol ??
        active?.symbol ??
        currencyProvider.priceSymbol ??
        '';
    final String symbolPosition =
        (overrideCurrency?.symbolPosition ?? active?.symbolPosition ?? 'left')
            .toLowerCase();
    final int decimals = overrideCurrency?.noOfDecimal ??
        active?.noOfDecimal ??
        2;
    final String thousandsSeparator = overrideCurrency?.thousandsSeparator ??
        active?.thousandsSeparator ??
        ',';
    final String decimalSeparator = overrideCurrency?.decimalSeparator ??
        active?.decimalSeparator ??
        '.';
    final String currencyCode =
        (overrideCurrency?.code ?? active?.code ?? 'USD').toUpperCase();

    final num rawMultiplier = convert
        ? (currencyProvider.currencyVal is num
            ? currencyProvider.currencyVal as num
            : 1)
        : 1;
    final double multiplier = rawMultiplier.toDouble();
    final double value = (amount * multiplier).toDouble();

    final bool isNegative = value.isNegative;
    final double magnitude = value.abs();

    final digits = magnitude.toStringAsFixed(decimals);
    final parts = digits.split('.');
    final integerPart = parts[0];
    final buffer = StringBuffer();
    for (int index = 0; index < integerPart.length; index++) {
      buffer.write(integerPart[index]);
      final remaining = integerPart.length - index - 1;
      if (remaining > 0 && thousandsSeparator.isNotEmpty && remaining % 3 == 0) {
        buffer.write(thousandsSeparator);
      }
    }
    var formatted = buffer.toString();
    if (decimals > 0) {
      final fraction = parts.length > 1 ? parts[1] : ''.padRight(decimals, '0');
      formatted = '$formatted$decimalSeparator$fraction';
    }

    final trimmedSymbol = symbol.trim();
    String combined;
    if (trimmedSymbol.isEmpty) {
      combined = formatted;
    } else if (symbolPosition == 'right') {
      combined = '$formatted$trimmedSymbol';
    } else {
      combined = '$trimmedSymbol$formatted';
    }

    final String withSign = isNegative ? '-$combined' : combined;
    if (!includeCode) {
      return withSign;
    }
    return '$withSign $currencyCode'.trim();
  }
}
