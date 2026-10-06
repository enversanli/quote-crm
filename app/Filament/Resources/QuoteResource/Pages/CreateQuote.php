<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Resources\QuoteResource;
use App\Models\OrderItem;
use Filament\Resources\Pages\CreateRecord;

class CreateQuote extends CreateRecord
{
    protected static string $resource = QuoteResource::class;

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $lines = OrderItem::whereHas('itemGroup', fn ($q) => $q->where('name', 'Event Hub - Dienstleistungen'))
            ->get()
            ->map(fn (OrderItem $item) => [
                'order_item_id'    => $item->id,
                'description'      => $item->name,
                'unit'             => $item->unit ?? '',
                'quantity'         => 1,
                'unit_price'       => (float) ($item->price ?? 0),
                'discount_type'    => null,
                'discount_value'   => null,
                'include_in_total' => true,
                'notes'            => null,
            ])
            ->values()
            ->toArray();

        $this->form->fill(['quoteLines' => $lines]);

        $this->callHook('afterFill');
    }

    protected function afterCreate(): void
    {
        // The observer logged the initial status on create; attach the optional comment to it.
        if (filled($comment = $this->data['status_comment'] ?? null)) {
            $this->record->statusChanges()->latest('id')->first()?->update(['comment' => $comment]);
        }

        QuoteResource::notifySameDayBookings($this->record);
    }
}
