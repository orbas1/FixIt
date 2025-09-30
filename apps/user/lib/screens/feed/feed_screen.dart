import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../config.dart';
import '../../models/feed_job_model.dart';
import '../../providers/app_pages_providers/feed/feed_provider.dart';

class FeedScreen extends StatefulWidget {
  const FeedScreen({super.key});

  @override
  State<FeedScreen> createState() => _FeedScreenState();
}

class _FeedScreenState extends State<FeedScreen> {
  final ScrollController _scrollController = ScrollController();
  final TextEditingController _searchController = TextEditingController();
  Map<String, dynamic> _activeFilters = const {};
  FeedProvider? _provider;
  Future<void>? _bootstrapFuture;

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final provider = Provider.of<FeedProvider>(context);
    if (_provider != provider) {
      _provider = provider;
      _bootstrapFuture = provider.bootstrap(
        forceRefresh: provider.jobs.isEmpty,
        filters: _activeFilters,
      );
    }
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _onScroll() {
    final provider = _provider;
    if (provider == null) return;
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent - 200) {
      provider.fetchNext();
    }
  }

  Future<void> _onRefresh() async {
    await _provider?.fetchNext(reset: true, filters: _activeFilters);
  }

  void _openFilters() {
    showModalBottomSheet<Map<String, dynamic>?>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => _FeedFilterSheet(initialFilters: _activeFilters),
    ).then((value) {
      if (value != null) {
        setState(() => _activeFilters = value);
        _provider?.fetchNext(reset: true, filters: value);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: Text(translations?.landingActionExplore ?? 'Browse jobs'),
        actions: [
          IconButton(
            onPressed: _openFilters,
            icon: const Icon(Icons.filter_list_outlined),
            tooltip: translations?.filter ?? 'Filter',
          ),
        ],
      ),
      body: SafeArea(
        child: FutureBuilder<void>(
          future: _bootstrapFuture,
          builder: (context, snapshot) {
            return Column(
              children: [
                _buildSearchBar(theme),
                Expanded(
                  child: RefreshIndicator(
                    onRefresh: _onRefresh,
                    child: Consumer<FeedProvider>(
                      builder: (context, provider, child) {
                        if (provider.isLoading && provider.jobs.isEmpty) {
                          return const Center(child: CircularProgressIndicator());
                        }
                        if (provider.jobs.isEmpty) {
                          return EmptyLayout(
                            title: translations?.noDataFound ?? 'No jobs available right now',
                            subtitle: translations?.noDataFoundDesc ??
                                'Try adjusting your filters or check back later as new jobs are posted frequently.',
                            buttonText: translations?.refresh ?? 'Refresh',
                            onTap: _onRefresh,
                          );
                        }
                        return ListView.builder(
                          controller: _scrollController,
                          physics: const AlwaysScrollableScrollPhysics(),
                          itemCount: provider.jobs.length + (provider.hasMore ? 1 : 0),
                          itemBuilder: (context, index) {
                            if (index >= provider.jobs.length) {
                              return const Padding(
                                padding: EdgeInsets.all(24),
                                child: Center(child: CircularProgressIndicator()),
                              );
                            }
                            final job = provider.jobs[index];
                            return _FeedJobTile(job: job);
                          },
                        );
                      },
                    ),
                  ),
                ),
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _buildSearchBar(ThemeData theme) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
      child: TextField(
        controller: _searchController,
        textInputAction: TextInputAction.search,
        decoration: InputDecoration(
          hintText: translations?.search ?? 'Search for jobs, skills, or keywords',
          prefixIcon: const Icon(Icons.search),
          suffixIcon: _searchController.text.isEmpty
              ? null
              : IconButton(
                  onPressed: () {
                    _searchController.clear();
                    _provider?.fetchNext(reset: true, filters: _activeFilters);
                  },
                  icon: const Icon(Icons.clear),
                ),
        ),
        onSubmitted: (value) {
          final filters = Map<String, dynamic>.from(_activeFilters);
          if (value.trim().isEmpty) {
            filters.remove('q');
          } else {
            filters['q'] = value.trim();
          }
          setState(() => _activeFilters = filters);
          _provider?.fetchNext(reset: true, filters: filters);
        },
      ),
    );
  }
}

class _FeedJobTile extends StatelessWidget {
  const _FeedJobTile({required this.job});

  final FeedJobModel job;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final formatter = NumberFormat.simpleCurrency(name: job.currency ?? 'USD');
    final budget = job.finalBudget ?? job.initialBudget ?? 0;
    final postedAt = job.createdAt != null
        ? DateFormat.yMMMd().format(job.createdAt!.toLocal())
        : null;

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      child: InkWell(
        borderRadius: BorderRadius.circular(20),
        onTap: () => GoRouter.of(context).push('/jobs/${job.id}', extra: job),
        child: Padding(
          padding: const EdgeInsets.all(18),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                job.title,
                style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700),
              ),
              if (postedAt != null) ...[
                const SizedBox(height: 4),
                Text('${translations?.postedOn ?? 'Posted'} $postedAt',
                    style: theme.textTheme.bodySmall),
              ],
              const SizedBox(height: 10),
              Text(
                job.description ??
                    translations?.noDataFoundDesc ??
                        'No description provided',
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 12),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  Chip(label: Text(formatter.format(budget))),
                  Chip(label: Text(job.status.toUpperCase())),
                  if (job.durationValue != null && job.durationUnit != null)
                    Chip(label: Text('${job.durationValue} ${job.durationUnit}')),
                  if (job.distanceKm != null)
                    Chip(label: Text('${job.distanceKm!.toStringAsFixed(1)} km away')),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _FeedFilterSheet extends StatefulWidget {
  const _FeedFilterSheet({required this.initialFilters});

  final Map<String, dynamic> initialFilters;

  @override
  State<_FeedFilterSheet> createState() => _FeedFilterSheetState();
}

class _FeedFilterSheetState extends State<_FeedFilterSheet> {
  late RangeValues _budgetRange;
  late double _radius;
  String? _status;

  @override
  void initState() {
    super.initState();
    final filters = widget.initialFilters;
    _budgetRange = RangeValues(
      (filters['budget_min'] as num?)?.toDouble() ?? 0,
      (filters['budget_max'] as num?)?.toDouble() ?? 2000,
    );
    _radius = (filters['radius_km'] as num?)?.toDouble() ?? 10;
    _status = filters['status'] as String?;
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Padding(
      padding: EdgeInsets.only(
        left: 24,
        right: 24,
        top: 24,
        bottom: MediaQuery.of(context).viewInsets.bottom + 24,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(translations?.filter ?? 'Filter',
              style: theme.textTheme.titleLarge),
          const SizedBox(height: 16),
          Text(translations?.budget ?? 'Budget range',
              style: theme.textTheme.titleMedium),
          RangeSlider(
            values: _budgetRange,
            min: 0,
            max: 5000,
            divisions: 100,
            labels: RangeLabels(
              '\$${_budgetRange.start.round()}',
              '\$${_budgetRange.end.round()}',
            ),
            onChanged: (value) => setState(() => _budgetRange = value),
          ),
          const SizedBox(height: 12),
          Text(translations?.distance ?? 'Distance (km)',
              style: theme.textTheme.titleMedium),
          Slider(
            value: _radius,
            min: 1,
            max: 100,
            divisions: 20,
            label: '${_radius.round()} km',
            onChanged: (value) => setState(() => _radius = value),
          ),
          const SizedBox(height: 12),
          Text(translations?.status ?? 'Status',
              style: theme.textTheme.titleMedium),
          const SizedBox(height: 8),
          Wrap(
            spacing: 10,
            children: ['open', 'awarded', 'closed']
                .map(
                  (status) => ChoiceChip(
                    label: Text(status.toUpperCase()),
                    selected: _status == status,
                    onSelected: (selected) =>
                        setState(() => _status = selected ? status : null),
                  ),
                )
                .toList(),
          ),
          const SizedBox(height: 24),
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: () => Navigator.of(context).pop(<String, dynamic>{}),
                  child: Text(translations?.clear ?? 'Clear'),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.of(context).pop(<String, dynamic>{
                      'budget_min': _budgetRange.start.round(),
                      'budget_max': _budgetRange.end.round(),
                      'radius_km': _radius.round(),
                      if (_status != null) 'status': _status,
                    });
                  },
                  child: Text(translations?.apply ?? 'Apply'),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
