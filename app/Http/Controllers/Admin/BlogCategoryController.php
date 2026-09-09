<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);
        $data['slug'] = Str::slug($data['name']);

        BlogCategory::create($data);

        return back()->with('status', 'Blog category added.');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        if ($blogCategory->blogs()->exists()) {
            return back()->withErrors('Move or delete posts in this category first.');
        }

        $blogCategory->delete();

        return back()->with('status', 'Blog category deleted.');
    }
}
