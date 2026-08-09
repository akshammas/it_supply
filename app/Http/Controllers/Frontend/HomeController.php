<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Solution;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('frontend.home', [
            'featuredCategories' => Category::whereNull('parent_id')
                ->where('status', true)
                ->orderBy('sort_order')
                ->take(8)
                ->get(),
            'featuredBrands' => Brand::where('status', true)
                ->orderBy('sort_order')
                ->take(10)
                ->get(),
            'featuredProducts' => Product::with(['brand', 'primaryImage'])
                ->where('status', true)
                ->where('featured', true)
                ->latest()
                ->take(8)
                ->get(),
            'solutions' => Solution::where('status', true)
                ->take(6)
                ->get(),
        ]);
    }
}
