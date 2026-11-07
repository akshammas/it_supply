<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'blog_category_id', 'author_id', 'title', 'slug', 'featured_image',
        'excerpt', 'content', 'status', 'published_at', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'status' => 'boolean',
        'published_at' => 'datetime',
    ];


    protected array $frontendCacheKeys = ['sitemap.xml'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** Shop categories whose page shows this post (not the blog's own category). */
    public function productCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_blog');
    }

    /** Brands whose page shows this post. */
    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'brand_blog');
    }
}
