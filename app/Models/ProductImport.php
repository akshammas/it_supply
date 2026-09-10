<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImport extends Model
{
    protected $fillable = [
        'user_id', 'original_filename', 'file_path', 'column_mapping',
        'total_rows', 'processed_rows', 'imported_count', 'updated_count',
        'failed_count', 'errors', 'status',
    ];

    protected $casts = [
        'column_mapping' => 'array',
        'errors' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function progressPercent(): int
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return (int) round(($this->processed_rows / $this->total_rows) * 100);
    }
}
