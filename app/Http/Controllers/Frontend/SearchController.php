<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request, ProductController $products): View
    {
        $results = $request->filled('q')
            ? $products->filteredQuery($request)->paginate(24)->withQueryString()
            : null;

        return view('frontend.products.index', [
            'products' => $results,
            'brands' => Brand::where('status', true)->orderBy('name')->get(),
            'categories' => Category::where('status', true)->orderBy('name')->get(),
            'heading' => $request->filled('q') ? 'Search results for "'.$request->string('q').'"' : 'Search',
        ]);
    }
}
