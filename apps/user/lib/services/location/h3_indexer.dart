import 'dart:math' as math;

class H3Indexer {
  const H3Indexer({this.resolution = 8});

  final int resolution;

  int indexFor(double latitude, double longitude) {
    final normalisedResolution = resolution.clamp(0, 15);
    final lat = latitude.clamp(-90.0, 90.0);
    final lng = longitude.clamp(-180.0, 180.0);

    final scale = 1000.0 * math.pow(2, normalisedResolution);
    final latBucket = ((lat + 90.0) * scale).floor();
    final lngBucket = ((lng + 180.0) * scale).floor();

    return (latBucket << 32) | (lngBucket & 0xFFFFFFFF);
  }
}
