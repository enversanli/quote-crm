<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $guarded = [];

    protected $casts = [
        'event_date'           => 'date',
        'valid_until'          => 'date',
        'hourly_rate'          => 'decimal:2',
        'hours'                => 'decimal:2',
        'venue_subtotal'       => 'decimal:2',
        'vat_rate'             => 'decimal:2',
        'discount_value'       => 'decimal:2',
        'venue_discount_value' => 'decimal:2',
        'attendee_count'       => 'integer',
        'payment_status'       => 'string',
    ];

    // ──────────────────────────────────────────────
    // Boot
    // ──────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Quote $quote) {
            if (empty($quote->quote_number)) {
                $year  = now()->year;
                $count = static::whereYear('created_at', $year)->count() + 1;
                $quote->quote_number = 'QT-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function (Quote $quote) {
            // Venue net after venue-level discount
            $venueGross          = round((float) ($quote->hours ?? 0) * (float) ($quote->hourly_rate ?? 0), 2);
            $venueDiscount       = self::calcDiscount($venueGross, $quote->venue_discount_type, (float) ($quote->venue_discount_value ?? 0));
            $quote->venue_subtotal = round($venueGross - $venueDiscount, 2);
        });
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function quoteLines(): HasMany
    {
        return $this->hasMany(QuoteLine::class);
    }

    // ──────────────────────────────────────────────
    // Computed attributes  (require quoteLines loaded)
    // ──────────────────────────────────────────────

    /** Net venue + net lines (only included lines), before global discount */
    public function getSubtotalNetAttribute(): float
    {
        $linesNet = $this->quoteLines
            ->filter(fn (QuoteLine $l) => (bool) $l->include_in_total)
            ->sum(fn (QuoteLine $l) => $l->line_total);

        return round((float) $this->venue_subtotal + $linesNet, 2);
    }

    /** Global discount amount applied on subtotal_net */
    public function getDiscountAmountAttribute(): float
    {
        return self::calcDiscount(
            $this->subtotal_net,
            $this->discount_type,
            (float) ($this->discount_value ?? 0)
        );
    }

    /** Net total after global discount */
    public function getNetAfterDiscountAttribute(): float
    {
        return round($this->subtotal_net - $this->discount_amount, 2);
    }

    /** VAT amount */
    public function getVatAmountAttribute(): float
    {
        return round($this->net_after_discount * (float) ($this->vat_rate ?? 19) / 100, 2);
    }

    /** Gross total (brutto) */
    public function getTotalGrossAttribute(): float
    {
        return round($this->net_after_discount + $this->vat_amount, 2);
    }

    /** Human-readable customer label */
    public function getCustomerDisplayNameAttribute(): string
    {
        if ($this->customer) {
            return $this->customer->display_name;
        }

        $parts = array_filter([
            trim("{$this->customer_first_name} {$this->customer_last_name}"),
            $this->customer_company,
        ]);

        return implode(' · ', $parts) ?: '—';
    }

    // ──────────────────────────────────────────────
    // Shared discount helper
    // ──────────────────────────────────────────────

    public static function calcDiscount(float $amount, ?string $type, float $value): float
    {
        if (! $type || $value <= 0 || $amount <= 0) {
            return 0.0;
        }

        return match ($type) {
            'percentage' => round($amount * $value / 100, 2),
            'fixed'      => round(min($amount, $value), 2),
            default      => 0.0,
        };
    }
}