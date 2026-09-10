<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Cache;

/**
 * Attach to any model whose changes should invalidate cached frontend
 * output immediately, rather than waiting out the cache's normal TTL.
 * Define $frontendCacheKeys on the model listing which cache keys its
 * saves/deletes should clear.
 *
 * Usage:
 *   class Category extends Model
 *   {
 *       use BustsFrontendCache;
 *       protected array $frontendCacheKeys = ['nav.categories', 'homepage.data', 'sitemap.xml'];
 *   }
 */
trait BustsFrontendCache
{
    public static function bootBustsFrontendCache(): void
    {
        static::saved(fn ($model) => $model->forgetFrontendCache());
        static::deleted(fn ($model) => $model->forgetFrontendCache());
    }

    protected function forgetFrontendCache(): void
    {
        foreach ($this->frontendCacheKeys ?? [] as $key) {
            Cache::forget($key);
        }
    }
}
