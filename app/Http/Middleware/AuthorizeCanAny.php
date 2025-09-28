<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthorizeCanAny
{
  public function handle($request, Closure $next, ...$permissions)
  {
    // Check if the user is authenticated
    if (Auth::check()) {
      // Check if the user has any of the specified permissions
      foreach ($permissions as $permission) {
        if (Auth::user()->can($permission)) {
          return $next($request);
        }
      }
    }

    // If none of the permissions matched, deny access
    return abort(403);
  }
}
