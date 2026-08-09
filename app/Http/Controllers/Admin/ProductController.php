<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SpecificationGroup;
use App\Services\ImageService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(protected ProductService $products, protected ImageService $images)
    {
    }

    public function index(Request $request): View
    {
        $query = Product::with(['brand', 'category'])
            ->when($request->filled('search'), fn ($q) => $q->whereFullText(['name', 'sku', 'model_number'], $request->string('search')))
            ->when($request->filled('brand_id'), fn ($q) => $q->where('brand_id', $request->integer('brand_id')))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status') === 'active'));

        $products = $query->latest()->paginate(25)->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'brands' => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(),
            'brands' => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'specGroups' => SpecificationGroup::with('specifications')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = $this->products->create($request->validated());

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $product->load(['images', 'specifications']);

        return view('admin.products.form', [
            'product' => $product,
            'brands' => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'specGroups' => SpecificationGroup::with('specifications')->orderBy('sort_order')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update($product, $request->validated());

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        foreach ($product->images as $image) {
            $this->images->delete($image->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    public function duplicate(Product $product): RedirectResponse
    {
        $copy = $this->products->duplicate($product);

        return redirect()->route('admin.products.edit', $copy)->with('status', 'Product duplicated. Review and add images before publishing.');
    }

    public function deleteImage(Request $request, Product $product, ProductImage $image): RedirectResponse|JsonResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        $wasPrimary = $image->is_primary;
        $this->images->delete($image->image);
        $image->delete();

        if ($wasPrimary) {
            $product->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Image removed.']);
        }

        return back()->with('status', 'Image removed.');
    }

    public function setPrimaryImage(Request $request, Product $product, ProductImage $image): RedirectResponse|JsonResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Primary image updated.']);
        }

        return back()->with('status', 'Primary image updated.');
    }

    /**
     * Section 25: bulk actions from the product list checkboxes.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:delete,activate,deactivate,feature,unfeature,category,brand'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['exists:products,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
        ]);

        $products = Product::whereIn('id', $data['ids']);

        match ($data['action']) {
            'delete' => $products->get()->each(function (Product $product) {
                foreach ($product->images as $image) {
                    $this->images->delete($image->image);
                }
                $product->delete();
            }),
            'activate' => $products->update(['status' => true]),
            'deactivate' => $products->update(['status' => false]),
            'feature' => $products->update(['featured' => true]),
            'unfeature' => $products->update(['featured' => false]),
            'category' => $products->update(['category_id' => $data['category_id']]),
            'brand' => $products->update(['brand_id' => $data['brand_id']]),
        };

        return back()->with('status', 'Bulk action applied to '.count($data['ids']).' product(s).');
    }

    public function export()
    {
        $filename = 'products-'.now()->format('Y-m-d-His').'.csv';

        $columns = ['id', 'name', 'sku', 'model_number', 'brand', 'category', 'price', 'price_type', 'stock', 'status'];

        return response()->streamDownload(function () use ($columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            Product::with(['brand', 'category'])->orderBy('id')->chunk(200, function ($products) use ($handle) {
                foreach ($products as $product) {
                    fputcsv($handle, [
                        $product->id,
                        $product->name,
                        $product->sku,
                        $product->model_number,
                        $product->brand?->name,
                        $product->category?->name,
                        $product->price,
                        $product->price_type,
                        $product->stock,
                        $product->status ? 'active' : 'inactive',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}