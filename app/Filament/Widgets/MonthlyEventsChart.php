<?php

namespace App\Filament\Widgets;

use App\Models\Quote;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyEventsChart extends ChartWidget
{
    protected static ?int    $sort    = 3;
    protected static ?string $heading = 'Events per Month';
    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $months  = collect();
        $labels  = [];
        $counts  = [];
        $revenue = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months->push($month);
            $labels[] = $month->format('M Y');
        }

        for ($i = 0; $i <= 5; $i++) {
            $month = Carbon::now()->addMonths($i);
            if ($i === 0) continue; // current month already added above
            $months->push($month);
            $labels[] = $month->format('M Y');
        }

        // Re-build: 6 past months (including current) + 5 future months
        $labels  = [];
        $counts  = [];
        $revenue = [];

        for ($i = 5; $i >= 0; $i--) {
            $month    = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $counts[] = Quote::whereNotNull('event_date')
                ->whereYear('event_date', $month->year)
                ->whereMonth('event_date', $month->month)
                ->whereNotIn('status', ['rejected', 'expired'])
                ->count();

            $revenue[] = Quote::where('status', 'accepted')
                ->whereYear('event_date', $month->year)
                ->whereMonth('event_date', $month->month)
                ->get()
                ->sum(fn ($q) => (float) $q->total_gross);
        }

        for ($i = 1; $i <= 5; $i++) {
            $month    = Carbon::now()->addMonths($i);
            $labels[] = $month->format('M Y');

            $counts[] = Quote::whereNotNull('event_date')
                ->whereYear('event_date', $month->year)
                ->whereMonth('event_date', $month->month)
                ->whereNotIn('status', ['rejected', 'expired'])
                ->count();

            $revenue[] = null; // no revenue for future months
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Events',
                    'data'            => $counts,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'borderColor'     => 'rgba(245, 158, 11, 0.9)',
                    'borderWidth'     => 2,
                    'borderRadius'    => 4,
                    'type'            => 'bar',
                    'yAxisID'         => 'y',
                ],
            ],
            'labels' => $labels,
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
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => ['stepSize' => 1, 'precision' => 0],
                    'grid'        => ['color' => 'rgba(0,0,0,0.04)'],
                ],
                'x' => [
                    'grid' => ['display' => false],
                ],
            ],
        ];
    }
}
