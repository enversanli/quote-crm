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
- Event type (meeting, conference, workshop, seminar, training, birthday, wedding, gala, team_event, other)
- Organizer type (private / business)
- Attendee count
- Status (default: draft)
- Notes

## Steps

1. Collect all details from the user.
2. Look up or create the Customer record via tinker if an email is provided (use `firstOrCreate`).
3. Look up the Business record by name if a venue was mentioned.
4. Calculate `hours` from start/end time if both are given (decimal, e.g. 2.5).
5. Run `php artisan tinker --execute="..."` to create the Quote (and Customer if needed).
6. Print the generated quote number and a link: `http://localhost/manager/quotes/{id}/edit`

## Tinker template

```php
$customer = null;
// If email given:
$customer = \App\Models\Customer::firstOrCreate(
    ['email' => 'EMAIL'],
    ['first_name' => 'FIRST', 'last_name' => 'LAST', 'company_name' => 'COMPANY', 'phone' => 'PHONE', 'country' => 'DE']
);

$business = \App\Models\Business::where('name', 'like', '%VENUE%')->first();

$quote = \App\Models\Quote::create([
    'customer_id'         => $customer?->id,
    'customer_first_name' => 'FIRST',
    'customer_last_name'  => 'LAST',
    'customer_company'    => 'COMPANY',
    'customer_email'      => 'EMAIL',
    'customer_phone'      => 'PHONE',
    'business_id'         => $business?->id,
    'event_date'          => 'YYYY-MM-DD',
    'event_start_time'    => 'HH:MM:SS',   // or null
    'event_end_time'      => 'HH:MM:SS',   // or null
    'hours'               => HOURS,         // decimal or null
    'hourly_rate'         => 117.00,
    'attendee_count'      => COUNT,         // or null
    'organizer_type'      => 'private',     // or 'business' or null
    'event_type'          => 'TYPE',        // or null
    'status'              => 'draft',
    'payment_status'      => 'unpaid',
    'vat_rate'            => 19,
    'notes'               => 'NOTES',       // or null
]);

echo $quote->quote_number . ' — ID: ' . $quote->id;
```

After running, confirm the quote was created and show the quote number + edit URL.
