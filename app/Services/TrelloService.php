<?php

namespace App\Services;

use App\Models\Quote;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrelloService
{
    private const BASE = 'https://api.trello.com/1';

    private string $key;
    private string $token;

    public function __construct()
    {
        $this->key   = config('trello.key', '');
        $this->token = config('trello.token', '');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->key) && ! empty($this->token);
    }

    /** Create a card for a new quote; returns the Trello card ID or null on failure. */
    public function createCard(Quote $quote): ?string
    {
        $listId = config('trello.lists.' . ($quote->status ?? 'draft'));

        if (! $listId) {
            return null;
        }

        $response = Http::post(self::BASE . '/cards', $this->payload([
            'idList' => $listId,
            'name'   => $this->cardName($quote),
            'desc'   => $this->cardDescription($quote),
            'due'    => $quote->event_date?->toIso8601String(),
        ]));

        if ($response->failed()) {
            Log::error('Trello createCard failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        return $response->json('id');
    }

    /** Move an existing card to the list that matches the given quote status. */
    public function moveCard(string $cardId, string $status): void
    {
        $listId = config('trello.lists.' . $status);

        if (! $listId) {
            return;
        }

        $response = Http::put(self::BASE . "/cards/{$cardId}", $this->payload([
            'idList' => $listId,
        ]));

        if ($response->failed()) {
            Log::error('Trello moveCard failed', ['cardId' => $cardId, 'status' => $response->status()]);
        }
    }

    /** Refresh the card name and description from the current quote state. */
    public function updateCard(string $cardId, Quote $quote): void
    {
        $response = Http::put(self::BASE . "/cards/{$cardId}", $this->payload([
            'name' => $this->cardName($quote),
            'desc' => $this->cardDescription($quote),
            'due'  => $quote->event_date?->toIso8601String(),
        ]));

        if ($response->failed()) {
            Log::error('Trello updateCard failed', ['cardId' => $cardId, 'status' => $response->status()]);
        }
    }

    /** Add the "Completed" label to a card, creating it on the board if needed. */
    public function addCompletedLabel(string $cardId): void
    {
        $labelId = $this->resolveCompletedLabel();

        if (! $labelId) {
            return;
        }

        Http::post(self::BASE . "/cards/{$cardId}/idLabels", $this->payload([
            'value' => $labelId,
        ]));
    }

    /** Remove the "Completed" label from a card. */
    public function removeCompletedLabel(string $cardId): void
    {
        $labelId = $this->resolveCompletedLabel();

        if (! $labelId) {
            return;
        }

        Http::delete(self::BASE . "/cards/{$cardId}/idLabels/{$labelId}", $this->payload([]));
    }

    /** Find the "Completed" label on the board, or create it if missing. */
    private function resolveCompletedLabel(): ?string
    {
        $boardId = config('trello.board_id');

        if (! $boardId) {
            return null;
        }

        $response = Http::get(self::BASE . "/boards/{$boardId}/labels", $this->payload([]));

        if ($response->failed()) {
            Log::error('Trello fetchLabels failed', ['status' => $response->status()]);
            return null;
        }

        $existing = collect($response->json())
            ->firstWhere('name', 'Completed');

        if ($existing) {
            return $existing['id'];
        }

        $created = Http::post(self::BASE . "/boards/{$boardId}/labels", $this->payload([
            'name'  => 'Completed',
            'color' => 'green',
        ]));

        if ($created->failed()) {
            Log::error('Trello createLabel failed', ['status' => $created->status()]);
            return null;
        }

        return $created->json('id');
    }

    /** Post a comment to the card. */
    public function addComment(string $cardId, string $text): void
    {
        $response = Http::post(self::BASE . "/cards/{$cardId}/actions/comments", $this->payload([
            'text' => $text,
        ]));

        if ($response->failed()) {
            Log::error('Trello addComment failed', ['cardId' => $cardId, 'status' => $response->status()]);
        }
    }

    // ──────────────────────────────────────────────

    private function payload(array $data): array
    {
        return array_merge(['key' => $this->key, 'token' => $this->token], $data);
    }

    private function cardName(Quote $quote): string
    {
        $parts = array_filter([
            $quote->customer_display_name !== '—' ? $quote->customer_display_name : null,
            $quote->event_date?->format('d.m.Y'),
            $quote->quote_number,
        ]);

        return implode(' · ', $parts) ?: ($quote->quote_number ?? 'New Quote');
    }

    private function cardDescription(Quote $quote): string
    {
        $quote->loadMissing(['customer', 'business', 'quoteLines']);

        $lines = [
            '**Quote:** '      . ($quote->quote_number ?? '—'),
            '**Customer:** '   . $quote->customer_display_name,
            '**Venue:** '      . ($quote->business?->name ?? '—'),
            '**Event Date:** ' . ($quote->event_date?->format('d.m.Y') ?? '—'),
            '**Total (gross):** €' . number_format((float) $quote->total_gross, 2, ',', '.'),
            '**Payment:** '    . $this->paymentLabel($quote->payment_status),
        ];

        $included = $quote->quoteLines->filter(fn ($l) => (bool) $l->include_in_total);

        if ($included->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '---';
            $lines[] = '';
            $lines[] = '**Services:**';

            foreach ($included as $line) {
                $qty     = (float) $line->quantity;
                $price   = (float) $line->unit_price;
                $total   = (float) $line->line_total;
                $lines[] = '• ' . $line->description
                    . ' — ' . number_format($qty, 0, ',', '.')
                    . ' × €' . number_format($price, 2, ',', '.')
                    . ' = €' . number_format($total, 2, ',', '.');
            }
        }

        return implode("\n", $lines);
    }

    private function paymentLabel(?string $status): string
    {
        return match ($status) {
            'partial' => 'Partial',
            'paid'    => 'Paid ✓',
            default   => 'Unpaid',
        };
    }
}
