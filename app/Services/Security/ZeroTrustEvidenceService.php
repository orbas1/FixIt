<?php

namespace App\Services\Security;

use App\Models\ZeroTrustAccessEvent;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ZeroTrustEvidenceService
{
    public function captureSnapshot(?Carbon $date = null): array
    {
        $date ??= Carbon::now();
        $from = $date->copy()->startOfDay();
        $to = $date->copy()->endOfDay();

        $events = ZeroTrustAccessEvent::query()
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $decisionCounts = $events
            ->groupBy('decision')
            ->map->count();

        $signalFrequency = $this->aggregateSignals($events);

        return [
            'captured_at' => Carbon::now()->toIso8601String(),
            'interval' => [
                'start' => $from->toIso8601String(),
                'end' => $to->toIso8601String(),
            ],
            'totals' => [
                'events' => $events->count(),
                'unique_users' => $events->pluck('user_id')->unique()->count(),
                'decisions' => [
                    'allow' => (int) $decisionCounts->get('allow', 0),
                    'challenge' => (int) $decisionCounts->get('challenge', 0),
                    'deny' => (int) $decisionCounts->get('deny', 0),
                ],
            ],
            'signals' => [
                'top' => $signalFrequency->take(10)->map(function ($count, $signal) use ($events) {
                    $percentage = $events->count() > 0 ? round(($count / $events->count()) * 100, 2) : 0;

                    return [
                        'signal' => $signal,
                        'count' => $count,
                        'percentage' => $percentage,
                    ];
                })->values()->all(),
            ],
            'controls' => [
                'enforced' => $this->aggregateControls($events)->all(),
            ],
        ];
    }

    public function persistSnapshot(array $snapshot): string
    {
        $timestamp = Carbon::now();
        $path = sprintf(
            'zero-trust/%s/snapshot-%s.json',
            $timestamp->format('Y/m/d'),
            $timestamp->format('His')
        );

        Storage::disk('security_evidence')->put($path, json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $path;
    }

    protected function aggregateSignals(Collection $events): Collection
    {
        return $events
            ->flatMap(function (ZeroTrustAccessEvent $event) {
                return Arr::wrap($event->signals);
            })
            ->filter()
            ->flatten()
            ->map(function ($signal) {
                if (is_array($signal)) {
                    return Arr::get($signal, 'name');
                }

                return $signal;
            })
            ->filter()
            ->countBy()
            ->sortDesc();
    }

    protected function aggregateControls(Collection $events): Collection
    {
        return $events
            ->flatMap(function (ZeroTrustAccessEvent $event) {
                return Arr::wrap($event->enforced_controls);
            })
            ->filter()
            ->countBy()
            ->sortDesc()
            ->map(function ($count, $control) use ($events) {
                $total = max($events->count(), 1);

                return [
                    'control' => $control,
                    'count' => $count,
                    'percentage' => round(($count / $total) * 100, 2),
                ];
            })
            ->values();
    }
}
