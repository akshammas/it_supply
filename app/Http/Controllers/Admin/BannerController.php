<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        return view('admin.banners.index', [
            'banners' => Banner::with(['categories', 'brands'])->orderBy('sort_order')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.banners.form', [
            'banner'     => new Banner(),
            'categories' => $this->categoryOptions(),
            'brands'     => $this->brandOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['image'] = $request->file('image')->store('banners', 'public');

        [$categoryIds, $brandIds] = $this->takeTargets($data);

        $banner = Banner::create($data);
        $banner->categories()->sync($categoryIds);
        $banner->brands()->sync($brandIds);

        return redirect()->route('admin.banners.index')->with('status', 'Banner created.');
    }

    public function edit(Banner $banner): View
    {
        $banner->load(['categories', 'brands']);

        return view('admin.banners.form', [
            'banner'     => $banner,
            'categories' => $this->categoryOptions(),
            'brands'     => $this->brandOptions(),
        ]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $data = $this->validated($request, required: false);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        [$categoryIds, $brandIds] = $this->takeTargets($data);

        $banner->update($data);
        $banner->categories()->sync($categoryIds);
        $banner->brands()->sync($brandIds);

        return redirect()->route('admin.banners.index')->with('status', 'Banner updated.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('status', 'Banner deleted.');
    }

    protected function validated(Request $request, bool $required = true): array
    {
        $data = $request->validate([
            'title'         => ['nullable', 'string', 'max:255'],
            'subtitle'      => ['nullable', 'string', 'max:255'],
            'image'         => [$required ? 'required' : 'nullable', 'image', 'max:2048'],
            'link_url'      => ['nullable', 'string', 'max:500'],
            'button_text'   => ['nullable', 'string', 'max:100'],
            'position'      => ['required', 'string', 'in:home_hero,home_promo,category_top,brand_top'],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
            'brand_ids'     => ['required_if:position,brand_top', 'nullable', 'array'],
            'brand_ids.*'   => ['integer', 'exists:brands,id'],
            'brand_ids'     => ['nullable', 'array'],
            'brand_ids.*'   => ['integer', 'exists:brands,id'],
        ],[
            'brand_ids.required_if' => 'Tick at least one brand for a Brand Page Top banner.',
        ]);

        // Unchecked checkbox sends nothing, so read it explicitly.
        $data['status'] = $request->boolean('status');

        return $data;
    }

    /**
     * Pull the target ids out of $data. Categories only count for
     * category_top, brands only for brand_top; anything else is cleared.
     * Empty list = "show on every category / every brand page".
     */
    protected function takeTargets(array &$data): array
    {
        $categoryIds = $data['position'] === 'category_top' ? ($data['category_ids'] ?? []) : [];
        $brandIds    = $data['position'] === 'brand_top'    ? ($data['brand_ids'] ?? [])    : [];

        unset($data['category_ids'], $data['brand_ids']);

        return [$categoryIds, $brandIds];
    }

    protected function categoryOptions()
    {
        return Category::with('parent')->orderBy('name')->get();
    }

    protected function brandOptions()
    {
        return Brand::orderBy('name')->get(['id', 'name']);
    }
}