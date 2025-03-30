<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if ($request->user()->role !== $role) {
            return redirect()->route('unauthorized');
        }

        return $next($request);
    }
}