<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = Blog::with('category')->latest()->paginate(20);
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.blog.index', compact('posts', 'categories'));
    }

    public function create(): View
    {
        return view('admin.blog.form', [
            'post' => new Blog(),
            'categories' => BlogCategory::orderBy('name')->get(),
            'shopCategories' => Category::with('parent')->orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['author_id'] = Auth::id();
        [$categoryIds, $brandIds] = $this->extractPageLinks($data);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('blog', 'public');
        }

        $post = Blog::create($data);
        $post->productCategories()->sync($categoryIds);
        $post->brands()->sync($brandIds);

        return redirect()->route('admin.blog.index')->with('status', 'Post created.');
    }

    public function edit(Blog $post): View
    {
        return view('admin.blog.form', [
            'post' => $post->load(['productCategories', 'brands']),
            'categories' => BlogCategory::orderBy('name')->get(),
            'shopCategories' => Category::with('parent')->orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Blog $post): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        [$categoryIds, $brandIds] = $this->extractPageLinks($data);

        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->store('blog', 'public');
        }

        $post->update($data);
        $post->productCategories()->sync($categoryIds);
        $post->brands()->sync($brandIds);

        return redirect()->route('admin.blog.index')->with('status', 'Post updated.');
    }

    public function destroy(Blog $post): RedirectResponse
    {
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->delete();

        return redirect()->route('admin.blog.index')->with('status', 'Post deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
            'status' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'brand_ids' => ['nullable', 'array'],
            'brand_ids.*' => ['integer', 'exists:brands,id'],
        ]);
    }

    /** Pull the category/brand ids out of the validated data so only real columns reach create()/update(). */
    protected function extractPageLinks(array &$data): array
    {
        $categoryIds = $data['category_ids'] ?? [];
        $brandIds = $data['brand_ids'] ?? [];
        unset($data['category_ids'], $data['brand_ids']);

        return [$categoryIds, $brandIds];
    }
}
