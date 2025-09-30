<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class MonitorApiPerformance
{
    public function handle(Request $request, Closure $next): Response
    {
        $isApiRequest = str_starts_with($request->path(), 'api/');

        if (! $isApiRequest || ! Config::get('performance.query_budget.enabled', true)) {
            return $next($request);
        }

        $shouldProfileQueries = $this->shouldProfileQueries();
        $slowQueryThresholdMs = (float) Config::get('performance.query_budget.slow_query_threshold_ms', 150.0);
        $defaultMaxQueries = (int) Config::get('performance.query_budget.default_max_queries', 25);

        $routeName = $request->route()?->getName() ?? $request->path();
        $routeSpecificBudget = Config::get('performance.query_budget.routes.' . $routeName);
        $maxQueries = (int) ($routeSpecificBudget ?? $defaultMaxQueries);

        $start = microtime(true);
        $queries = [];

        if ($shouldProfileQueries) {
            $connection = DB::connection();
            $connection->flushQueryLog();
            $connection->enableQueryLog();
        }

        /** @var Response $response */
        $response = $next($request);

        $durationMs = (microtime(true) - $start) * 1000;

        if ($shouldProfileQueries) {
            $connection = DB::connection();
            $queries = $connection->getQueryLog();
            $connection->disableQueryLog();
        }

        $queryCount = count($queries);
        $slowQueries = [];
        $totalQueryTime = 0.0;

        foreach ($queries as $query) {
            $time = isset($query['time']) ? (float) $query['time'] : 0.0;
            $totalQueryTime += $time;
            if ($time >= $slowQueryThresholdMs) {
                $slowQueries[] = [
                    'sql' => $query['query'] ?? 'unknown',
                    'time_ms' => $time,
                    'bindings' => $query['bindings'] ?? [],
                ];
            }
        }

        $this->logIfBudgetsExceeded(
            $request,
            $routeName,
            $durationMs,
            $queryCount,
            $maxQueries,
            $slowQueries,
            $totalQueryTime
        );

        $response->headers->set('X-Request-Duration', number_format($durationMs, 2, '.', ''));
        $response->headers->set('X-Query-Count', (string) $queryCount);
        $response->headers->set('X-Query-Time', number_format($totalQueryTime, 2, '.', ''));

        if ($queryCount > $maxQueries) {
            $response->headers->set('X-Query-Budget-Exceeded', 'true');
        }

        return $response;
    }

    protected function shouldProfileQueries(): bool
    {
        try {
            $connection = DB::connection();
            return method_exists($connection, 'enableQueryLog');
        } catch (BindingResolutionException $exception) {
            Log::channel('performance')->error('Failed to resolve database connection for performance profiling', [
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    protected function logIfBudgetsExceeded(
        Request $request,
        string $routeName,
        float $durationMs,
        int $queryCount,
        int $maxQueries,
        array $slowQueries,
        float $totalQueryTime
    ): void {
        $latencyBudget = (float) Config::get('performance.latency_budget_ms.api', 400.0);
        $channel = Log::channel('performance');

        if ($durationMs > $latencyBudget) {
            $channel->warning('API latency budget exceeded', [
                'route' => $routeName,
                'duration_ms' => $durationMs,
                'latency_budget_ms' => $latencyBudget,
                'method' => $request->getMethod(),
                'path' => $request->path(),
            ]);
        }

        if ($queryCount > $maxQueries) {
            $channel->warning('Query budget exceeded', [
                'route' => $routeName,
                'query_count' => $queryCount,
                'query_budget' => $maxQueries,
                'method' => $request->getMethod(),
                'path' => $request->path(),
            ]);
        }

        foreach ($slowQueries as $slowQuery) {
            $channel->notice('Slow query detected', [
                'route' => $routeName,
                'sql' => $slowQuery['sql'],
                'time_ms' => $slowQuery['time_ms'],
                'method' => $request->getMethod(),
                'path' => $request->path(),
            ]);
        }

        if ($totalQueryTime > $durationMs * 0.75) {
            $channel->info('Queries dominating request time', [
                'route' => $routeName,
                'total_query_time_ms' => $totalQueryTime,
                'duration_ms' => $durationMs,
                'method' => $request->getMethod(),
                'path' => $request->path(),
            ]);
        }
    }
}
