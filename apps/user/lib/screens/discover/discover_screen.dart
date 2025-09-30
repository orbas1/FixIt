import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../config.dart';
import '../../models/feed_job_model.dart';
import '../../providers/app_pages_providers/feed/feed_provider.dart';

class DiscoverScreen extends StatefulWidget {
  const DiscoverScreen({super.key});

  @override
  State<DiscoverScreen> createState() => _DiscoverScreenState();
}

class _DiscoverScreenState extends State<DiscoverScreen> {
  final TextEditingController _searchController = TextEditingController();
  final List<String> _distanceOptions = const ['5 km', '10 km', '25 km'];
  final List<String> _statusOptions = const ['open', 'awarded'];

  String? _selectedDistance;
  String? _selectedStatus;
  FeedProvider? _feedProvider;
  Future<void>? _bootstrapFuture;

  @override
  void initState() {
    super.initState();
    _searchController.addListener(_onSearchChanged);
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final provider = Provider.of<FeedProvider>(context);
    if (_feedProvider != provider) {
      _feedProvider = provider;
      _bootstrapFuture = provider.bootstrap(
        forceRefresh: provider.jobs.isEmpty,
        filters: _buildFilters(),
      );
    }
  }

  @override
  void dispose() {
    _searchController.removeListener(_onSearchChanged);
    _searchController.dispose();
    super.dispose();
  }

  void _onSearchChanged() {
    final query = _searchController.text.trim();
    _feedProvider?.fetchNext(reset: true, filters: _buildFilters(query: query));
  }

  Map<String, dynamic> _buildFilters({String? query}) {
    final filters = <String, dynamic>{};
    final search = query ?? _searchController.text.trim();
    if (search.isNotEmpty) {
      filters['q'] = search;
    }
    if (_selectedDistance != null) {
      filters['radius_km'] = int.tryParse(_selectedDistance!.split(' ').first);
    }
    if (_selectedStatus != null) {
      filters['status'] = _selectedStatus;
    }
    return filters;
  }

  Future<void> _refresh() async {
    await _feedProvider?.fetchNext(reset: true, filters: _buildFilters());
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: Text(translations?.landingDiscoverTitle ?? 'Discover work'),
        actions: [
          IconButton(
            tooltip: translations?.refresh ?? 'Refresh',
            onPressed: () => _feedProvider?.fetchNext(reset: true, filters: _buildFilters()),
            icon: const Icon(Icons.refresh),
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
                _buildFilterChips(theme),
                const Divider(height: 1),
                Expanded(
                  child: RefreshIndicator(
                    onRefresh: _refresh,
                    child: Consumer<FeedProvider>(
                      builder: (context, provider, child) {
                        if (provider.isLoading && provider.jobs.isEmpty) {
                          return const Center(child: CircularProgressIndicator());
                        }
                        if (provider.jobs.isEmpty) {
                          return EmptyLayout(
                            title: translations?.noDataFound ?? 'No results found',
                            subtitle: translations?.noDataFoundDesc ??
                                'Try adjusting your filters or widening your search radius.',
                            buttonText: translations?.refresh ?? 'Refresh',
                            onTap: _refresh,
                          );
                        }
                        return ListView.separated(
                          physics: const AlwaysScrollableScrollPhysics(),
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                          itemCount: provider.jobs.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 12),
                          itemBuilder: (context, index) {
                            final job = provider.jobs[index];
                            return _DiscoverJobTile(job: job);
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
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
      child: TextField(
        controller: _searchController,
        decoration: InputDecoration(
          hintText: translations?.search ?? 'Search services or keywords',
          prefixIcon: const Icon(Icons.search),
          suffixIcon: _searchController.text.isEmpty
              ? null
              : IconButton(
                  icon: const Icon(Icons.clear),
                  onPressed: () {
                    _searchController.clear();
                    _feedProvider?.fetchNext(reset: true, filters: _buildFilters(query: ''));
                  },
                ),
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(20)),
        ),
      ),
    );
  }

  Widget _buildFilterChips(ThemeData theme) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Wrap(
        spacing: 10,
        runSpacing: 10,
        children: [
          for (final distance in _distanceOptions)
            FilterChip(
              label: Text(distance),
              selected: _selectedDistance == distance,
              onSelected: (selected) {
                setState(() {
                  _selectedDistance = selected ? distance : null;
                });
                _feedProvider?.fetchNext(reset: true, filters: _buildFilters());
              },
            ),
          for (final status in _statusOptions)
            FilterChip(
              label: Text(status.toUpperCase()),
              selected: _selectedStatus == status,
              onSelected: (selected) {
                setState(() {
                  _selectedStatus = selected ? status : null;
                });
                _feedProvider?.fetchNext(reset: true, filters: _buildFilters());
              },
            ),
        ],
      ),
    );
  }
}

class _DiscoverJobTile extends StatelessWidget {
  const _DiscoverJobTile({required this.job});

  final FeedJobModel job;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final currencyFormatter = NumberFormat.simpleCurrency(name: job.currency ?? 'USD');
    final budget = job.finalBudget ?? job.initialBudget ?? 0;

    return ListTile(
      onTap: () => GoRouter.of(context).push('/jobs/${job.id}', extra: job),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
      tileColor: theme.colorScheme.surfaceVariant,
      title: Text(job.title, style: theme.textTheme.titleMedium),
      subtitle: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (job.description != null)
            Text(
              job.description!,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          const SizedBox(height: 6),
          Wrap(
            spacing: 8,
            runSpacing: 4,
            children: [
              Chip(
                label: Text(currencyFormatter.format(budget)),
                backgroundColor: theme.colorScheme.primaryContainer,
                labelStyle: theme.textTheme.bodyMedium?.copyWith(
                  color: theme.colorScheme.onPrimaryContainer,
                  fontWeight: FontWeight.bold,
                ),
              ),
              if (job.distanceKm != null)
                Chip(label: Text('${job.distanceKm!.toStringAsFixed(1)} km away')),
              Chip(label: Text(job.status.toUpperCase())),
            ],
          ),
        ],
      ),
      trailing: const Icon(Icons.chevron_right),
    );
  }
}
