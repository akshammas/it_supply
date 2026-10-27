<?php

namespace App\Models;

use App\Models\Concerns\BustsFrontendCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class Popup extends Model
{
    use BustsFrontendCache;

    protected array $frontendCacheKeys = ['popups.active'];

    protected $fillable = [
        'name', 'layout', 'title', 'body', 'image', 'button_text', 'button_url', 'button_new_tab',
        'trigger', 'trigger_delay', 'trigger_scroll', 'frequency', 'show_on', 'custom_paths', 'device',
        'starts_at', 'ends_at', 'status', 'sort_order','show_form', 'form_button_text', 'success_message', 'consent_text', 'privacy_url', 'consent_required',
    ];

    protected $casts = [
        'status'         => 'boolean',
        'button_new_tab' => 'boolean',
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
        'show_form'        => 'boolean',
        'consent_required' => 'boolean',
    ];

    public const LAYOUTS = [
        'standard'   => 'Standard (image, title, text, button)',
        'image_only' => 'Image only (the image is the popup)',
    ];

    public const TRIGGERS = [
        'delay'       => 'After a few seconds',
        'scroll'      => 'After scrolling down the page',
        'exit_intent' => 'When the visitor is about to leave',
    ];

    public const FREQUENCIES = [
        'every_visit'      => 'Every page visit',
        'once_per_session' => 'Once per session (until the browser is closed)',
        'once_per_day'     => 'Once per day',
        'once_per_week'    => 'Once per week',
        'once_ever'        => 'Only once, never again',
    ];

    public const SHOW_ON = [
        'all'        => 'All pages',
        'home'       => 'Home page only',
        'products'   => 'Product pages (list and details)',
        'categories' => 'Category pages',
        'custom'     => 'Custom pages (enter paths)',
    ];

    public const DEVICES = [
        'all'     => 'Desktop and mobile',
        'desktop' => 'Desktop only',
        'mobile'  => 'Mobile only',
    ];

    /** The popup (if any) that should appear on the current request. */
    public static function forRequest(Request $request): ?self
    {
        // Never interrupt the quote form or admin screens
        if ($request->is('quote-request*', 'admin*')) {
            return null;
        }

        $all = Cache::remember('popups.active', now()->addMinutes(5), fn () =>
            static::where('status', true)->orderBy('sort_order')->orderBy('id')->get()
        );

        $now = now();

        return $all->first(fn (self $p) => $p->isLive($now) && $p->matchesPage($request));
    }

    public function isLive($now = null): bool
    {
        $now = $now ?: now();

        return $this->status
            && (! $this->starts_at || $this->starts_at <= $now)
            && (! $this->ends_at || $this->ends_at >= $now);
    }

    public function matchesPage(Request $request): bool
    {
        return match ($this->show_on) {
            'all'        => true,
            'home'       => $request->routeIs('home'),
            'products'   => $request->routeIs('products.*'),
            'categories' => $request->routeIs('categories.*'),
            'custom'     => $this->matchesCustomPaths($request),
            default      => false,
        };
    }

    protected function matchesCustomPaths(Request $request): bool
    {
        foreach (preg_split('/\r\n|\r|\n/', (string) $this->custom_paths) as $line) {
            $path = trim($line);
            if ($path === '') {
                continue;
            }
            $path = $path === '/' ? '/' : trim($path, '/');
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }

    /** live | scheduled | expired | off (for the admin list) */
    public function getStateAttribute(): string
    {
        if (! $this->status) return 'off';
        if ($this->starts_at && $this->starts_at > now()) return 'scheduled';
        if ($this->ends_at && $this->ends_at < now()) return 'expired';

        return 'live';
    }
}