<?php

namespace App\Observers;

use App\Models\Quote;
use App\Models\QuoteLine;
use App\Services\TrelloService;

class QuoteLineObserver
{
    public function __construct(private TrelloService $trello) {}

    public function saved(QuoteLine $line): void
    {
        $this->syncCard($line);
    }

    public function deleted(QuoteLine $line): void
    {
        $this->syncCard($line);
    }

    private function syncCard(QuoteLine $line): void
    {
        if (! $this->trello->isConfigured()) {
            return;
        }

        $quote = Quote::find($line->quote_id);

        if (! $quote || ! $quote->trello_card_id) {
            return;
        }

        $this->trello->updateCard($quote->trello_card_id, $quote);
    }
}
