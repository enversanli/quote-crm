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
    public function pushDraftInvoice(Quote $quote): string
    {
        $quote->loadMissing(['customer', 'business', 'quoteLines']);

        $contactId = $quote->customer ? $this->pushContact($quote->customer) : null;
        $payload   = $this->buildVoucherPayload($quote, $contactId);

        unset($payload['expirationDate']);
        $payload['paymentConditions'] = [
            'paymentTermLabel'    => 'Zahlbar innerhalb 30 Tagen netto',
            'paymentTermDuration' => 30,
        ];

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
    public function pushInvoice(Quote $quote): string
    {
        $quote->loadMissing(['customer', 'business', 'quoteLines']);

        $contactId = $quote->customer ? $this->pushContact($quote->customer) : null;
        $payload   = $this->buildVoucherPayload($quote, $contactId);

        // Invoices use paymentConditions instead of expirationDate
        unset($payload['expirationDate']);
        $payload['paymentConditions'] = [
            'paymentTermLabel'    => 'Zahlbar innerhalb 30 Tagen netto',
            'paymentTermDuration' => 30,
        ];

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

    private function buildVoucherPayload(Quote $quote, ?string $contactId): array
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

        if ($quote->notes) {
            $payload['remark'] = $quote->notes;
        }

        return $payload;
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
