<!DOCTYPE html>
<html lang="{{ $language ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <title>Quote {{ $quote->quote_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            background: #fff;
        }

        /* ── Top address bar (matches existing PDFs) ── */
        .top-bar {
            background: #f5f5f5;
            border-bottom: 1px solid #ddd;
            padding: 6px 36px;
            font-size: 8.5px;
            color: #888;
            text-align: left;
        }

        /* ── Page content wrapper ── */
        .page {
            padding: 28px 40px 95px;
        }

        /* ── Document title block ── */
        .doc-heading {
            text-align: center;
            margin-bottom: 24px;
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 14px;
        }
        .doc-heading .doc-label {
            font-size: 9px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 4px;
        }
        .doc-heading .doc-title {
            font-size: 22px;
            font-weight: bold;
            color: #1a1a1a;
        }
        .doc-heading .doc-subtitle {
            font-size: 10px;
            color: #555;
            margin-top: 3px;
        }

        /* ── Two-column info (customer / issuer) ── */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { width: 50%; vertical-align: top; padding: 0 8px; }
        .info-table td:first-child { padding-left: 0; border-right: 1px solid #e5e5e5; padding-right: 20px; }
        .info-table td:last-child  { padding-left: 20px; padding-right: 0; }

        .info-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #999;
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 1px solid #efefef;
        }
        .info-value {
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.7;
        }
        .info-value strong { color: #000; }

        /* ── Meta row (status / date / validity) ── */
        .meta-row {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: #f9f9f9;
            border: 1px solid #e8e8e8;
        }
        .meta-row td {
            padding: 7px 14px;
            font-size: 10px;
            border-right: 1px solid #e8e8e8;
            vertical-align: top;
        }
        .meta-row td:last-child { border-right: none; }
        .meta-row .m-label { font-size: 8px; color: #999; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 2px; }
        .meta-row .m-value { font-weight: bold; color: #1a1a1a; }

        /* ── Status badges ── */
        .badge { display: inline-block; padding: 1px 7px; border-radius: 8px; font-size: 9px; font-weight: bold; }
        .badge-draft    { background: #efefef; color: #777; }
        .badge-sent     { background: #dbeafe; color: #1e40af; }
        .badge-accepted { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-expired  { background: #fef3c7; color: #92400e; }

        /* ── Notes ── */
        .notes-box {
            border-left: 3px solid #1a1a1a;
            padding: 6px 12px;
            margin-bottom: 18px;
            font-size: 10px;
            color: #444;
            background: #fafafa;
        }

        /* ── Section heading (matches style of "Unsere Räumlichkeiten" in existing PDFs) ── */
        .section-heading {
            font-size: 13px;
            font-weight: bold;
            color: #1a1a1a;
            text-align: center;
            margin: 18px 0 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #ddd;
        }

        /* ── Venue block ── */
        .venue-block {
            border: 1px solid #e0e0e0;
            padding: 10px 16px;
            margin-bottom: 18px;
        }
        .venue-block-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #999;
            margin-bottom: 8px;
        }
        .venue-row { width: 100%; border-collapse: collapse; }
        .venue-row td { font-size: 10.5px; color: #333; padding: 3px 0; }
        .venue-row .v-label  { color: #999; width: 130px; }
        .venue-row .v-amount { text-align: right; }
        .venue-row .v-strike { text-decoration: line-through; color: #bbb; }
        .venue-row .v-discount { color: #cc2200; }

        /* ── Group / items table ── */
        .group { margin-bottom: 16px; page-break-inside: avoid; }
        .group-title {
            background: #1a1a1a;
            color: #fff;
            padding: 5px 10px;
            font-size: 9.5px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table thead tr { background: #f5f5f5; }
        .items-table th {
            text-align: left;
            padding: 5px 8px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #777;
            border-bottom: 1px solid #ddd;
        }
        .items-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
            font-size: 10px;
            color: #333;
        }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        .qty { font-weight: bold; }
        .line-discount-label { font-size: 9px; color: #cc2200; }
        .subtotal-row td {
            background: #f0f0f0 !important;
            font-weight: bold;
            font-size: 10px;
            border-top: 1px solid #ddd;
        }

        /* ── Informational services section ── */
        .section-heading-info {
            font-size: 13px;
            font-weight: bold;
            color: #666;
            text-align: center;
            margin: 22px 0 4px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #bbb;
        }
        .info-notice {
            text-align: center;
            font-size: 8.5px;
            color: #999;
            font-style: italic;
            margin-bottom: 10px;
            letter-spacing: 0.3px;
        }
        .group-title-info {
            background: #e8e8e8;
            color: #555;
            padding: 5px 10px;
            font-size: 9.5px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-left: 3px solid #aaa;
        }
        .items-table .info-row td { color: #666; font-style: italic; background: #fafafa; }
        .subtotal-row-info td {
            background: #f5f5f5 !important;
            font-weight: bold;
            font-size: 10px;
            color: #777;
            border-top: 1px dashed #ccc;
            font-style: italic;
        }

        /* ── Cost per person box ── */
        .cost-per-person-box {
            margin-top: 20px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            padding: 12px 16px;
            text-align: center;
        }
        .cpp-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #3b82f6;
            margin-bottom: 4px;
        }
        .cpp-amount {
            font-size: 22px;
            font-weight: 700;
            color: #1d4ed8;
            line-height: 1;
        }
        .cpp-note {
            font-size: 8.5px;
            color: #93c5fd;
            margin-top: 4px;
            font-style: italic;
        }

        /* ── Pricing page informational block ── */
        .info-pricing-block {
            margin-top: 20px;
            border-top: 1px dashed #ccc;
            padding-top: 14px;
        }
        .info-pricing-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #aaa;
            margin-bottom: 10px;
            text-align: center;
            font-style: italic;
        }
        .pricing-table .p-info { color: #aaa; font-style: italic; }
        .pricing-table .p-info-total td { color: #888; font-style: italic; font-weight: 600; border-top: 1px dashed #ccc; padding-top: 8px; }

        /* ══════════════════════════════════════════
           Informational services — own page
        ══════════════════════════════════════════ */
        .page-info {
            page-break-before: always;
            padding: 28px 40px 95px;
        }

        /* ══════════════════════════════════════════
           PAGE 2 — Pricing summary
        ══════════════════════════════════════════ */
        .page2 {
            page-break-before: always;
            padding: 28px 40px 95px;
        }

        /* ── Pricing summary (centered, clean) ── */
        .pricing-wrapper {
            max-width: 420px;
            margin: 40px auto 0;
        }
        .pricing-section-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #999;
            margin-bottom: 16px;
            text-align: center;
        }
        .pricing-table { width: 100%; border-collapse: collapse; }
        .pricing-table td { padding: 6px 0; font-size: 12.5px; color: #333; }
        .pricing-table .p-label  { padding-right: 20px; }
        .pricing-table .p-amount { text-align: right; white-space: nowrap; }
        .pricing-table .p-muted  { color: #999; font-size: 11px; }
        .pricing-table .p-discount { color: #cc2200; }
        .pricing-table .p-divider td { border-top: 1px solid #ddd; padding-top: 10px; }
        .pricing-table .p-bold td { font-weight: 600; color: #1a1a1a; }
        .pricing-table .p-grand td {
            border-top: 2px solid #1a1a1a;
            padding-top: 12px;
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
        }

        /* ── Bank / payment bar (sits above the dark footer) ── */
        .bank-bar {
            position: fixed;
            bottom: 46px;
            left: 0;
            right: 0;
            background: #fff;
            border-top: 1px solid #e0e0e0;
            border-bottom: 2px solid #1a1a1a;
            padding: 6px 36px 7px;
            text-align: center;
        }
        .bank-bar-label {
            display: block;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #1a1a1a;
            margin-bottom: 3px;
        }
        .bank-bar-info {
            display: block;
            font-size: 9.5px;
            font-weight: 600;
            color: #1a1a1a;
            letter-spacing: 0.3px;
        }

        /* ── Dark footer bar (matches existing PDFs) ── */
        .footer-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1a1a1a;
            color: #ccc;
            padding: 10px 36px;
            font-size: 8.5px;
            line-height: 1.6;
        }
        .footer-inner { width: 100%; border-collapse: collapse; }
        .footer-inner td { vertical-align: middle; }
        .footer-left  { color: #ccc; font-size: 8.5px; }
        .footer-right {
            text-align: right;
            font-size: 13px;
            font-weight: bold;
            color: #fff;
            letter-spacing: 1px;
        }
        .footer-right .footer-sub {
            display: block;
            font-size: 7.5px;
            font-weight: normal;
            letter-spacing: 2px;
            color: #aaa;
            margin-top: 1px;
        }

        /* ── Terms & Conditions page ── */
        .terms-page {
            page-break-before: always;
            padding: 28px 40px 95px;
        }
        .terms-heading {
            font-size: 16px;
            font-weight: bold;
            color: #1a1a1a;
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1a1a1a;
        }
        .terms-deposit-highlight {
            background: #fff8e1;
            border: 1px solid #f59e0b;
            border-left: 4px solid #d97706;
            padding: 10px 14px;
            margin-bottom: 20px;
            font-size: 11px;
            font-weight: bold;
            color: #92400e;
            text-align: center;
        }
        .terms-section {
            margin-bottom: 13px;
        }
        .terms-section-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            color: #fff;
            background: #1a1a1a;
            padding: 4px 8px;
            margin-bottom: 0;
        }
        .terms-section-body {
            font-size: 9.5px;
            color: #444;
            line-height: 1.65;
            padding: 7px 10px;
            border: 1px solid #e8e8e8;
            border-top: none;
            background: #fafafa;
        }
        .terms-signature {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 18px;
        }
        .terms-sig-table { width: 100%; border-collapse: collapse; }
        .terms-sig-table td { width: 50%; padding: 0 16px; vertical-align: bottom; }
        .terms-sig-table td:first-child { padding-left: 0; }
        .terms-sig-table td:last-child  { padding-right: 0; }
        .sig-line {
            border-bottom: 1px solid #1a1a1a;
            height: 40px;
            margin-bottom: 5px;
        }
        .sig-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #999;
        }
    </style>
    @if($preview ?? false)
    <style>
        body { background: #cbd5e1 !important; }
        .preview-page-wrap {
            max-width: 794px;
            margin: 24px auto 48px;
            background: #fff;
            box-shadow: 0 4px 32px rgba(0,0,0,0.18);
        }
    </style>
    @endif
</head>
<body>

@php
// ── Translations ──────────────────────────────────────────────────────────
$lang = $language ?? 'en';

$translations = [
    'en' => [
        'quote_for'           => 'Quote For',
        'issued_by'           => 'Issued By',
        'status'              => 'Status',
        'event_date'          => 'Event Date',
        'time'                => 'Time',
        'valid_until'         => 'Valid Until',
        'created'             => 'Created',
        'notes_label'         => 'Notes',
        'status_draft'        => 'Draft',
        'status_sent'         => 'Sent',
        'status_accepted'     => 'Accepted',
        'status_rejected'     => 'Rejected',
        'status_expired'      => 'Expired',
        'venue_rental'        => 'Venue Rental',
        'venue'               => 'Venue',
        'duration'            => 'Duration',
        'hours_unit'          => 'hours',
        'hourly_rate'         => 'Hourly Rate',
        'gross_amount'        => 'Gross Amount',
        'discount'            => 'Discount',
        'venue_net'           => 'Venue Net',
        'additional_services' => 'Additional Services',
        'col_description'     => 'Description',
        'col_qty'             => 'Qty',
        'col_unit'            => 'Unit',
        'col_unit_price'      => 'Unit Price',
        'col_discount'        => 'Discount',
        'col_net_total'       => 'Net Total',
        'col_note'              => 'Note',
        'price_summary'         => 'Price Summary',
        'cost_breakdown'        => 'Cost Breakdown',
        'venue_net_label'       => 'Venue rental (net)',
        'services_net'          => 'Services (net)',
        'netto'                 => 'Net (subtotal)',
        'discount_pct'          => 'Discount ({value}%)',
        'discount_fixed'        => 'Discount (fixed)',
        'net_after_discount'    => 'Net after discount',
        'vat'                   => 'VAT ({rate}%)',
        'brutto'                => 'Total (gross)',
        'valid_until_footer'    => 'Valid until',
        'informational_services'=> 'Optional Services',
        'info_notice'           => 'These services are not included in your quote. If you\'d like to add any of them, simply let us know.',
        'info_total_label'      => 'Informational total (not charged)',
        'attendee_count'        => 'Attendees',
        'cost_per_person'       => 'Cost per person',
        'cost_per_person_note'  => 'Based on total gross price',
        'bank_details'               => 'Payment Details',
        'terms_title'                => 'Terms & Conditions',
        'terms_deposit_highlight'    => 'Upon acceptance of this quote, a deposit of 50% of the total gross amount is required to confirm your booking.',
        'terms_booking_title'        => 'Booking Confirmation',
        'terms_booking_body'         => 'This quote is valid until the date stated above. Acceptance of this quote constitutes a binding booking agreement between the client and SK Eventspace GmbH. The booking is confirmed only upon receipt of the required deposit payment.',
        'terms_payment_title'        => 'Payment Terms',
        'terms_payment_body'         => '50% of the total gross amount is due upon acceptance of this quote as a booking deposit. The remaining 50% must be paid no later than 14 days before the event date. Payments are to be made by bank transfer to the account details provided.',
        'terms_cancellation_title'   => 'Cancellation Policy',
        'terms_cancellation_body'    => 'Cancellations must be submitted in writing. The following cancellation fees apply:<br>&bull; Cancellation more than 30 days before the event: deposit is refundable minus a 10% administration fee.<br>&bull; Cancellation 14&ndash;30 days before the event: 50% of the total gross amount is due.<br>&bull; Cancellation less than 14 days before the event: 100% of the total gross amount is due.<br>SK Eventspace GmbH reserves the right to cancel the event in cases of force majeure without liability.',
        'terms_force_majeure_title'  => 'Force Majeure',
        'terms_force_majeure_body'   => 'Neither party shall be liable for failure to perform its obligations where such failure is caused by circumstances beyond its reasonable control, including but not limited to natural disasters, pandemics, government-imposed restrictions, or strikes. In such cases, both parties shall make reasonable efforts to reschedule the event.',
        'terms_governing_law_title'  => 'Governing Law & Jurisdiction',
        'terms_governing_law_body'   => 'This agreement is governed by the laws of the Federal Republic of Germany. The place of jurisdiction is Berlin.',
        'terms_sig_customer'         => 'Client — Date & Signature',
        'terms_sig_company'          => 'SK Eventspace GmbH — Date & Signature',
    ],
    'de' => [
        'quote_for'           => 'Angebot für',
        'issued_by'           => 'Aussteller',
        'status'              => 'Status',
        'event_date'          => 'Veranstaltungsdatum',
        'time'                => 'Uhrzeit',
        'valid_until'         => 'Gültig bis',
        'created'             => 'Erstellt',
        'notes_label'         => 'Anmerkungen',
        'status_draft'        => 'Entwurf',
        'status_sent'         => 'Versendet',
        'status_accepted'     => 'Akzeptiert',
        'status_rejected'     => 'Abgelehnt',
        'status_expired'      => 'Abgelaufen',
        'venue_rental'        => 'Raummiete',
        'venue'               => 'Veranstaltungsort',
        'duration'            => 'Dauer',
        'hours_unit'          => 'Std.',
        'hourly_rate'         => 'Stundenpreis',
        'gross_amount'        => 'Bruttobetrag',
        'discount'            => 'Rabatt',
        'venue_net'           => 'Raum Netto',
        'additional_services' => 'Zusätzliche Leistungen',
        'col_description'     => 'Beschreibung',
        'col_qty'             => 'Menge',
        'col_unit'            => 'Einheit',
        'col_unit_price'      => 'Einzelpreis',
        'col_discount'        => 'Rabatt',
        'col_net_total'       => 'Netto gesamt',
        'col_note'              => 'Anmerkung',
        'price_summary'         => 'Preisübersicht',
        'cost_breakdown'        => 'Kostenaufschlüsselung',
        'venue_net_label'       => 'Raummiete (netto)',
        'services_net'          => 'Leistungen (netto)',
        'netto'                 => 'Netto',
        'discount_pct'          => 'Rabatt ({value}%)',
        'discount_fixed'        => 'Rabatt (Festbetrag)',
        'net_after_discount'    => 'Netto nach Rabatt',
        'vat'                   => 'zzgl. MwSt. ({rate}%)',
        'brutto'                => 'Brutto',
        'valid_until_footer'    => 'Gültig bis',
        'informational_services'=> 'Optionale Leistungen',
        'info_notice'           => 'Diese Leistungen sind nicht im Angebot enthalten. Teilen Sie uns gerne mit, wenn Sie eine oder mehrere davon hinzufügen möchten.',
        'info_total_label'      => 'Informationssumme (nicht berechnet)',
        'attendee_count'        => 'Teilnehmer',
        'cost_per_person'       => 'Kosten pro Person',
        'cost_per_person_note'  => 'Basierend auf dem Bruttogesamtpreis',
        'bank_details'               => 'Bankverbindung',
        'terms_title'                => 'Allgemeine Geschäftsbedingungen',
        'terms_deposit_highlight'    => 'Mit Annahme dieses Angebots ist eine Anzahlung von 50 % des Gesamtbruttobetrags zur Bestätigung Ihrer Buchung fällig.',
        'terms_booking_title'        => 'Buchungsbestätigung',
        'terms_booking_body'         => 'Dieses Angebot ist gültig bis zum oben angegebenen Datum. Die Annahme dieses Angebots stellt eine verbindliche Buchungsvereinbarung zwischen dem Auftraggeber und der SK Eventspace GmbH dar. Die Buchung gilt erst als bestätigt, wenn die Anzahlung eingegangen ist.',
        'terms_payment_title'        => 'Zahlungsbedingungen',
        'terms_payment_body'         => 'Bei Annahme dieses Angebots sind 50 % des Gesamtbruttobetrags als Anzahlung fällig. Die verbleibenden 50 % sind spätestens 14 Tage vor dem Veranstaltungsdatum zu entrichten. Zahlungen erfolgen per Überweisung auf das angegebene Bankkonto.',
        'terms_cancellation_title'   => 'Stornierungsbedingungen',
        'terms_cancellation_body'    => 'Stornierungen müssen schriftlich erfolgen. Es gelten folgende Stornogebühren:<br>&bull; Stornierung mehr als 30 Tage vor der Veranstaltung: Anzahlung wird abzüglich einer Bearbeitungsgebühr von 10 % erstattet.<br>&bull; Stornierung 14&ndash;30 Tage vor der Veranstaltung: 50 % des Gesamtbruttobetrags sind fällig.<br>&bull; Stornierung weniger als 14 Tage vor der Veranstaltung: 100 % des Gesamtbruttobetrags sind fällig.<br>Die SK Eventspace GmbH behält sich das Recht vor, die Veranstaltung in Fällen höherer Gewalt ohne Haftung abzusagen.',
        'terms_force_majeure_title'  => 'Höhere Gewalt',
        'terms_force_majeure_body'   => 'Keine der Parteien haftet für die Nichterfüllung ihrer Verpflichtungen, sofern diese durch Umstände verursacht wird, die außerhalb ihrer zumutbaren Kontrolle liegen, einschließlich Naturkatastrophen, Pandemien, behördliche Einschränkungen oder Streiks. In solchen Fällen werden beide Parteien angemessene Bemühungen unternehmen, die Veranstaltung zu verschieben.',
        'terms_governing_law_title'  => 'Anwendbares Recht & Gerichtsstand',
        'terms_governing_law_body'   => 'Diese Vereinbarung unterliegt dem Recht der Bundesrepublik Deutschland. Gerichtsstand ist Berlin.',
        'terms_sig_customer'         => 'Auftraggeber — Datum & Unterschrift',
        'terms_sig_company'          => 'SK Eventspace GmbH — Datum & Unterschrift',
    ],
    'tr' => [
        'quote_for'           => 'Müşteri',
        'issued_by'           => 'Düzenleyen',
        'status'              => 'Status',
        'event_date'          => 'Etkinlik Tarihi',
        'time'                => 'Saat',
        'valid_until'         => 'Geçerlilik Tarihi',
        'created'             => 'Oluşturma Tarihi',
        'notes_label'         => 'Notlar',
        'status_draft'        => 'Taslak',
        'status_sent'         => 'Gönderildi',
        'status_accepted'     => 'Kabul Edildi',
        'status_rejected'     => 'Reddedildi',
        'status_expired'      => 'Süresi Doldu',
        'venue_rental'        => 'Mekan Kiralama',
        'venue'               => 'Mekan',
        'duration'            => 'Süre',
        'hours_unit'          => 'saat',
        'hourly_rate'         => 'Saatlik Ücret',
        'gross_amount'        => 'Brüt Tutar',
        'discount'            => 'İndirim',
        'venue_net'           => 'Mekan Net',
        'additional_services' => 'Ek Hizmetler',
        'col_description'     => 'Açıklama',
        'col_qty'             => 'Adet',
        'col_unit'            => 'Birim',
        'col_unit_price'      => 'Birim Fiyat',
        'col_discount'        => 'İndirim',
        'col_net_total'       => 'Net Toplam',
        'col_note'              => 'Not',
        'price_summary'         => 'Fiyat Özeti',
        'cost_breakdown'        => 'Maliyet Dökümü',
        'venue_net_label'       => 'Mekan Kiralama (net)',
        'services_net'          => 'Hizmetler (net)',
        'netto'                 => 'Net (ara toplam)',
        'discount_pct'          => 'Indirim ({value}%)',
        'discount_fixed'        => 'Indirim (sabit)',
        'net_after_discount'    => 'Indirim Sonrasi Net',
        'vat'                   => 'KDV ({rate}%)',
        'brutto'                => 'Brut Toplam',
        'valid_until_footer'    => 'Gecerlilik',
        'informational_services'=> 'İsteğe Bağlı Hizmetler',
        'info_notice'           => 'Bu hizmetler teklife dahil değildir. Herhangi birini eklemek isterseniz lütfen bize bildirin.',
        'info_total_label'      => 'Bilgi toplamı (ücretlendirilmez)',
        'attendee_count'        => 'Katılımcı',
        'cost_per_person'       => 'Kişi başı maliyet',
        'cost_per_person_note'  => 'Brüt toplam fiyata göre',
        'bank_details'               => 'Ödeme Bilgileri',
        'terms_title'                => 'Genel Hükümler ve Koşullar',
        'terms_deposit_highlight'    => 'Bu teklifin kabul edilmesiyle birlikte, rezervasyonunuzu onaylamak için toplam brüt tutarın %50\'si oranında ön ödeme yapılması gerekmektedir.',
        'terms_booking_title'        => 'Rezervasyon Onayı',
        'terms_booking_body'         => 'Bu teklif, yukarıda belirtilen tarihe kadar geçerlidir. Bu teklifin kabul edilmesi, müşteri ile SK Eventspace GmbH arasında bağlayıcı bir rezervasyon sözleşmesi oluşturur. Rezervasyon, yalnızca gerekli ön ödemenin alınmasıyla onaylanmış sayılır.',
        'terms_payment_title'        => 'Ödeme Koşulları',
        'terms_payment_body'         => 'Bu teklifin kabul edilmesiyle birlikte toplam brüt tutarın %50\'si ön ödeme olarak ödenir. Kalan %50, etkinlik tarihinden en geç 14 gün önce ödenmelidir. Ödemeler, belirtilen banka hesabına havale yoluyla yapılır.',
        'terms_cancellation_title'   => 'İptal Politikası',
        'terms_cancellation_body'    => 'İptaller yazılı olarak iletilmelidir. Aşağıdaki iptal koşulları geçerlidir:<br>&bull; Etkinlikten 30 günden fazla önce iptal: Depozito, %10 idari ücret düşülerek iade edilir.<br>&bull; Etkinlikten 14&ndash;30 gün önce iptal: Toplam brüt tutarın %50\'si tahsil edilir.<br>&bull; Etkinlikten 14 günden az önce iptal: Toplam brüt tutarın %100\'ü tahsil edilir.<br>SK Eventspace GmbH, mücbir sebep hallerinde etkinliği sorumluluk almaksızın iptal etme hakkını saklı tutar.',
        'terms_force_majeure_title'  => 'Mücbir Sebep',
        'terms_force_majeure_body'   => 'Hiçbir taraf; doğal afetler, salgın hastalıklar, hükümet tarafından getirilen kısıtlamalar veya grevler dahil, makul kontrolü dışındaki koşullar nedeniyle yükümlülüklerini yerine getirememesinden sorumlu tutulamaz. Bu gibi durumlarda her iki taraf da etkinliği yeniden planlamak için makul çabayı gösterecektir.',
        'terms_governing_law_title'  => 'Uygulanacak Hukuk ve Yargı Yetkisi',
        'terms_governing_law_body'   => 'Bu sözleşme, Almanya Federal Cumhuriyeti hukuku kapsamındadır. Yetkili mahkeme Berlin\'dir.',
        'terms_sig_customer'         => 'Müşteri — Tarih ve İmza',
        'terms_sig_company'          => 'SK Eventspace GmbH — Tarih ve İmza',
    ],
];

$t = $translations[$lang] ?? $translations['en'];

$statusLabel = match($quote->status) {
    'draft'    => $t['status_draft'],
    'sent'     => $t['status_sent'],
    'accepted' => $t['status_accepted'],
    'rejected' => $t['status_rejected'],
    'expired'  => $t['status_expired'],
    default    => $quote->status,
};

$venueGross    = round((float)$quote->hours * (float)$quote->hourly_rate, 2);
$venueDiscount = \App\Models\Quote::calcDiscount(
    $venueGross,
    $quote->venue_discount_type,
    (float)($quote->venue_discount_value ?? 0)
);
$venueNet = round($venueGross - $venueDiscount, 2);

$firstName = $quote->customer?->first_name ?? $quote->customer_first_name;
$lastName  = $quote->customer?->last_name  ?? $quote->customer_last_name;
$company   = $quote->customer?->company_name ?? $quote->customer_company;
$email     = $quote->customer?->email  ?? $quote->customer_email;
$phone     = $quote->customer?->phone  ?? $quote->customer_phone;
@endphp

@if($preview ?? false)
<div style="
    position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
    background: #1d4ed8; color: #fff;
    padding: 10px 20px;
    display: flex; align-items: center; justify-content: space-between;
    font-family: system-ui, sans-serif; font-size: 13px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.25);
">
    <span>
        <strong>Draft Preview</strong>
        &nbsp;—&nbsp; This is an HTML preview. The final PDF will include the cover and back pages.
    </span>
    <span style="display:flex; gap:12px; align-items:center;">
        <a href="?show_prices=1&language={{ request('language','en') }}" style="color:#bfdbfe; text-decoration:none; font-size:12px;">Show prices</a>
        <a href="?show_prices=0&language={{ request('language','en') }}" style="color:#bfdbfe; text-decoration:none; font-size:12px;">Hide prices</a>
        <a href="?language=en&show_prices={{ request('show_prices',1) }}" style="color:#bfdbfe; text-decoration:none; font-size:12px;">EN</a>
        <a href="?language=de&show_prices={{ request('show_prices',1) }}" style="color:#bfdbfe; text-decoration:none; font-size:12px;">DE</a>
        <a href="?language=tr&show_prices={{ request('show_prices',1) }}" style="color:#bfdbfe; text-decoration:none; font-size:12px;">TR</a>
    </span>
</div>
<div style="height: 44px;"></div>
@endif

{{-- ── Fixed dark footer (PDF only — hidden in browser preview) ── --}}
@if(!($preview ?? false))
<div class="footer-bar">
    <table class="footer-inner">
        <tr>
            <td class="footer-left">
                SK Eventspace GmbH &nbsp;·&nbsp; Zimmerstr. 26/27 &nbsp;·&nbsp; 10969 Berlin<br>
                info@event-hub-checkpoint.de &nbsp;·&nbsp; www.event-hub-checkpoint.de
            </td>
            <td class="footer-right">
                EVENT HUB
                <span class="footer-sub">CHECKPOINT CHARLIE</span>
            </td>
        </tr>
    </table>
</div>

{{-- ── Bank bar (fixed, white, above dark footer) ── --}}
<div class="bank-bar">
    <span class="bank-bar-label">Bankverbindung</span>
    <span class="bank-bar-info">SK Eventspace GmbH &nbsp;·&nbsp; Berliner Volksbank &nbsp;·&nbsp; IBAN: DE97 1009 0000 3093 5380 04 &nbsp;·&nbsp; BIC: BEVODEBBXXX</span>
</div>
@endif

{{-- ══════════════════════════════════════════
     PAGE 1 — Quote details & service items
══════════════════════════════════════════ --}}

@if($preview ?? false)<div class="preview-page-wrap">@endif
{{-- Top address bar --}}
<div class="top-bar">
    SK Eventspace GmbH &nbsp;·&nbsp; Zimmerstr. 26/27 &nbsp;·&nbsp; 10969 Berlin &nbsp;·&nbsp; info@event-hub-checkpoint.de
</div>

<div class="page">

    {{-- Document heading --}}
    <div class="doc-heading">
        <div class="doc-label">{{ $t['price_summary'] }}</div>
        <div class="doc-title">{{ $lang === 'de' ? 'Angebot' : ($lang === 'tr' ? 'Teklif' : 'Quote') }} {{ $quote->quote_number }}</div>
        @if($quote->business)
            <div class="doc-subtitle">{{ $quote->business->name }}</div>
        @endif
    </div>

    {{-- Meta: status / event date / time / valid until / created --}}
    <table class="meta-row">
        <tr>
            <td>
                <div class="m-label">{{ $t['status'] }}</div>
                <div class="m-value"><span class="badge badge-{{ $quote->status }}">{{ $statusLabel }}</span></div>
            </td>
            @if($quote->event_date)
            <td>
                <div class="m-label">{{ $t['event_date'] }}</div>
                <div class="m-value">{{ $quote->event_date->format('d.m.Y') }}</div>
            </td>
            @endif
            @if($quote->event_start_time && $quote->event_end_time)
            <td>
                <div class="m-label">{{ $t['time'] }}</div>
                <div class="m-value">
                    {{ \Carbon\Carbon::parse($quote->event_start_time)->format('H:i') }}
                    –
                    {{ \Carbon\Carbon::parse($quote->event_end_time)->format('H:i') }}
                    @if($quote->hours)
                        ({{ rtrim(rtrim(number_format((float)$quote->hours, 2, ',', '.'), '0'), ',') }} {{ $t['hours_unit'] }})
                    @endif
                </div>
            </td>
            @endif
            @if($quote->valid_until)
            <td>
                <div class="m-label">{{ $t['valid_until'] }}</div>
                <div class="m-value">{{ $quote->valid_until->format('d.m.Y') }}</div>
            </td>
            @endif
            @if($quote->attendee_count)
            <td>
                <div class="m-label">{{ $t['attendee_count'] }}</div>
                <div class="m-value">{{ number_format($quote->attendee_count, 0, ',', '.') }}</div>
            </td>
            @endif
            <td>
                <div class="m-label">{{ $t['created'] }}</div>
                <div class="m-value">{{ now()->format('d.m.Y') }}</div>
            </td>
        </tr>
    </table>

    {{-- Customer / Issuer --}}
    <table class="info-table">
        <tr>
            <td>
                <div class="info-label">{{ $t['quote_for'] }}</div>
                <div class="info-value">
                    @if($company)
                        <strong>{{ $company }}</strong><br>
                        @if($firstName || $lastName) {{ trim("$firstName $lastName") }}<br> @endif
                    @elseif($firstName || $lastName)
                        <strong>{{ trim("$firstName $lastName") }}</strong><br>
                    @else
                        —
                    @endif
                    @if($email) {{ $email }}<br> @endif
                    @if($phone) {{ $phone }}     @endif
                </div>
            </td>
            <td>
                <div class="info-label">{{ $t['issued_by'] }}</div>
                <div class="info-value">
                    <strong>Enver Sanli</strong><br>
                    SK Eventspace GmbH<br>
                    e.sanli@event-hub-checkpoint.de<br>
                    +49 163 951 8970
                </div>
            </td>
        </tr>
    </table>

    {{-- Notes --}}
    @if($quote->notes)
        <div class="notes-box"><strong>{{ $t['notes_label'] }}:</strong> {{ $quote->notes }}</div>
    @endif

    {{-- Venue rental --}}
    @if($quote->hourly_rate || $quote->hours || $quote->business)
        <div class="section-heading">{{ $t['venue_rental'] }}</div>
        <div class="venue-block">
            <table class="venue-row">
                @if($quote->business)
                    <tr>
                        <td class="v-label">{{ $t['venue'] }}</td>
                        <td>{{ $quote->business->name }}</td>
                        <td class="v-amount"></td>
                    </tr>
                @endif
                @if($quote->hours)
                    <tr>
                        <td class="v-label">{{ $t['duration'] }}</td>
                        <td>{{ rtrim(rtrim(number_format((float)$quote->hours, 2, ',', '.'), '0'), ',') }} {{ $t['hours_unit'] }}</td>
                        <td class="v-amount"></td>
                    </tr>
                @endif
                @if($showPrices && $quote->hourly_rate)
                    <tr>
                        <td class="v-label">{{ $t['hourly_rate'] }}</td>
                        <td>{{ number_format((float)$quote->hourly_rate, 2, ',', '.') }} €</td>
                        <td class="v-amount"></td>
                    </tr>
                    <tr>
                        <td class="v-label">{{ $t['gross_amount'] }}</td>
                        <td></td>
                        <td class="v-amount {{ $venueDiscount > 0 ? 'v-strike' : '' }}">
                            {{ number_format($venueGross, 2, ',', '.') }} €
                        </td>
                    </tr>
                    @if($venueDiscount > 0)
                        <tr>
                            <td class="v-label v-discount">
                                {{ $t['discount'] }}
                                @if($quote->venue_discount_type === 'percentage')
                                    ({{ rtrim(rtrim(number_format((float)$quote->venue_discount_value, 2, ',', '.'), '0'), ',') }}%)
                                @endif
                            </td>
                            <td></td>
                            <td class="v-amount v-discount">− {{ number_format($venueDiscount, 2, ',', '.') }} €</td>
                        </tr>
                        <tr>
                            <td class="v-label" style="font-weight:600;">{{ $t['venue_net'] }}</td>
                            <td></td>
                            <td class="v-amount" style="font-weight:600;">{{ number_format($venueNet, 2, ',', '.') }} €</td>
                        </tr>
                    @endif
                @endif
            </table>
        </div>
    @endif

    {{-- Quote lines: split included vs informational --}}
    @php
        $includedLines      = $quote->quoteLines->filter(fn ($l) => (bool) $l->include_in_total);
        $informationalLines = $quote->quoteLines->filter(
            fn ($l) => ! (bool) $l->include_in_total
                && ($l->description || $l->orderItem || (float) $l->unit_price > 0)
        );
    @endphp

    {{-- ── Included lines ── --}}
    @if($includedLines->isNotEmpty())
        @php
            $grouped = $includedLines->groupBy(function ($line) use ($t) {
                return $line->orderItem?->itemGroup?->name ?? $t['additional_services'];
            })->sortKeys();
        @endphp

        <div class="section-heading">{{ $t['additional_services'] }}</div>

        @foreach($grouped as $groupName => $lines)
            @php
                $groupDiscount = $lines->sum(fn ($l) => $l->line_discount);
                $groupNet      = $lines->sum(fn ($l) => $l->line_total);
            @endphp

            <div class="group">
                <div class="group-title">{{ $groupName }}</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width:38%">{{ $t['col_description'] }}</th>
                            <th style="width:7%">{{ $t['col_qty'] }}</th>
                            <th style="width:8%">{{ $t['col_unit'] }}</th>
                            @if($showPrices)
                                <th class="text-right" style="width:13%">{{ $t['col_unit_price'] }}</th>
                                <th class="text-right" style="width:10%">{{ $t['col_discount'] }}</th>
                                <th class="text-right" style="width:12%">{{ $t['col_net_total'] }}</th>
                            @endif
                            <th>{{ $t['col_note'] }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lines as $line)
                            @php
                                $lineDiscount = $line->line_discount;
                                $lineNet      = $line->line_total;
                            @endphp
                            <tr>
                                <td>{{ $line->description ?: ($line->orderItem?->name ?? '—') }}</td>
                                <td class="qty">{{ rtrim(rtrim(number_format((float)$line->quantity, 2, ',', '.'), '0'), ',') }}</td>
                                <td>{{ $line->unit ?? $line->orderItem?->unit ?? '—' }}</td>
                                @if($showPrices)
                                    <td class="text-right">{{ number_format((float)$line->unit_price, 2, ',', '.') }} €</td>
                                    <td class="text-right">
                                        @if($lineDiscount > 0)
                                            <span class="line-discount-label">
                                                @if($line->discount_type === 'percentage')
                                                    {{ rtrim(rtrim(number_format((float)$line->discount_value, 2, ',', '.'), '0'), ',') }}%
                                                @else
                                                    − {{ number_format($lineDiscount, 2, ',', '.') }} €
                                                @endif
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-right">{{ number_format($lineNet, 2, ',', '.') }} €</td>
                                @endif
                                <td>{{ $line->notes ?? '' }}</td>
                            </tr>
                        @endforeach

                        @if($showPrices)
                            <tr class="subtotal-row">
                                <td colspan="{{ $showPrices ? 4 : 2 }}"></td>
                                @if($groupDiscount > 0)
                                    <td class="text-right" style="color:#cc2200;">
                                        − {{ number_format($groupDiscount, 2, ',', '.') }} €
                                    </td>
                                @else
                                    <td></td>
                                @endif
                                <td class="text-right">{{ number_format($groupNet, 2, ',', '.') }} €</td>
                                <td></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

</div>{{-- end .page --}}

{{-- Informational services shown on their own page only when prices are hidden
     (when prices are shown they appear merged into the pricing page below) --}}
@if(!$showPrices && $informationalLines->isNotEmpty())
    @php
        $infoGrouped = $informationalLines->groupBy(function ($line) use ($t) {
            return $line->orderItem?->itemGroup?->name ?? $t['informational_services'];
        })->sortKeys();
    @endphp

    <div class="page-info">

        <div class="top-bar" style="margin: -28px -40px 28px; padding: 6px 40px;">
            SK Eventspace GmbH &nbsp;·&nbsp; Zimmerstr. 26/27 &nbsp;·&nbsp; 10969 Berlin &nbsp;·&nbsp; info@event-hub-checkpoint.de
        </div>

        <div class="section-heading-info">{{ $t['informational_services'] }}</div>
        <div class="info-notice">{{ $t['info_notice'] }}</div>

        @foreach($infoGrouped as $groupName => $lines)
            <div class="group">
                <div class="group-title-info">{{ $groupName }}</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width:38%">{{ $t['col_description'] }}</th>
                            <th style="width:7%">{{ $t['col_qty'] }}</th>
                            <th style="width:8%">{{ $t['col_unit'] }}</th>
                            <th>{{ $t['col_note'] }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lines as $line)
                            <tr class="info-row">
                                <td>{{ $line->description ?: ($line->orderItem?->name ?? '—') }}</td>
                                <td class="qty">{{ rtrim(rtrim(number_format((float)$line->quantity, 2, ',', '.'), '0'), ',') }}</td>
                                <td>{{ $line->unit ?? $line->orderItem?->unit ?? '—' }}</td>
                                <td>{{ $line->notes ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

    </div>{{-- end .page-info --}}
@endif


{{-- ══════════════════════════════════════════
     PAGE 2 — Pricing summary
══════════════════════════════════════════ --}}
@if($showPrices)
    @php
        $subtotalNet    = $quote->subtotal_net;
        $discountAmount = $quote->discount_amount;
        $netAfterDisc   = $quote->net_after_discount;
        $vatRate        = (float)($quote->vat_rate ?? 19);
        $vatAmount      = $quote->vat_amount;
        $totalGross     = $quote->total_gross;

        // Only included lines contribute to the totals
        $linesNet       = $quote->quoteLines->filter(fn ($l) => (bool) $l->include_in_total)->sum(fn ($l) => $l->line_total);

        // Informational lines — shown separately, not in total
        $infoLines      = $quote->quoteLines->filter(fn ($l) => ! (bool) $l->include_in_total);
        $infoNet        = $infoLines->sum(fn ($l) => $l->line_total);

        $vatRateFmt = rtrim(rtrim(number_format($vatRate, 2, ',', '.'), '0'), ',');
        $vatLabel   = str_replace('{rate}', $vatRateFmt, $t['vat']);

        if ($quote->discount_type === 'percentage') {
            $discValue = rtrim(rtrim(number_format((float)$quote->discount_value, 2, ',', '.'), '0'), ',');
            $discLabel = str_replace('{value}', $discValue, $t['discount_pct']);
        } else {
            $discLabel = $t['discount_fixed'];
        }
    @endphp

    <div class="page2">

        {{-- Top address bar --}}
        <div class="top-bar" style="margin: -28px -40px 28px; padding: 6px 40px;">
            SK Eventspace GmbH &nbsp;·&nbsp; Zimmerstr. 26/27 &nbsp;·&nbsp; 10969 Berlin &nbsp;·&nbsp; info@event-hub-checkpoint.de
        </div>

        {{-- Informational services merged onto this page --}}
        @if($informationalLines->isNotEmpty())
            @php
                $infoGrouped = $informationalLines->groupBy(function ($line) use ($t) {
                    return $line->orderItem?->itemGroup?->name ?? $t['informational_services'];
                })->sortKeys();
            @endphp

            <div class="section-heading-info">{{ $t['informational_services'] }}</div>
            <div class="info-notice">{{ $t['info_notice'] }}</div>

            @foreach($infoGrouped as $groupName => $lines)
                <div class="group">
                    <div class="group-title-info">{{ $groupName }}</div>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width:38%">{{ $t['col_description'] }}</th>
                                <th style="width:7%">{{ $t['col_qty'] }}</th>
                                <th style="width:8%">{{ $t['col_unit'] }}</th>
                                <th class="text-right" style="width:13%">{{ $t['col_unit_price'] }}</th>
                                <th class="text-right" style="width:10%">{{ $t['col_discount'] }}</th>
                                <th class="text-right" style="width:12%">{{ $t['col_net_total'] }}</th>
                                <th>{{ $t['col_note'] }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lines as $line)
                                @php
                                    $lineDiscount = $line->line_discount;
                                    $lineNet      = $line->line_total;
                                @endphp
                                <tr class="info-row">
                                    <td>{{ $line->description ?: ($line->orderItem?->name ?? '—') }}</td>
                                    <td class="qty">{{ rtrim(rtrim(number_format((float)$line->quantity, 2, ',', '.'), '0'), ',') }}</td>
                                    <td>{{ $line->unit ?? $line->orderItem?->unit ?? '—' }}</td>
                                    <td class="text-right">{{ number_format((float)$line->unit_price, 2, ',', '.') }} €</td>
                                    <td class="text-right">
                                        @if($lineDiscount > 0)
                                            <span class="line-discount-label">
                                                @if($line->discount_type === 'percentage')
                                                    {{ rtrim(rtrim(number_format((float)$line->discount_value, 2, ',', '.'), '0'), ',') }}%
                                                @else
                                                    − {{ number_format($lineDiscount, 2, ',', '.') }} €
                                                @endif
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-right">{{ number_format($lineNet, 2, ',', '.') }} €</td>
                                    <td>{{ $line->notes ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @endif

        {{-- Price summary heading --}}
        <div class="doc-heading" style="margin-top: 48px;">
            <div class="doc-label">{{ $quote->quote_number }}</div>
            <div class="doc-title">{{ $t['price_summary'] }}</div>
            @if($quote->business)
                <div class="doc-subtitle">{{ $quote->business->name }}</div>
            @endif
        </div>

        <div class="pricing-wrapper">
            <div class="pricing-section-label">{{ $t['cost_breakdown'] }}</div>

            <table class="pricing-table">

                @if($venueNet > 0)
                    <tr>
                        <td class="p-label p-muted">{{ $t['venue_net_label'] }}</td>
                        <td class="p-amount p-muted">{{ number_format($venueNet, 2, ',', '.') }} €</td>
                    </tr>
                @endif

                @if($linesNet > 0)
                    <tr>
                        <td class="p-label p-muted">{{ $t['services_net'] }}</td>
                        <td class="p-amount p-muted">{{ number_format($linesNet, 2, ',', '.') }} €</td>
                    </tr>
                @endif

                <tr class="p-divider p-bold">
                    <td class="p-label">{{ $t['netto'] }}</td>
                    <td class="p-amount">{{ number_format($subtotalNet, 2, ',', '.') }} €</td>
                </tr>

                @if($discountAmount > 0)
                    <tr>
                        <td class="p-label p-discount">{{ $discLabel }}</td>
                        <td class="p-amount p-discount">− {{ number_format($discountAmount, 2, ',', '.') }} €</td>
                    </tr>
                    <tr class="p-bold">
                        <td class="p-label">{{ $t['net_after_discount'] }}</td>
                        <td class="p-amount">{{ number_format($netAfterDisc, 2, ',', '.') }} €</td>
                    </tr>
                @endif

                <tr>
                    <td class="p-label p-muted">{{ $vatLabel }}</td>
                    <td class="p-amount p-muted">{{ number_format($vatAmount, 2, ',', '.') }} €</td>
                </tr>

                <tr class="p-grand">
                    <td class="p-label">{{ $t['brutto'] }}</td>
                    <td class="p-amount">{{ number_format($totalGross, 2, ',', '.') }} €</td>
                </tr>

            </table>

            @if(($showCostPerPerson ?? false) && ($quote->attendee_count ?? 0) > 0 && $totalGross > 0)
                @php $costPerPerson = round($totalGross / $quote->attendee_count, 2); @endphp
                <div class="cost-per-person-box">
                    <div class="cpp-label">{{ $t['cost_per_person'] }}</div>
                    <div class="cpp-amount">{{ number_format($costPerPerson, 2, ',', '.') }} €</div>
                    <div class="cpp-note">
                        {{ $t['cost_per_person_note'] }} · {{ number_format($quote->attendee_count, 0, ',', '.') }} {{ $t['attendee_count'] }}
                    </div>
                </div>
            @endif

        </div>

    </div>
@endif
{{-- ══════════════════════════════════════════
     TERMS & CONDITIONS PAGE
══════════════════════════════════════════ --}}
@if($preview ?? false)<div class="preview-page-wrap">@endif
<div class="terms-page">

    <div class="top-bar" style="margin: -28px -40px 28px; padding: 6px 40px;">
        SK Eventspace GmbH &nbsp;·&nbsp; Zimmerstr. 26/27 &nbsp;·&nbsp; 10969 Berlin &nbsp;·&nbsp; info@event-hub-checkpoint.de
    </div>

    <div class="terms-heading">{{ $t['terms_title'] }}</div>

    <div class="terms-deposit-highlight">{!! $t['terms_deposit_highlight'] !!}</div>

    <div class="terms-section">
        <div class="terms-section-title">{{ $t['terms_booking_title'] }}</div>
        <div class="terms-section-body">{!! $t['terms_booking_body'] !!}</div>
    </div>

    <div class="terms-section">
        <div class="terms-section-title">{{ $t['terms_payment_title'] }}</div>
        <div class="terms-section-body">{!! $t['terms_payment_body'] !!}</div>
    </div>

    <div class="terms-section">
        <div class="terms-section-title">{{ $t['terms_cancellation_title'] }}</div>
        <div class="terms-section-body">{!! $t['terms_cancellation_body'] !!}</div>
    </div>

    <div class="terms-section">
        <div class="terms-section-title">{{ $t['terms_force_majeure_title'] }}</div>
        <div class="terms-section-body">{!! $t['terms_force_majeure_body'] !!}</div>
    </div>

    <div class="terms-section">
        <div class="terms-section-title">{{ $t['terms_governing_law_title'] }}</div>
        <div class="terms-section-body">{!! $t['terms_governing_law_body'] !!}</div>
    </div>

    @if($showSignature ?? false)
    <div class="terms-signature">
        <table class="terms-sig-table">
            <tr>
                <td>
                    <div class="sig-line"></div>
                    <div class="sig-label">{{ $t['terms_sig_customer'] }}</div>
                </td>
                <td>
                    <div class="sig-line"></div>
                    <div class="sig-label">{{ $t['terms_sig_company'] }}</div>
                </td>
            </tr>
        </table>
    </div>
    @endif

</div>
@if($preview ?? false)</div>@endif

</body>
</html>
