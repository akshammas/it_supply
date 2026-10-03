<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Solution;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Section 56: cache the homepage's featured/curated content —
        // this touches 6 tables on every single visit otherwise. Busted
        // immediately on relevant saves via BustsFrontendCache; a 10-min
        // TTL is the safety net if something changes without a cache-bust
        // hook (e.g. a raw DB edit).
        $data = Cache::remember('homepage.data', now()->addMinutes(10), function () {
            return [
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
                'heroBanners' => Banner::where('position', 'home_hero')
                    ->where('status', true)
                    ->orderBy('sort_order')
                    ->get(),
                'promoBanners' => Banner::where('position', 'home_promo')
                    ->where('status', true)
                    ->orderBy('sort_order')
                    ->take(3)
                    ->get(),
            ];
        });

        return view('frontend.home', $data);
    }
}
