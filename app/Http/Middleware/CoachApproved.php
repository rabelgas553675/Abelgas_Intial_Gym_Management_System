<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CoachApproved
{
    public function handle(Request $request, Closure $next)
    {
        $user   = auth()->user();
        $member = $user?->member;

        if (!$member) {
            return $next($request);
        }

        // Keep the member on the dashboard instead of forcing them to a waiting screen.
        // Pending or rejected coach requests are still tracked on the member record,
        // but they should not be stuck on a blocking page.
        return $next($request);
    }
}