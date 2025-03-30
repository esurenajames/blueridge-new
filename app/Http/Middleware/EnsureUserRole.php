<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if ($request->user()->role !== $role) {
            return Inertia::render('errors/Unauthorized');
        }

        return $next($request);
    }
}