<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        return view('admin.banners.index', [
            'banners' => Banner::with('categories')->orderBy('sort_order')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.banners.form', [
            'banner' => new Banner(),
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['image'] = $request->file('image')->store('banners', 'public');
        $categoryIds = $this->takeCategoryIds($data);

        $banner = Banner::create($data);
        $banner->categories()->sync($categoryIds);

        return redirect()->route('admin.banners.index')->with('status', 'Banner created.');
    }

    public function edit(Banner $banner): View
    {
        $banner->load('categories');

        return view('admin.banners.form', [
            'banner' => $banner,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $data = $this->validated($request, required: false);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $categoryIds = $this->takeCategoryIds($data);

        $banner->update($data);
        $banner->categories()->sync($categoryIds);

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
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'image' => [$required ? 'required' : 'nullable', 'image', 'max:2048'],
            'link_url' => ['nullable', 'string', 'max:500'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'position' => ['required', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        // An unticked checkbox sends nothing, so read it explicitly
        $data['status'] = $request->boolean('status');

        return $data;
    }

    /** Pull category_ids out of the banner data; only "Category Page Top" banners keep them. */
    protected function takeCategoryIds(array &$data): array
    {
        $ids = $data['position'] === 'category_top' ? ($data['category_ids'] ?? []) : [];
        unset($data['category_ids']);

        return array_map('intval', $ids);
    }

    protected function categoryOptions()
    {
        return Category::with('parent')->orderBy('name')->get();
    }
}