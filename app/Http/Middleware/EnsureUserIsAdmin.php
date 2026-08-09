<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Backend roles allowed into /admin. Update this list as new roles
     * are introduced (see section 21 of the plan). Start with a plain
     * string column on `users`; move to spatie/laravel-permission only
     * once you need per-action permissions rather than per-role access.
     */
    protected array $adminRoles = [
        'super_admin',
        'product_manager',
        'sales',
        'content_manager',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $this->adminRoles, true)) {
            abort(403, 'You do not have access to the admin area.');
        }

        if ($user->status !== 'active') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Your admin account has been deactivated. Contact a super admin.');
        }

        return $next($request);
    }
}
