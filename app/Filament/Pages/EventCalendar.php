<?php

namespace App\Filament\Pages;

use App\Models\Quote;
use Filament\Pages\Page;

class EventCalendar extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Event Calendar';
    protected static ?string $title           = 'Event Calendar';
    protected static ?int    $navigationSort  = 0;
    protected static string  $view            = 'filament.pages.event-calendar';

    public ?string $statusFilter = null;
    public ?string $dateFilter   = null;
    public ?string $monthFilter  = null;

    public function setFilter(?string $status): void
    {
        $this->statusFilter = $status;
    }

    public function clearDateFilter(): void
    {
        $this->dateFilter = null;
    }

    public function clearMonthFilter(): void
    {
        $this->monthFilter = null;
    }

    public function getViewData(): array
    {
        $quotes = Quote::query()
            ->whereNotNull('event_date')
            ->when($this->statusFilter, fn ($q) => $this->statusFilter === 'accepted'
                ? $q->whereIn('status', ['accepted', 'completed'])
                : $q->where('status', $this->statusFilter)
            )
            ->when($this->dateFilter,   fn ($q) => $q->whereDate('event_date', $this->dateFilter))
            ->when($this->monthFilter,  fn ($q) => $q->whereYear('event_date', substr($this->monthFilter, 0, 4))
                                                      ->whereMonth('event_date', substr($this->monthFilter, 5, 2)))
            ->with(['customer', 'business'])
            ->orderBy('event_date')
            ->get();

        $today = now()->startOfDay();

        // Split into upcoming and past, group each by "YYYY-MM" for month headers
        $upcoming = $quotes->filter(fn ($q) => $q->event_date->gte($today))
            ->groupBy(fn ($q) => $q->event_date->format('Y-m'));

        $past = $quotes->filter(fn ($q) => $q->event_date->lt($today))
            ->sortByDesc('event_date')
            ->groupBy(fn ($q) => $q->event_date->format('Y-m'));

        return compact('upcoming', 'past', 'today');
    }
}
