<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopupLead extends Model
{
    protected $fillable = ['popup_id', 'name', 'phone', 'page_url', 'consented', 'status', 'ip_address'];

    protected $casts = ['consented' => 'boolean'];

    public function popup(): BelongsTo
    {
        return $this->belongsTo(Popup::class);
    }

    /** Digits only, for a wa.me link (the visitor should include the country code) */
    public function getWhatsappNumberAttribute(): string
    {
        return preg_replace('/\D/', '', $this->phone);
    }
}