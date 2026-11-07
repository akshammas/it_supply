<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\LogsActivity;

class Brand extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name', 'slug', 'logo', 'description', 'seo_content', 'website',
        'meta_title', 'meta_description', 'status', 'sort_order',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function faqs(): BelongsToMany
    {
        return $this->belongsToMany(Faq::class, 'brand_faq');
    }

    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'brand_blog');
    }
    
    
    protected array $frontendCacheKeys = ['nav.brands', 'homepage.data', 'sitemap.xml'];
}
