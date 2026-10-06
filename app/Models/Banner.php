<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Concerns\BustsFrontendCache;   // ← 1. add this line at the top, with the other `use` lines


class Banner extends Model
{
    use HasFactory;
    use BustsFrontendCache;    

    protected $fillable = [
        'title', 'subtitle', 'image', 'link_url', 'button_text', 'position', 'sort_order', 'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function categories(): BelongsToMany
        {
            return $this->belongsToMany(Category::class, 'banner_category');
        }

    public function brands(): BelongsToMany
        {
            return $this->belongsToMany(Brand::class, 'banner_brand');
        }


    protected array $frontendCacheKeys = ['homepage.data'];
}
