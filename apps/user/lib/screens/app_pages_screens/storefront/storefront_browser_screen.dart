import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../../config.dart';
import '../../../models/provider_model.dart';
import '../../../services/marketplace/storefront_repository.dart';

enum _StorefrontSortOption { featured, highestRated, newest }

class StorefrontBrowserScreen extends StatefulWidget {
  const StorefrontBrowserScreen({super.key});

  @override
  State<StorefrontBrowserScreen> createState() => _StorefrontBrowserScreenState();
}

class _StorefrontBrowserScreenState extends State<StorefrontBrowserScreen> {
  final StorefrontRepository _repository = StorefrontRepository();
  final TextEditingController _searchCtrl = TextEditingController();
  final FocusNode _searchFocus = FocusNode();

  late Future<List<ProviderModel>> _future;
  List<ProviderModel> _providers = const [];
  String _query = '';
  _StorefrontSortOption _sort = _StorefrontSortOption.featured;
  double _minimumRating = 0;

  @override
  void initState() {
    super.initState();
    _future = _loadProviders();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    _searchFocus.dispose();
    super.dispose();
  }

  Future<List<ProviderModel>> _loadProviders({bool preferCache = true}) async {
    final providers = await _repository.browse(
      search: _query.isEmpty ? null : _query,
      preferCache: preferCache,
      sort: _mapSort(_sort),
    );
    if (!mounted) return providers;
    setState(() {
      _providers = providers;
    });
    return providers;
  }

  Future<void> _refresh() async {
    setState(() {
      _future = _loadProviders(preferCache: false);
    });
    await _future;
  }

  StorefrontSort _mapSort(_StorefrontSortOption option) {
    switch (option) {
      case _StorefrontSortOption.highestRated:
        return StorefrontSort.highestRated;
      case _StorefrontSortOption.newest:
        return StorefrontSort.newest;
      case _StorefrontSortOption.featured:
      default:
        return StorefrontSort.featured;
    }
  }

