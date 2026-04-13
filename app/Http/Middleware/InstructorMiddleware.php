<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InstructorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->isInstructor()) {
            return $next($request);
        }
        return redirect()->route('dashboard')
            ->with('error', 'Access denied. Instructors only.');
    }
}