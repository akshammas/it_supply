<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Optional finer-grained gate on top of EnsureUserIsAdmin, e.g.
 *   Route::middleware('role:super_admin,product_manager')->group(...)
 * Use sparingly — prefer policies/gates once the permission model grows
 * beyond a handful of routes.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user() || ! in_array($request->user()->role, $roles, true)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
