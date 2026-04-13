<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MemberMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->isMember()) {
            return $next($request);
        }
        return redirect()->route('dashboard')
            ->with('error', 'Access denied. Members only.');
    }
}