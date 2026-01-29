<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check() || !Auth::guard('admin')->user()->isAdmin()) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Access denied. Admin privileges required.']);
        }

        return $next($request);
    }
}