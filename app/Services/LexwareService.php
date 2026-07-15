<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Quote;

class LexwareService
{
    public function __construct(private LexwareClient $client) {}

    /**
     * Sync customer to Lexware contact. Returns the Lexware contact ID.
     * If already synced, reuses the stored ID without updating.
     */
    public function pushContact(Customer $customer): string
    {
        if ($customer->lexware_contact_id) {
            return $customer->lexware_contact_id;
        }

        $data = [
            'version' => 0,
            'roles'   => ['customer' => (object) []],
        ];

        if ($customer->company_name) {
            // Lexware contacts are either a company or a person, never both.
            // A named individual at a company goes into contactPersons, not top-level person.
            $data['company'] = [
                'name'           => $customer->company_name,
                'contactPersons' => [[
                    'firstName' => $customer->first_name,
                    'lastName'  => $customer->last_name,
                    'primary'   => true,
                ]],
            ];
        } else {
            $data['person'] = [
                'firstName' => $customer->first_name,
                'lastName'  => $customer->last_name,
            ];
        }

        if ($customer->email) {
            $data['emailAddresses'] = ['business' => [$customer->email]];
        }

        if ($customer->phone) {
            $data['phoneNumbers'] = ['business' => [$customer->phone]];
        }

        if ($customer->address || $customer->postal_code || $customer->city) {
            $billingAddress = ['countryCode' => $customer->country ?? 'DE'];

            if ($customer->address)     $billingAddress['street'] = $customer->address;
            if ($customer->postal_code) $billingAddress['zip']    = $customer->postal_code;
            if ($customer->city)        $billingAddress['city']   = $customer->city;

            $data['addresses'] = ['billing' => [$billingAddress]];
        }

        $result = $this->client->createContact($data);
        $id     = $result['id'];

        $customer->update(['lexware_contact_id' => $id]);

        return $id;
    }

    /**
     * Push quote as a Lexware quotation.
     */
    public function pushQuotation(Quote $quote): string
    {
        $quote->loadMissing(['customer', 'business', 'quoteLines']);

        $contactId = $quote->customer ? $this->pushContact($quote->customer) : null;
        $payload   = $this->buildVoucherPayload($quote, $contactId);

        \Illuminate\Support\Facades\Log::debug('Lexware quotation payload', $payload);

        $result = $this->client->createQuotation($payload);
        $id     = $result['id'];

        $quote->update(['lexware_quotation_id' => $id]);

        return $id;
    }

    /**
     * Push quote as a Lexware draft invoice (not finalized).
     */
    public function pushDraftInvoice(Quote $quote, ?array $paymentTerms = null): string
    {
        $quote->loadMissing(['customer', 'business', 'quoteLines']);

        $contactId = $quote->customer ? $this->pushContact($quote->customer) : null;
        $payload   = $this->buildVoucherPayload($quote, $contactId, includeAgreements: (bool) $paymentTerms);

        unset($payload['expirationDate']);

        if ($paymentTerms) {
            $payload['paymentConditions'] = [
                'paymentTermLabel'    => $paymentTerms['label'] ?? 'Zahlbar innerhalb 30 Tagen netto',
                'paymentTermDuration' => $paymentTerms['duration'] ?? 30,
            ];
        }

        $serviceDate = $quote->event_date ?? now();
        $payload['shippingConditions'] = [
            'shippingDate' => $serviceDate->format('Y-m-d\TH:i:s.000P'),
            'shippingType' => 'service',
        ];

        \Illuminate\Support\Facades\Log::debug('Lexware draft invoice payload', $payload);

        $result = $this->client->createInvoice($payload, finalize: false);
        $id     = $result['id'];

        $quote->update(['lexware_quotation_id' => $id]);

        return $id;
    }

    /**
     * Push quote as a finalized Lexware invoice (accepted state).
     */
    public function pushInvoice(Quote $quote, ?array $paymentTerms = null): string
    {
        $quote->loadMissing(['customer', 'business', 'quoteLines']);

        $contactId = $quote->customer ? $this->pushContact($quote->customer) : null;
        $payload   = $this->buildVoucherPayload($quote, $contactId, includeAgreements: (bool) $paymentTerms);

        // Invoices use paymentConditions instead of expirationDate
        unset($payload['expirationDate']);

        if ($paymentTerms) {
            $payload['paymentConditions'] = [
                'paymentTermLabel'    => $paymentTerms['label'] ?? 'Zahlbar innerhalb 30 Tagen netto',
                'paymentTermDuration' => $paymentTerms['duration'] ?? 30,
            ];
        }

        // Required by Lexware when finalizing — use event date if available, otherwise today
        $serviceDate = $quote->event_date ?? now();
        $payload['shippingConditions'] = [
            'shippingDate' => $serviceDate->format('Y-m-d\TH:i:s.000P'),
            'shippingType' => 'service',
        ];

        $result = $this->client->createInvoice($payload, finalize: true);
        $id     = $result['id'];

        $quote->update(['lexware_invoice_id' => $id]);

        return $id;
    }

