<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRouteAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()->getName();
        if ($routeName == 'frontend.logout') {
            return $next($request);
        } else if (Auth::check()) {
            $user = Auth::user();
            
            if ($user->hasRole(RoleEnum::ADMIN) || $user->hasRole(RoleEnum::SERVICEMAN) || $user->hasRole(RoleEnum::PROVIDER)) {
                if (!$request->is('backend*')) {
                    return redirect()->back()->with('error', 'You cannot access frontend');
                } 
                return $next($request);
            }
            
            if ($user->hasRole(RoleEnum::CONSUMER)) {
                if ($request->is('backend*')) {
                    return redirect()->back()->with('error', 'You cannot access admin panel');
                }
                return $next($request);
            }
        } 

        return $next($request);
    }
}