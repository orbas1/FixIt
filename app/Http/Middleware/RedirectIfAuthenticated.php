<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {

        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {

            if (Auth::guard($guard)->check()) {

                if (Auth::user()->roles->first()?->name != 'user') {

                    return redirect()->route('backend.dashboard');

                } elseif (Auth::user()->roles->first()?->name == 'user') {

                    return redirect()->route('frontend.account.profile.index');
                } else {

                    return redirect()->route('frontend.home');
                }
            }
        }

        return $next($request);
    }
}