<?php

namespace App\Observers;

use App\Models\Quote;
use App\Services\TrelloService;

class QuoteObserver
{
    public function __construct(private TrelloService $trello) {}

    public function created(Quote $quote): void
    {
        $this->logStatusChange($quote, null);

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
        if ($quote->wasChanged('status')) {
            $this->logStatusChange($quote, $quote->getOriginal('status'));
        }

        if (! $this->trello->isConfigured() || ! $quote->trello_card_id) {
            return;
        }

        $cardId = $quote->trello_card_id;

        if ($quote->wasChanged('status')) {
            $this->trello->moveCard($cardId, $quote->status);
        }

        if ($quote->wasChanged('payment_status')) {
            $comment = match ($quote->payment_status) {
                'invoice_sent' => '📨 Invoice sent to customer.',
                'partial'      => '💳 Partial payment received.',
                'paid'         => '✅ Payment completed — fully paid.',
                default        => 'ℹ️ Payment status reset to Unpaid.',
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

    /** One quote_status_changes row per status step, carrying the optional comment from the form. */
    private function logStatusChange(Quote $quote, ?string $fromStatus): void
    {
        $quote->statusChanges()->create([
            'from_status' => $fromStatus,
            'to_status'   => $quote->status ?? 'draft',
            'comment'     => filled($quote->statusChangeComment) ? $quote->statusChangeComment : null,
            'user_id'     => auth()->id(),
        ]);

        $quote->statusChangeComment = null;
    }
}
