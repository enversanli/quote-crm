# Email Quote

Generate the quote PDF and open a ready-to-review draft in the Microsoft Outlook **desktop app** for a given quote — reusing the exact same PDF-generation and Outlook-drafting logic already built into `QuoteResource` (the "Email" row action in the Filament admin panel at `/manager/quotes`). This command does **not** duplicate that logic and does **not** send anything automatically — it only opens an unsent, editable draft in Outlook with the PDF attached, exactly like clicking "Email" → "Open in Outlook" in the app itself.

## What to ask the user

- **Quote** — a quote number (`QT-2026-00058`) or numeric ID. If not given, use the most recently created quote.

The following are optional — use sensible defaults if not provided, matching the app's own "Email" modal defaults:
- **Language** (`de` / `en` / `tr`) — default `de` (formal German, matches the built-in template).
- **Include cover & back pages** (`with_cover`) — default `false`.

Confirm the resolved quote (number, customer, event date) with the user before opening Outlook if it was auto-selected (i.e. no quote was named).

## Requirements

- Must run on the actual machine (not a sandboxed/remote shell) with `php` available and the **Microsoft Outlook desktop app installed and running** — the underlying `openInOutlook()` helper drives it via AppleScript (`tell application "Microsoft Outlook"`), which only works on macOS.
- The recipient is taken from the quote: `customer.email` if the quote has a linked `Customer`, otherwise the snapshotted `customer_email` field on the quote itself. If both are empty, still open the Outlook draft (subject, body, PDF attached) with an **empty recipient field** — this is only ever an unsent draft, so the user can fill in the "To" address themselves before sending. Tell the user this happened; don't abort.

## Steps

1. Resolve the quote: look it up by `quote_number` or `id` if given, otherwise `Quote::where('status', 'draft')->orderByDesc('created_at')->first()`.
2. Write the PHP below to `/tmp/email_quote.php`, then run:
   ```bash
   php artisan tinker --execute="require '/tmp/email_quote.php';"
   ```
3. This reuses `App\Filament\Resources\QuoteResource::buildEmailParts()` (subject/body/recipient), `generateQuotePdf()` (renders `pdf.quote` via DomPDF, optionally merges cover/back pages via FPDI, writes to a temp file) and `openInOutlook()` (AppleScript: creates a new outgoing message in Outlook, sets subject/body/recipient, attaches the PDF, then `open`s and `activate`s it — it never calls `send`).
4. Report back: quote number, recipient email (or a note that it's empty and needs to be filled in), subject line, and a reminder that an **unsent draft** is now open and focused in Outlook for the user to review before sending.

## Tinker template

Write this to `/tmp/email_quote.php` (fill in the placeholders), then run the command from step 2:

```php
<?php

// ── Resolve the quote ───────────────────────────────────────────────────
// Option A — a specific quote was named:
// $quote = \App\Models\Quote::where('quote_number', 'QT-2026-00058')->firstOrFail();
// Option B — no quote named: most recent draft
$quote = \App\Models\Quote::where('status', 'draft')->orderByDesc('created_at')->first();

if (! $quote) {
    echo "No draft quote found." . PHP_EOL;
    exit;
}

$language  = 'de';   // 'de' | 'en' | 'tr' — ask the user, default 'de'
$withCover = false;  // ask the user, default false

// ── Reuse the app's own private helpers via reflection (no re-implementation) ──
$resource = \App\Filament\Resources\QuoteResource::class;

$buildEmailParts = new \ReflectionMethod($resource, 'buildEmailParts');
$buildEmailParts->setAccessible(true);
[$to, $subject, $body] = $buildEmailParts->invoke(null, $quote, $language);

if (empty($to)) {
    echo "Note: no customer email on file — draft will open with an empty recipient field to fill in manually." . PHP_EOL;
}

$generatePdf = new \ReflectionMethod($resource, 'generateQuotePdf');
$generatePdf->setAccessible(true);
$pdfPath = $generatePdf->invoke(null, $quote, $language, $withCover);

$openInOutlook = new \ReflectionMethod($resource, 'openInOutlook');
$openInOutlook->setAccessible(true);
$openInOutlook->invoke(null, $to, $subject, $body, $pdfPath);

echo "Quote: {$quote->quote_number}" . PHP_EOL;
echo "To: {$to}" . PHP_EOL;
echo "Subject: {$subject}" . PHP_EOL;
echo "Outlook draft opened (unsent) with PDF attached: {$pdfPath}" . PHP_EOL;
```

After running, tell the user the draft is open and focused in Outlook, ready for them to review and hit send — nothing is sent automatically.
