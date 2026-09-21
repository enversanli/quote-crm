---
description: Insert a HubSpot contact into the Leads table (name/email/phone only)
---

# hubspot-lead

Creates one row in the `leads` table from HubSpot contact details that were already collected elsewhere (usually by the `eventhub-hubspot-lead-sync` Cowork skill, which queries HubSpot and sends the contact's name/email/phone here). This command does **not** talk to HubSpot itself — it only writes the local Lead record.

## Input

Arguments are `name`, `email`, `phone` (phone may be blank). Example invocation:

```
/hubspot-lead "Erik Becker" "erik.becker@german-u15.de" "+49 30 1234567"
```

If any argument is missing, ask the user for it before proceeding — do not guess or fabricate a name/email/phone.

## What this does NOT do

- It does not query HubSpot. That happens in the Cowork session, which has the HubSpot connector.
- It does not create a Quote, only a Lead row (`leads` table — the prospecting-style table with name/email/phone/category/rating/website/etc; only name/email/phone are populated here, per how this project is currently set up).
- It does not touch any other table.

## Steps

1. **Dedupe check.** Before inserting, check whether a lead with this email already exists:

```bash
php artisan tinker --execute="
\$existing = \App\Models\Lead::where('email', '<email>')->first();
if (\$existing) {
    echo 'DUPLICATE: Lead #' . \$existing->id . ' (' . \$existing->name . ') already exists with this email.' . PHP_EOL;
} else {
    echo 'NO_DUPLICATE' . PHP_EOL;
}
"
```

   If a duplicate is found, stop and report that to the user — do not create a second row.

2. **Create the lead**, only if step 1 returned `NO_DUPLICATE`:

```bash
php artisan tinker --execute="
\$lead = \App\Models\Lead::create([
    'name'  => '<name>',
    'email' => '<email>',
    'phone' => '<phone>',
]);
echo 'CREATED: Lead #' . \$lead->id . PHP_EOL;
"
```

3. **Report back** the outcome plainly: either the new Lead ID and the name/email/phone that were saved, or the duplicate that was found and skipped. Do not claim success unless the tinker output actually printed `CREATED:` or `DUPLICATE:`.

## Requirements

- Must run in a real local terminal/session with PHP available (`php artisan tinker`) inside this project — a cloud/bridge session without PHP cannot run this.
