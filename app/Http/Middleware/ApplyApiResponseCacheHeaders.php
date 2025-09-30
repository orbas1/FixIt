<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyApiResponseCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($request->isMethodSafe() && str_starts_with($request->path(), 'api/')) {
            $response->headers->set('Cache-Control', 'public, max-age=60, stale-while-revalidate=60');
            $response->headers->set('Vary', trim($response->headers->get('Vary') . ', Accept-Language', ', '));
        }

        return $response;
    }
}
