import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../config.dart';
import '../../design_system/components/fixit_button.dart';
import '../../design_system/components/fixit_card.dart';
import '../../design_system/components/fixit_section_header.dart';
import '../../design_system/fixit_theme.dart';
import '../../models/feed_job_model.dart';
import '../../providers/app_pages_providers/feed/feed_provider.dart';
import '../../services/state/app_state_store.dart';

class LandingScreen extends StatefulWidget {
  const LandingScreen({super.key});

  @override
  State<LandingScreen> createState() => _LandingScreenState();
}

class _LandingScreenState extends State<LandingScreen>
    with AutomaticKeepAliveClientMixin<LandingScreen> {
  FeedProvider? _feedProvider;
  Future<void>? _bootstrapFuture;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final provider = Provider.of<FeedProvider>(context);
    if (_feedProvider != provider) {
      _feedProvider = provider;
      _bootstrapFuture = provider.bootstrap(
        forceRefresh: provider.jobs.isEmpty,
        filters: const {'visibility': 'public'},
      );
    }
  }

  @override
  bool get wantKeepAlive => true;

  Future<void> _refreshFeed() async {
    if (_feedProvider == null) return;
    await _feedProvider!.fetchNext(reset: true);
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    final designSystem = context.fixitTheme;
    final colors = designSystem.colors;
    final spacing = designSystem.spacing;
    final isAuthenticated =
        context.watch<AppStateStore>().isAuthenticated;

    return Scaffold(
      backgroundColor: colors.backgroundPrimary,
      body: SafeArea(
        child: FutureBuilder<void>(
          future: _bootstrapFuture,
          builder: (context, snapshot) {
            return RefreshIndicator(
              onRefresh: _refreshFeed,
              child: CustomScrollView(
                physics: const BouncingScrollPhysics(parent: AlwaysScrollableScrollPhysics()),
                slivers: [
                  SliverToBoxAdapter(child: _buildHero(context, isAuthenticated)),
                  SliverPadding(
                    padding: EdgeInsets.symmetric(horizontal: spacing.lg, vertical: spacing.md),
                    sliver: _buildDiscoverPreview(context),
                  ),
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildHero(BuildContext context, bool isAuthenticated) {
    final designSystem = context.fixitTheme;
    final colors = designSystem.colors;
    final spacing = designSystem.spacing;
    final radius = designSystem.radius;
    final typography = designSystem.typography;

    return Container(
      margin: EdgeInsets.all(spacing.lg),
      padding: EdgeInsets.symmetric(horizontal: spacing.xxl, vertical: spacing.xxl),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(radius.lg),
        gradient: LinearGradient(
          colors: [colors.brandPrimary, colors.brandSecondary],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        boxShadow: [
          BoxShadow(
            color: colors.shadow,
            offset: const Offset(0, 20),
            blurRadius: 40,
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            translations?.landingHeadline ?? 'Expert help for every home project',
            style: typography.headlineMedium.copyWith(
              color: colors.brandOnPrimary,
            ),
          ),
          SizedBox(height: spacing.md),
          Text(
            translations?.landingSubheading ??
                'Book trusted professionals for repairs, cleaning, deliveries, and more with transparent pricing and protected payments.',
            style: typography.bodyLarge.copyWith(
              color: colors.brandOnPrimary.withOpacity(0.85),
            ),
          ),
          SizedBox(height: spacing.xl),
          Wrap(
            spacing: spacing.md,
            runSpacing: spacing.sm,
            children: [
              FixitButton(
                label: isAuthenticated
                    ? (translations?.landingActionExplore ?? 'Browse jobs')
                    : (translations?.loginNow ?? 'Sign in'),
                onPressed: () =>
                    route.pushNamed(context, isAuthenticated ? routeName.feed : routeName.login),
                fullWidth: false,
                backgroundColorOverride: colors.brandOnPrimary,
                foregroundColorOverride: colors.brandPrimary,
              ),
              FixitButton(
                label: translations?.landingActionCreate ?? 'Create account',
                onPressed: () => route.pushNamed(context, routeName.registerUser),
                variant: FixitButtonVariant.ghost,
                foregroundColorOverride: colors.brandOnPrimary,
              ),
            ],
          ),
        ],
      ),
    );
  }

  SliverList _buildDiscoverPreview(BuildContext context) {
    final designSystem = context.fixitTheme;
    final colors = designSystem.colors;
    final spacing = designSystem.spacing;
    final typography = designSystem.typography;
    return SliverList(
      delegate: SliverChildListDelegate([
        FixitSectionHeader(
          title: translations?.landingDiscoverTitle ?? 'Trending nearby requests',
          trailing: GestureDetector(
            onTap: () => route.pushNamed(context, routeName.discover),
            child: Text(translations?.viewAll ?? 'View all'),
          ),
        ),
        SizedBox(height: spacing.md),
        Consumer<FeedProvider>(
          builder: (context, provider, child) {
            if (provider.isLoading && provider.jobs.isEmpty) {
              return const Center(
                child: Padding(
                  padding: EdgeInsets.all(32),
                  child: CircularProgressIndicator(),
                ),
              );
            }

            if (provider.jobs.isEmpty) {
              return FixitCard(
                title: translations?.noDataFound ?? 'No jobs yet',
                subtitle: translations?.noDataFoundDesc ??
                    'Once customers post new work in your area it will appear here instantly.',
                actions: [
                  FixitButton(
                    label: translations?.refresh ?? 'Refresh',
                    onPressed: _refreshFeed,
                    variant: FixitButtonVariant.secondary,
                  ),
                ],
                child: const SizedBox.shrink(),
              );
            }

            final jobs = provider.jobs.take(5).toList();
            return Column(
              children: [
                for (final job in jobs)
                  Padding(
                    padding: EdgeInsets.only(bottom: spacing.md),
                    child: _JobPreviewCard(job: job),
                  ),
              ],
            );
          },
        ),
      ]),
    );
  }
}

class _JobPreviewCard extends StatelessWidget {
  const _JobPreviewCard({required this.job});

  final FeedJobModel job;

  @override
  Widget build(BuildContext context) {
    final designSystem = context.fixitTheme;
    final colors = designSystem.colors;
    final spacing = designSystem.spacing;
    final radius = designSystem.radius;
    final typography = designSystem.typography;
    final currencyFormatter = NumberFormat.compactCurrency(symbol: job.currency ?? r'$');
    final estimated = job.finalBudget ?? job.initialBudget ?? 0;
    final chips = <String>[
      if (job.durationValue != null && job.durationUnit != null)
        '${job.durationValue} ${job.durationUnit}',
      if (job.distanceKm != null)
        '${job.distanceKm!.toStringAsFixed(1)} km away',
      if ((job.bidsSummary?['total'] as num?) != null)
        '${job.bidsSummary!['total']} bids',
    ];

    return FixitCard(
      title: job.title,
      subtitle: job.description ??
          translations?.landingNoDescription ??
              'Detailed scope will be shared after you send a proposal.',
      actions: [
        Container(
          padding: EdgeInsets.symmetric(horizontal: spacing.sm, vertical: spacing.xs),
          decoration: BoxDecoration(
            color: colors.brandPrimary.withOpacity(0.12),
            borderRadius: BorderRadius.circular(radius.sm),
          ),
          child: Text(
            job.status.toUpperCase(),
            style: typography.labelMedium.copyWith(
              color: colors.brandPrimary,
              letterSpacing: 0.6,
            ),
          ),
        ),
      ],
      onTap: () => GoRouter.of(context).push('/jobs/${job.id}', extra: job),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                currencyFormatter.format(estimated),
                style: typography.titleMedium.copyWith(
                  fontWeight: FontWeight.bold,
                  color: colors.brandPrimary,
                ),
              ),
              if (job.createdAt != null)
                Text(
                  DateFormat.MMMd().format(job.createdAt!),
                  style: typography.bodySmall.copyWith(color: colors.textTertiary),
                ),
            ],
          ),
          SizedBox(height: spacing.sm),
          Wrap(
            spacing: spacing.sm,
            runSpacing: spacing.xs,
            children: chips
                .where((chip) => chip.isNotEmpty)
                .map(
                  (chip) => Container(
                    padding: EdgeInsets.symmetric(
                      horizontal: spacing.sm,
                      vertical: spacing.xs,
                    ),
                    decoration: BoxDecoration(
                      color: colors.surfaceHigh,
                      borderRadius: BorderRadius.circular(radius.lg),
                    ),
                    child: Text(
                      chip,
                      style: typography.labelMedium.copyWith(color: colors.textSecondary),
                    ),
                  ),
                )
                .toList(),
          ),
        ],
      ),
    );
  }
}
