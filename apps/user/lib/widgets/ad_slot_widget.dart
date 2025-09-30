import 'dart:async';

import 'package:cached_network_image/cached_network_image.dart';
import 'package:collection/collection.dart';
import 'package:flutter/material.dart';
import 'package:get_it/get_it.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher_string.dart';
import 'package:visibility_detector/visibility_detector.dart';

import '../config.dart';
import '../models/ads/placement_models.dart';
import '../services/ads/placement_repository.dart';
import 'dot_indicator.dart';

class AdSlotWidget extends StatefulWidget {
  const AdSlotWidget({
    super.key,
    required this.slot,
    this.zones,
    this.locale,
    this.height,
    this.aspectRatio,
    this.borderRadius,
    this.padding,
    this.margin,
    this.autoPlay = true,
    this.autoPlayInterval = const Duration(seconds: 6),
    this.showNavigationButtons = false,
    this.showPageIndicator = true,
    this.onPlacementTap,
    this.repository,
  });

  final String slot;
  final List<String>? zones;
  final String? locale;
  final double? height;
  final double? aspectRatio;
  final BorderRadiusGeometry? borderRadius;
  final EdgeInsetsGeometry? padding;
  final EdgeInsetsGeometry? margin;
  final bool autoPlay;
  final Duration autoPlayInterval;
  final bool showNavigationButtons;
  final bool showPageIndicator;
  final void Function(PlacementItem item)? onPlacementTap;
  final PlacementRepository? repository;

  @override
  State<AdSlotWidget> createState() => _AdSlotWidgetState();
}

class _AdSlotWidgetState extends State<AdSlotWidget> {
  PlacementPayload? _payload;
  Object? _error;
  bool _loading = false;
  bool _requested = false;
  int _currentIndex = 0;
  Timer? _autoPlayTimer;
  late final PageController _pageController;
  List<String>? _resolvedZones;

  @override
  void initState() {
    super.initState();
    _pageController = PageController();
  }

