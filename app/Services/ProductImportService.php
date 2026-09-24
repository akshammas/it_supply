<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductImport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Sections 26-29: CSV import with column mapping, validation, batched
 * processing, and per-row error reporting. This service does the actual
 * row-by-row work; ProcessProductImportChunk (a queued job) calls it in
 * batches of ~100 rows so a 5,000-row CSV never runs in a single request
 * on shared hosting.
 */
class ProductImportService
{
    public array $systemFields = [
        'name' => 'Product Name',
        'sku' => 'SKU',
        'model_number' => 'Model Number',
        'short_description' => 'Short Description',
        'description' => 'Description',
        'brand' => 'Brand (must already exist)',
        'category' => 'Category (must already exist)',
        'price' => 'Price',
        'price_type' => 'Price Type (fixed/sale/on_request)',
        'stock' => 'Stock Quantity',
        'images' => 'Image URLs (comma-separated)',
    ];

    public function __construct(protected ImageService $images)
    {
    }

    public function handleUpload(\Illuminate\Http\UploadedFile $file): array
    {
        $path = $file->store('imports', 'local');
        $fullPath = Storage::disk('local')->path($path);

        $handle = fopen($fullPath, 'r');
        $headers = array_map([$this, 'toUtf8'], fgetcsv($handle));

        $sampleRows = [];
        for ($i = 0; $i < 3; $i++) {
            $row = fgetcsv($handle);
            if ($row === false) {
                break;
            }
            $sampleRows[] = array_map([$this, 'toUtf8'], $row);
        }

        fclose($handle);

        return [
            'path' => $path,
            'headers' => $headers,
            'sampleRows' => $sampleRows,
        ];
    }

    public function countRows(string $path): int
    {
        $fullPath = Storage::disk('local')->path($path);
        $handle = fopen($fullPath, 'r');
        fgetcsv($handle);

        $count = 0;
        while (fgetcsv($handle) !== false) {
            $count++;
        }

        fclose($handle);

        return $count;
    }

    public function readChunk(string $path, array $headers, array $mapping, int $offset, int $limit): array
    {
        $fullPath = Storage::disk('local')->path($path);
        $handle = fopen($fullPath, 'r');
        fgetcsv($handle);

        for ($i = 0; $i < $offset; $i++) {
            if (fgetcsv($handle) === false) {
                fclose($handle);
                return [];
            }
        }

        $rows = [];
        for ($i = 0; $i < $limit; $i++) {
            $raw = fgetcsv($handle);
            if ($raw === false) {
                break;
            }

            $row = [];
            foreach ($mapping as $systemField => $csvColumn) {
                if ($csvColumn === null || $csvColumn === '') {
                    continue;
                }
                $columnIndex = array_search($csvColumn, $headers, true);
                $value = $columnIndex !== false ? trim((string) ($raw[$columnIndex] ?? '')) : null;
                $row[$systemField] = $this->toUtf8($value);
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    protected function toUtf8(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1252');

        if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
            return $converted;
        }

        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }

    /**
     * Processes one row. Returns:
     *   ['result' => 'imported'|'updated', 'warnings' => string[]]
     * on success (the row's core data was valid — warnings list any
     * individual image URLs that failed but didn't block the product
     * itself), or throws for a genuine row failure (missing name,
     * invalid price).
     *
     * Brand/Category are auto-created when they don't already exist,
     * rather than failing the row — see the note above processRow's
     * brand/category lookups.
     *
     * Image failures are DELIBERATELY non-fatal to the row: a CSV with
     * 10 image URLs per product where 1 is broken should still import
     * the product with its other 9 images, not fail the whole row.
     */
    public function processRow(array $row): array
    {
        if (empty($row['name'])) {
            throw new \RuntimeException('Missing product name');
        }

        // Brand/category are auto-created if they don't already exist,
        // matched case-insensitively so "Dell" and "dell" in different
        // rows resolve to the same record rather than creating duplicates.
        $brandId = null;
        if (! empty($row['brand'])) {
            $brand = Brand::whereRaw('LOWER(name) = ?', [Str::lower($row['brand'])])->first();

            if (! $brand) {
                $brand = Brand::create([
                    'name' => $row['brand'],
                    'slug' => $this->uniqueSlug(Brand::class, $row['brand']),
                    'status' => true,
                ]);
            }

            $brandId = $brand->id;
        }

        $categoryId = null;
        if (! empty($row['category'])) {
            $category = Category::whereRaw('LOWER(name) = ?', [Str::lower($row['category'])])->first();

            if (! $category) {
                $category = Category::create([
                    'name' => $row['category'],
                    'slug' => $this->uniqueSlug(Category::class, $row['category']),
                    'status' => true,
                ]);
            }

            $categoryId = $category->id;
        }

        if (! empty($row['price']) && ! is_numeric($row['price'])) {
            throw new \RuntimeException("Invalid price: {$row['price']}");
        }

        // Each image URL is independent — one bad link among many doesn't
        // sink the whole row. Failures are collected as warnings, not
        // thrown, so the product still saves with whatever DID download.
        $downloadedImages = [];
        $warnings = [];

        if (! empty($row['images'])) {
            foreach (array_filter(array_map('trim', explode(',', $row['images']))) as $url) {
                try {
                    $downloadedImages[] = $this->images->downloadFromUrl($url, 'products/import');
                } catch (Throwable $e) {
                    $warnings[] = "Image skipped ({$url}): {$e->getMessage()}";
                }
            }
        }

        $sku = $row['sku'] ?? null;
        $existing = $sku ? Product::where('sku', $sku)->first() : null;

        $data = array_filter([
            'name' => $row['name'],
            'slug' => Str::slug($row['name']).($existing ? '' : '-'.Str::lower(Str::random(4))),
            'sku' => $sku,
            'model_number' => $row['model_number'] ?? null,
            'short_description' => $row['short_description'] ?? null,
            'description' => $row['description'] ?? null,
            'brand_id' => $brandId,
            'category_id' => $categoryId,
            'price' => $row['price'] ?? null,
            'price_type' => $row['price_type'] ?? 'on_request',
            'stock' => $row['stock'] ?? 0,
        ], fn ($v) => $v !== null && $v !== '');

        if ($existing) {
            unset($data['slug']);
            $existing->update($data);
            $product = $existing;
            $result = 'updated';
        } else {
            $data['status'] = true;
            $data['stock_status'] = 'in_stock';
            $data['condition'] = 'new';
            $product = Product::create($data);
            $result = 'imported';
        }

        $existingImageCount = $product->images()->count();

        foreach ($downloadedImages as $index => $image) {
            ProductImage::create([
                'product_id' => $product->id,
                'image' => $image['path'],
                'alt_text' => $product->name,
                'sort_order' => $existingImageCount + $index,
                'is_primary' => $existingImageCount === 0 && $index === 0,
            ]);
        }

        return ['result' => $result, 'warnings' => $warnings];
    }

    /**
     * Generates a unique slug for an auto-created Brand/Category, so two
     * different rows with names that slugify to the same thing (e.g.
     * "HP" and "H.P.") don't collide on the unique slug constraint.
     */
    protected function uniqueSlug(string $modelClass, string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while ($modelClass::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function writeErrorCsv(ProductImport $import): string
    {
        $path = "imports/errors/{$import->id}.csv";
        $handle = fopen('php://temp', 'w');
        fputcsv($handle, ['Row', 'Error']);

        foreach ($import->errors ?? [] as $error) {
            fputcsv($handle, [$error['row'], $error['message']]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        Storage::disk('local')->put($path, $content);

        return $path;
    }
}