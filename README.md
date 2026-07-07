# Quote CRM

Laravel 12 + Filament 3 admin CRM for event venue quote and order management. The entire admin UI lives inside Filament (no custom controllers/views for CRUD). PDF generation uses DomPDF.

## Features

- Quote management with auto-generated quote numbers (`QT-YYYY-#####`), line items, discounts, and PDF export
- Order management with a product catalog (item groups → order items → order lines)
- Customer and business (venue) contact management
- Lead tracking with CSV import/export
- Quote and order PDF generation (preview + download) via DomPDF

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 (PHP ^8.2) |
| Admin panel | Filament 3.2 (mounted at `/manager`) |
| Frontend build | Vite 7 + Tailwind CSS 4 |
| Database | SQLite (dev), configurable to MySQL |
| Queue / Cache / Session | Database driver |
| PDF | barryvdh/laravel-dompdf + FPDF/FPDI |
| Testing | PHPUnit 11 |

## Getting Started

```bash
composer setup        # install deps → copy .env → key:generate → migrate → npm install → build
```

## Development

```bash
composer dev          # Laravel server, queue listener, log tail, and Vite dev server concurrently
npm run dev           # Vite HMR only (if running frontend separately)
```

## Testing

```bash
composer test          # php artisan config:clear && php artisan test
```

## Useful Artisan Commands

```bash
php artisan serve
php artisan migrate
php artisan tinker
php artisan pail        # real-time log streaming
```

See `CLAUDE.md` for a full architecture overview, data model, and Filament resource reference.

## License

Proprietary — all rights reserved.
