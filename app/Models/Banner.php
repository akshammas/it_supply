<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'subtitle', 'image', 'link_url', 'button_text', 'position', 'sort_order', 'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];


    protected array $frontendCacheKeys = ['homepage.data'];
}
