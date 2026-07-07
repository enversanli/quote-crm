<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteLine extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity'         => 'decimal:2',
        'unit_price'       => 'decimal:2',
        'discount_value'   => 'decimal:2',
        'include_in_total' => 'boolean',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /** Line gross (qty × unit_price) */
    public function getLineGrossAttribute(): float
    {
        return round((float) $this->quantity * (float) $this->unit_price, 2);
    }

    /** Discount amount for this line */
    public function getLineDiscountAttribute(): float
    {
        return Quote::calcDiscount(
            $this->line_gross,
            $this->discount_type,
            (float) ($this->discount_value ?? 0)
        );
    }

    /** Net line total after discount */
    public function getLineTotalAttribute(): float
    {
        return round($this->line_gross - $this->line_discount, 2);
    }
}