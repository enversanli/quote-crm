<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Bestellung #{{ $order->id }} — {{ $order->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            background: #fff;
            padding: 0;
        }

        .top-bar {
            background: #111827;
            color: #fff;
            padding: 22px 40px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 0.5px;
            color: #f9fafb;
        }

        .company-address {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 3px;
        }

        .body {
            padding: 32px 40px 40px;
        }

        /* ── Document header ── */
        .doc-title {
            font-size: 22px;
            font-weight: bold;
            color: #111827;
        }

        .doc-subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }

        .doc-divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 16px 0 20px;
        }

        .meta-table {
            width: auto;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .meta-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .meta-label {
            color: #9ca3af;
            font-size: 11px;
            padding-right: 16px;
            width: 90px;
        }

        .meta-value {
            color: #374151;
            font-size: 11px;
        }

        /* ── Badge ── */
        .badge {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 9px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-draft     { background: #f3f4f6; color: #6b7280; }
        .badge-confirmed { background: #d1fae5; color: #065f46; }
        .badge-completed { background: #dbeafe; color: #1e40af; }

        /* ── Info two-column block ── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .info-table td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }

        .info-box-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 5px;
            padding-bottom: 4px;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-box-value {
            font-size: 12px;
            color: #111827;
            line-height: 1.7;
        }

        /* ── Notes ── */
        .notes-box {
            background: #f9fafb;
            border-left: 3px solid #d1d5db;
            padding: 10px 14px;
            margin-bottom: 24px;
            font-size: 11px;
            color: #4b5563;
        }

        /* ── Group block ── */
        .group {
            margin-bottom: 22px;
        }

        .group-title {
            background: #1f2937;
            color: #f9fafb;
            padding: 7px 12px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        /* ── Items table ── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table thead tr {
            background: #f9fafb;
        }

        .items-table th {
            text-align: left;
            padding: 7px 10px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }

        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
            font-size: 12px;
            color: #374151;
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .text-right { text-align: right; }

        .qty {
            font-weight: bold;
            color: #111827;
        }

        .subtotal-row td {
            background: #f3f4f6 !important;
            font-weight: bold;
            font-size: 11px;
            color: #374151;
            border-top: 1px solid #e5e7eb;
        }

        /* ── Informational services section ── */
        .section-heading-info {
            font-size: 13px;
            font-weight: bold;
            color: #6b7280;
            border-bottom: 1px dashed #d1d5db;
            padding-bottom: 8px;
            margin: 28px 0 6px;
        }
        .info-notice {
            font-size: 9.5px;
            color: #9ca3af;
            font-style: italic;
            margin-bottom: 14px;
        }
        .group-title-info {
            background: #e5e7eb;
            color: #6b7280;
            padding: 7px 12px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            border-left: 3px solid #9ca3af;
        }
        .items-table .info-row td {
            color: #9ca3af;
            font-style: italic;
            background: #fafafa;
        }
        .subtotal-row-info td {
            background: #f9fafb !important;
            font-weight: bold;
            font-size: 11px;
            color: #9ca3af;
            border-top: 1px dashed #d1d5db;
            font-style: italic;
        }
        .grand-total-table .info-row td {
            font-size: 12px;
            color: #9ca3af;
            font-style: italic;
            border-top: 1px dashed #d1d5db;
            padding-top: 8px;
        }

        /* ── Grand total ── */
        .grand-total-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .grand-total-table td {
            padding: 4px 0;
            font-size: 12px;
            color: #374151;
        }

        .grand-total-table .spacer {
            width: 60%;
        }

        .grand-total-table .label-col {
            color: #6b7280;
            text-align: right;
            padding-right: 20px;
            white-space: nowrap;
        }

        .grand-total-table .amount-col {
            text-align: right;
            white-space: nowrap;
        }

        .grand-total-table .grand-row td {
            font-size: 15px;
            font-weight: bold;
            color: #111827;
            border-top: 2px solid #111827;
            padding-top: 8px;
        }

        /* ── Footer ── */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 48px;
            border-top: 1px solid #e5e7eb;
            padding-top: 14px;
        }

        .footer-table td {
            padding-top: 14px;
            vertical-align: bottom;
        }

        .footer-left {
            font-size: 10px;
            color: #9ca3af;
        }

        .footer-right {
            font-size: 10px;
            color: #9ca3af;
            text-align: right;
        }

        .footer-right .footer-name {
            display: block;
            font-size: 11px;
            font-weight: bold;
            color: #374151;
        }
    </style>
</head>
<body>

    {{-- ── Top bar ── --}}
    <div class="top-bar">
        <div class="company-name">SK Eventspace GmbH</div>
        <div class="company-address">Zimmerstraße 26–27 · 10969 Berlin</div>
    </div>

    <div class="body">

        {{-- ── Title ── --}}
        <div class="doc-title">Bestellung #{{ $order->id }}</div>
        <div class="doc-subtitle">{{ $order->name }}</div>
        <hr class="doc-divider">

        {{-- ── Meta ── --}}
        <table class="meta-table">
            @if($order->date)
            <tr>
                <td class="meta-label">Datum</td>
                <td class="meta-value">{{ $order->date->format('d. F Y') }}</td>
            </tr>
            @endif
            <tr>
                <td class="meta-label">Status</td>
                <td class="meta-value">
                    <span class="badge badge-{{ $order->status }}">
                        {{ match($order->status) {
                            'draft'     => 'Entwurf',
                            'confirmed' => 'Bestätigt',
                            'completed' => 'Abgeschlossen',
                            default     => $order->status,
                        } }}
                    </span>
                </td>
            </tr>
            <tr>
                <td class="meta-label">Erstellt am</td>
                <td class="meta-value">{{ now()->format('d. F Y, H:i') }} Uhr</td>
            </tr>
        </table>

        {{-- ── Rechnung An / Aufgestellt von ── --}}
        <table class="info-table">
            <tr>
                <td style="padding-right: 24px;">
                    <div class="info-box-label">Rechnung an</div>
                    <div class="info-box-value">
                        <strong>SK Eventspace GmbH</strong><br>
                        Zimmerstraße 26–27<br>
                        10969 Berlin<br>
                        Deutschland
                    </div>
                </td>
                <td style="padding-left: 24px;">
                    <div class="info-box-label">Aufgestellt von</div>
                    <div class="info-box-value">
                        <strong>Enver Sanli</strong> - SK Eventspace GmbH <br>
                        +49 163 951 8970 <br>
                        e.sanli@event-hub-checkpoint.de
                    </div>
                </td>
            </tr>
        </table>

        {{-- ── Notes ── --}}
        @if($order->notes)
            <div class="notes-box">
                <strong>Anmerkungen:</strong> {{ $order->notes }}
            </div>
        @endif

        {{-- ── Included item groups ── --}}
        @php $grandTotal = 0; @endphp

        @foreach($grouped as $groupName => $lines)
            @php
                $groupTotal = $lines->sum(fn ($l) => ($l->orderItem->price ?? 0) * $l->quantity);
                $grandTotal += $groupTotal;
            @endphp

            <div class="group">
                <div class="group-title">{{ $groupName }}</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 38%">Artikel</th>
                            <th style="width: 10%">Menge</th>
                            <th style="width: 10%">Einheit</th>
                            @if($showPrices)
                                <th class="text-right" style="width: 14%">Einzelpreis</th>
                                <th class="text-right" style="width: 14%">Gesamtpreis</th>
                            @endif
                            <th>Anmerkung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lines as $line)
                            @php $lineTotal = ($line->orderItem->price ?? 0) * $line->quantity; @endphp
                            <tr>
                                <td>{{ $line->orderItem->name }}</td>
                                <td class="qty">{{ rtrim(rtrim(number_format($line->quantity, 2, ',', '.'), '0'), ',') }}</td>
                                <td>{{ $line->orderItem->unit ?? '–' }}</td>
                                @if($showPrices)
                                    <td class="text-right">
                                        {{ $line->orderItem->price !== null ? number_format($line->orderItem->price, 2, ',', '.') . ' €' : '–' }}
                                    </td>
                                    <td class="text-right">
                                        {{ $line->orderItem->price !== null ? number_format($lineTotal, 2, ',', '.') . ' €' : '–' }}
                                    </td>
                                @endif
                                <td>{{ $line->notes ?? '' }}</td>
                            </tr>
                        @endforeach

                        @if($showPrices && $groupTotal > 0)
                            <tr class="subtotal-row">
                                <td colspan="{{ $showPrices ? 4 : 2 }}" class="text-right">
                                    Zwischensumme
                                </td>
                                <td class="text-right">{{ number_format($groupTotal, 2, ',', '.') }} €</td>
                                <td></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        @endforeach

        {{-- ── Grand total (included lines only) ── --}}
        @if($showPrices && $grandTotal > 0)
            <table class="grand-total-table">
                <tr class="grand-row">
                    <td class="spacer"></td>
                    <td class="label-col">Gesamtbetrag (netto)</td>
                    <td class="amount-col">{{ number_format($grandTotal, 2, ',', '.') }} €</td>
                </tr>
            </table>
        @endif

        {{-- ── Informational lines (not included in total) ── --}}
        @if($infoGrouped->isNotEmpty())
            <div class="section-heading-info">Informationsleistungen</div>
            <div class="info-notice">
                Die folgenden Positionen werden nur zur Information aufgeführt und sind nicht im Gesamtbetrag enthalten.
            </div>

            @foreach($infoGrouped as $groupName => $lines)
                @php
                    $groupTotal = $lines->sum(fn ($l) => ($l->orderItem->price ?? 0) * $l->quantity);
                @endphp

                <div class="group">
                    <div class="group-title-info">{{ $groupName }}</div>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width: 38%">Artikel</th>
                                <th style="width: 10%">Menge</th>
                                <th style="width: 10%">Einheit</th>
                                @if($showPrices)
                                    <th class="text-right" style="width: 14%">Einzelpreis</th>
                                    <th class="text-right" style="width: 14%">Gesamtpreis</th>
                                @endif
                                <th>Anmerkung</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lines as $line)
                                @php $lineTotal = ($line->orderItem->price ?? 0) * $line->quantity; @endphp
                                <tr class="info-row">
                                    <td>{{ $line->orderItem->name }}</td>
                                    <td class="qty">{{ rtrim(rtrim(number_format($line->quantity, 2, ',', '.'), '0'), ',') }}</td>
                                    <td>{{ $line->orderItem->unit ?? '–' }}</td>
                                    @if($showPrices)
                                        <td class="text-right">
                                            {{ $line->orderItem->price !== null ? number_format($line->orderItem->price, 2, ',', '.') . ' €' : '–' }}
                                        </td>
                                        <td class="text-right">
                                            {{ $line->orderItem->price !== null ? number_format($lineTotal, 2, ',', '.') . ' €' : '–' }}
                                        </td>
                                    @endif
                                    <td>{{ $line->notes ?? '' }}</td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            @endforeach
        @endif

        {{-- ── Footer ── --}}
        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    Bestellung #{{ $order->id }} ·
                    {{ $order->orderLines->count() }} {{ $order->orderLines->count() === 1 ? 'Position' : 'Positionen' }}
                    · SK Eventspace GmbH, Zimmerstraße 26–27, 10969 Berlin
                </td>
                <td class="footer-right">
                    <span class="footer-name">Enver Sanli</span>
                    SK Eventspace GmbH
                </td>
            </tr>
        </table>

    </div>
</body>
</html>