  @override
  void dispose() {
    _autoPlayTimer?.cancel();
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final borderRadius = widget.borderRadius ?? BorderRadius.circular(AppRadius.r8);
    final padding = widget.padding ?? EdgeInsets.zero;
    final margin = widget.margin ?? EdgeInsets.zero;
    final height = widget.height ?? (widget.aspectRatio != null
        ? MediaQuery.sizeOf(context).width / widget.aspectRatio!
        : Sizes.s220);

    return VisibilityDetector(
      key: ValueKey('ad-slot-${widget.slot}'),
      onVisibilityChanged: (info) {
        if (!_requested && info.visibleFraction > 0.2) {
          _requested = true;
          _loadPlacement();
        }
      },
      child: Container(
        margin: margin,
        child: Padding(
          padding: padding,
          child: AnimatedSize(
            duration: const Duration(milliseconds: 250),
            curve: Curves.easeInOut,
            child: SizedBox(
              height: height,
              child: _buildContent(borderRadius),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildContent(BorderRadiusGeometry borderRadius) {
    if (_payload != null && _payload!.items.isNotEmpty) {
      return Stack(
        children: [
          _buildCarousel(borderRadius),
          if (widget.showPageIndicator && _payload!.items.length > 1)
            Positioned(
              bottom: Insets.i12,
              left: 0,
              right: 0,
              child: Center(
                child: DotIndicator(
                  list: List.generate(_payload!.items.length, (index) => index),
                  selectedIndex: _currentIndex,
                ),
              ),
            ),
          if (widget.showNavigationButtons && _payload!.items.length > 1)
            _NavigationButton(
              alignment: Alignment.centerLeft,
              onPressed: () {
                final previous = (_currentIndex - 1) % _payload!.items.length;
                _goTo(previous);
              },
            ),
          if (widget.showNavigationButtons && _payload!.items.length > 1)
            _NavigationButton(
              alignment: Alignment.centerRight,
              onPressed: () {
                final next = (_currentIndex + 1) % _payload!.items.length;
                _goTo(next);
              },
            ),
        ],
      );
    }

    if (_loading) {
      return _buildSkeleton(borderRadius);
    }

    if (_error != null) {
      return _buildFallback(borderRadius);
    }

    return _buildSkeleton(borderRadius);
  }

  Widget _buildCarousel(BorderRadiusGeometry borderRadius) {
    final items = _payload!.items;
    return ClipRRect(
      borderRadius: borderRadius,
      child: PageView.builder(
        controller: _pageController,
        physics: const PageScrollPhysics(),
        itemCount: items.length,
        onPageChanged: (index) {
          setState(() => _currentIndex = index);
        },
        itemBuilder: (context, index) {
          final item = items[index];
          return _buildCreative(item, borderRadius).inkWell(
            onTap: () => _handleTap(item),
          );
        },
      ),
    );
  }

  Widget _buildSkeleton(BorderRadiusGeometry borderRadius) {
    return ClipRRect(
      borderRadius: borderRadius,
      child: Container(
        decoration: BoxDecoration(
          color: appColor(context).stroke.withOpacity(0.2),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
          children: List.generate(
            3,
            (index) => Expanded(
              child: Container(
                margin: EdgeInsets.symmetric(horizontal: Insets.i6),
                decoration: BoxDecoration(
                  color: appColor(context).stroke.withOpacity(0.4 + (index * 0.1)),
                  borderRadius: BorderRadius.circular(AppRadius.r10),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildFallback(BorderRadiusGeometry borderRadius) {
    return ClipRRect(
      borderRadius: borderRadius,
      child: Container(
        color: appColor(context).stroke.withOpacity(0.25),
        alignment: Alignment.center,
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.image_not_supported_outlined, color: appColor(context).lightText),
            const VSpace(Sizes.s8),
            Text(
              language(context, translations?.noDataFound) ?? 'Ad unavailable',
              style: appCss.dmDenseRegular14.textColor(appColor(context).lightText),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCreative(PlacementItem item, BorderRadiusGeometry borderRadius) {
    final creative = item.creative;
    if (creative.isVideo) {
      return _buildVideoCreative(item, borderRadius);
    }
    return _buildImageCreative(item, borderRadius);
  }

  Widget _buildImageCreative(PlacementItem item, BorderRadiusGeometry borderRadius) {
    final creative = item.creative;
    final preferred = _selectImageSource(creative.sources);
    final placeholderColor = _colorFromHex(creative.placeholderColor);
    return DecoratedBox(
      decoration: BoxDecoration(
        color: placeholderColor,
        borderRadius: borderRadius,
      ),
      child: preferred == null
          ? _buildFallback(borderRadius)
          : CachedNetworkImage(
              imageUrl: preferred,
              fit: BoxFit.cover,
              placeholder: (context, url) => Container(color: placeholderColor),
              errorWidget: (context, url, error) => _buildFallback(borderRadius),
            ),
    );
  }

  Widget _buildVideoCreative(PlacementItem item, BorderRadiusGeometry borderRadius) {
    final placeholderColor = _colorFromHex(item.creative.placeholderColor);
    return Container(
      decoration: BoxDecoration(
        color: placeholderColor,
        borderRadius: borderRadius,
      ),
      child: Stack(
        alignment: Alignment.center,
        children: [
          Icon(Icons.play_circle_outline, size: Sizes.s50, color: appColor(context).whiteBg.withOpacity(0.85)),
          Positioned(
            bottom: Insets.i12,
            right: Insets.i12,
            child: Text(
              translations?.watchNow ?? 'Watch',
              style: appCss.dmDenseMedium12.textColor(appColor(context).whiteBg),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _handleTap(PlacementItem item) async {
    if (widget.onPlacementTap != null) {
      widget.onPlacementTap!(item);
      return;
    }

    if (item.creative.isVideo) {
      final url = item.creative.sources.firstWhereOrNull((source) => source.url.isNotEmpty)?.url;
      if (url != null && url.isNotEmpty) {
        await launchUrlString(url, mode: LaunchMode.externalApplication);
      }
    }
  }

  Future<void> _loadPlacement() async {
    final repository = widget.repository ?? GetIt.I<PlacementRepository>();
    final locale = _resolveLocale();
    final zones = await _resolveZones();

    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final result = await repository.loadPlacement(
        slot: widget.slot,
        zones: zones,
        locale: locale,
      );
      if (!mounted) return;
      setState(() {
        _payload = result.payload;
        _loading = false;
        _currentIndex = 0;
      });
      _restartAutoPlay();
      if (result.shouldRevalidate) {
        unawaited(_backgroundRevalidate(repository, zones, locale));
      }
    } catch (error) {
      if (!mounted) return;
      setState(() {
        _error = error;
        _loading = false;
      });
    }
  }

  Future<void> _backgroundRevalidate(PlacementRepository repository, List<String> zones, String locale) async {
    try {
      final refreshed = await repository.revalidatePlacement(
        slot: widget.slot,
        zones: zones,
        locale: locale,
      );
      if (!mounted) {
        return;
      }
      if (refreshed.source == PlacementDataSource.network) {
        setState(() {
          _payload = refreshed.payload;
          _currentIndex = 0;
        });
        _restartAutoPlay();
      }
    } catch (error) {
      debugPrint('Failed to refresh placement ${widget.slot}: $error');
    }
  }

  void _restartAutoPlay() {
    _autoPlayTimer?.cancel();
    if (!widget.autoPlay || _payload == null || _payload!.items.length <= 1) {
      return;
    }

    _autoPlayTimer = Timer.periodic(widget.autoPlayInterval, (timer) {
      if (!mounted || !_pageController.hasClients || _payload == null || _payload!.items.isEmpty) {
        return;
      }
      final next = (_currentIndex + 1) % _payload!.items.length;
      _goTo(next);
    });
  }

  void _goTo(int index) {
    if (!_pageController.hasClients) {
      return;
    }
    _pageController.animateToPage(
      index,
      duration: const Duration(milliseconds: 450),
      curve: Curves.easeOut,
    );
  }

  Future<List<String>> _resolveZones() async {
    if (widget.zones != null) {
      return widget.zones!;
    }
    if (_resolvedZones != null) {
      return _resolvedZones!;
    }
    try {
      final preferences = GetIt.I<SharedPreferences>();
      final list = preferences.getStringList(session.zoneIds);
      if (list != null && list.isNotEmpty) {
        _resolvedZones = list;
        return list;
      }
      final raw = preferences.getString(session.zoneIds);
      if (raw != null && raw.isNotEmpty) {
        final zones = raw
            .split(',')
            .map((value) => value.trim())
            .where((value) => value.isNotEmpty)
            .toList();
        _resolvedZones = zones;
        return zones;
      }
    } catch (_) {
      // ignore and fall back to empty zones
    }
    _resolvedZones = const [];
    return const [];
  }

  String _resolveLocale() {
    if (widget.locale != null && widget.locale!.isNotEmpty) {
      return widget.locale!;
    }
    final locale = Localizations.maybeLocaleOf(context);
    if (locale != null) {
      return locale.toLanguageTag();
    }
    return 'en';
  }

  String? _selectImageSource(List<PlacementSource> sources) {
    final priority = ['image/avif', 'image/webp'];
    for (final mime in priority) {
      final match = sources.firstWhereOrNull((source) => source.type == mime && source.url.isNotEmpty);
      if (match != null) {
        return match.url;
      }
    }
    final first = sources.firstWhereOrNull((source) => source.url.isNotEmpty);
    return first?.url;
  }

  Color _colorFromHex(String value) {
    var hex = value.replaceAll('#', '');
    if (hex.length == 6) {
      hex = 'FF$hex';
    }
    final parsed = int.tryParse(hex, radix: 16);
    if (parsed == null) {
      return appColor(context).stroke.withOpacity(0.3);
    }
    return Color(parsed);
  }
}

class _NavigationButton extends StatelessWidget {
  const _NavigationButton({
    required this.alignment,
    required this.onPressed,
  });

  final Alignment alignment;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: alignment,
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: Insets.i8),
        decoration: BoxDecoration(
          color: appColor(context).whiteBg,
          borderRadius: BorderRadius.circular(AppRadius.r20),
          boxShadow: [
            BoxShadow(
              color: appColor(context).darkText.withOpacity(0.08),
              blurRadius: 6,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: IconButton(
          icon: Icon(
            alignment == Alignment.centerLeft ? Icons.chevron_left : Icons.chevron_right,
            color: appColor(context).primary,
          ),
          onPressed: onPressed,
        ),
      ),
    );
  }
}
