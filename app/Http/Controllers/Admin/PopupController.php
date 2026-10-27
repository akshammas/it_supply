<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Popup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PopupController extends Controller
{
    public function index(): View
    {
        return view('admin.popups.index', [
            'popups' => Popup::orderBy('sort_order')->orderByDesc('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        $popup = new Popup([
            'layout' => 'standard', 'trigger' => 'delay', 'trigger_delay' => 3, 'trigger_scroll' => 50,
            'frequency' => 'once_per_session', 'show_on' => 'home', 'device' => 'all',
            'status' => true, 'sort_order' => 0,'show_form' => false, 'form_button_text' => 'Request a Call Back',
        ]);

        return view('admin.popups.form', compact('popup'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('popups', 'public');
        }

        Popup::create($data);

        return redirect()->route('admin.popups.index')->with('status', 'Popup created.');
    }

    public function edit(Popup $popup): View
    {
        return view('admin.popups.form', compact('popup'));
    }

    public function update(Request $request, Popup $popup): RedirectResponse
    {
        $data = $this->validated($request, $popup);

        if ($request->hasFile('image')) {
            $this->deleteImage($popup);
            $data['image'] = $request->file('image')->store('popups', 'public');
        } elseif ($request->boolean('remove_image') && $data['layout'] !== 'image_only') {
            $this->deleteImage($popup);
            $data['image'] = null;
        }

        $popup->update($data);

        return redirect()->route('admin.popups.index')->with('status', 'Popup updated.');
    }

    public function toggle(Popup $popup): RedirectResponse
    {
        // Don't touch updated_at: visitors who already saw it should not see it again just because it was switched off/on
        $popup->timestamps = false;
        $popup->status = ! $popup->status;
        $popup->save();

        return back()->with('status', 'Popup ' . ($popup->status ? 'turned on.' : 'turned off.'));
    }

    public function destroy(Popup $popup): RedirectResponse
    {
        $this->deleteImage($popup);
        $popup->delete();

        return redirect()->route('admin.popups.index')->with('status', 'Popup deleted.');
    }

    protected function deleteImage(Popup $popup): void
    {
        if ($popup->image) {
            Storage::disk('public')->delete($popup->image);
        }
    }

    protected function validated(Request $request, ?Popup $popup = null): array
    {
        $needsImage = $request->input('layout') === 'image_only'
            && ! ($popup?->image)
            && ! $request->hasFile('image');

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'layout'         => ['required', Rule::in(array_keys(Popup::LAYOUTS))],
            'title'          => ['nullable', 'string', 'max:150'],
            'body'           => ['nullable', 'string', 'max:1000'],
            'image'          => [$needsImage ? 'required' : 'nullable', 'image', 'max:2048'],
            'button_text'    => ['nullable', 'string', 'max:100'],
            'button_url'     => ['nullable', 'string', 'max:500'],
            'trigger'        => ['required', Rule::in(array_keys(Popup::TRIGGERS))],
            'trigger_delay'  => ['nullable', 'integer', 'min:0', 'max:120'],
            'trigger_scroll' => ['nullable', 'integer', 'min:5', 'max:100'],
            'frequency'      => ['required', Rule::in(array_keys(Popup::FREQUENCIES))],
            'show_on'        => ['required', Rule::in(array_keys(Popup::SHOW_ON))],
            'custom_paths'   => ['nullable', 'required_if:show_on,custom', 'string', 'max:1000'],
            'device'         => ['required', Rule::in(array_keys(Popup::DEVICES))],
            'starts_at'      => ['nullable', 'date'],
            'ends_at'        => ['nullable', 'date', Rule::when($request->filled('starts_at'), ['after:starts_at'])],
            'sort_order'     => ['nullable', 'integer', 'min:0'],
            'form_button_text' => ['nullable', 'string', 'max:60'],
            'success_message'  => ['nullable', 'string', 'max:255'],
            'consent_text'     => ['nullable', 'string', 'max:255'],
            'privacy_url'      => ['nullable', 'string', 'max:255'],
        ], [
            'image.required'        => 'Upload an image for an Image only popup.',
            'custom_paths.required_if' => 'Enter at least one page path for Custom pages.',
        ]);

        $data['status']         = $request->boolean('status');
        $data['show_form']        = $request->boolean('show_form');
        $data['consent_required'] = $request->boolean('consent_required');
        $data['form_button_text'] = $data['form_button_text'] ?? 'Request a Call Back';
        $data['button_new_tab'] = $request->boolean('button_new_tab');
        $data['trigger_delay']  = $data['trigger_delay'] ?? 3;
        $data['trigger_scroll'] = $data['trigger_scroll'] ?? 50;
        $data['sort_order']     = $data['sort_order'] ?? 0;

        unset($data['image']); // handled by the caller

        return $data;
    }
}