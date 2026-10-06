<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Banner;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Category $category): View
    {
        abort_unless($category->status, 404);

        $products = $category->products()
            ->with(['brand', 'primaryImage'])
            ->where('status', true)
            ->latest()
            ->paginate(24);

        $children = $category->children()->where('status', true)->orderBy('sort_order')->get();

        $topBanners = Banner::where('position', 'category_top')
            ->where('status', true)
            ->where(function ($q) use ($category) {
                $q->whereDoesntHave('categories')   // nothing ticked = every category
                  ->orWhereHas('categories', fn ($c) => $c->where('categories.id', $category->id));
            })
            ->orderBy('sort_order')
            ->get();

        return view('frontend.categories.show', compact('category', 'products', 'children', 'topBanners'));

    }
}
