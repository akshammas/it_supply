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