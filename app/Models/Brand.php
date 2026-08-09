<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\LogsActivity;

class Brand extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name', 'slug', 'logo', 'description', 'website',
        'meta_title', 'meta_description', 'status', 'sort_order',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
