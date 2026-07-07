<?php

namespace App\Filament\Widgets;

use App\Models\Quote;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading    = 'Revenue — Last 6 Months';
    protected static ?int    $sort        = 2;
    protected static ?string $maxHeight  = '240px';
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));
        $start  = now()->subMonths(5)->startOfMonth();

        $accepted = Quote::whereIn('status', ['accepted', 'completed'])
            ->whereNotNull('event_date')
            ->where('event_date', '>=', $start)
            ->with('quoteLines')
            ->get()
            ->groupBy(fn ($q) => $q->event_date->format('Y-m'));

        $collected = Quote::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->whereNotNull('event_date')
            ->where('event_date', '>=', $start)
            ->with('quoteLines')
            ->get()
            ->groupBy(fn ($q) => $q->event_date->format('Y-m'));

        $acceptedData = $months->map(fn ($m) =>
            round($accepted->get($m->format('Y-m'), collect())->sum(fn ($q) => $q->total_gross), 2)
        )->values()->toArray();

        $collectedData = $months->map(fn ($m) =>
            round($collected->get($m->format('Y-m'), collect())->sum(fn ($q) => $q->total_gross), 2)
        )->values()->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Accepted',
                    'data'            => $acceptedData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.75)',
                    'borderColor'     => 'rgb(16, 185, 129)',
                    'borderWidth'     => 1,
                    'borderRadius'    => 4,
                ],
                [
                    'label'           => 'Collected (paid)',
                    'data'            => $collectedData,
                    'backgroundColor' => 'rgba(99, 102, 241, 0.75)',
                    'borderColor'     => 'rgb(99, 102, 241)',
                    'borderWidth'     => 1,
                    'borderRadius'    => 4,
                ],
            ],
            'labels' => $months->map(fn ($m) => $m->format('M Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'top'],
            ],
            'scales' => [
                'y' => [
                    'ticks' => [
                        'callback' => "function(v){ return '€ ' + v.toLocaleString('de-DE'); }",
                    ],
                ],
            ],
        ];
    }
}
