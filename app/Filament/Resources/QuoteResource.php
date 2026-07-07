<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Models\Business;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Quote;
use App\Services\LexwareService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use setasign\Fpdi\Fpdi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Quotes';

    protected static ?int $navigationSort = 1;

    // ══════════════════════════════════════════════
    // Private helpers
    // ══════════════════════════════════════════════

    /** Apply a discount to $amount; returns the discount amount (not the result). */
    private static function calcDiscount(float $amount, ?string $type, float $value): float
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

    /**
     * Full pricing breakdown from live form state.
     * Returns every intermediate value needed for display.
     */
    private static function computePricing(Forms\Get $get): array
    {
        // ── Venue ─────────────────────────────────
        $hours      = (float) ($get('hours') ?? 0);
        $rate       = (float) ($get('hourly_rate') ?? 0);
        $venueGross = round($hours * $rate, 2);

        $venueDType    = $get('venue_discount_type');
        $venueDValue   = (float) ($get('venue_discount_value') ?? 0);
        $venueDiscount = self::calcDiscount($venueGross, $venueDType, $venueDValue);
        $venueNet      = round($venueGross - $venueDiscount, 2);

        // ── Lines ─────────────────────────────────
        $linesGross    = 0.0;  // included in total
        $linesDiscount = 0.0;
        $infoGross     = 0.0;  // informational only (not in total)
        $infoDiscount  = 0.0;

        foreach ($get('quoteLines') ?? [] as $line) {
            $lineGross = round((float) ($line['quantity'] ?? 0) * (float) ($line['unit_price'] ?? 0), 2);
            $lineDisc  = self::calcDiscount($lineGross, $line['discount_type'] ?? null, (float) ($line['discount_value'] ?? 0));

            if ($line['include_in_total'] ?? true) {
                $linesGross    += $lineGross;
                $linesDiscount += $lineDisc;
            } else {
                $infoGross    += $lineGross;
                $infoDiscount += $lineDisc;
            }
        }

        $linesNet    = round($linesGross - $linesDiscount, 2);
        $infoNet     = round($infoGross - $infoDiscount, 2);
        $subtotalNet = round($venueNet + $linesNet, 2);

        // ── Global discount ────────────────────────
        $gType          = $get('discount_type');
        $gValue         = (float) ($get('discount_value') ?? 0);
        $globalDiscount = self::calcDiscount($subtotalNet, $gType, $gValue);
        $netAfterDisc   = round($subtotalNet - $globalDiscount, 2);

        // ── VAT ───────────────────────────────────
        $vatRate    = (float) ($get('vat_rate') ?? 19);
        $vatAmount  = round($netAfterDisc * $vatRate / 100, 2);
        $totalGross = round($netAfterDisc + $vatAmount, 2);

        return compact(
            'venueGross', 'venueDiscount', 'venueNet',
            'linesGross', 'linesDiscount', 'linesNet',
            'infoGross', 'infoDiscount', 'infoNet',
            'subtotalNet', 'globalDiscount', 'netAfterDisc',
            'vatRate', 'vatAmount', 'totalGross',
            'gType', 'gValue'
        );
    }

    /** Re-compute & set the venue net display after any venue pricing field changes. */
    private static function refreshVenueNet(Forms\Set $set, Forms\Get $get, ?float $overrideHours = null): void
    {
        $hours        = $overrideHours ?? (float) ($get('hours') ?? 0);
        $rate         = (float) ($get('hourly_rate') ?? 0);
        $gross        = round($hours * $rate, 2);
        $discount     = self::calcDiscount($gross, $get('venue_discount_type'), (float) ($get('venue_discount_value') ?? 0));
        $net          = round($gross - $discount, 2);

        $set('venue_gross_display',   $gross > 0  ? number_format($gross,    2, ',', '.') : null);
        $set('venue_discount_display', $discount > 0 ? number_format($discount, 2, ',', '.') : null);
        $set('venue_net_display',     $net > 0    ? number_format($net,      2, ',', '.') : null);
    }

    /** Re-compute the start/end → hours, then refresh venue net. */
    private static function refreshHoursFromTime(Forms\Set $set, Forms\Get $get): void
    {
        $start = $get('event_start_time');
        $end   = $get('event_end_time');

        if ($start && $end) {
            try {
                $s = Carbon::createFromFormat('H:i:s', strlen($start) === 5 ? $start . ':00' : $start);
                $e = Carbon::createFromFormat('H:i:s', strlen($end) === 5 ? $end . ':00' : $end);

                if ($e->gt($s)) {
                    $hours = round($s->diffInMinutes($e) / 60, 2);
                    $set('hours', $hours);
                    self::refreshVenueNet($set, $get, $hours);
                    return;
                }
            } catch (\Exception) {
            }
        }

        self::refreshVenueNet($set, $get);
    }

    /** Re-compute line_total_display inside a repeater item. */
    private static function refreshLineTotal(Forms\Set $set, Forms\Get $get): void
    {
        $gross    = round((float) ($get('quantity') ?? 0) * (float) ($get('unit_price') ?? 0), 2);
        $discount = self::calcDiscount($gross, $get('discount_type'), (float) ($get('discount_value') ?? 0));
        $net      = round($gross - $discount, 2);

        $set('line_gross_display',    $gross > 0    ? number_format($gross,    2, ',', '.') : null);
        $set('line_discount_display', $discount > 0 ? number_format($discount, 2, ',', '.') : null);
        $set('line_total_display',    $net >= 0     ? number_format($net,      2, ',', '.') : null);
    }

    private static function buildEmailParts(Quote $record, string $lang): array
    {
        $record->loadMissing(['customer', 'business']);

        $to        = trim($record->customer?->email ?? $record->customer_email ?? '');
        $firstName = $record->customer?->first_name ?? $record->customer_first_name ?? '';
        $lastName  = $record->customer?->last_name  ?? $record->customer_last_name  ?? '';
        $name      = trim("$firstName $lastName")
                     ?: ($record->customer?->company_name ?? $record->customer_company ?? '');
        $business   = $record->business?->name ?? '';
        $eventDate  = $record->event_date?->format('d.m.Y') ?? '';
        $validUntil = $record->valid_until?->format('d.m.Y') ?? '';

        $sig = "\n\nEnver Sanli\nSK Eventspace GmbH\ne.sanli@event-hub-checkpoint.de\n+49 163 951 8970";

        $when = $eventDate ? ($lang === 'de' ? " am {$eventDate}" : ($lang === 'tr' ? " {$eventDate} tarihinde" : " on {$eventDate}")) : '';
        $at   = $business  ? ($lang === 'de' ? " im {$business}" : ($lang === 'tr' ? " {$business} mekanında" : " at {$business}")) : '';

        $templates = [
            'de' => [
                'subject' => trim('Angebot' . ($business ? " – {$business}" : '') . ($eventDate ? " · {$eventDate}" : '')),
                'body'    => "Sehr geehrte(r) {$name},\n\n"
                    . "vielen Dank für Ihr Interesse an unserem Veranstaltungsort.\n\n"
                    . "Im Anhang finden Sie unser Angebot für Ihre Veranstaltung{$when}{$at}."
                    . ($validUntil ? "\n\nDas Angebot ist gültig bis: {$validUntil}." : '') . "\n\n"
                    . "Für Rückfragen stehen wir Ihnen gerne zur Verfügung.\n\nMit freundlichen Grüßen,{$sig}",
            ],
            'en' => [
                'subject' => trim('Quote' . ($business ? " – {$business}" : '') . ($eventDate ? " · {$eventDate}" : '')),
                'body'    => "Dear {$name},\n\n"
                    . "Thank you for your interest in our venue.\n\n"
                    . "Please find our quote attached for your event{$when}{$at}."
                    . ($validUntil ? "\n\nThis quote is valid until: {$validUntil}." : '') . "\n\n"
                    . "Please do not hesitate to contact us if you have any questions.\n\nBest regards,{$sig}",
            ],
            'tr' => [
                'subject' => trim('Teklif' . ($business ? " – {$business}" : '') . ($eventDate ? " · {$eventDate}" : '')),
                'body'    => "Sayın {$name},\n\n"
                    . "Mekanımıza gösterdiğiniz ilgi için teşekkür ederiz.\n\n"
                    . "Etkinliğinize{$when}{$at} ait teklifimizi ekte bulabilirsiniz."
                    . ($validUntil ? "\n\nTeklifin geçerlilik tarihi: {$validUntil}." : '') . "\n\n"
                    . "Herhangi bir sorunuz olursa lütfen bizimle iletişime geçin.\n\nSaygılarımızla,{$sig}",
            ],
        ];

        $tpl = $templates[$lang] ?? $templates['de'];

        return [$to, $tpl['subject'], $tpl['body']];
    }

    /**
     * Build the quote PDF (optionally with cover pages) and return the raw content string.
     * Pass the same $data array that the download modal collects.
     */
    public static function buildQuotePdf(Quote $record, array $data): string
    {
        $record->loadMissing(['customer', 'business', 'quoteLines.orderItem.itemGroup']);

        $quotePdfOutput = Pdf::loadView('pdf.quote', [
            'quote'             => $record,
            'showPrices'        => $data['show_prices'] ?? true,
            'language'          => $data['language'] ?? 'en',
            'showCostPerPerson' => $data['show_cost_per_person'] ?? false,
            'showSignature'     => $data['show_signature'] ?? false,
            'preview'           => false,
        ])->setPaper('a4')->output();

        if (! ($data['with_cover'] ?? true)) {
            return $quotePdfOutput;
        }

        $firstPdf  = public_path('docs/angebot-de-first.pdf');
        $secondPdf = public_path('docs/angebot-de-second.pdf');

        $innerTmp = tempnam(sys_get_temp_dir(), 'qinner_') . '.pdf';
        file_put_contents($innerTmp, $quotePdfOutput);

        $fpdi = new Fpdi();
        foreach ([$firstPdf, $innerTmp, $secondPdf] as $file) {
            if (! file_exists($file)) continue;
            $count = $fpdi->setSourceFile($file);
            for ($i = 1; $i <= $count; $i++) {
                $tpl = $fpdi->importPage($i);
                $fpdi->AddPage('P', 'A4');
                $fpdi->useTemplate($tpl, 0, 0, 210, 297, true);
            }
        }

        $merged = $fpdi->Output('S');
        @unlink($innerTmp);

        return $merged;
    }

    private static function generateQuotePdf(Quote $record, string $lang, bool $withCover): string
    {
        $content   = self::buildQuotePdf($record, [
            'show_prices'        => true,
            'language'           => $lang,
            'with_cover'         => $withCover,
            'show_cost_per_person' => false,
        ]);
        $tempQuote = tempnam(sys_get_temp_dir(), 'quote_') . '.pdf';
        file_put_contents($tempQuote, $content);

        return $tempQuote;
    }

    private static function openInOutlook(string $to, string $subject, string $body, string $pdfPath): void
    {
        // Build CRLF-separated body (email standard; Outlook respects double-CRLF as paragraph break)
        $lines       = explode("\n", $body);
        $scriptLines = array_map(
            fn ($l) => '"' . str_replace(['"', '\\'], ['\\"', '\\\\'], $l) . '"',
            $lines
        );
        $crlf       = '(ASCII character 13) & (ASCII character 10)';
        $bodyScript = implode(" & {$crlf} & ", $scriptLines);

        $subjectEsc = str_replace(['"', '\\'], ['\\"', '\\\\'], $subject);
        $toEsc      = str_replace(['"', '\\'], ['\\"', '\\\\'], $to);
        $pathEsc    = str_replace(['"', '\\'], ['\\"', '\\\\'], $pdfPath);

        $script = <<<APPLESCRIPT
tell application "Microsoft Outlook"
    set msgBody to {$bodyScript}
    set theMessage to make new outgoing message
    set subject of theMessage to "{$subjectEsc}"
    set plain text content of theMessage to msgBody
    make new to recipient at theMessage with properties {email address:{address:"{$toEsc}"}}
    make new attachment at theMessage with properties {file:(POSIX file "{$pathEsc}")}
    open theMessage
    activate
end tell
APPLESCRIPT;

        $scriptFile = tempnam(sys_get_temp_dir(), 'outlook_') . '.scpt';
        file_put_contents($scriptFile, $script);
        $output = [];
        exec('osascript ' . escapeshellarg($scriptFile) . ' 2>&1', $output);
        if (!empty($output)) {
            \Illuminate\Support\Facades\Log::error('osascript error', ['output' => implode("\n", $output)]);
        }
        @unlink($scriptFile);
    }

    public static function pdfFileName(Quote $record): string
    {
        $parts = array_filter([
            trim($record->customer_first_name . ' ' . $record->customer_last_name)
                ?: ($record->customer?->full_name ?? null)
                ?: $record->customer_company
                ?: null,
            $record->business?->name,
            $record->event_date?->format('d-m-Y'),
        ]);

        $slug = \Illuminate\Support\Str::slug(implode(' ', $parts));

        return ($slug ?: $record->quote_number) . '.pdf';
    }

    /** Discount type options (shared between venue and global). */
    private static function discountTypeOptions(): array
    {
        return [
            'percentage' => '% Percentage',
            'fixed'      => '€ Fixed amount',
        ];
    }

    // ══════════════════════════════════════════════
    // Form
    // ══════════════════════════════════════════════

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Tabs::make('quote_tabs')
                ->tabs([

                    // ── Tab 1: Overview ───────────────────────────────────
                    Forms\Components\Tabs\Tab::make('Overview')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Forms\Components\Section::make('Quote Details')
                                ->schema([
                                    Forms\Components\TextInput::make('quote_number')
                                        ->label('Quote #')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->placeholder('Auto-generated on save')
                                        ->afterStateHydrated(fn (Forms\Set $set, $state) => $set('quote_number', $state)),

                                    Forms\Components\Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'draft'     => 'Draft',
                                            'sent'      => 'Sent',
                                            'accepted'  => 'Accepted',
                                            'completed' => 'Completed',
                                            'rejected'  => 'Rejected',
                                            'expired'   => 'Expired',
                                        ])
                                        ->default('draft')
                                        ->required(),

                                    Forms\Components\Select::make('payment_status')
                                        ->label('Payment')
                                        ->options([
                                            'unpaid'  => 'Unpaid',
                                            'partial' => 'Partial',
                                            'paid'    => 'Paid',
                                        ])
                                        ->default('unpaid')
                                        ->required(),

                                    Forms\Components\DatePicker::make('valid_until')
                                        ->label('Valid Until')
                                        ->nullable(),

                                    // VAT rate — live so the breakdown refreshes
                                    Forms\Components\TextInput::make('vat_rate')
                                        ->label('VAT Rate (%)')
                                        ->numeric()
                                        ->suffix('%')
                                        ->default(19)
                                        ->required()
                                        ->live()
                                        ->minValue(0)
                                        ->maxValue(100),

                                    Forms\Components\Textarea::make('notes')
                                        ->label('Notes')
                                        ->nullable()
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ])
                                ->columns(4),
                        ]),

                    // ── Tab 2: Event & Venue ──────────────────────────────
                    Forms\Components\Tabs\Tab::make('Event & Venue')
                        ->icon('heroicon-o-calendar-days')
                        ->schema([
                            Forms\Components\Section::make('Event & Venue')
                                ->schema([
                                    Forms\Components\DatePicker::make('event_date')
                                        ->label('Event Date')
                                        ->nullable(),

                                    Forms\Components\Select::make('business_id')
                                        ->label('Business / Venue')
                                        ->options(Business::orderBy('name')->pluck('name', 'id'))
                                        ->searchable()
                                        ->nullable()
                                        ->live()
                                        ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                            $business = Business::find($state);
                                            if ($business?->default_hourly_rate !== null) {
                                                $set('hourly_rate', (float) $business->default_hourly_rate);
                                            }
                                            self::refreshVenueNet($set, $get);
                                        }),

                                    Forms\Components\TimePicker::make('event_start_time')
                                        ->label('Start Time')
                                        ->seconds(false)
                                        ->nullable()
                                        ->live()
                                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::refreshHoursFromTime($set, $get)),

                                    Forms\Components\TimePicker::make('event_end_time')
                                        ->label('End Time')
                                        ->seconds(false)
                                        ->nullable()
                                        ->live()
                                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::refreshHoursFromTime($set, $get)),

                                    Forms\Components\TextInput::make('hourly_rate')
                                        ->label('Hourly Rate (€)')
                                        ->numeric()
                                        ->prefix('€')
                                        ->nullable()
                                        ->live()
                                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::refreshVenueNet($set, $get))
                                        ->helperText('Pre-filled from selected business.'),

                                    Forms\Components\TextInput::make('hours')
                                        ->label('Hours')
                                        ->numeric()
                                        ->nullable()
                                        ->minValue(0)
                                        ->live()
                                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get, $state) => self::refreshVenueNet($set, $get, (float) ($state ?? 0)))
                                        ->helperText('Auto-calculated from start / end time.'),

                                    Forms\Components\TextInput::make('attendee_count')
                                        ->label('Attendees')
                                        ->numeric()
                                        ->nullable()
                                        ->integer()
                                        ->minValue(1)
                                        ->live()
                                        ->suffix('persons')
                                        ->helperText('Used to calculate cost per person (informational).'),

                                    Forms\Components\Select::make('organizer_type')
                                        ->label('Organizer Type')
                                        ->options([
                                            'private'  => 'Private',
                                            'business' => 'Business',
                                        ])
                                        ->nullable()
                                        ->placeholder('— optional —'),

                                    Forms\Components\Select::make('event_type')
                                        ->label('Event Type')
                                        ->options([
                                            'meeting'      => 'Meeting',
                                            'conference'   => 'Conference',
                                            'workshop'     => 'Workshop',
                                            'seminar'      => 'Seminar',
                                            'training'     => 'Training',
                                            'birthday'     => 'Birthday',
                                            'wedding'      => 'Wedding',
                                            'gala'         => 'Gala / Dinner',
                                            'team_event'   => 'Team Event',
                                            'other'        => 'Other',
                                        ])
                                        ->nullable()
                                        ->placeholder('— optional —'),

                                    // Venue discount
                                    Forms\Components\Select::make('venue_discount_type')
                                        ->label('Venue Discount Type')
                                        ->options(self::discountTypeOptions())
                                        ->nullable()
                                        ->live()
                                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::refreshVenueNet($set, $get)),

                                    Forms\Components\TextInput::make('venue_discount_value')
                                        ->label('Venue Discount Value')
                                        ->numeric()
                                        ->nullable()
                                        ->live()
                                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::refreshVenueNet($set, $get))
                                        ->visible(fn (Forms\Get $get) => filled($get('venue_discount_type'))),

                                    // Venue pricing display (read-only)
                                    Forms\Components\TextInput::make('venue_gross_display')
                                        ->label('Venue Gross')
                                        ->prefix('€')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->placeholder('—')
                                        ->afterStateHydrated(fn (Forms\Set $set, Forms\Get $get) => self::refreshVenueNet($set, $get)),

                                    Forms\Components\TextInput::make('venue_discount_display')
                                        ->label('Venue Discount')
                                        ->prefix('− €')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->placeholder('—')
                                        ->visible(fn (Forms\Get $get) => filled($get('venue_discount_type'))),

                                    Forms\Components\TextInput::make('venue_net_display')
                                        ->label('Venue Net (after discount)')
                                        ->prefix('€')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->placeholder('—')
                                        ->visible(fn (Forms\Get $get) => filled($get('venue_discount_type'))),
                                ])
                                ->columns(2),
                        ]),

                    // ── Tab 3: Customer ───────────────────────────────────
                    Forms\Components\Tabs\Tab::make('Customer')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Forms\Components\Section::make('Customer')
                                ->description('Select an existing customer to auto-fill the fields, or enter details manually.')
                                ->schema([
                                    Forms\Components\Select::make('customer_id')
                                        ->label('Existing Customer')
                                        ->options(
                                            Customer::orderBy('last_name')->orderBy('first_name')
                                                ->get()
                                                ->mapWithKeys(fn (Customer $c) => [$c->id => $c->display_name])
                                        )
                                        ->searchable()
                                        ->nullable()
                                        ->live()
                                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                                            $customer = Customer::find($state);
                                            if ($customer) {
                                                $set('customer_first_name', $customer->first_name);
                                                $set('customer_last_name',  $customer->last_name);
                                                $set('customer_company',    $customer->company_name);
                                                $set('customer_email',      $customer->email);
                                                $set('customer_phone',      $customer->phone);
                                            }
                                        })
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('customer_first_name')->label('First Name')->nullable(),
                                    Forms\Components\TextInput::make('customer_last_name')->label('Last Name')->nullable(),
                                    Forms\Components\TextInput::make('customer_company')->label('Company')->nullable()->columnSpan(2),
                                    Forms\Components\TextInput::make('customer_email')->label('Email')->email()->nullable(),
                                    Forms\Components\TextInput::make('customer_phone')->label('Phone')->tel()->nullable(),

                                    Forms\Components\Placeholder::make('customer_address_display')
                                        ->label('Address')
                                        ->content(function (Forms\Get $get) {
                                            $customer = Customer::find($get('customer_id'));
                                            if (! $customer) {
                                                return 'No address on file — link an existing customer to show one.';
                                            }

                                            $lines = array_filter([
                                                $customer->address,
                                                trim("{$customer->postal_code} {$customer->city}"),
                                                $customer->country,
                                            ]);

                                            return $lines ? implode(', ', $lines) : 'No address on file for this customer.';
                                        })
                                        ->columnSpanFull(),

                                    Forms\Components\Actions::make([
                                        Forms\Components\Actions\Action::make('save_as_customer')
                                            ->label('Save as Customer')
                                            ->icon('heroicon-o-user-plus')
                                            ->color('primary')
                                            ->action(function (Forms\Get $get, Forms\Set $set) {
                                                $firstName = trim($get('customer_first_name') ?? '');
                                                $lastName  = trim($get('customer_last_name') ?? '');

                                                if (! $firstName && ! $lastName) {
                                                    Notification::make()
                                                        ->title('Name required')
                                                        ->body('Please enter at least a first or last name.')
                                                        ->warning()
                                                        ->send();
                                                    return;
                                                }

                                                $email = trim($get('customer_email') ?? '') ?: null;

                                                // Reuse existing customer if same email is already in the table
                                                $customer = $email
                                                    ? Customer::firstOrNew(['email' => $email])
                                                    : new Customer();

                                                $customer->fill([
                                                    'first_name'   => $firstName,
                                                    'last_name'    => $lastName,
                                                    'company_name' => trim($get('customer_company') ?? '') ?: null,
                                                    'email'        => $email,
                                                    'phone'        => trim($get('customer_phone') ?? '') ?: null,
                                                    'country'      => 'DE',
                                                ])->save();

                                                $set('customer_id', $customer->id);

                                                Notification::make()
                                                    ->title($customer->wasRecentlyCreated ? 'Customer created' : 'Customer updated')
                                                    ->body($customer->display_name . ' saved to Contacts.')
                                                    ->success()
                                                    ->send();
                                            }),
                                    ])
                                    ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    // ── Tab 4: Services & Pricing ─────────────────────────
                    Forms\Components\Tabs\Tab::make('Services & Pricing')
                        ->icon('heroicon-o-receipt-percent')
                        ->schema([
                            Forms\Components\Section::make('Quote Lines')
                                ->schema([
                                    Forms\Components\Repeater::make('quoteLines')
                                        ->relationship()
                                        ->label('')
                                        ->schema([

                                            // ── Row 1: Catalog item & description ──────────
                                            Forms\Components\Grid::make(12)
                                                ->schema([
                                                    Forms\Components\Select::make('order_item_id')
                                                        ->label('Catalog Item')
                                                        ->options(
                                                            OrderItem::whereHas('itemGroup', fn ($q) => $q->where('name', 'Event Hub - Dienstleistungen'))
                                                                ->orderBy('name')
                                                                ->pluck('name', 'id')
                                                                ->toArray()
                                                        )
                                                        ->searchable()
                                                        ->nullable()
                                                        ->live()
                                                        ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                                            $item = OrderItem::find($state);
                                                            if ($item) {
                                                                $set('description', $item->name);
                                                                $set('unit',        $item->unit);
                                                                $set('unit_price',  (float) ($item->price ?? 0));
                                                            }
                                                            self::refreshLineTotal($set, $get);
                                                        })
                                                        ->columnSpan(5),

                                                    Forms\Components\TextInput::make('description')
                                                        ->label('Description')
                                                        ->nullable()
                                                        ->columnSpan(7),
                                                ]),

                                            // ── Row 2: Qty · Price · Net Total ─────────────
                                            Forms\Components\Grid::make(12)
                                                ->schema([
                                                    Forms\Components\TextInput::make('unit')
                                                        ->label('Unit')
                                                        ->nullable()
                                                        ->columnSpan(2),

                                                    Forms\Components\TextInput::make('quantity')
                                                        ->label('Qty')
                                                        ->numeric()
                                                        ->default(1)
                                                        ->required()
                                                        ->minValue(0)
                                                        ->live()
                                                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::refreshLineTotal($set, $get))
                                                        ->columnSpan(2),

                                                    Forms\Components\TextInput::make('unit_price')
                                                        ->label('Unit Price')
                                                        ->numeric()
                                                        ->prefix('€')
                                                        ->default(0)
                                                        ->live()
                                                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::refreshLineTotal($set, $get))
                                                        ->columnSpan(3),

                                                    // Net total — styled card (replaces disabled TextInput)
                                                    Forms\Components\Placeholder::make('line_total_display')
                                                        ->label('Net Total')
                                                        ->columnSpan(5)
                                                        ->content(function (Forms\Get $get): HtmlString {
                                                            $gross    = round((float) ($get('quantity') ?? 0) * (float) ($get('unit_price') ?? 0), 2);
                                                            $discount = self::calcDiscount($gross, $get('discount_type'), (float) ($get('discount_value') ?? 0));
                                                            $net      = round($gross - $discount, 2);
                                                            $inTotal  = $get('include_in_total') ?? true;

                                                            $accent = $inTotal ? '#059669' : '#d97706';
                                                            $bg     = $inTotal ? '#f0fdf4' : '#fffbeb';
                                                            $border = $inTotal ? '#bbf7d0' : '#fde68a';

                                                            $left = $discount > 0
                                                                ? '<span style="font-size:11px;color:#9ca3af;">€&nbsp;'
                                                                    . number_format($gross, 2, ',', '.')
                                                                    . '&nbsp;<span style="color:#ef4444;">−&nbsp;€&nbsp;'
                                                                    . number_format($discount, 2, ',', '.')
                                                                    . '</span></span>'
                                                                : '<span style="font-size:11px;color:#9ca3af;">net</span>';

                                                            return new HtmlString(
                                                                '<div style="display:flex;justify-content:space-between;align-items:center;'
                                                                . 'background:' . $bg . ';border:1px solid ' . $border . ';'
                                                                . 'border-radius:8px;padding:8px 14px;min-height:42px;">'
                                                                . $left
                                                                . '<strong style="font-size:16px;color:' . $accent . ';">€&nbsp;'
                                                                . number_format($net, 2, ',', '.')
                                                                . '</strong>'
                                                                . '</div>'
                                                            );
                                                        }),
                                                ]),

                                            // ── Row 3: Discount · Include toggle · Notes ────
                                            Forms\Components\Grid::make(12)
                                                ->schema([
                                                    Forms\Components\Select::make('discount_type')
                                                        ->label('Discount')
                                                        ->options(self::discountTypeOptions())
                                                        ->nullable()
                                                        ->live()
                                                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::refreshLineTotal($set, $get))
                                                        ->columnSpan(3),

                                                    Forms\Components\TextInput::make('discount_value')
                                                        ->label('Disc. Value')
                                                        ->numeric()
                                                        ->nullable()
                                                        ->live()
                                                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::refreshLineTotal($set, $get))
                                                        ->visible(fn (Forms\Get $get) => filled($get('discount_type')))
                                                        ->columnSpan(2),

                                                    Forms\Components\Toggle::make('include_in_total')
                                                        ->label('Add to Total')
                                                        ->default(true)
                                                        ->live()
                                                        ->onIcon('heroicon-m-calculator')
                                                        ->offIcon('heroicon-m-eye')
                                                        ->onColor('success')
                                                        ->offColor('warning')
                                                        ->helperText(fn (Forms\Get $get) => ($get('include_in_total') ?? true)
                                                            ? 'Price included in total'
                                                            : 'Shown only — not counted in total'
                                                        )
                                                        ->columnSpan(3),

                                                    Forms\Components\TextInput::make('notes')
                                                        ->label('Note')
                                                        ->nullable()
                                                        ->columnSpan(4),
                                                ]),

                                        ])
                                        ->columns(1)
                                        ->addActionLabel('Add Line')
                                        ->reorderable()
                                        ->cloneable()
                                        ->collapsible()
                                        ->itemLabel(function (array $state): string {
                                            $description = trim($state['description'] ?? '');
                                            $qty         = (float) ($state['quantity'] ?? 1);
                                            $price       = (float) ($state['unit_price'] ?? 0);
                                            $gross       = round($qty * $price, 2);

                                            $discType  = $state['discount_type'] ?? null;
                                            $discValue = (float) ($state['discount_value'] ?? 0);
                                            $discount  = 0;
                                            if ($discType && $discValue > 0 && $gross > 0) {
                                                $discount = $discType === 'percentage'
                                                    ? round($gross * $discValue / 100, 2)
                                                    : round(min($gross, $discValue), 2);
                                            }
                                            $net = round($gross - $discount, 2);

                                            $title = $description ?: 'New line';

                                            return $net > 0
                                                ? $title . ' · € ' . number_format($net, 2, ',', '.')
                                                : $title;
                                        })
                                        ->columnSpanFull(),
                                ]),
                        ]),  // end Tab: Services & Pricing

                    // ── Tab 5: Discount & Totals ──────────────────────────
                    Forms\Components\Tabs\Tab::make('Discount & Totals')
                        ->icon('heroicon-o-receipt-percent')
                        ->schema([
                            Forms\Components\Section::make('Global Discount')
                                ->schema([
                                    Forms\Components\Select::make('discount_type')
                                        ->label('Global Discount Type')
                                        ->options(self::discountTypeOptions())
                                        ->nullable()
                                        ->live()
                                        ->helperText('Applied on the combined net subtotal (venue + lines).'),

                                    Forms\Components\TextInput::make('discount_value')
                                        ->label('Global Discount Value')
                                        ->numeric()
                                        ->nullable()
                                        ->live()
                                        ->visible(fn (Forms\Get $get) => filled($get('discount_type'))),
                                ])
                                ->columns(2),

                            Forms\Components\Section::make('Price Breakdown')
                                ->schema([
                                    Forms\Components\Placeholder::make('price_breakdown')
                                        ->label('')
                                        ->columnSpanFull()
                                        ->content(function (Forms\Get $get): HtmlString {
                            $p   = self::computePricing($get);
                            $fmt = fn (float $v) => '€&nbsp;' . number_format($v, 2, ',', '.');

                            $row = fn (string $label, float $amount, string $style = '', string $prefix = '') =>
                                "<tr>
                                    <td style='padding:3px 0;color:#374151;{$style}'>{$label}</td>
                                    <td style='text-align:right;padding:3px 0;{$style}'>{$prefix}{$fmt($amount)}</td>
                                </tr>";

                            $html  = '<table style="width:100%;max-width:420px;margin-left:auto;border-collapse:collapse;font-size:13px;">';

                            // Venue block
                            if ($p['venueGross'] > 0) {
                                $html .= $row('Venue rental (net)', $p['venueGross'], 'color:#6b7280;');
                                if ($p['venueDiscount'] > 0) {
                                    $html .= $row('Venue discount', $p['venueDiscount'], 'color:#dc2626;', '− ');
                                    $html .= $row('Venue subtotal', $p['venueNet'], 'font-weight:500;');
                                }
                            }

                            // Lines block (included in total)
                            if ($p['linesGross'] > 0) {
                                $html .= $row('Services (net)', $p['linesGross'], 'color:#6b7280;');
                                if ($p['linesDiscount'] > 0) {
                                    $html .= $row('Item discounts', $p['linesDiscount'], 'color:#dc2626;', '− ');
                                }
                            }

                            // Informational lines block (not counted in total)
                            if ($p['infoGross'] > 0) {
                                $html .= '<tr><td colspan="2" style="border-top:1px dashed #d1d5db;padding-top:6px;"></td></tr>';
                                $html .= "<tr>
                                    <td colspan='2' style='font-size:11px;color:#9ca3af;letter-spacing:0.05em;text-transform:uppercase;padding-bottom:4px;'>
                                        ℹ️ Informational — not included in total
                                    </td>
                                </tr>";
                                $html .= $row('Display-only services', $p['infoGross'], 'color:#9ca3af;font-style:italic;');
                                if ($p['infoDiscount'] > 0) {
                                    $html .= $row('Display-only discounts', $p['infoDiscount'], 'color:#9ca3af;font-style:italic;', '− ');
                                }
                                $html .= '<tr><td colspan="2" style="border-top:1px dashed #d1d5db;padding-top:4px;"></td></tr>';
                            }

                            // Subtotal
                            $html .= '<tr><td colspan="2" style="border-top:1px solid #e5e7eb;padding-top:6px;"></td></tr>';
                            $html .= $row('Net subtotal', $p['subtotalNet'], 'font-weight:600;');

                            // Global discount
                            if ($p['globalDiscount'] > 0) {
                                $label = $p['gType'] === 'percentage'
                                    ? "Discount ({$p['gValue']}%)"
                                    : 'Discount (fixed)';
                                $html .= $row($label, $p['globalDiscount'], 'color:#dc2626;', '− ');
                                $html .= $row('Net after discount', $p['netAfterDisc'], 'font-weight:600;');
                            }

                            // VAT
                            $html .= $row("VAT ({$p['vatRate']}%)", $p['vatAmount'], 'color:#6b7280;');

                            // Gross total
                            $html .= '<tr><td colspan="2" style="border-top:2px solid #111827;padding-top:8px;"></td></tr>';
                            $html .= "<tr>
                                <td style='font-size:15px;font-weight:700;padding:4px 0;'>Total (gross)</td>
                                <td style='text-align:right;font-size:15px;font-weight:700;padding:4px 0;'>{$fmt($p['totalGross'])}</td>
                            </tr>";

                            $html .= '</table>';

                            // Cost per person (informational)
                            $attendees = (int) ($get('attendee_count') ?? 0);
                            if ($attendees > 0 && $p['totalGross'] > 0) {
                                $cpp = round($p['totalGross'] / $attendees, 2);
                                $html .= '<div style="margin-top:12px;padding:8px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:4px;display:flex;justify-content:space-between;align-items:center;">';
                                $html .= "<span style='font-size:11px;color:#1d4ed8;'>Cost per person &nbsp;<span style='color:#93c5fd;font-size:10px;'>({$attendees} attendees)</span></span>";
                                $html .= "<strong style='font-size:13px;color:#1d4ed8;'>{$fmt($cpp)}</strong>";
                                $html .= '</div>';
                            }

                            return new HtmlString($html);
                                        }),
                                ]),
                        ]),  // end Tab: Discount & Totals
                ])
                ->columnSpanFull()
                ->persistTabInQueryString(),

            // ── 6. Sticky summary bar (fixed at bottom of viewport) ───────
            Forms\Components\Placeholder::make('sticky_summary')
                ->label('')
                ->columnSpanFull()
                ->content(function (Forms\Get $get): HtmlString {
                    $p         = self::computePricing($get);
                    $fmt       = fn (float $v) => number_format($v, 2, ',', '.');
                    $attendees = (int) ($get('attendee_count') ?? 0);

                    $nettoStr = $fmt($p['netAfterDisc']);
                    $totalStr = $fmt($p['totalGross']);
                    $vatStr   = $fmt($p['vatAmount']);
                    $vatRate  = (int) $p['vatRate'];

                    $cppHtml = '';
                    if ($attendees > 0 && $p['totalGross'] > 0) {
                        $cpp     = round($p['totalGross'] / $attendees, 2);
                        $cppHtml = '<div style="width:1px;height:36px;background:#e5e7eb;"></div>'
                            . '<div style="display:flex;flex-direction:column;align-items:flex-end;background:#eff6ff;padding:6px 14px;border-radius:6px;border:1px solid #bfdbfe;">'
                            . '<span style="font-size:10px;color:#60a5fa;text-transform:uppercase;letter-spacing:0.06em;font-weight:600;">Per Person&nbsp;(' . $attendees . ')</span>'
                            . '<strong style="font-size:14px;color:#1d4ed8;">&#8364;&nbsp;' . $fmt($cpp) . '&nbsp;<span style="font-size:9px;font-weight:700;background:#bfdbfe;color:#1e40af;padding:1px 4px;border-radius:3px;">B</span></strong>'
                            . '</div>';
                    }

                    $hourlyHtml = '';
                    $venueNet   = $p['venueNet'];
                    $hours      = (float) ($get('hours') ?? 0);
                    if ($venueNet > 0 && $hours > 0) {
                        $perHour    = round($venueNet / $hours, 2);
                        $hoursLabel = number_format($hours, 2, ',', '.');
                        $hourlyHtml = '<div style="width:1px;height:36px;background:#e5e7eb;"></div>'
                            . '<div style="display:flex;flex-direction:column;align-items:flex-end;background:#fefce8;padding:6px 14px;border-radius:6px;border:1px solid #fde68a;">'
                            . '<span style="font-size:10px;color:#ca8a04;text-transform:uppercase;letter-spacing:0.06em;font-weight:600;">Per Hour&nbsp;(' . $hoursLabel . 'h)</span>'
                            . '<strong style="font-size:14px;color:#92400e;">&#8364;&nbsp;' . $fmt($perHour) . '&nbsp;<span style="font-size:9px;font-weight:700;background:#fde68a;color:#78350f;padding:1px 4px;border-radius:3px;">N</span></strong>'
                            . '</div>';
                    }

                    return new HtmlString(
                        '<div x-data x-init="document.body.style.paddingBottom=\'72px\'" style="height:0;overflow:visible;">'
                        . '<div style="position:fixed;bottom:0;left:0;right:0;z-index:9999;background:rgba(255,255,255,0.97);backdrop-filter:blur(6px);border-top:2px solid #e5e7eb;box-shadow:0 -4px 20px rgba(0,0,0,0.07);padding:12px 32px;display:flex;gap:24px;align-items:center;">'
                        . '<span style="font-size:11px;color:#d1d5db;margin-right:auto;letter-spacing:0.04em;">LIVE SUMMARY</span>'

                        // Netto
                        . '<div style="display:flex;flex-direction:column;align-items:flex-end;">'
                        . '<span style="font-size:10px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.06em;font-weight:600;">Netto</span>'
                        . '<strong style="font-size:15px;color:#374151;">&#8364;&nbsp;' . $nettoStr . '</strong>'
                        . '</div>'

                        . '<div style="width:1px;height:36px;background:#e5e7eb;"></div>'

                        // MwSt.
                        . '<div style="display:flex;flex-direction:column;align-items:flex-end;">'
                        . '<span style="font-size:10px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.06em;font-weight:600;">MwSt.&nbsp;' . $vatRate . '%</span>'
                        . '<strong style="font-size:15px;color:#374151;">&#8364;&nbsp;' . $vatStr . '</strong>'
                        . '</div>'

                        . '<div style="width:1px;height:36px;background:#e5e7eb;"></div>'

                        // Total (Brutto)
                        . '<div style="display:flex;flex-direction:column;align-items:flex-end;">'
                        . '<span style="font-size:10px;color:#6b7280;text-transform:uppercase;letter-spacing:0.06em;font-weight:700;">Total (Brutto)</span>'
                        . '<strong style="font-size:18px;color:#111827;">&#8364;&nbsp;' . $totalStr . '</strong>'
                        . '</div>'

                        // Cost per person (conditional)
                        . $cppHtml

                        // Per hour — venue net / hours (conditional)
                        . $hourlyHtml

                        . '</div>'
                        . '</div>'
                    );
                }),
        ]);
    }

    // ══════════════════════════════════════════════
    // Table
    // ══════════════════════════════════════════════

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quote_number')
                    ->label('Quote #')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('customer_display_name')
                    ->label('Customer')
                    ->searchable(['customer_first_name', 'customer_last_name', 'customer_company'])
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('event_date')
                    ->label('Event Date')
                    ->date('d.m.Y')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray'    => 'draft',
                        'info'    => 'sent',
                        'success' => 'accepted',
                        'primary' => 'completed',
                        'danger'  => 'rejected',
                        'warning' => 'expired',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft'     => 'Draft',
                        'sent'      => 'Sent',
                        'accepted'  => 'Accepted',
                        'completed' => 'Completed',
                        'rejected'  => 'Rejected',
                        'expired'   => 'Expired',
                        default     => $state,
                    })
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'danger'  => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
                    ])
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'partial' => 'Partial',
                        'paid'    => 'Paid',
                        default   => 'Unpaid',
                    })
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('organizer_type')
                    ->label('Organizer')
                    ->colors([
                        'info'    => 'business',
                        'warning' => 'private',
                    ])
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'private'  => 'Private',
                        'business' => 'Business',
                        default    => '—',
                    })
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\BadgeColumn::make('event_type')
                    ->label('Event Type')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'meeting'    => 'Meeting',
                        'conference' => 'Conference',
                        'workshop'   => 'Workshop',
                        'seminar'    => 'Seminar',
                        'training'   => 'Training',
                        'birthday'   => 'Birthday',
                        'wedding'    => 'Wedding',
                        'gala'       => 'Gala / Dinner',
                        'team_event' => 'Team Event',
                        'other'      => 'Other',
                        default      => '—',
                    })
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('venue_subtotal')
                    ->label('Venue (net)')
                    ->money('EUR')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('total_gross')
                    ->label('Total (gross)')
                    ->getStateUsing(fn (Quote $record): string => $record->total_gross > 0
                        ? '€ ' . number_format($record->total_gross, 2, ',', '.')
                        : '—'
                    )
                    ->sortable(false),

                Tables\Columns\TextColumn::make('vat_rate')
                    ->label('VAT')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Valid Until')
                    ->date('d.m.Y')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('quoteLines'))
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->multiple()
                    ->options([
                        'draft'     => 'Draft',
                        'sent'      => 'Sent',
                        'accepted'  => 'Accepted',
                        'completed' => 'Completed',
                        'rejected'  => 'Rejected',
                        'expired'   => 'Expired',
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment')
                    ->multiple()
                    ->options([
                        'unpaid'  => 'Unpaid',
                        'partial' => 'Partial',
                        'paid'    => 'Paid',
                    ]),

                Tables\Filters\SelectFilter::make('business_id')
                    ->label('Business / Venue')
                    ->relationship('business', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('organizer_type')
                    ->label('Organizer')
                    ->options([
                        'private'  => 'Private',
                        'business' => 'Business',
                    ]),

                Tables\Filters\SelectFilter::make('event_type')
                    ->label('Event Type')
                    ->multiple()
                    ->options([
                        'meeting'    => 'Meeting',
                        'conference' => 'Conference',
                        'workshop'   => 'Workshop',
                        'seminar'    => 'Seminar',
                        'training'   => 'Training',
                        'birthday'   => 'Birthday',
                        'wedding'    => 'Wedding',
                        'gala'       => 'Gala / Dinner',
                        'team_event' => 'Team Event',
                        'other'      => 'Other',
                    ]),

                Tables\Filters\Filter::make('event_date')
                    ->label('Event Date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'],  fn ($q, $v) => $q->whereDate('event_date', '>=', $v))
                        ->when($data['until'], fn ($q, $v) => $q->whereDate('event_date', '<=', $v))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'])  $indicators[] = Tables\Filters\Indicator::make('Event from ' . Carbon::parse($data['from'])->format('d.m.Y'))->removeField('from');
                        if ($data['until']) $indicators[] = Tables\Filters\Indicator::make('Event until ' . Carbon::parse($data['until'])->format('d.m.Y'))->removeField('until');
                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('preview_pdf')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Quote $record) => route('quotes.preview', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\ActionGroup::make([
                    self::sendEmailAction(),
                    self::downloadPdfAction()->label('PDF'),
                    Tables\Actions\Action::make('duplicate')
                        ->label('Duplicate')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('gray')
                        ->action(function (Quote $record) {
                            $new = $record->replicate(['quote_number']);
                            $new->status       = 'draft';
                            $new->quote_number = null;
                            $new->save();

                            foreach ($record->quoteLines as $line) {
                                $new->quoteLines()->create($line->only([
                                    'order_item_id', 'description', 'unit',
                                    'quantity', 'unit_price',
                                    'discount_type', 'discount_value',
                                    'include_in_total',
                                    'notes',
                                ]));
                            }
                        }),
                    self::lexwareQuotationAction(),
                    self::lexwareDraftInvoiceAction(),
                    self::lexwareInvoiceAction(),
                ])->label('More')->icon('heroicon-m-ellipsis-vertical')->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ── Shared actions (used in both table row and edit page header) ──────────

    public static function downloadPdfAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('download_pdf')
            ->label('Download PDF')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->form([
                Forms\Components\Toggle::make('show_prices')
                    ->label('Show prices in PDF')
                    ->default(true),
                Forms\Components\Select::make('language')
                    ->label('PDF Language')
                    ->options(['en' => 'English', 'de' => 'Deutsch (German)', 'tr' => 'Türkçe (Turkish)'])
                    ->default('en')
                    ->required(),
                Forms\Components\Toggle::make('with_cover')
                    ->label('Include cover & back pages')
                    ->helperText('Merges the Angebot cover and back PDF around the quote.')
                    ->default(false),
                Forms\Components\Toggle::make('show_cost_per_person')
                    ->label('Show cost per person')
                    ->helperText('Displays cost per person on the pricing page (requires attendee count on the quote).')
                    ->default(false),
                Forms\Components\Toggle::make('show_signature')
                    ->label('Include signature block')
                    ->helperText('Adds signature lines for client and Event Hub at the end of the Terms page.')
                    ->default(false),
            ])
            ->modalHeading('Download Quote as PDF')
            ->modalSubmitActionLabel('Download')
            ->action(function (Quote $record, array $data) {
                $content = self::buildQuotePdf($record, $data);

                return response()->streamDownload(
                    fn () => print($content),
                    self::pdfFileName($record)
                );
            });
    }

    public static function sendEmailAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('send_email')
            ->label('Email')
            ->icon('heroicon-o-envelope')
            ->color('info')
            ->form([
                Forms\Components\Select::make('language')
                    ->label('Language')
                    ->options(['de' => 'Deutsch', 'en' => 'English', 'tr' => 'Türkçe'])
                    ->default('de')
                    ->required(),
                Forms\Components\Toggle::make('with_cover')
                    ->label('Include cover pages')
                    ->default(false),
            ])
            ->modalHeading('Send Quote by Email')
            ->modalSubmitActionLabel('Open in Outlook')
            ->action(function (Quote $record, array $data) {
                [$to, $subject, $body] = self::buildEmailParts($record, $data['language']);
                $pdf = self::generateQuotePdf($record, $data['language'], $data['with_cover'] ?? true);
                self::openInOutlook($to, $subject, $body, $pdf);
                register_shutdown_function(fn () => @unlink($pdf));

                Notification::make()
                    ->title('Outlook opened')
                    ->body('Compose window ready with PDF attached.')
                    ->success()
                    ->send();
            });
    }

    public static function lexwareQuotationAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('push_lexware_quotation')
            ->label(fn (Quote $record) => $record->lexware_quotation_id ? 'Lexware (sent)' : 'Lexware Quotation')
            ->icon('heroicon-o-document-text')
            ->color(fn (Quote $record) => $record->lexware_quotation_id ? 'success' : 'primary')
            ->visible(fn (Quote $record) => ! $record->lexware_invoice_id)
            ->disabled(fn (Quote $record) => (bool) $record->lexware_quotation_id)
            ->tooltip(fn (Quote $record) => $record->lexware_quotation_id
                ? 'Sent to Lexware: ' . $record->lexware_quotation_id
                : 'Create a quotation in Lexware Office'
            )
            ->requiresConfirmation()
            ->modalHeading('Send as Lexware Quotation')
            ->modalDescription('This will create a quotation in Lexware Office and link it to this quote.')
            ->action(function (Quote $record) {
                try {
                    $id = app(LexwareService::class)->pushQuotation($record);
                    Notification::make()->title('Quotation created in Lexware')->body("ID: {$id}")->success()->send();
                } catch (\Throwable $e) {
                    Notification::make()->title('Lexware sync failed')->body($e->getMessage())->danger()->send();
                }
            });
    }

    public static function lexwareDraftInvoiceAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('push_lexware_draft_invoice')
            ->label(fn (Quote $record) => $record->lexware_quotation_id ? 'Lexware (sent)' : 'Lexware Draft Invoice')
            ->icon('heroicon-o-paper-airplane')
            ->color(fn (Quote $record) => $record->lexware_quotation_id ? 'success' : 'primary')
            ->visible(fn (Quote $record) => ! $record->lexware_invoice_id)
            ->disabled(fn (Quote $record) => (bool) $record->lexware_quotation_id)
            ->tooltip(fn (Quote $record) => $record->lexware_quotation_id
                ? 'Sent to Lexware: ' . $record->lexware_quotation_id
                : 'Create a draft invoice in Lexware Office'
            )
            ->requiresConfirmation()
            ->modalHeading('Send as Lexware Draft Invoice')
            ->modalDescription('This will create a draft invoice in Lexware Office and link it to this quote.')
            ->action(function (Quote $record) {
                try {
                    $id = app(LexwareService::class)->pushDraftInvoice($record);
                    Notification::make()->title('Draft invoice created in Lexware')->body("ID: {$id}")->success()->send();
                } catch (\Throwable $e) {
                    Notification::make()->title('Lexware sync failed')->body($e->getMessage())->danger()->send();
                }
            });
    }

    public static function lexwareInvoiceAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('push_lexware_invoice')
            ->label(fn (Quote $record) => $record->lexware_invoice_id ? 'Invoice (sent)' : 'Lexware Invoice')
            ->icon('heroicon-o-banknotes')
            ->color(fn (Quote $record) => $record->lexware_invoice_id ? 'success' : 'warning')
            ->disabled(fn (Quote $record) => (bool) $record->lexware_invoice_id)
            ->tooltip(fn (Quote $record) => $record->lexware_invoice_id
                ? 'Already synced: ' . $record->lexware_invoice_id
                : 'Create a finalized invoice in Lexware Office'
            )
            ->requiresConfirmation()
            ->modalHeading('Create Finalized Invoice in Lexware')
            ->modalDescription('This will create a finalized invoice in Lexware Office. This cannot be undone.')
            ->action(function (Quote $record) {
                try {
                    $id = app(LexwareService::class)->pushInvoice($record);
                    Notification::make()->title('Invoice created in Lexware')->body("ID: {$id}")->success()->send();
                } catch (\Throwable $e) {
                    Notification::make()->title('Lexware sync failed')->body($e->getMessage())->danger()->send();
                }
            });
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'edit'   => Pages\EditQuote::route('/{record}/edit'),
        ];
    }
}