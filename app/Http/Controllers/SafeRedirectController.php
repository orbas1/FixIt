<?php

namespace App\Http\Controllers;

use App\Services\Security\LinkRedirectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SafeRedirectController extends Controller
{
    public function __invoke(Request $request, LinkRedirectService $redirector)
    {
        $url = (string) $request->query('u', '');
        $signature = (string) $request->query('sig', '');
        $expires = (int) $request->query('exp', 0);

        if ($url === '' || $signature === '' || ! $redirector->verify($url, $signature, $expires)) {
            Log::warning('Blocked unsafe redirect attempt', [
                'url' => $url,
                'signature' => $signature,
                'expires' => $expires,
            ]);

            abort(403, 'Invalid or expired redirect token.');
        }

        return redirect()->away($url);
    }
}
