<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Mirrors Laravel Breeze's AuthenticatedSessionController, but scoped to
 * /admin/login: only users with a backend role can sign in here, failed
 * attempts are rate limited, and successful logins are timestamped and
 * written to the activity log.
 */
class AdminAuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Route-level throttle:5,1 middleware handles brute-force protection;
        // this is a defense-in-depth check on top of that.
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (! in_array($user->role, ['super_admin', 'product_manager', 'sales', 'content_manager'], true)
            || $user->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('This account does not have admin access.'),
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        AdminActivityLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'module' => 'auth',
            'record_id' => $user->id,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            AdminActivityLog::create([
                'user_id' => $user->id,
                'action' => 'logout',
                'module' => 'auth',
                'record_id' => $user->id,
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
