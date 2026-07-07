# EventHub CRM — CLAUDE.md

## Project Overview

Laravel 12 + Filament 3 admin CRM for event venue quote and order management. The entire admin UI lives inside Filament (no custom controllers/views for CRUD). PDF generation uses DomPDF.

## Key Commands

```bash
# Development
composer dev          # Starts Laravel server, queue listener, log tail, and Vite dev server concurrently
npm run dev           # Vite HMR only (if running frontend separately)

# First-time setup
composer setup        # install deps → copy .env → key:generate → migrate → npm install → build

# Testing
composer test         # php artisan config:clear && php artisan test

# Assets
npm run build         # Production Vite build

# Artisan shortcuts
php artisan serve
php artisan migrate
php artisan tinker
php artisan pail      # real-time log streaming
```

## Architecture

| Layer | Technology | Notes |
|-------|-----------|-------|
| Framework | Laravel 12 | PHP ^8.2 |
| Admin panel | Filament 3.2 | Mounted at `/manager`, Amber color scheme, auth required |
| Frontend build | Vite 7 + Tailwind CSS 4 | `resources/css/app.css` + `resources/js/app.js` |
| Database | SQLite (dev) | Configurable to MySQL via `.env` |
| Queue / Cache / Session | Database driver | All three use the database backend |
| PDF | barryvdh/laravel-dompdf + FPDF/FPDI | Quote PDFs rendered from `resources/views/pdf/quote.blade.php` |
| Testing | PHPUnit 11 | In-memory SQLite, test env in `phpunit.xml` |

## Important Directories

```
app/
  Models/                  # Eloquent models (Quote, Customer, Business, Order, OrderItem, QuoteLine, OrderLine, ItemGroup, Lead, User)
  Filament/Resources/      # Admin panel CRUD resources
  Providers/Filament/      # ManagerPanelProvider — configures Filament panel
database/
  migrations/              # 24 migration files
resources/views/pdf/       # Blade templates for PDF output
```

## Data Model Highlights

- **Quote** — core entity; auto-generated number format `QT-YYYY-#####`; fields: `event_date`, `hourly_rate`, `hours`, `venue_subtotal`, `status`, `valid_until`, `organizer_type`, `event_type`, `attendee_count`
- **QuoteLine** — belongs to Quote; supports quantity, unit price, discount (% or fixed), links optionally to an OrderItem
- **Customer / Business** — separate entities; Business holds `default_hourly_rate` and `capacity`
- **ItemGroup → OrderItem → OrderLine** — product catalog hierarchy used in orders
- Quote cascade-deletes its QuoteLines

## Filament Resources

`QuoteResource`, `OrderResource`, `CustomerResource`, `BusinessResource`, `LeadResource`, `ItemGroupResource`, `OrderItemResource` — all in `app/Filament/Resources/`.

Each resource has `Pages/` subdirectory with Create / Edit / List pages.

## Environment

Default `.env` values to be aware of:
- `DB_CONNECTION=sqlite` → `database/database.sqlite`
- `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`
- `MAIL_MAILER=log` (dev — emails go to log file)
- `APP_URL=http://localhost`

## Testing Notes

- Tests use an in-memory SQLite database (set in `phpunit.xml`)
- Test suite split: `tests/Unit/` and `tests/Feature/`
- Queue is synchronous in test env (`QUEUE_CONNECTION=sync`)

## Models & Relationships

| Model | Key Relations | Notes |
|-------|--------------|-------|
| `User` | — | `canAccessPanel()` checks email domain for Filament access |
| `Customer` | `quotes()` hasMany | Computed attrs: `full_name`, `display_name` (appends company) |
| `Business` | `quotes()` hasMany | Holds `default_hourly_rate` (decimal:2), `capacity` (integer) |
| `Quote` | `customer()` belongsTo, `business()` belongsTo, `quoteLines()` hasMany | Auto-generates `QT-YYYY-#####` on `creating`; recalculates `venue_subtotal` on `saving`; computed attrs: `subtotal_net`, `discount_amount`, `net_after_discount`, `vat_amount`, `total_gross`, `customer_display_name` |
| `QuoteLine` | `quote()` belongsTo, `orderItem()` belongsTo (nullable) | Computed attrs: `line_gross`, `line_discount`, `line_total`; `include_in_total` boolean |
| `Order` | `orderLines()` hasMany | `status` default `draft` |
| `OrderLine` | `order()` belongsTo, `orderItem()` belongsTo | `include_in_total` boolean |
| `OrderItem` | `itemGroup()` belongsTo, `orderLines()` hasMany | Has `price` (decimal:2), `unit` string |
| `ItemGroup` | `orderItems()` hasMany | Simple name-only grouping |
| `Lead` | — | `$guarded = []`; no relationships |

### Pricing / Discount logic

`Quote::calcDiscount($amount, $type, $value)` — shared static helper also used by `QuoteLine`. Discount types are `percentage` or `fixed` (stored as strings). VAT default is 19% (`vat_rate`). Venue discount and line-level discount are separate from the global quote discount.

