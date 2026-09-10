<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Converts every uploaded image to WebP and stores it at three sizes
 * (section 55): 300px thumbnail, 800px medium, 1200px large. Product
 * cards use the 300px variant, the product page uses 800/1200px.
 */
class ImageService
{
    protected array $sizes = [
        'thumb' => 300,
        'medium' => 800,
        'large' => 1200,
    ];

    /**
     * @return array{path: string, sizes: array<string, string>}
     */
    public function store(UploadedFile $file, string $directory = 'products'): array
    {
        return $this->processAndStore(Image::read($file), $directory);
    }

    /**
     * Section 28: CSV image URL handling. Explicitly fetches the URL via
     * Laravel's HTTP client first, rather than relying on Intervention's
     * internal URL support (which depends on allow_url_fopen and can
     * fail silently in some hosting environments). This way, a bad URL
     * throws a clear, catchable exception every time — never a silent
     * no-op.
     */
    public function downloadFromUrl(string $url, string $directory = 'products'): array
    {
        $response = Http::timeout(15)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; ProductImporter/1.0)'])
            ->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException("Could not download image (HTTP {$response->status()}): {$url}");
        }

        $contentType = $response->header('Content-Type');
        if ($contentType && ! str_starts_with($contentType, 'image/')) {
            throw new \RuntimeException("URL did not return an image (got {$contentType}): {$url}");
        }

        $body = $response->body();

        if (empty($body)) {
            throw new \RuntimeException("Downloaded file was empty: {$url}");
        }

        $image = Image::read($body);

        return $this->processAndStore($image, $directory);
    }

    protected function processAndStore($image, string $directory): array
    {
        $filename = Str::uuid().'.webp';
        $basePath = trim($directory, '/');

        $sizePaths = [];

        foreach ($this->sizes as $label => $width) {
            $resized = (clone $image)->scaleDown(width: $width);
            $encoded = $resized->toWebp(quality: 82);

            $path = "{$basePath}/{$label}/{$filename}";
            Storage::disk('public')->put($path, (string) $encoded);

            $sizePaths[$label] = $path;
        }

        return [
            'path' => $sizePaths['medium'],
            'sizes' => $sizePaths,
        ];
    }

    public function delete(string $path): void
    {
        foreach ($this->sizes as $label => $width) {
            $variant = preg_replace('#/(thumb|medium|large)/#', "/{$label}/", $path);
            Storage::disk('public')->delete($variant);
        }
    }
}