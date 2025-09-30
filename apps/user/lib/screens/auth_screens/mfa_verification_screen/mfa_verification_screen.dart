import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../config.dart';

class MfaVerificationScreen extends StatefulWidget {
  const MfaVerificationScreen({super.key});

  @override
  State<MfaVerificationScreen> createState() => _MfaVerificationScreenState();
}

class _MfaVerificationScreenState extends State<MfaVerificationScreen> {
  Map<String, dynamic>? _payload;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final args = ModalRoute.of(context)?.settings.arguments;
    if (args is Map && !mapEquals(_payload, args)) {
      _payload = Map<String, dynamic>.from(args);
      final mfaProvider =
          Provider.of<MfaChallengeProvider>(context, listen: false);
      mfaProvider.initializeChallenge(
        payload: _payload!,
        email: mfaProvider.email ??
            Provider.of<LoginProvider>(context, listen: false)
                .emailController
                .text
                .trim(),
        message: _payload!['message'] as String?,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<MfaChallengeProvider>(
      builder: (context, provider, child) {
        final theme = appColor(context);
        final timeRemaining = provider.timeRemaining;
        final bool isExpired =
            provider.expiresAt != null && DateTime.now().isAfter(provider.expiresAt!);
        final countdown = timeRemaining == null
            ? null
            : timeRemaining.isNegative
                ? language(context, 'Challenge expired')
                : '${timeRemaining.inMinutes.remainder(60).toString().padLeft(2, '0')}:${(timeRemaining.inSeconds.remainder(60)).toString().padLeft(2, '0')}';

        final methodLabels = <String, String>{
          'totp': 'Authenticator app',
          'recovery_code': 'Recovery code',
        };

        return Scaffold(
          appBar: AppBar(
            title: Text(
              language(context, 'Two-factor verification'),
              style:
                  appCss.dmDenseSemiBold18.textColor(theme.darkText),
            ),
            leading: IconButton(
              icon: Icon(Icons.arrow_back, color: theme.darkText),
              onPressed: () => route.pop(context),
            ),
            backgroundColor: theme.whiteBg,
            elevation: 0,
          ),
          backgroundColor: theme.fieldCardBg,
          body: SafeArea(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if ((provider.infoMessage ?? '').isNotEmpty)
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: theme.primary.withOpacity(0.08),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        provider.infoMessage!,
                        style: appCss.dmDenseMedium14.textColor(theme.darkText),
                      ),
                    ),
                  const SizedBox(height: 24),
                  Text(
                    language(context, 'Approve this sign-in'),
                    style:
                        appCss.dmDenseSemiBold20.textColor(theme.darkText),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    provider.email == null
                        ? 'Open your authenticator app and enter the 6-digit code.'
                        : 'We sent a verification challenge for ${provider.email}. Open your authenticator app and enter the 6-digit code.',
                    style:
                        appCss.dmDenseMedium14.textColor(theme.lightText),
                  ),
                  const SizedBox(height: 24),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: provider.methods
                        .map((method) => Chip(
                              label: Text(
                                methodLabels[method] ?? method,
                                style: appCss.dmDenseMedium12
                                    .textColor(theme.primary),
                              ),
                              backgroundColor: theme.whiteBg,
                              shape: StadiumBorder(
                                side: BorderSide(color: theme.primary.withOpacity(0.2)),
                              ),
                            ))
                        .toList(),
                  ),
                  const SizedBox(height: 20),
                  TextFieldCommon(
                    hintText: 'Authentication code',
                    controller: provider.codeController,
                    keyboardType: TextInputType.number,
                    maxLength: 6,
                    counterText: '',
                  ),
                  const SizedBox(height: 16),
                  ButtonCommon(
                    title: 'Verify code',
                    onTap: provider.isSubmitting || isExpired
                        ? null
                        : () => provider.submitTotp(context),
                    isLoading: provider.isSubmitting,
                  ),
                  const SizedBox(height: 24),
                  Divider(color: theme.stroke.withOpacity(0.4)),
                  const SizedBox(height: 24),
                  Text(
                    language(context, 'Trouble with your authenticator?'),
                    style: appCss.dmDenseSemiBold16.textColor(theme.darkText),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    'Use one of your recovery codes if you cannot access your authenticator device.',
                    style:
                        appCss.dmDenseMedium14.textColor(theme.lightText),
                  ),
                  const SizedBox(height: 16),
                  TextFieldCommon(
                    hintText: 'Recovery code',
                    controller: provider.recoveryCodeController,
                    keyboardType: TextInputType.text,
                  ),
                  const SizedBox(height: 16),
                  ButtonCommon(
                    title: 'Use recovery code',
                    color: theme.whiteBg,
                    fontColor: theme.primary,
                    borderColor: theme.primary,
                    onTap: provider.isSubmitting || isExpired
                        ? null
                        : () => provider.submitRecoveryCode(context),
                    isLoading: provider.isSubmitting,
                  ),
                  const SizedBox(height: 20),
                  Text(
                    'Recovery codes remaining: ${provider.recoveryCodesRemaining}',
                    style:
                        appCss.dmDenseMedium14.textColor(theme.lightText),
                  ),
                  if (countdown != null) ...[
                    const SizedBox(height: 12),
                    Text(
                      isExpired
                          ? language(context, 'Challenge expired')
                          : 'Challenge expires in $countdown',
                      style: appCss.dmDenseMedium14
                          .textColor(isExpired ? theme.red : theme.primary),
                    ),
                  ],
                  if (!provider.isChallengeActive) ...[
                    const SizedBox(height: 20),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: theme.red.withOpacity(0.08),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        'This verification challenge has expired. Return to the login screen and request a new code.',
                        style:
                            appCss.dmDenseMedium14.textColor(theme.red),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}
