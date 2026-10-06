<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Banner extends Model
{
    use HasFactory;

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


    protected array $frontendCacheKeys = ['homepage.data'];
}
