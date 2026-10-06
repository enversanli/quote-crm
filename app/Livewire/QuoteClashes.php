<?php

namespace App\Livewire;

use App\Models\Quote;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Topbar indicator: upcoming days where one venue has several sent/accepted quotes.
 * Rendered next to the user menu by ManagerPanelProvider.
 */
class QuoteClashes extends Component
{
    #[On('quotes-changed')]
    public function refresh(): void
    {
        // Re-render only.
    }

    public function render()
    {
        $clashes = Quote::upcomingClashes();

        return view('livewire.quote-clashes', [
            'clashes'    => $clashes,
            'quoteCount' => $clashes->sum(fn ($group) => $group->count()),
        ]);
    }
}
