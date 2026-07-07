<?php

namespace App\Console\Commands;

use App\Models\Quote;
use App\Services\TrelloService;
use Illuminate\Console\Command;

class TrelloSync extends Command
{
    protected $signature   = 'trello:sync {--all : Sync all quotes without a Trello card}';
    protected $description = 'Push existing quotes to Trello';

    public function handle(TrelloService $trello): int
    {
        if (! $trello->isConfigured()) {
            $this->error('Trello is not configured. Set TRELLO_API_KEY and TRELLO_TOKEN in .env');
            return self::FAILURE;
        }

        $pending = Quote::whereNull('trello_card_id')
            ->orderBy('created_at')
            ->get();

        if ($pending->isEmpty()) {
            $this->info('All quotes already have a Trello card.');
            return self::SUCCESS;
        }

        if ($this->option('all')) {
            $selected = $pending;
        } else {
            $rows = $pending->map(fn ($q) => [
                $q->id,
                $q->quote_number ?? '—',
                $q->customer_display_name,
                $q->status,
                $q->event_date?->format('d.m.Y') ?? '—',
            ])->toArray();

            $this->table(['ID', 'Quote #', 'Customer', 'Status', 'Event Date'], $rows);

            $input = $this->ask('Enter quote IDs to sync (comma-separated), or "all" to sync everything');

            if (strtolower(trim($input)) === 'all') {
                $selected = $pending;
            } else {
                $ids      = array_map('trim', explode(',', $input));
                $selected = $pending->whereIn('id', $ids);

                if ($selected->isEmpty()) {
                    $this->warn('No matching quotes found for the given IDs.');
                    return self::FAILURE;
                }
            }
        }

        $this->info("Syncing {$selected->count()} quote(s)…");
        $bar = $this->output->createProgressBar($selected->count());
        $bar->start();

        $success = 0;
        foreach ($selected as $quote) {
            $cardId = $trello->createCard($quote);
            if ($cardId) {
                $quote->updateQuietly(['trello_card_id' => $cardId]);
                $success++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. {$success}/{$selected->count()} cards created.");

        return self::SUCCESS;
    }
}
