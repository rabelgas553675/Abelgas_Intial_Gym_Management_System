<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Strict admin gate.
 *
 * NOTE: the existing AdminMiddleware uses canManageMembers(), which is true for
 * BOTH admin and staff, so it must NOT be used for the Audit Trail.
 */
class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        abort_unless($user && $user->isAdmin(), 403, 'The Audit Trail is restricted to administrators.');

        return $next($request);
    }
}