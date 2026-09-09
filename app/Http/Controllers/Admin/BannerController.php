<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        return view('admin.banners.index', ['banners' => Banner::orderBy('sort_order')->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.banners.form', ['banner' => new Banner()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['image'] = $request->file('image')->store('banners', 'public');

        Banner::create($data);

        return redirect()->route('admin.banners.index')->with('status', 'Banner created.');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.form', compact('banner'));
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $data = $this->validated($request, required: false);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('status', 'Banner updated.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('status', 'Banner deleted.');
    }

    protected function validated(Request $request, bool $required = true): array
    {
        return $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'image' => [$required ? 'required' : 'nullable', 'image', 'max:2048'],
            'link_url' => ['nullable', 'string', 'max:500'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'position' => ['required', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['boolean'],
        ]);
    }
}
