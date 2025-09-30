import 'dart:convert';

import 'package:collection/collection.dart';
import 'package:hive/hive.dart';

import '../../config.dart';
import '../../models/provider_model.dart';
import '../logging/app_logger.dart';

class StorefrontRepository {
  StorefrontRepository({ApiServices? services, HiveInterface? hive})
      : _apiServices = services ?? apiServices,
        _hive = hive ?? Hive;

  final ApiServices _apiServices;
  final HiveInterface _hive;

  static const _cacheBox = 'storefront_cache_v1';

  Future<List<ProviderModel>> browse({
    String? search,
    bool preferCache = true,
    StorefrontSort sort = StorefrontSort.featured,
  }) async {
    if (preferCache) {
      final cached = await _readCache();
      if (cached.isNotEmpty) {
        return _applySort(cached, sort: sort, search: search);
      }
    }

    try {
      final uri = Uri.parse(api.provider).replace(queryParameters: {
        'with': 'services,categories,reviews,media',
        'sort': sort.apiValue,
        if (search != null && search.trim().isNotEmpty)
          'search': search.trim(),
      });

      final response = await _apiServices.getApi(
        uri.toString(),
        const [],
        isToken: true,
        isData: true,
      );

      if (response.isSuccess == true && response.data != null) {
        final payload = response.data;
        final providers = <ProviderModel>[];

        if (payload is List) {
          for (final entry in payload) {
            if (entry is Map<String, dynamic>) {
              providers.add(ProviderModel.fromJson(entry));
            } else if (entry is Map) {
              providers
                  .add(ProviderModel.fromJson(Map<String, dynamic>.from(entry)));
            }
          }
        } else if (payload is Map) {
          final data = payload['data'];
          if (data is List) {
            for (final entry in data) {
              if (entry is Map<String, dynamic>) {
                providers.add(ProviderModel.fromJson(entry));
              } else if (entry is Map) {
                providers.add(
                    ProviderModel.fromJson(Map<String, dynamic>.from(entry)));
              }
            }
          }
        }

        await _cacheProviders(providers);
        return _applySort(providers, sort: sort, search: search);
      }
    } catch (error, stackTrace) {
      AppLogger.instance.error(
        'StorefrontRepository: browse failed',
        error: error,
        stackTrace: stackTrace,
      );
    }

    final cached = await _readCache();
    if (cached.isNotEmpty) {
      return _applySort(cached, sort: sort, search: search);
    }

    return const [];
  }

  Future<void> _cacheProviders(List<ProviderModel> providers) async {
    if (!_hive.isBoxOpen(_cacheBox)) {
      await _hive.openBox<String>(_cacheBox);
    }

    final box = _hive.box<String>(_cacheBox);
    final entries = providers
        .map((provider) => StorefrontCacheEntry(
              provider: provider,
              cachedAt: DateTime.now(),
            ))
        .toList();

    await box.put(
      'providers',
      StorefrontCacheEntry.encodeList(entries),
    );
  }

  Future<List<ProviderModel>> _readCache() async {
    if (!_hive.isBoxOpen(_cacheBox)) {
      await _hive.openBox<String>(_cacheBox);
    }
    final box = _hive.box<String>(_cacheBox);
    final raw = box.get('providers');
    if (raw == null) {
      return const [];
    }

    return StorefrontCacheEntry.decodeList(raw)
        .sortedBy<DateTime>((entry) => entry.cachedAt)
        .map((entry) => entry.provider)
        .toList();
  }

  List<ProviderModel> _applySort(
    List<ProviderModel> providers, {
    StorefrontSort sort = StorefrontSort.featured,
    String? search,
  }) {
    Iterable<ProviderModel> workingSet = providers;

    if (search != null && search.trim().isNotEmpty) {
      final lower = search.trim().toLowerCase();
      workingSet = workingSet.where((provider) {
        final matchesName = provider.name?.toLowerCase().contains(lower) ?? false;
        final matchesDescription =
            provider.description?.toLowerCase().contains(lower) ?? false;
        final matchesCategory = provider.categories?.any((category) =>
                category.name?.toLowerCase().contains(lower) ?? false) ??
            false;
        return matchesName || matchesDescription || matchesCategory;
      });
    }

    switch (sort) {
      case StorefrontSort.highestRated:
        workingSet = workingSet.sorted((a, b) {
          final aRating = a.reviewRatings ?? 0;
          final bRating = b.reviewRatings ?? 0;
          final compare = bRating.compareTo(aRating);
          if (compare != 0) return compare;
          final aReviews = a.reviewCount ?? 0;
          final bReviews = b.reviewCount ?? 0;
          return bReviews.compareTo(aReviews);
        });
        break;
      case StorefrontSort.newest:
        workingSet = workingSet.sorted((a, b) {
          DateTime? parseDate(String? input) =>
              input != null ? DateTime.tryParse(input) : null;
          final aDate = parseDate(a.createdAt);
          final bDate = parseDate(b.createdAt);
          if (aDate == null && bDate == null) return 0;
          if (aDate == null) return 1;
          if (bDate == null) return -1;
          return bDate.compareTo(aDate);
        });
        break;
      case StorefrontSort.featured:
      default:
        workingSet = workingSet.sorted((a, b) {
          final aServed = double.tryParse(a.served ?? '') ?? 0;
          final bServed = double.tryParse(b.served ?? '') ?? 0;
          if (bServed != aServed) {
            return bServed.compareTo(aServed);
          }
          final aRating = a.reviewRatings ?? 0;
          final bRating = b.reviewRatings ?? 0;
          return bRating.compareTo(aRating);
        });
        break;
    }

    return workingSet.toList(growable: false);
  }
}

enum StorefrontSort { featured, highestRated, newest }

extension on StorefrontSort {
  String get apiValue {
    switch (this) {
      case StorefrontSort.highestRated:
        return 'rating';
      case StorefrontSort.newest:
        return 'newest';
      case StorefrontSort.featured:
      default:
        return 'featured';
    }
  }
}

class StorefrontCacheEntry {
  StorefrontCacheEntry({required this.provider, required this.cachedAt});

  final ProviderModel provider;
  final DateTime cachedAt;

  Map<String, dynamic> toJson() => {
        'provider': provider.toJson(),
        'cached_at': cachedAt.toIso8601String(),
      };

  static String encodeList(List<StorefrontCacheEntry> entries) =>
      jsonEncode(entries.map((entry) => entry.toJson()).toList());

  static List<StorefrontCacheEntry> decodeList(String raw) {
    final parsed = jsonDecode(raw) as List<dynamic>;
    return parsed
        .map((entry) => StorefrontCacheEntry.fromJson(
            Map<String, dynamic>.from(entry as Map<dynamic, dynamic>)))
        .toList();
  }

  factory StorefrontCacheEntry.fromJson(Map<String, dynamic> json) {
    return StorefrontCacheEntry(
      provider: ProviderModel.fromJson(
          Map<String, dynamic>.from(json['provider'] as Map<dynamic, dynamic>)),
      cachedAt: DateTime.parse(json['cached_at'] as String),
    );
  }
}
