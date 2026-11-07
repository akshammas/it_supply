<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Faq extends Model
{
    protected $fillable = ['question', 'answer', 'sort_order', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_faq');
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'brand_faq');
    }

    /** Active FAQs ticked for this category (shown on the category page). */
    public function scopeForCategory(Builder $query, Category $category): Builder
    {
        return $query->where('status', true)
            ->whereHas('categories', fn ($c) => $c->where('categories.id', $category->id))
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** Active FAQs ticked for this brand (shown on the brand page). */
    public function scopeForBrand(Builder $query, Brand $brand): Builder
    {
        return $query->where('status', true)
            ->whereHas('brands', fn ($b) => $b->where('brands.id', $brand->id))
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * Active FAQs that are ticked for any of the product's categories.
     * A FAQ with no categories ticked never matches. whereHas() returns each
     * FAQ once, even if it is ticked on several of the product's categories.
     */
    public function scopeForProduct(Builder $query, Product $product): Builder
    {
        $categoryIds = array_filter([$product->category_id]);

        return $query->where('status', true)
            ->whereHas('categories', fn ($c) => $c->whereIn('categories.id', $categoryIds))
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}