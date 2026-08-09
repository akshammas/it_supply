<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
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

        return view('frontend.categories.show', compact('category', 'products', 'children'));
    }
}
