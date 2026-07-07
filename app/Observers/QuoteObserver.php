<?php

namespace App\Observers;

use App\Models\Quote;
use App\Services\TrelloService;

class QuoteObserver
{
    public function __construct(private TrelloService $trello) {}

    public function created(Quote $quote): void
    {
        if (! $this->trello->isConfigured()) {
            return;
        }

        $cardId = $this->trello->createCard($quote);

        if ($cardId) {
            $quote->updateQuietly(['trello_card_id' => $cardId]);
        }
    }

    public function updated(Quote $quote): void
    {
        if (! $this->trello->isConfigured() || ! $quote->trello_card_id) {
            return;
        }

        $cardId = $quote->trello_card_id;

        if ($quote->wasChanged('status')) {
            $this->trello->moveCard($cardId, $quote->status);
        }

        if ($quote->wasChanged('payment_status')) {
            $comment = match ($quote->payment_status) {
                'partial' => '💳 Partial payment received.',
                'paid'    => '✅ Payment completed — fully paid.',
                default   => 'ℹ️ Payment status reset to Unpaid.',
            };
            $this->trello->addComment($cardId, $comment);

            if ($quote->payment_status === 'paid') {
                $this->trello->addCompletedLabel($cardId);
            } else {
                $this->trello->removeCompletedLabel($cardId);
            }
        }

        if ($quote->wasChanged(['status', 'payment_status', 'customer_first_name', 'customer_last_name',
            'customer_company', 'event_date', 'hours', 'hourly_rate', 'venue_subtotal'])) {
            $this->trello->updateCard($cardId, $quote);
        }
    }
}
