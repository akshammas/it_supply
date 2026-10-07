<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = $this->filteredQuery($request)->paginate(24)->withQueryString();

        return view('frontend.products.index', [
            'products' => $products,
            'brands' => Brand::where('status', true)->orderBy('name')->get(),
            'categories' => Category::where('status', true)->orderBy('name')->get(),
            'heading' => collect([
                $request->filled('category') ? optional(Category::where('slug', $request->string('category'))->first())->name : null,
                $request->filled('brand') ? optional(Brand::where('slug', $request->string('brand'))->first())->name : null,
            ])->filter()->implode(' – ') ?: 'All Products',
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->status, 404);

        $product->load(['brand', 'category', 'images', 'variants', 'specifications.specification.group']);

        $related = Product::with(['brand', 'primaryImage'])
            ->where('status', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        // Group specifications by their spec group, in the order the
        // admin defined (section 13: Overview / Specifications / Features).
        $specGroups = $product->specifications
            ->groupBy(fn ($ps) => $ps->specification->group->name ?? 'Specifications');

        return view('frontend.products.show', compact('product', 'related', 'specGroups'));
    }

    /**
     * Shared filter/search logic used by ProductController::index and
     * SearchController::index so both stay consistent (section 30-31).
     */
    public function filteredQuery(Request $request)
    {
        return Product::with(['brand', 'category', 'primaryImage'])
            ->where('status', true)
            ->when($request->filled('q'), fn ($q) => $q->whereFullText(['name', 'sku', 'model_number'], $request->string('q')))
            ->when($request->filled('category'), fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $request->string('category'))))
            ->when($request->filled('brand'), fn ($q) => $q->whereHas('brand', fn ($b) => $b->where('slug', $request->string('brand'))))
            ->when($request->filled('price_type'), fn ($q) => $q->where('price_type', $request->string('price_type')))
            ->when($request->filled('stock_status'), fn ($q) => $q->where('stock_status', $request->string('stock_status')))
            ->when($request->get('sort') === 'price_asc', fn ($q) => $q->orderByRaw('COALESCE(sale_price, price) asc'))
            ->when($request->get('sort') === 'price_desc', fn ($q) => $q->orderByRaw('COALESCE(sale_price, price) desc'))
            ->when($request->get('sort') === 'name', fn ($q) => $q->orderBy('name'))
            ->when(! $request->filled('sort'), fn ($q) => $q->latest());
    }
}
