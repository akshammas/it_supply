<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Section 51: one screen, key-value settings, no .env editing needed for
 * ordinary business info. Every key below is read elsewhere in the app
 * via Setting::get('key', $fallback) — see PHASE7_NOTES.md for the full
 * list of what to update once this lands.
 */
class SettingController extends Controller
{
    protected array $fields = [
        'company_name', 'logo', 'favicon', 'email', 'phone', 'whatsapp_number',
        'address', 'google_maps_url', 'facebook_url', 'instagram_url', 'linkedin_url',
        'currency', 'vat_percent', 'default_enquiry_message',
        'seo_title', 'seo_description',
        'hero_title', 'hero_subtitle',
    ];

    protected array $fileFields = ['logo', 'favicon'];

    public function edit(): View
    {
        $settings = Setting::whereIn('key', $this->fields)->pluck('value', 'key');

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:1024'],
            'favicon' => ['nullable', 'image', 'max:512'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'google_maps_url' => ['nullable', 'url', 'max:500'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'currency' => ['nullable', 'string', 'max:10'],
            'vat_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'default_enquiry_message' => ['nullable', 'string', 'max:1000'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($this->fileFields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('settings', 'public');
            } else {
                unset($data[$field]); // don't overwrite an existing logo/favicon with nothing
            }
        }

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('status', 'Settings updated.');
    }
}
