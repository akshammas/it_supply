<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Converts every uploaded image to WebP and stores it at three sizes
 * (section 55): 300px thumbnail, 800px medium, 1200px large. Product
 * cards use the 300px variant, the product page uses 800/1200px.
 *
 * Requires: composer require intervention/image
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
        $filename = Str::uuid().'.webp';
        $basePath = trim($directory, '/');

        $sizePaths = [];

        foreach ($this->sizes as $label => $width) {
            $image = Image::read($file)->scaleDown(width: $width);
            $encoded = $image->toWebp(quality: 82);

            $path = "{$basePath}/{$label}/{$filename}";
            Storage::disk('public')->put($path, (string) $encoded);

            $sizePaths[$label] = $path;
        }

        return [
            // "path" is the canonical reference stored on the model —
            // the medium size is what most front-end templates should use.
            'path' => $sizePaths['medium'],
            'sizes' => $sizePaths,
        ];
    }

    public function delete(string $path): void
    {
        // Derive and remove all three size variants from one stored path.
        foreach ($this->sizes as $label => $width) {
            $variant = preg_replace('#/(thumb|medium|large)/#', "/{$label}/", $path);
            Storage::disk('public')->delete($variant);
        }
    }
}
