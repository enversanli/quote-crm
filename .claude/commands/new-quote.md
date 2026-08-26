# New Quote

Create a new quote in the EventHub CRM database using `php artisan tinker`.

## What to ask the user

If any of the following are missing from their message, ask for them before proceeding:
- **Customer name** (first + last, or company name)
- **Event date** (dd.mm.yyyy or yyyy-mm-dd)

The following are optional — use sensible defaults if not provided:
- Customer email / phone
- Customer company
- Business / venue (skip if not given)
- Event start time / end time (HH:MM)
- Hourly rate (default: 117.00)
- Event type (`meeting`, `conference`, `workshop`, `seminar`, `training`, `birthday`, `wedding`, `gala`, `team_event`, `other`)
- Organizer type (`private` / `business`)
- Attendee count
- Valid until date (default: 30 days from today)
- Status (default: `draft`; options: `draft`, `sent`, `accepted`, `completed`, `rejected`, `expired`)
- Payment status (default: `unpaid`; options: `unpaid`, `invoice_sent`, `partial`, `paid`)
- Venue discount type + value (`percentage` or `fixed`)
- Global discount type + value (`percentage` or `fixed`)
- Notes

## Default service lines

Every new quote gets these lines automatically — no need to ask the user.
Lines are linked to catalog items from the **"Event Hub - Dienstleistungen"** item group via `order_item_id`.

### Standard services (`unit_price = 0`, `include_in_total = true`)
Price is overridden to €0 because these are already covered by the venue price.
Note: `"Im Grundpreis enthalten"`

| Description                  | Catalog match (partial name search) | Qty |
|------------------------------|-------------------------------------|-----|
| Reinigung                    | `Reinigung`                         | 1   |
| Wifi / WLAN                  | `Wifi`                              | 1   |
| Lautsprecher mit Mikrofonen  | `Lautsprecher`                      | 1   |
| Beamer                       | `Beamer`                            | 1   |
| Flipcharts                   | `Flipchart`                         | 2   |

### Optional drinks (`include_in_total = false`, shown informational only)
Use the **actual catalog price** (not €0) so it's ready if the customer adds them.
Note: `"Optional – Preis auf Anfrage"`

| Description                | Catalog match (partial name search) | Qty |
|----------------------------|-------------------------------------|-----|
| Fritz Cola / Fritz Orange  | `Fritz`                             | 1   |
| Wasser (Sprudel / Still)   | `Wasser`                            | 1   |
| Orangensaft / Apfelsaft    | `Bauer`                             | 1   |

## Steps

1. Collect all details from the user.
2. Look up or create the Customer record via tinker:
   - If email is provided: use `firstOrCreate` keyed on email.
   - If no email but name is provided: create a new Customer.
     **Note: `last_name` is NOT NULL — use `''` if the customer has no last name.**
   - If neither name nor email — set `$customer = null` and use the manual snapshot fields only.
3. Look up the Business record by name if a venue was mentioned.
4. Calculate `hours` from start/end time if both are given (decimal, e.g. 2.5).
5. Run `php artisan tinker --execute="require '/tmp/new_quote.php';"` — write the PHP to `/tmp/new_quote.php` first.
6. Print the generated quote number and a link: `http://localhost/manager/quotes/{id}/edit`

## Tinker template

Write this to `/tmp/new_quote.php`, then run:
```bash
php artisan tinker --execute="require '/tmp/new_quote.php';"
```

