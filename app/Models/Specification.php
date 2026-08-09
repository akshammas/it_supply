<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specification extends Model
{
    use HasFactory;

    protected $fillable = ['specification_group_id', 'name', 'sort_order'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(SpecificationGroup::class, 'specification_group_id');
    }

    public function productSpecifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class);
    }
}
