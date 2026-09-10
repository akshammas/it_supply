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

    /**
     * Stores the uploaded CSV, reads its header row + a few sample rows
     * for the column-mapping screen. Doesn't touch the database yet.
     */
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
        fgetcsv($handle); // skip header

        $count = 0;
        while (fgetcsv($handle) !== false) {
            $count++;
        }

        fclose($handle);

        return $count;
    }

    /**
     * Reads rows [offset, offset+limit) — NOT counting the header row —
     * as associative arrays keyed by the mapped system field names.
     */
    public function readChunk(string $path, array $headers, array $mapping, int $offset, int $limit): array
    {
        $fullPath = Storage::disk('local')->path($path);
        $handle = fopen($fullPath, 'r');
        fgetcsv($handle); // skip header

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

    /**
     * CSVs exported from Shopify/Excel are frequently saved as
     * Windows-1252, not UTF-8 — smart quotes, en-dashes, and inch marks
     * (’ – " " etc.) are the usual culprits. Reading them as raw UTF-8
     * produces invalid byte sequences that later crash json_encode()
     * when Eloquent tries to save the import's error log. Detect and
     * convert rather than silently stripping the characters.
     */
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

        // Last-resort fallback: strip anything that still isn't valid
        // UTF-8 rather than letting one bad byte crash the whole import.
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }

    /**
     * Processes one row. Returns ['result' => 'imported'|'updated', ...]
     * on success, or throws with a human-readable message on failure —
     * the caller (the queued job) catches it and records the row number
     * + message into the import's error log.
     */
    public function processRow(array $row): string
    {
        if (empty($row['name'])) {
            throw new \RuntimeException('Missing product name');
        }

        $brandId = null;
        if (! empty($row['brand'])) {
            $brand = Brand::whereRaw('LOWER(name) = ?', [Str::lower($row['brand'])])->first();
            if (! $brand) {
                throw new \RuntimeException("Invalid brand: {$row['brand']}");
            }
            $brandId = $brand->id;
        }

        $categoryId = null;
        if (! empty($row['category'])) {
            $category = Category::whereRaw('LOWER(name) = ?', [Str::lower($row['category'])])->first();
            if (! $category) {
                throw new \RuntimeException("Invalid category: {$row['category']}");
            }
            $categoryId = $category->id;
        }

        if (! empty($row['price']) && ! is_numeric($row['price'])) {
            throw new \RuntimeException("Invalid price: {$row['price']}");
        }

        // Download+validate images BEFORE touching the database, so a bad
        // image URL fails the row cleanly without leaving a half-created
        // product behind.
        $downloadedImages = [];
        if (! empty($row['images'])) {
            foreach (array_filter(array_map('trim', explode(',', $row['images']))) as $url) {
                try {
                    $downloadedImages[] = $this->images->downloadFromUrl($url, 'products/import');
                } catch (Throwable $e) {
                    throw new \RuntimeException("Invalid image URL: {$url}");
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
            // Don't overwrite the slug of an existing product on update.
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

        foreach ($downloadedImages as $index => $image) {
            ProductImage::create([
                'product_id' => $product->id,
                'image' => $image['path'],
                'alt_text' => $product->name,
                'sort_order' => $index,
                'is_primary' => $index === 0 && $product->images()->count() === 0,
            ]);
        }

        return $result;
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
