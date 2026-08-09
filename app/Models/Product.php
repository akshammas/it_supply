<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Concerns\LogsActivity;

class Product extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'brand_id', 'category_id', 'name', 'slug', 'sku', 'model_number',
        'short_description', 'description', 'price', 'sale_price', 'price_type',
        'stock', 'stock_status', 'condition', 'featured', 'status',
        'meta_title', 'meta_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'featured' => 'boolean',
        'status' => 'boolean',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function specifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class);
    }

    public function solutions(): BelongsToMany
    {
        return $this->belongsToMany(Solution::class, 'solution_product')->withPivot('sort_order');
    }

    public function whatsappMessage(): string
    {
        return "Hello,\n\nI am interested in:\n\nProduct: {$this->name}\nSKU: {$this->sku}\n\nPlease provide price and availability.\n\nThank you.";
    }
}