```php
<?php

// ── Customer ──────────────────────────────────────────────────────────
$customer = null;

// Option A — email provided: find or create
// $customer = \App\Models\Customer::firstOrCreate(
//     ['email' => 'EMAIL'],
//     ['first_name' => 'FIRST', 'last_name' => 'LAST', 'company_name' => 'COMPANY', 'phone' => 'PHONE', 'country' => 'DE']
// );

// Option B — no email: create by name only
// Note: last_name is NOT NULL — use '' if the customer has no last name
// $customer = \App\Models\Customer::create([
//     'first_name' => 'FIRST', 'last_name' => 'LAST', 'company_name' => 'COMPANY', 'country' => 'DE'
// ]);

// ── Business / Venue ──────────────────────────────────────────────────
$business = \App\Models\Business::where('name', 'like', '%VENUE%')->first();

// ── Quote ─────────────────────────────────────────────────────────────
$quote = \App\Models\Quote::create([
    'customer_id'          => $customer?->id,
    'customer_first_name'  => 'FIRST',
    'customer_last_name'   => 'LAST',       // use '' if no last name
    'customer_company'     => 'COMPANY',    // or null
    'customer_email'       => 'EMAIL',      // or null
    'customer_phone'       => 'PHONE',      // or null
    'business_id'          => $business?->id,
    'event_date'           => 'YYYY-MM-DD',
    'event_start_time'     => 'HH:MM:SS',   // or null
    'event_end_time'       => 'HH:MM:SS',   // or null
    'hours'                => HOURS,         // decimal or null
    'hourly_rate'          => 117.00,
    'attendee_count'       => COUNT,         // or null
    'organizer_type'       => 'private',     // 'private' | 'business' | null
    'event_type'           => 'TYPE',        // or null
    'valid_until'          => now()->addDays(30)->toDateString(),
    'venue_discount_type'  => null,
    'venue_discount_value' => null,
    'discount_type'        => null,
    'discount_value'       => null,
    'status'               => 'draft',
    'payment_status'       => 'unpaid',
    'vat_rate'             => 19,
    'notes'                => null,
]);

// ── Catalog lookup helper ─────────────────────────────────────────────
$catalog = \App\Models\OrderItem::whereHas('itemGroup', fn($q) =>
    $q->where('name', 'Event Hub - Dienstleistungen')
)->get();

$find = fn(string $needle) => $catalog->first(fn($i) =>
    str_contains(mb_strtolower($i->name), mb_strtolower($needle))
);

// ── Standard services (€0 — included in venue price) ──────────────────
foreach ([
    ['needle' => 'Reinigung',    'description' => 'Reinigung',                   'qty' => 1],
    ['needle' => 'Wifi',         'description' => 'Wifi / WLAN',                 'qty' => 1],
    ['needle' => 'Lautsprecher', 'description' => 'Lautsprecher mit Mikrofonen', 'qty' => 1],
    ['needle' => 'Beamer',       'description' => 'Beamer',                      'qty' => 1],
    ['needle' => 'Flipchart',    'description' => 'Flipcharts',                  'qty' => 2],
] as $svc) {
    $item = $find($svc['needle']);
    $quote->quoteLines()->create([
        'order_item_id'    => $item?->id,
        'description'      => $svc['description'],
        'unit'             => $item?->unit ?? '',
        'quantity'         => $svc['qty'],
        'unit_price'       => 0,
        'include_in_total' => true,
        'notes'            => 'Im Grundpreis enthalten',
    ]);
}

// ── Optional drinks (informational only — not counted in total) ────────
foreach ([
    ['needle' => 'Fritz',  'description' => 'Fritz Cola / Fritz Orange'],
    ['needle' => 'Wasser', 'description' => 'Wasser (Sprudel / Still)'],
    ['needle' => 'Bauer',  'description' => 'Orangensaft / Apfelsaft'],
] as $drink) {
    $item = $find($drink['needle']);
    $quote->quoteLines()->create([
        'order_item_id'    => $item?->id,
        'description'      => $drink['description'],
        'unit'             => $item?->unit ?? 'Stk.',
        'quantity'         => 1,
        'unit_price'       => (float) ($item?->price ?? 0),
        'include_in_total' => false,
        'notes'            => 'Optional – Preis auf Anfrage',
    ]);
}

echo $quote->quote_number . ' — ID: ' . $quote->id . PHP_EOL;
echo 'Business: ' . ($business ? $business->name : 'NOT FOUND') . PHP_EOL;
```

After running, confirm the quote was created with its service lines and show the quote number + edit URL.
