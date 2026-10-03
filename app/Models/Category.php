<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\BustsFrontendCache;

class Category extends Model
{
    use HasFactory;
    use LogsActivity;
    use BustsFrontendCache;

    protected array $frontendCacheKeys = ['nav.categories', 'nav.mega', 'homepage.data', 'sitemap.xml'];

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'image',
        'meta_title', 'meta_description', 'status', 'sort_order','icon'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function setIconAttribute($value): void
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/^bi\s+/', '', $value);      // "bi bi-laptop" -> "bi-laptop"

        if ($value !== '' && !str_starts_with($value, 'bi-')) {
            $value = 'bi-' . $value;                        // "laptop" -> "bi-laptop"
        }

        $this->attributes['icon'] = $value !== '' ? $value : null;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
    
}
