import 'dart:convert';

class PlacementPayload {
  PlacementPayload({
    required this.slot,
    required this.items,
    required this.meta,
  });

  final String slot;
  final List<PlacementItem> items;
  final PlacementMeta meta;

  bool get hasItems => items.isNotEmpty;

  Map<String, dynamic> toJson() {
    return {
      'slot': slot,
      'items': items.map((item) => item.toJson()).toList(),
      'meta': meta.toJson(),
    };
  }

  factory PlacementPayload.fromJson(Map<String, dynamic> json) {
    final itemsJson = json['items'] as List<dynamic>? ?? [];
    return PlacementPayload(
      slot: json['slot'] as String? ?? '',
      items: itemsJson
          .map((item) => PlacementItem.fromJson(Map<String, dynamic>.from(item as Map)))
          .toList(),
      meta: PlacementMeta.fromJson(Map<String, dynamic>.from(json['meta'] as Map? ?? {})),
    );
  }
}

class PlacementMeta {
  PlacementMeta({
    required this.servedAt,
    required this.ttl,
    required this.staleTtl,
    required this.fresh,
  });

  final DateTime servedAt;
  final int ttl;
  final int staleTtl;
  final bool fresh;

  Map<String, dynamic> toJson() {
    return {
      'served_at': servedAt.toIso8601String(),
      'ttl': ttl,
      'stale_ttl': staleTtl,
      'fresh': fresh,
    };
  }

  factory PlacementMeta.fromJson(Map<String, dynamic> json) {
    return PlacementMeta(
      servedAt: DateTime.tryParse(json['served_at'] as String? ?? '') ?? DateTime.now(),
      ttl: (json['ttl'] as num?)?.toInt() ?? 60,
      staleTtl: (json['stale_ttl'] as num?)?.toInt() ?? 300,
      fresh: json['fresh'] as bool? ?? true,
    );
  }
}

class PlacementItem {
  PlacementItem({
    required this.id,
    required this.slot,
    required this.type,
    required this.variant,
    required this.creative,
    required this.provider,
    required this.services,
    required this.window,
    required this.zone,
  });

  final int? id;
  final String slot;
  final String type;
  final String? variant;
  final PlacementCreative creative;
  final PlacementProvider provider;
  final List<PlacementService> services;
  final PlacementWindow window;
  final String? zone;

  bool get hasServices => services.isNotEmpty;

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'slot': slot,
      'type': type,
      'variant': variant,
      'creative': creative.toJson(),
      'provider': provider.toJson(),
      'services': services.map((service) => service.toJson()).toList(),
      'window': window.toJson(),
      'zone': zone,
    };
  }

  factory PlacementItem.fromJson(Map<String, dynamic> json) {
    final servicesJson = json['services'] as List<dynamic>? ?? [];
    return PlacementItem(
      id: (json['id'] as num?)?.toInt(),
      slot: json['slot'] as String? ?? '',
      type: json['type'] as String? ?? 'banner',
      variant: json['variant'] as String?,
      creative: PlacementCreative.fromJson(Map<String, dynamic>.from(json['creative'] as Map? ?? {})),
      provider: PlacementProvider.fromJson(Map<String, dynamic>.from(json['provider'] as Map? ?? {})),
      services: servicesJson
          .map((service) => PlacementService.fromJson(Map<String, dynamic>.from(service as Map)))
          .toList(),
      window: PlacementWindow.fromJson(Map<String, dynamic>.from(json['window'] as Map? ?? {})),
      zone: json['zone']?.toString(),
    );
  }
}

class PlacementCreative {
  PlacementCreative({
    required this.kind,
    required this.sources,
    required this.placeholderColor,
    required this.aspectRatio,
  });

  final String kind;
  final List<PlacementSource> sources;
  final String placeholderColor;
  final double aspectRatio;

  bool get isVideo => kind == 'video';

  Map<String, dynamic> toJson() {
    return {
      'kind': kind,
      'sources': sources.map((source) => source.toJson()).toList(),
      'placeholder': {
        'dominant_color': placeholderColor,
      },
      'aspect_ratio': aspectRatio,
    };
  }

