import '../../../config.dart';
import '../../../design_system/components/fixit_button.dart';
import '../../../design_system/components/fixit_card.dart';
import '../../../design_system/fixit_theme.dart';

class NotificationScreen extends StatefulWidget {
  const NotificationScreen({super.key});

  @override
  State<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends State<NotificationScreen>
    with TickerProviderStateMixin {
  @override
  Widget build(BuildContext context) {
    return Consumer<NotificationProvider>(builder: (context1, value, child) {
      final designSystem = context.fixitTheme;
      final colors = designSystem.colors;
      final typography = designSystem.typography;
      return StatefulWrapper(
          onInit: () => Future.delayed(
              DurationClass.ms50, () => value.onAnimate(this, context)),
          child: WillPopScope(
              onWillPop: () async {
                value.onBack();
                return true;
              },
              child: Scaffold(
                  appBar: AppBar(
                      leadingWidth: 80,
                      title: Text(
                        language(context, translations!.notifications),
                        style:
                            typography.titleLarge.copyWith(color: colors.textPrimary),
                      ),
                      centerTitle: true,
                      leading: CommonArrow(
                          arrow: rtl(context)
                              ? eSvgAssets.arrowRight
                              : eSvgAssets.arrowLeft,
                          onTap: () {
                            value.onBack();
                            route.pop(context);
                          }).paddingAll(Insets.i8),
                      actions: [
                        if (value.notificationList.isNotEmpty)
                          CommonArrow(
                            arrow: eSvgAssets.readAll,
                            onTap: () => value.readAll(context),
                          ),
                        if (value.notificationList.isNotEmpty)
                          const HSpace(Sizes.s10),
                        if (value.notificationList.isNotEmpty)
                          CommonArrow(
                            arrow: eSvgAssets.delete,
                            color: colors.statusCritical.withOpacity(0.1),
                            svgColor: colors.statusCritical,
                            onTap: () => value.deleteNotificationConfirmation(
                                context, this),
                          ).paddingOnly(
                              right: rtl(context) ? 0 : Insets.i20,
                              left: rtl(context) ? Insets.i20 : 0)
                      ]),
                  body: /* value.isNotificationLoading
                      ? Center(
                          child: Image.asset(
                          eGifAssets.loader,
                          height: Sizes.s100,
                        ))
                      :  */
                      RefreshIndicator(
                    onRefresh: () async {
                      value.getNotificationList(context);
                    },
                    child: value.notificationList.isNotEmpty
                        ? ListView.builder(
                            physics: const BouncingScrollPhysics(),
                            padding: const EdgeInsets.all(Insets.i20),
                            itemCount: value.notificationList.length + 1,
                            itemBuilder: (context, index) {
                              if (index == 0) {
                                return _NotificationPreferenceCard(provider: value);
                              }
                              final notification = value.notificationList[index - 1];
                              return NotificationLayout(
                                data: notification,
                                onTap: () => value.onTap(notification, context, index - 1),
                              ).paddingOnly(top: Insets.i16);
                            })
                        : ListView(
                            physics: const BouncingScrollPhysics(),
                            padding: const EdgeInsets.all(Insets.i20),
                            children: [
                              _NotificationPreferenceCard(provider: value),
                              const VSpace(Sizes.s20),
                              EmptyLayout(
                                title: translations?.nothingHere,
                                subtitle: translations?.clickTheRefresh,
                                buttonText: translations?.refresh,
                                isButtonShow: false,
                                widget: Stack(children: [
                                  Image.asset(eImageAssets.notiGirl, height: Sizes.s346),
                                  if (value.animationController != null)
                                    Positioned(
                                      top: MediaQuery.of(context).size.height * 0.04,
                                      left: MediaQuery.of(context).size.height * 0.055,
                                      child: RotationTransition(
                                        turns: Tween(begin: 0.05, end: -.1)
                                            .chain(CurveTween(curve: Curves.elasticInOut))
                                            .animate(value.animationController!),
                                        child: Image.asset(
                                          eImageAssets.notificationBell,
                                          height: Sizes.s40,
                                          width: Sizes.s40,
                                        ),
                                      ),
                                    ),
                                ]),
                              ),
                            ],
                          ),
                  ))));
    });
  }
}

class _NotificationPreferenceCard extends StatelessWidget {
  const _NotificationPreferenceCard({required this.provider});

  final NotificationProvider provider;

  @override
  Widget build(BuildContext context) {
    final designSystem = context.fixitTheme;
    final colors = designSystem.colors;
    final spacing = designSystem.spacing;
    final typography = designSystem.typography;

    return FixitCard(
      title: translations?.notificationPreferences ?? 'Notification preferences',
      subtitle: translations?.notificationPreferencesSubtitle ??
          'Choose which alerts you receive and configure quiet hours to mute interruptions.',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          ...NotificationCategory.values.map((category) {
            final enabled = provider.categoryPreferences[category] ?? true;
            return Padding(
              padding: EdgeInsets.only(bottom: spacing.sm),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      category.label,
                      style: typography.bodyMedium.copyWith(color: colors.textPrimary),
                    ),
                  ),
                  Switch.adaptive(
                    value: enabled,
                    activeColor: colors.brandPrimary,
                    onChanged: (value) => provider.updateCategory(category, value),
                  ),
                ],
              ),
            );
          }),
          Divider(color: colors.border, height: spacing.xl),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      translations?.quietHours ?? 'Quiet hours',
                      style: typography.bodyMedium.copyWith(color: colors.textPrimary),
                    ),
                    SizedBox(height: spacing.xs),
                    Text(
                      provider.quietHoursLabel(context),
                      style: typography.bodySmall.copyWith(color: colors.textTertiary),
                    ),
                  ],
                ),
              ),
              Switch.adaptive(
                value: provider.quietHours.enabled,
                activeColor: colors.brandPrimary,
                onChanged: (enabled) {
                  final start = TimeOfDay(
                    hour: provider.quietHours.startMinute ~/ 60,
                    minute: provider.quietHours.startMinute % 60,
                  );
                  final end = TimeOfDay(
                    hour: provider.quietHours.endMinute ~/ 60,
                    minute: provider.quietHours.endMinute % 60,
                  );
                  provider.updateQuietHours(enabled: enabled, start: start, end: end);
                },
              ),
            ],
          ),
          SizedBox(height: spacing.md),
          FixitButton(
            label: translations?.adjustQuietHours ?? 'Adjust quiet hours',
            onPressed: () => _editQuietHours(context),
            variant: FixitButtonVariant.ghost,
            foregroundColorOverride: colors.brandPrimary,
            fullWidth: false,
          ),
        ],
      ),
    );
  }

  Future<void> _editQuietHours(BuildContext context) async {
    final startInitial = TimeOfDay(hour: provider.quietHours.startMinute ~/ 60, minute: provider.quietHours.startMinute % 60);
    final endInitial = TimeOfDay(hour: provider.quietHours.endMinute ~/ 60, minute: provider.quietHours.endMinute % 60);

    final start = await showTimePicker(context: context, initialTime: startInitial);
    if (start == null) return;
    final end = await showTimePicker(context: context, initialTime: endInitial);
    if (end == null) return;

    await provider.updateQuietHours(enabled: true, start: start, end: end);
  }
}
