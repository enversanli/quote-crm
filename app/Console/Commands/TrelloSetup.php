<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TrelloSetup extends Command
{
    protected $signature   = 'trello:setup';
    protected $description = 'Browse your Trello boards and list IDs to configure .env';

    private const BASE = 'https://api.trello.com/1';

    public function handle(): int
    {
        $key   = config('trello.key');
        $token = config('trello.token');

        if (! $key || ! $token) {
            $this->error('TRELLO_API_KEY and TRELLO_TOKEN must be set in .env first.');
            $this->line('Get them at: https://trello.com/power-ups/admin');
            return self::FAILURE;
        }

        $auth = ['key' => $key, 'token' => $token];

        // ── Fetch boards ──────────────────────────────────────────────
        $this->info('Fetching your Trello boards…');

        $response = Http::get(self::BASE . '/members/me/boards', array_merge($auth, [
            'fields' => 'id,name,url',
            'filter' => 'open',
        ]));

        if ($response->failed()) {
            $this->error('Trello API returned HTTP ' . $response->status() . ': ' . $response->body());
            return self::FAILURE;
        }

        $boards = $response->json();

        if (empty($boards)) {
            $this->warn('No open boards found for this account.');
            return self::SUCCESS;
        }

        $this->table(['#', 'Board ID', 'Name'], array_map(
            fn ($i, $b) => [$i + 1, $b['id'], $b['name']],
            array_keys($boards),
            $boards
        ));

        // ── Select a board ────────────────────────────────────────────
        $choice = $this->ask('Enter the board number to see its lists (or press Enter to show all)');

        if ($choice === null || $choice === '') {
            $selected = $boards;
        } elseif (is_numeric($choice) && isset($boards[(int) $choice - 1])) {
            $selected = [$boards[(int) $choice - 1]];
        } else {
            $this->error('Invalid selection.');
            return self::FAILURE;
        }

        // ── Fetch & display lists for each selected board ─────────────
        foreach ($selected as $board) {
            $this->newLine();
            $this->line("<fg=cyan>Board: {$board['name']}</>");

            $listsResponse = Http::get(self::BASE . "/boards/{$board['id']}/lists", array_merge($auth, [
                'fields' => 'id,name',
                'filter' => 'open',
            ]));

            if ($listsResponse->failed()) {
                $this->warn("  Could not fetch lists for board {$board['name']}.");
                continue;
            }

            $lists = $listsResponse->json();

            if (empty($lists)) {
                $this->warn('  No lists found.');
                continue;
            }

            $this->table(['List ID', 'Name'], array_map(
                fn ($l) => [$l['id'], $l['name']],
                $lists
            ));
        }

        // ── Print .env snippet ────────────────────────────────────────
        $this->newLine();
        $this->line('<fg=yellow>Copy the relevant IDs into your .env:</>');
        $this->line('  TRELLO_LIST_DRAFT=');
        $this->line('  TRELLO_LIST_SENT=');
        $this->line('  TRELLO_LIST_ACCEPTED=');
        $this->line('  TRELLO_LIST_REJECTED=');
        $this->line('  TRELLO_LIST_EXPIRED=');

        return self::SUCCESS;
    }
}
