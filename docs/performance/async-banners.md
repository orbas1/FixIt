# Async Banner & Placement Improvements

## Diagnosis Summary

- **Blocking rendering:** Banner markup on home and category pages rendered synchronously with Blade, loading multiple full-resolution images before first contentful paint.
- **Redundant queries:** Each request executed identical ad queries without reuse, increasing DB load.
- **Layout shift:** Images loaded without reserved aspect ratios causing CLS spikes (>0.08 measured pre-change).
- **Fragmented caching:** No shared API for placements; mobile/web implemented distinct logic.

## Remediation Plan

| Area | Action | Owner | Metric |
| --- | --- | --- | --- |
| Placement delivery | Introduce `GET /api/v1/placements/{slot}` with SWR caching and ETags | Platform | 60s cache / 300s stale window |
| Web rendering | Replace Blade loops with `<ad-slot>` custom element, lazy fetch via IntersectionObserver | Frontend | CLS < 0.02 |
| Mobile rendering | Add `AdSlotWidget` using cached Dio client + Hive SWR | Mobile | Scroll load < 16ms |
| Measurement | Capture LCP/CLS/INP via Web Vitals endpoint, persist to log for regression tracking | Platform | 95th percentile trend |

## Implementation Notes

- **PlacementService** centralises slot config (`config/placements.php`), caches payload + ETag in Redis/Cache for TTL/stale windows, dispatches refresh jobs after TTL.
- **Media processing** ensures AVIF/WEBP conversions and dominant color placeholder to prevent flashes.
- **Front-end** custom element caches responses in `localStorage`, honours `If-None-Match`, and renders skeletons while fetching.
- **Mobile** widget defers fetch until visible, persists to Hive cache, revalidates in background.
- **Flutter mobile** now ships `AdSlotWidget` backed by a SWR repository, Hive cache, and Dio client registered in the dependency container for consistent async rendering and navigation hooks.
- **Measurement** uses `/api/v1/metrics/web-vitals` to log aggregated metrics (beacon friendly).

## Rollout Checklist

- [x] Configured placement slots (`home_special_offers`, `category_special_offers`, `feed_inline`).
- [x] Updated backend repositories & forms to manage slot identifiers.
- [x] Added feature tests covering caching, zone filtering, and error handling.
- [x] Documented async strategy and metrics pipeline (this file).
- [x] Enabled Web Vitals sampling (35% default) with `navigator.sendBeacon` fallback.
- [x] Integrated Flutter placement repository, widget, and regression tests for SWR behaviour.

## Metrics & Budgets

- **CLS budget:** 0.02 for pages with banners (enforced via CI Lighthouse).
- **LCP improvement target:** ≥15% vs. synchronous baseline.
- **Ad payload budget:** <20KB gzip per page for creative metadata (creative assets cached separately).
- **SLO:** Placement API p95 < 120ms, error rate < 0.1%.

## Follow-up

1. Integrate queue worker to process placement refresh asynchronously in production.
2. Extend measurement pipeline to aggregate metrics into DataDog/Splunk dashboards.
3. Add AB experiment toggles for sampling fetch strategies (preload vs. intersection).
4. Backfill placement slots for legacy ads (script available in seeder roadmap).
