<?php

namespace App\Filament\Pages;

use App\Models\Quote;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class Statistics extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Statistics';
    protected static ?string $title           = 'Statistics';
    protected static ?int    $navigationSort  = 1;
    protected static string  $view            = 'filament.pages.statistics';

    public int $year;

    public function mount(): void
    {
        $this->year = now()->year;
    }

    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    public function getAvailableYears(): array
    {
        $years = Quote::whereNotNull('event_date')
            ->selectRaw("CAST(strftime('%Y', event_date) AS INTEGER) as year")
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($y) => (int) $y)
            ->toArray();

        if (! in_array(now()->year, $years)) {
            array_unshift($years, now()->year);
        }

        return $years;
    }

    public function getViewData(): array
    {
        $y = $this->year;

        // ── Revenue base: accepted + completed quotes with event_date in year ──
        $revenueQuotes = Quote::whereIn('status', ['accepted', 'completed'])
            ->whereYear('event_date', $y)
            ->get();

        $totalRevenue   = $revenueQuotes->sum(fn ($q) => (float) $q->total_gross);
        $totalEvents    = $revenueQuotes->count();
        $avgRevenue     = $totalEvents > 0 ? $totalRevenue / $totalEvents : 0;

        // ── Conversion rate ──
        $totalQuotes    = Quote::whereYear('created_at', $y)->count();
        $acceptedCount  = Quote::whereIn('status', ['accepted', 'completed'])
            ->whereYear('created_at', $y)->count();
        $conversionRate = $totalQuotes > 0 ? round(($acceptedCount / $totalQuotes) * 100, 1) : 0;

        // ── Open pipeline value ──
        $pipelineQuotes = Quote::whereIn('status', ['draft', 'sent'])->get();
        $pipelineValue  = $pipelineQuotes->sum(fn ($q) => (float) $q->total_gross);
        $pipelineCount  = $pipelineQuotes->count();

        // ── Monthly revenue (12 months) ──
        $monthlyRevenue = [];
        $monthlyEvents  = [];
        $monthLabels    = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthLabels[] = Carbon::create($y, $m, 1)->format('M');
            $mq = Quote::whereIn('status', ['accepted', 'completed'])
                ->whereYear('event_date', $y)
                ->whereMonth('event_date', $m)
                ->get();
            $monthlyRevenue[] = round($mq->sum(fn ($q) => (float) $q->total_gross), 2);
            $monthlyEvents[]  = $mq->count();
        }

        // ── Quote status breakdown (all quotes this year) ──
        $statusCounts = Quote::whereYear('created_at', $y)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusOrder  = ['accepted', 'completed', 'sent', 'draft', 'rejected', 'expired'];
        $statusColors = [
            'accepted'  => '#10b981',
            'completed' => '#059669',
            'sent'      => '#3b82f6',
            'draft'     => '#9ca3af',
            'rejected'  => '#ef4444',
            'expired'   => '#f59e0b',
        ];

        // ── Event type breakdown ──
        $eventTypes = Quote::whereNotNull('event_type')
            ->whereYear('event_date', $y)
            ->selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->orderByDesc('count')
            ->pluck('count', 'event_type')
            ->toArray();

        // ── Organizer type split ──
        $organizerTypes = Quote::whereNotNull('organizer_type')
            ->whereYear('event_date', $y)
            ->selectRaw('organizer_type, COUNT(*) as count')
            ->groupBy('organizer_type')
            ->pluck('count', 'organizer_type')
            ->toArray();

        $totalOrganizers = array_sum($organizerTypes);

        // ── Top customers by revenue ──
        $topCustomers = Quote::whereIn('status', ['accepted', 'completed'])
            ->whereYear('event_date', $y)
            ->with('customer')
            ->get()
            ->groupBy(fn ($q) => $q->customer?->id ?? 'anon_' . $q->id)
            ->map(fn ($quotes) => [
                'name'    => $quotes->first()->customer?->full_name
                             ?? trim(($quotes->first()->customer_first_name ?? '') . ' ' . ($quotes->first()->customer_last_name ?? ''))
                             ?: ($quotes->first()->customer_company ?? '—'),
                'events'  => $quotes->count(),
                'revenue' => $quotes->sum(fn ($q) => (float) $q->total_gross),
            ])
            ->sortByDesc('revenue')
            ->take(5)
            ->values();

        return compact(
            'totalRevenue', 'totalEvents', 'avgRevenue',
            'conversionRate', 'pipelineValue', 'pipelineCount',
            'totalQuotes', 'acceptedCount',
            'monthlyRevenue', 'monthlyEvents', 'monthLabels',
            'statusCounts', 'statusOrder', 'statusColors',
            'eventTypes',
            'organizerTypes', 'totalOrganizers',
            'topCustomers',
        );
    }
}
