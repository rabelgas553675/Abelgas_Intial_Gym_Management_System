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

        // If member chose a coach but not yet approved — redirect to waiting page
        if ($member->coach_status === 'pending') {
            return redirect()->route('member.waiting');
        }

        // If rejected — redirect to waiting page with rejected status
        if ($member->coach_status === 'rejected') {
            return redirect()->route('member.waiting');
        }

        return $next($request);
    }
}