  factory PlacementCreative.fromJson(Map<String, dynamic> json) {
    final sourcesJson = json['sources'] as List<dynamic>? ?? [];
    return PlacementCreative(
      kind: json['kind'] as String? ?? 'image',
      sources: sourcesJson
          .map((source) => PlacementSource.fromJson(Map<String, dynamic>.from(source as Map)))
          .toList(),
      placeholderColor: json['placeholder'] is Map
          ? (json['placeholder'] as Map)['dominant_color'] as String? ?? '#d6d3d1'
          : '#d6d3d1',
      aspectRatio: (json['aspect_ratio'] as num?)?.toDouble() ?? 16 / 9,
    );
  }
}

class PlacementSource {
  PlacementSource({
    required this.url,
    required this.type,
  });

  final String url;
  final String? type;

  Map<String, dynamic> toJson() {
    return {
      'url': url,
      'type': type,
    };
  }

  factory PlacementSource.fromJson(Map<String, dynamic> json) {
    return PlacementSource(
      url: json['url'] as String? ?? '',
      type: json['type'] as String?,
    );
  }
}

class PlacementProvider {
  PlacementProvider({
    required this.id,
    required this.slug,
    required this.name,
  });

  final int? id;
  final String? slug;
  final String? name;

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'slug': slug,
      'name': name,
    };
  }

  factory PlacementProvider.fromJson(Map<String, dynamic> json) {
    return PlacementProvider(
      id: (json['id'] as num?)?.toInt(),
      slug: json['slug'] as String?,
      name: json['name'] as String?,
    );
  }
}

class PlacementService {
  PlacementService({
    required this.id,
    required this.title,
    required this.price,
    required this.discount,
    required this.requiredServicemen,
    required this.media,
  });

  final int id;
  final String? title;
  final num? price;
  final num? discount;
  final int? requiredServicemen;
  final List<PlacementServiceMedia> media;

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'price': price,
      'discount': discount,
      'required_servicemen': requiredServicemen,
      'media': media.map((item) => item.toJson()).toList(),
    };
  }

  factory PlacementService.fromJson(Map<String, dynamic> json) {
    final mediaJson = json['media'] as List<dynamic>? ?? [];
    return PlacementService(
      id: (json['id'] as num? ?? 0).toInt(),
      title: json['title'] as String?,
      price: json['price'] as num?,
      discount: json['discount'] as num?,
      requiredServicemen: (json['required_servicemen'] as num?)?.toInt(),
      media: mediaJson
          .map((media) => PlacementServiceMedia.fromJson(Map<String, dynamic>.from(media as Map)))
          .toList(),
    );
  }
}

class PlacementServiceMedia {
  PlacementServiceMedia({
    required this.id,
    required this.url,
    required this.collection,
  });

  final int id;
  final String url;
  final String? collection;

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'url': url,
      'collection': collection,
    };
  }

  factory PlacementServiceMedia.fromJson(Map<String, dynamic> json) {
    return PlacementServiceMedia(
      id: (json['id'] as num? ?? 0).toInt(),
      url: json['url'] as String? ?? '',
      collection: json['collection'] as String?,
    );
  }
}

class PlacementWindow {
  PlacementWindow({
    required this.start,
    required this.end,
  });

  final DateTime? start;
  final DateTime? end;

  Map<String, dynamic> toJson() {
    return {
      'start': start?.toIso8601String(),
      'end': end?.toIso8601String(),
    };
  }

  factory PlacementWindow.fromJson(Map<String, dynamic> json) {
    return PlacementWindow(
      start: _parseDate(json['start']),
      end: _parseDate(json['end']),
    );
  }

  static DateTime? _parseDate(dynamic value) {
    if (value is String && value.isNotEmpty) {
      return DateTime.tryParse(value);
    }
    return null;
  }
}

PlacementPayload placementPayloadFromJson(String source) {
  return PlacementPayload.fromJson(jsonDecode(source) as Map<String, dynamic>);
}

String placementPayloadToJson(PlacementPayload payload) {
  return jsonEncode(payload.toJson());
}