  Iterable<ProviderModel> get _filteredProviders {
    Iterable<ProviderModel> workingSet = _providers;

    if (_minimumRating > 0) {
      workingSet = workingSet.where(
        (provider) => (provider.reviewRatings ?? 0) >= _minimumRating,
      );
    }

    return workingSet;
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Marketplace storefronts'),
        actions: [
          IconButton(
            tooltip: 'Refresh',
            onPressed: _refresh,
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: SafeArea(
        child: FutureBuilder<List<ProviderModel>>(
          future: _future,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting &&
                _providers.isEmpty) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snapshot.hasError && _providers.isEmpty) {
              return EmptyLayout(
                title: 'Unable to load storefronts',
                subtitle: snapshot.error.toString(),
                buttonText: translations?.retry ?? 'Retry',
                onTap: _refresh,
              );
            }
            if (_providers.isEmpty) {
              return EmptyLayout(
                title: 'No storefronts yet',
                subtitle:
                    'Once providers publish products and services they will appear in this marketplace view.',
                buttonText: translations?.refresh ?? 'Refresh',
                onTap: _refresh,
              );
            }

            final providers = _filteredProviders.toList(growable: false);
            return RefreshIndicator(
              onRefresh: _refresh,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  _buildSearch(theme),
                  const SizedBox(height: 12),
                  _buildSortAndFilters(theme),
                  const SizedBox(height: 16),
                  if (providers.isEmpty)
                    EmptyLayout(
                      title: 'No storefronts match the filters',
                      subtitle:
                          'Try reducing the minimum rating or clearing the search field.',
                      buttonText: 'Reset filters',
                      onTap: () {
                        setState(() {
                          _minimumRating = 0;
                          _sort = _StorefrontSortOption.featured;
                          _query = '';
                          _searchCtrl.clear();
                          _future = _loadProviders();
                        });
                      },
                    )
                  else
                    ...providers.map(_buildStorefrontCard),
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildSearch(ThemeData theme) {
    return TextField(
      controller: _searchCtrl,
      focusNode: _searchFocus,
      textInputAction: TextInputAction.search,
      decoration: InputDecoration(
        prefixIcon: const Icon(Icons.search),
        labelText: 'Search providers or categories',
        suffixIcon: _query.isEmpty
            ? null
            : IconButton(
                tooltip: 'Clear',
                icon: const Icon(Icons.clear),
                onPressed: () {
                  _searchCtrl.clear();
                  setState(() {
                    _query = '';
                    _future = _loadProviders();
                  });
                },
              ),
      ),
      onSubmitted: (value) {
        setState(() {
          _query = value.trim();
          _future = _loadProviders(preferCache: false);
        });
      },
      onChanged: (value) {
        setState(() {
          _query = value.trim();
        });
      },
    );
  }

  Widget _buildSortAndFilters(ThemeData theme) {
    final headlineStyle = theme.textTheme.labelLarge;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Sort by', style: headlineStyle),
        const SizedBox(height: 8),
        Wrap(
          spacing: 8,
          children: [
            ChoiceChip(
              label: const Text('Featured'),
              selected: _sort == _StorefrontSortOption.featured,
              onSelected: (value) {
                if (!value) return;
                setState(() {
                  _sort = _StorefrontSortOption.featured;
                  _future = _loadProviders();
                });
              },
            ),
            ChoiceChip(
              label: const Text('Highest rated'),
              selected: _sort == _StorefrontSortOption.highestRated,
              onSelected: (value) {
                if (!value) return;
                setState(() {
                  _sort = _StorefrontSortOption.highestRated;
                  _future = _loadProviders();
                });
              },
            ),
            ChoiceChip(
              label: const Text('Newest'),
              selected: _sort == _StorefrontSortOption.newest,
              onSelected: (value) {
                if (!value) return;
                setState(() {
                  _sort = _StorefrontSortOption.newest;
                  _future = _loadProviders();
                });
              },
            ),
          ],
        ),
        const SizedBox(height: 16),
        Text('Minimum rating', style: headlineStyle),
        const SizedBox(height: 8),
        Slider(
          value: _minimumRating,
          onChanged: (value) {
            setState(() {
              _minimumRating = value;
            });
          },
          onChangeEnd: (_) => setState(() {
            _future = _loadProviders();
          }),
          min: 0,
          max: 5,
          divisions: 10,
          label: _minimumRating.toStringAsFixed(1),
        ),
      ],
    );
  }

  Widget _buildStorefrontCard(ProviderModel provider) {
    final theme = Theme.of(context);
    final rating = provider.reviewRatings ?? 0;
    final reviews = provider.reviewCount ?? 0;
    final categories = provider.categories ?? const [];
    final services = provider.services ?? provider.relatedServices ?? const [];
    final formatter = NumberFormat.decimalPattern();
    final currencyFormatter = NumberFormat.currency(
      symbol: currency(context).priceSymbol,
      decimalDigits: 2,
    );

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () {
          route.pushNamed(
            context,
            routeName.providerDetailsScreen,
            arg: {'providerId': provider.id, 'provider': provider},
          );
        },
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          provider.name ?? 'Untitled storefront',
                          style: theme.textTheme.titleMedium
                              ?.copyWith(fontWeight: FontWeight.w700),
                        ),
                        if (provider.description?.isNotEmpty ?? false)
                          Padding(
                            padding: const EdgeInsets.only(top: 4),
                            child: Text(
                              provider.description!,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: theme.textTheme.bodySmall,
                            ),
                          ),
                      ],
                    ),
                  ),
                  Column(
                    children: [
                      Chip(
                        avatar: const Icon(Icons.star_rate_rounded, size: 18),
                        label: Text('${rating.toStringAsFixed(1)} • ${formatter.format(reviews)}'),
                      ),
                      if (provider.served != null)
                        Padding(
                          padding: const EdgeInsets.only(top: 6),
                          child: Chip(
                            avatar: const Icon(Icons.handshake_outlined, size: 18),
                            label: Text('${provider.served} jobs served'),
                          ),
                        ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 12),
              if (categories.isNotEmpty)
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: categories
                      .take(6)
                      .map((category) => Chip(label: Text(category.name ?? 'Category')))
                      .toList(),
                ),
              if (services.isNotEmpty) ...[
                const SizedBox(height: 12),
                Text(
                  'Popular services',
                  style: theme.textTheme.labelLarge,
                ),
                const SizedBox(height: 6),
                ...services.take(3).map(
                  (service) => ListTile(
                    contentPadding: EdgeInsets.zero,
                    dense: true,
                    leading: const Icon(Icons.check_circle_outline),
                    title: Text(service.title ?? 'Service'),
                    subtitle: Text(
                      service.description ?? 'No description provided yet.',
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    trailing: service.price != null
                        ? Text(
                            currencyFormatter.format(
                              (service.price is num)
                                  ? (service.price as num).toDouble()
                                  : double.tryParse(service.price.toString()) ?? 0,
                            ),
                            style: theme.textTheme.bodyMedium
                                ?.copyWith(fontWeight: FontWeight.w600),
                          )
                        : null,
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