    private function buildVoucherPayload(Quote $quote, ?string $contactId, bool $includeAgreements = true): array
    {
        $vatRate   = (float) ($quote->vat_rate ?? 19);
        $lineItems = [];

        // Venue rental line
        $venueNet = (float) $quote->venue_subtotal;
        if ($venueNet > 0) {
            $name = 'Raummiete';
            if ($quote->business?->name) {
                $name .= ' – ' . $quote->business->name;
            }

            $desc = null;
            if ($quote->hours) {
                $desc = 'Mietdauer: ' . number_format((float) $quote->hours, 2, ',', '.') . ' Std.';
                if ($quote->event_date) {
                    $desc .= ', ' . $quote->event_date->format('d.m.Y');
                }
            }

            $lineItems[] = $this->lineItem($name, $desc, 1, 'pauschal', $venueNet, $vatRate);
        }

        // Service lines (include_in_total only)
        foreach ($quote->quoteLines as $line) {
            if (! $line->include_in_total) {
                continue;
            }

            $qty = (float) ($line->quantity ?: 1);

            if ($line->discount_type === 'percentage' && (float) ($line->discount_value ?? 0) > 0) {
                // Pass gross unit price + discountPercentage field
                $unitPrice          = (float) $line->unit_price;
                $discountPercentage = (float) $line->discount_value;
            } else {
                // Fold any fixed discount into the effective unit price
                $unitPrice          = $qty > 0 ? round((float) $line->line_total / $qty, 6) : 0;
                $discountPercentage = 0;
            }

            $lineItems[] = $this->lineItem(
                $line->description ?: 'Dienstleistung',
                null,
                $qty,
                $line->unit ?: 'Stück',
                $unitPrice,
                $vatRate,
                $discountPercentage
            );
        }

        // Global discount → negative line
        $globalDiscount = (float) $quote->discount_amount;
        if ($globalDiscount > 0) {
            $label = $quote->discount_type === 'percentage'
                ? "Rabatt ({$quote->discount_value}%)"
                : 'Rabatt';

            $lineItems[] = $this->lineItem($label, null, 1, 'pauschal', -$globalDiscount, $vatRate);
        }

        // Address block
        $customer = $quote->customer;
        $address  = [
            'name'        => $quote->customer_display_name,
            'countryCode' => $customer?->country ?? 'DE',
        ];

        if ($customer?->address)     $address['street']  = $customer->address;
        if ($customer?->postal_code) $address['zip']     = $customer->postal_code;
        if ($customer?->city)        $address['city']    = $customer->city;

        if ($contactId) {
            $address['contactId'] = $contactId;
        }

        $payload = [
            'archived'      => false,
            'voucherDate'   => now()->format('Y-m-d\TH:i:s.000P'),
            'address'       => $address,
            'lineItems'     => $lineItems,
            'totalPrice'    => ['currency' => 'EUR'],
            'taxConditions' => ['taxType' => 'net'],
        ];

        if ($quote->quote_number) {
            $payload['title'] = "Angebot {$quote->quote_number}";
        }

        if ($quote->valid_until) {
            $payload['expirationDate'] = $quote->valid_until->format('Y-m-d\TH:i:s.000P');
        }

        $remarkParts = [];
        if ($quote->notes) {
            $remarkParts[] = $quote->notes;
        }
        if ($includeAgreements) {
            $remarkParts[] = $this->agreementsText();
        }

        if ($remarkParts) {
            $payload['remark'] = implode("\n\n", $remarkParts);
        }

        return $payload;
    }

    private function agreementsText(): string
    {
        return <<<TEXT
Allgemeine Geschäftsbedingungen

Mit Annahme dieses Angebots ist eine Anzahlung von 50 % des Gesamtbruttobetrags zur Bestätigung Ihrer Buchung fällig.

Buchungsbestätigung
Dieses Angebot ist gültig bis zum oben angegebenen Datum. Die Annahme dieses Angebots stellt eine verbindliche Buchungsvereinbarung zwischen dem Auftraggeber und der SK Eventspace GmbH dar. Die Buchung gilt erst als bestätigt, wenn die Anzahlung eingegangen ist.

Zahlungsbedingungen
Bei Annahme dieses Angebots sind 50 % des Gesamtbruttobetrags als Anzahlung fällig. Die verbleibenden 50 % sind spätestens 14 Tage vor dem Veranstaltungsdatum zu entrichten. Zahlungen erfolgen per Überweisung auf das angegebene Bankkonto.

Stornierungsbedingungen
Stornierungen müssen schriftlich erfolgen. Es gelten folgende Stornogebühren:
- Stornierung mehr als 30 Tage vor der Veranstaltung: Anzahlung wird abzüglich einer Bearbeitungsgebühr von 10 % erstattet.
- Stornierung 14-30 Tage vor der Veranstaltung: 50 % des Gesamtbruttobetrags sind fällig.
- Stornierung weniger als 14 Tage vor der Veranstaltung: 100 % des Gesamtbruttobetrags sind fällig.
Die SK Eventspace GmbH behält sich das Recht vor, die Veranstaltung in Fällen höherer Gewalt ohne Haftung abzusagen.

Höhere Gewalt
Keine der Parteien haftet für die Nichterfüllung ihrer Verpflichtungen, sofern diese durch Umstände verursacht wird, die außerhalb ihrer zumutbaren Kontrolle liegen, einschließlich Naturkatastrophen, Pandemien, behördliche Einschränkungen oder Streiks. In solchen Fällen werden beide Parteien angemessene Bemühungen unternehmen, die Veranstaltung zu verschieben.

Anwendbares Recht & Gerichtsstand
Diese Vereinbarung unterliegt dem Recht der Bundesrepublik Deutschland. Gerichtsstand ist Berlin.
TEXT;
    }

    private function lineItem(
        string $name,
        ?string $description,
        float $quantity,
        string $unitName,
        float $netAmount,
        float $taxRate,
        float $discountPercentage = 0
    ): array {
        $item = [
            'type'      => 'custom',
            'name'      => $name,
            'quantity'  => $quantity,
            'unitName'  => $unitName,
            'unitPrice' => [
                'currency'          => 'EUR',
                'netAmount'         => round($netAmount, 4),
                'taxRatePercentage' => $taxRate,
            ],
            'discountPercentage' => $discountPercentage,
        ];

        if ($description) {
            $item['description'] = $description;
        }

        return $item;
    }
}
