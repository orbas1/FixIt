<?php

namespace App\Http\Controllers\Testing;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ViewPreviewController extends Controller
{
    public function __construct(private readonly ViewFactory $viewFactory)
    {
    }

    public function __invoke(Request $request): View
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $view = (string) $request->query('view', '');
        abort_if(trim($view) === '', 400, 'A view name must be provided.');

        if (! $this->viewFactory->exists($view)) {
            abort(404, "View [{$view}] was not found.");
        }

        $data = $request->query('data', []);

        return $this->viewFactory->make($view, $this->normalizeDataPayload($data));
    }

    /**
     * @param  array<string, mixed>|string  $payload
     * @return array<string, mixed>
     */
    private function normalizeDataPayload(array|string $payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }

        $decoded = json_decode($payload, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return [];
    }
}
