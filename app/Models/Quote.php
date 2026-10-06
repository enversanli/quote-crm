<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

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
                $quote->quote_number = static::generateQuoteNumber();
            }
        });

        static::saving(function (Quote $quote) {
            // Venue net after venue-level discount
            $venueGross          = round((float) ($quote->hours ?? 0) * (float) ($quote->hourly_rate ?? 0), 2);
            $venueDiscount       = self::calcDiscount($venueGross, $quote->venue_discount_type, (float) ($quote->venue_discount_value ?? 0));
            $quote->venue_subtotal = round($venueGross - $venueDiscount, 2);
        });
    }

    /**
     * Next QT-YYYY-##### number for the current year, based on the highest
     * suffix actually in use (not a row count, which drifts once quotes are
     * deleted and collides with numbers still assigned to surviving quotes).
     */
    public static function generateQuoteNumber(): string
    {
        $year = now()->year;

        $maxSuffix = static::where('quote_number', 'like', "QT-{$year}-%")
            ->pluck('quote_number')
            ->map(fn (string $number) => (int) substr($number, -5))
            ->max() ?? 0;

        do {
            $maxSuffix++;
            $candidate = 'QT-' . $year . '-' . str_pad($maxSuffix, 5, '0', STR_PAD_LEFT);
        } while (static::where('quote_number', $candidate)->exists());

        return $candidate;
    }

    // ──────────────────────────────────────────────
    // Same-day bookings
    // ──────────────────────────────────────────────

    /** Statuses that occupy the venue (or are offered to a customer) for their event date. */
    public const BOOKING_STATUSES = ['sent', 'accepted', 'completed'];

    /**
     * Other booking-status quotes on the given date at the given venue
     * (any venue when $businessId is null), ordered by start time.
     */
    public static function sameDayBookings($date, $businessId = null, $exceptId = null): Collection
    {
        if (blank($date)) {
            return collect();
        }

        return static::query()
            ->whereIn('status', self::BOOKING_STATUSES)
            ->whereDate('event_date', $date)
            ->when($businessId, fn ($q) => $q->where('business_id', $businessId))
            ->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))
            ->orderBy('event_start_time')
            ->get();
    }

    /**
     * Upcoming days where a venue has more than one booking-status quote,
     * keyed by "Y-m-d|business_id" and sorted by date.
     */
    public static function upcomingClashes(): Collection
    {
        return static::query()
            ->with(['business', 'customer'])
            ->whereIn('status', self::BOOKING_STATUSES)
            ->whereDate('event_date', '>=', today())
            ->orderBy('event_date')
            ->orderBy('event_start_time')
            ->get()
            ->groupBy(fn (Quote $q) => $q->event_date->toDateString() . '|' . $q->business_id)
            ->filter(fn (Collection $group) => $group->count() > 1);
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