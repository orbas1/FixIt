import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../config.dart';
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
    final theme = Theme.of(context);
    final isAuthenticated =
        context.watch<AppStateStore>().isAuthenticated;

    return Scaffold(
      backgroundColor: theme.colorScheme.surface,
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
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                    sliver: _buildDiscoverPreview(theme),
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
    final theme = Theme.of(context);
    return Container(
      margin: const EdgeInsets.all(20),
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 28),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(24),
        gradient: LinearGradient(
          colors: [
            theme.colorScheme.primary,
            theme.colorScheme.primary.withOpacity(0.8),
          ],
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            translations?.landingHeadline ?? 'Expert help for every home project',
            style: theme.textTheme.headlineMedium?.copyWith(
              color: theme.colorScheme.onPrimary,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 12),
          Text(
            translations?.landingSubheading ??
                'Book trusted professionals for repairs, cleaning, deliveries, and more with transparent pricing and protected payments.',
            style: theme.textTheme.bodyMedium?.copyWith(
              color: theme.colorScheme.onPrimary.withOpacity(0.85),
            ),
          ),
          const SizedBox(height: 24),
          Row(
            children: [
              ElevatedButton(
                onPressed: () =>
                    route.pushNamed(context, isAuthenticated ? routeName.feed : routeName.login),
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
                ),
                child: Text(
                  isAuthenticated
                      ? (translations?.landingActionExplore ?? 'Browse jobs')
                      : (translations?.loginNow ?? 'Sign in'),
                ),
              ),
              const SizedBox(width: 16),
              TextButton(
                onPressed: () => route.pushNamed(context, routeName.registerUser),
                style: TextButton.styleFrom(
                  foregroundColor: theme.colorScheme.onPrimary,
                ),
                child: Text(translations?.landingActionCreate ?? 'Create account'),
              ),
            ],
          ),
        ],
      ),
    );
  }

  SliverList _buildDiscoverPreview(ThemeData theme) {
    final textTheme = theme.textTheme;
    return SliverList(
      delegate: SliverChildListDelegate([
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              translations?.landingDiscoverTitle ?? 'Trending nearby requests',
              style: textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700),
            ),
            TextButton(
              onPressed: () => route.pushNamed(context, routeName.discover),
              child: Text(translations?.viewAll ?? 'View all'),
            ),
          ],
        ),
        const SizedBox(height: 12),
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
              return EmptyLayout(
                title: translations?.noDataFound ?? 'No jobs yet',
                subtitle: translations?.noDataFoundDesc ??
                    'Once customers post new work in your area it will appear here instantly.',
                buttonText: translations?.refresh ?? 'Refresh',
                onTap: _refreshFeed,
              );
            }

            final jobs = provider.jobs.take(5).toList();
            return Column(
              children: [
                for (final job in jobs) _JobPreviewCard(job: job),
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
    final theme = Theme.of(context);
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

    return Semantics(
      label: 'Job preview card',
      hint: 'Opens job details',
      button: true,
      child: Card(
        margin: const EdgeInsets.only(bottom: 16),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        child: InkWell(
          borderRadius: BorderRadius.circular(18),
          onTap: () => GoRouter.of(context).push('/jobs/${job.id}', extra: job),
          child: Padding(
            padding: const EdgeInsets.all(18),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  job.title,
                  style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600),
                ),
                const SizedBox(height: 8),
                Text(
                  job.description ??
                      translations?.landingNoDescription ??
                          'Detailed scope will be shared after you send a proposal.',
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: theme.textTheme.bodyMedium?.copyWith(color: theme.textTheme.bodySmall?.color),
                ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      currencyFormatter.format(estimated),
                      style: theme.textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: theme.colorScheme.primary,
                      ),
                    ),
                    Text(
                      job.status.toUpperCase(),
                      style: theme.textTheme.labelMedium?.copyWith(
                        letterSpacing: 0.6,
                        color: theme.colorScheme.tertiary,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: chips
                      .where((chip) => chip.isNotEmpty)
                      .map(
                        (chip) => Chip(
                          label: Text(chip),
                          backgroundColor: theme.colorScheme.surfaceVariant,
                        ),
                      )
                      .toList(),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
