<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
