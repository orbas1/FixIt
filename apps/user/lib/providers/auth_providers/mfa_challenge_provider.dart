import 'dart:convert';

import 'package:fixit_user/config.dart';
import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:provider/provider.dart';

class MfaChallengeProvider with ChangeNotifier {
  final TextEditingController codeController = TextEditingController();
  final TextEditingController recoveryCodeController = TextEditingController();

  String? challengeId;
  DateTime? expiresAt;
  List<String> methods = const [];
  int recoveryCodesRemaining = 0;
  bool isSubmitting = false;
  String? email;
  String? infoMessage;

  void initializeChallenge({
    required Map<String, dynamic> payload,
    String? email,
    String? message,
  }) {
    challengeId = payload['challenge_id']?.toString() ??
        payload['challengeId']?.toString();
    final expiresRaw = payload['expires_at'] ?? payload['expiresAt'];
    expiresAt = expiresRaw is String ? DateTime.tryParse(expiresRaw) : null;
    methods = (payload['methods'] as List?)
            ?.map((method) => method.toString())
            .toList() ??
        const ['totp'];
    recoveryCodesRemaining =
        payload['recovery_codes_remaining'] as int? ?? recoveryCodesRemaining;
    this.email = email;
    infoMessage = message;
    codeController.clear();
    recoveryCodeController.clear();
    isSubmitting = false;
    notifyListeners();
  }

  Duration? get timeRemaining =>
      expiresAt == null ? null : expiresAt!.difference(DateTime.now());

  bool get isChallengeActive =>
      challengeId != null && (expiresAt == null || DateTime.now().isBefore(expiresAt!));

  Future<void> submitTotp(BuildContext context) async {
    final code = codeController.text.trim();
    if (code.isEmpty) {
      Fluttertoast.showToast(
        msg: "Enter the 6-digit code from your authenticator app.",
        backgroundColor: appColor(context).red,
      );
      return;
    }

    await _submit(
      context,
      body: {
        'challenge_id': challengeId,
        'code': code,
      },
    );
  }

  Future<void> submitRecoveryCode(BuildContext context) async {
    final recoveryCode = recoveryCodeController.text.trim();
    if (recoveryCode.isEmpty) {
      Fluttertoast.showToast(
        msg: "Enter one of your recovery codes.",
        backgroundColor: appColor(context).red,
      );
      return;
    }

    await _submit(
      context,
      body: {
        'challenge_id': challengeId,
        'recovery_code': recoveryCode,
      },
    );
  }

  Future<void> _submit(
    BuildContext context, {
    required Map<String, dynamic> body,
  }) async {
    if (challengeId == null) {
      Fluttertoast.showToast(
        msg: "The verification challenge is no longer available.",
        backgroundColor: appColor(context).red,
      );
      return;
    }

    try {
      showLoading(context);
      isSubmitting = true;
      notifyListeners();

      final response = await apiServices.postApi(
        api.verifyMfaChallenge,
        jsonEncode(body),
      );

      if (response.isSuccess == true) {
        if (response.data is Map) {
          final payload = Map<String, dynamic>.from(response.data as Map);
          recoveryCodesRemaining =
              payload['recovery_codes_remaining'] as int? ?? recoveryCodesRemaining;
        }
        if (context.mounted) {
          await Provider.of<LoginProvider>(context, listen: false)
              .finalizeAuthenticatedSession(
            context,
            successMessage: response.message,
          );
        }
        clearChallenge();
      } else {
        hideLoading(context);
        Fluttertoast.showToast(
          msg: response.message.isNotEmpty
              ? response.message
              : "Verification failed. Try again.",
          backgroundColor: appColor(context).red,
        );
      }
    } catch (error) {
      hideLoading(context);
      Fluttertoast.showToast(
        msg: "Something went wrong while verifying your code.",
        backgroundColor: appColor(context).red,
      );
    } finally {
      isSubmitting = false;
      notifyListeners();
    }
  }

  void clearChallenge() {
    challengeId = null;
    expiresAt = null;
    methods = const [];
    infoMessage = null;
    codeController.clear();
    recoveryCodeController.clear();
    notifyListeners();
  }

  @override
  void dispose() {
    codeController.dispose();
    recoveryCodeController.dispose();
    super.dispose();
  }
}
