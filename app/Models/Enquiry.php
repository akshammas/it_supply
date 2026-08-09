<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\LogsActivity;

class Enquiry extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'customer_id', 'name', 'company_name', 'email', 'phone', 'message',
        'delivery_location', 'required_delivery_date', 'status',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EnquiryItem::class);
    }

    public function quote(): HasMany
    {
        return $this->hasMany(Quote::class);
    }
}
