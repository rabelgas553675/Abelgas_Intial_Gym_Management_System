<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // This checks if the user is logged in and has the ability to manage members
        if (auth()->check() && auth()->user()->canManageMembers()) {
            return $next($request);
        }

        // If not, redirect back with an error
        return redirect()->route('dashboard')
            ->with('error', 'Access denied. Admins only.');
    }
}