## Filament Resources — Quick Reference

### QuoteResource
- **Navigation:** "Quotes", sort 1
- **Form tabs:** Overview (status, valid_until, vat_rate, notes) · Event & Venue (event_date, business, times, hourly_rate, hours auto-calc, attendee_count, organizer_type, event_type, venue discount) · Customer (searchable select or manual fields) · Services & Pricing (QuoteLine repeater + global discount + live price breakdown)
- **Table filters/actions:** preview_pdf, download_pdf (show_prices, language, with_cover, show_cost_per_person toggles), duplicate
- **Status values:** `draft` / `sent` / `accepted` / `rejected` / `expired`

### OrderResource
- **Navigation:** "Orders", sort 1
- **Form:** Bestelldetails (name, date, status, notes, grand_total display) + Bestellpositionen repeater (order_item grouped by category, quantity, price display, include_in_total)
- **Actions:** download_pdf (show_prices toggle)
- **Status values:** `draft` / `confirmed` / `completed`

### CustomerResource
- **Navigation:** "Contacts", sort 1
- **Form sections:** Personal Info, Address (country default `DE`), Notes

### BusinessResource
- **Navigation:** "Contacts", sort 2
- **Form sections:** Business Details, Venue/Pricing (hourly_rate, capacity, address), Notes

### LeadResource
- **Navigation:** default (no group)
- **Bulk actions:** Import (LeadImporter) + Export (LeadExporter)
- **Custom row actions:** `visit_website`, `open_maps`

### ItemGroupResource / OrderItemResource
- **Navigation:** "Orders", sort 3 / sort 2

## Filament Imports & Exports

| Class | File | Purpose |
|-------|------|---------|
| `LeadImporter` | `app/Filament/Imports/LeadImporter.php` | CSV import for Leads; `firstOrNew` on name; merges `address_2` into `address` when address is empty |
| `LeadExporter` | `app/Filament/Exports/LeadExporter.php` | CSV export of all Lead fields |

## Routes

| Method | URI | Auth | Description |
|--------|-----|------|-------------|
| GET | `/` | — | Welcome page |
| GET | `/quotes/{quote}/preview` | `auth` | Returns quote PDF rendered as HTML; query params: `language`, `show_prices`, `with_cover`, `show_cost_per_person` |

All Filament panel routes live under `/manager` (mounted in `ManagerPanelProvider`).

## Database Tables

| Table | Created by migration | Key columns |
|-------|---------------------|-------------|
| `users` | `0001_01_01_000000` | standard Laravel auth fields |
| `leads` | `2026_03_09_134339` | name, category, rating, reviews_count, address, address_2, email, status_now, website, mobile, phone, google_maps_link |
| `item_groups` | `2026_04_17_000001` | name |
| `order_items` | `2026_04_17_000002` | item_group_id (FK), name, unit, price (decimal 10,2) |
| `orders` | `2026_04_17_000003` | name, date, notes, status (default draft) |
| `order_lines` | `2026_04_17_000004` | order_id (FK cascade), order_item_id (FK cascade), quantity, notes, include_in_total (bool) |
| `customers` | `2026_04_27_000001` | first_name, last_name, company_name, email, phone, address, city, postal_code, country (default DE), notes |
| `businesses` | `2026_04_27_000002` | name, default_hourly_rate, capacity, contact_person, email, phone, address, city, postal_code, website, notes |
| `quotes` | `2026_04_27_000003` + alters | quote_number (unique), customer_id (nullable FK), business_id (nullable FK), snapshot customer fields, event_date, times, hourly_rate, hours, venue_subtotal, status, valid_until, vat_rate, discount_type/value, venue_discount_type/value, attendee_count, organizer_type, event_type |
| `quote_lines` | `2026_04_27_000004` + alters | quote_id (FK cascade), order_item_id (nullable FK), description, unit, quantity, unit_price, discount_type/value, include_in_total |

## PDF Generation

- Quote PDFs: `resources/views/pdf/quote.blade.php` rendered via DomPDF
- Preview route (`/quotes/{quote}/preview`) renders the same template as HTML
- Download action supports: `language` (string), `show_prices` (bool), `with_cover` (bool), `show_cost_per_person` (bool)
- Order PDFs rendered via same DomPDF pipeline with `show_prices` toggle

## Key Conventions

- All money values stored as `decimal(10,2)`; displayed with EUR prefix via Filament `money('EUR')`
- Discount types stored as strings: `'percentage'` or `'fixed'`
- Status fields stored as strings (no enums); badge colors mapped in resource column definitions
- Customer info is snapshotted onto Quote at creation (separate `customer_first_name` etc. columns) — customer can be looked up or filled manually
- `$guarded = []` used on Lead; all other models use standard fillable/guarded defaults
- Panel access controlled by `User::canAccessPanel()` (email domain check)
