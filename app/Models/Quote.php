<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\LogsActivity;

class Quote extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'quote_number', 'enquiry_id', 'customer_id', 'created_by',
        'subtotal', 'vat_percent', 'vat_amount', 'shipping', 'discount', 'total',
        'validity_days', 'status', 'notes', 'pdf_path',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'vat_percent' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('line_total');
        $this->vat_amount = round($this->subtotal * ($this->vat_percent / 100), 2);
        $this->total = $this->subtotal + $this->vat_amount + $this->shipping - $this->discount;
        $this->save();
    }
}
