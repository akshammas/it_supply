<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(protected ImageService $imageService)
    {
    }

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
            $data['status'] = (bool) ($data['status'] ?? false);
            $data['featured'] = (bool) ($data['featured'] ?? false);

            $specs = Arr::pull($data, 'specs', []);
            $images = Arr::pull($data, 'images', []);
            $primaryIndex = Arr::pull($data, 'primary_image_index');

            $product = Product::create($data);

            $this->syncSpecifications($product, $specs);
            $this->storeImages($product, $images, $primaryIndex);

            return $product;
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
            $data['status'] = (bool) ($data['status'] ?? false);
            $data['featured'] = (bool) ($data['featured'] ?? false);

            $specs = Arr::pull($data, 'specs', []);
            $images = Arr::pull($data, 'images', []);
            $primaryIndex = Arr::pull($data, 'primary_image_index');

            $product->update($data);

            $this->syncSpecifications($product, $specs);

            if (! empty($images)) {
                $this->storeImages($product, $images, $primaryIndex);
            }

            return $product->fresh();
        });
    }

    /**
     * Creates a copy of a product (section 24: "Duplicate") with a new
     * SKU/slug placeholder, its specifications, but not its images —
     * images are excluded deliberately so the admin doesn't end up with
     * duplicate files pointing at the same source product's photos.
     */
    public function duplicate(Product $product): Product
    {
        return DB::transaction(function () use ($product) {
            $copy = $product->replicate(['sku', 'slug']);
            $copy->sku = $product->sku.'-copy-'.Str::lower(Str::random(4));
            $copy->slug = Str::slug($product->name).'-copy-'.Str::lower(Str::random(4));
            $copy->status = false; // new copies start inactive until reviewed
            $copy->save();

            foreach ($product->specifications as $spec) {
                $copy->specifications()->create([
                    'specification_id' => $spec->specification_id,
                    'value' => $spec->value,
                    'sort_order' => $spec->sort_order,
                ]);
            }

            return $copy;
        });
    }

    protected function syncSpecifications(Product $product, array $specs): void
    {
        $product->specifications()->delete();

        $sort = 0;
        foreach ($specs as $specificationId => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $product->specifications()->create([
                'specification_id' => $specificationId,
                'value' => $value,
                'sort_order' => $sort++,
            ]);
        }
    }

    protected function storeImages(Product $product, array $images, ?int $primaryIndex): void
    {
        $existingCount = $product->images()->count();

        foreach ($images as $index => $file) {
            $stored = $this->imageService->store($file, "products/{$product->id}");

            ProductImage::create([
                'product_id' => $product->id,
                'image' => $stored['path'],
                'alt_text' => $product->name,
                'sort_order' => $existingCount + $index,
                'is_primary' => $existingCount === 0 && ($primaryIndex === null || $primaryIndex === $index),
            ]);
        }

        // If this batch explicitly nominated a primary image, demote all others.
        if ($primaryIndex !== null) {
            $newPrimary = $product->images()->latest('id')->skip($primaryIndex)->first();
            if ($newPrimary) {
                $product->images()->where('id', '!=', $newPrimary->id)->update(['is_primary' => false]);
                $newPrimary->update(['is_primary' => true]);
            }
        }
    }
}
