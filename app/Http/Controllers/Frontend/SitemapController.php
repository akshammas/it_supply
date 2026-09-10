<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\Solution;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Section 40: sitemap.xml covering products, categories, brands,
 * solutions, blog, pages. Rebuilding this on every request would mean
 * querying every table in the catalogue on every crawler hit, so it's
 * cached for 6 hours — plenty fresh for SEO purposes, and busted early
 * whenever a Product/Category/Brand/Solution/Blog/Page is saved (see
 * BustsFrontendCache).
 */
class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHours(6), function () {
            $urls = collect();

            $urls->push(['loc' => route('home'), 'lastmod' => now(), 'priority' => '1.0']);
            $urls->push(['loc' => route('products.index'), 'lastmod' => now(), 'priority' => '0.9']);
            $urls->push(['loc' => route('solutions.index'), 'lastmod' => now(), 'priority' => '0.7']);
            $urls->push(['loc' => route('blog.index'), 'lastmod' => now(), 'priority' => '0.6']);

            Product::where('status', true)->select('slug', 'updated_at')->cursor()->each(function ($product) use ($urls) {
                $urls->push(['loc' => route('products.show', $product), 'lastmod' => $product->updated_at, 'priority' => '0.8']);
            });

            Category::where('status', true)->select('slug', 'updated_at')->cursor()->each(function ($category) use ($urls) {
                $urls->push(['loc' => route('categories.show', $category), 'lastmod' => $category->updated_at, 'priority' => '0.7']);
            });

            Brand::where('status', true)->select('slug', 'updated_at')->cursor()->each(function ($brand) use ($urls) {
                $urls->push(['loc' => route('brands.show', $brand), 'lastmod' => $brand->updated_at, 'priority' => '0.6']);
            });

            Solution::where('status', true)->select('slug', 'updated_at')->cursor()->each(function ($solution) use ($urls) {
                $urls->push(['loc' => route('solutions.show', $solution), 'lastmod' => $solution->updated_at, 'priority' => '0.6']);
            });

            Blog::where('status', true)->select('slug', 'updated_at')->cursor()->each(function ($post) use ($urls) {
                $urls->push(['loc' => route('blog.show', $post), 'lastmod' => $post->updated_at, 'priority' => '0.5']);
            });

            Page::where('status', true)->select('slug', 'updated_at')->cursor()->each(function ($page) use ($urls) {
                $urls->push(['loc' => route('pages.show', $page), 'lastmod' => $page->updated_at, 'priority' => '0.4']);
            });

            return view('sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
