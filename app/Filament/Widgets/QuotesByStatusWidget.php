<?php

namespace App\Filament\Widgets;

use App\Models\Quote;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class QuotesByStatusWidget extends ChartWidget
{
    protected static ?string $heading  = 'Quotes by Status';
    protected static ?int    $sort     = 3;
    protected static ?string $maxHeight = '240px';
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $rows = Quote::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statuses = [
            'draft'     => ['label' => 'Draft',     'color' => '#9ca3af'],
            'sent'      => ['label' => 'Sent',      'color' => '#60a5fa'],
            'accepted'  => ['label' => 'Accepted',  'color' => '#34d399'],
            'completed' => ['label' => 'Completed', 'color' => '#818cf8'],
            'rejected'  => ['label' => 'Rejected',  'color' => '#f87171'],
            'expired'   => ['label' => 'Expired',   'color' => '#fbbf24'],
        ];

        $counts = array_map(fn ($s) => $rows->get($s, 0), array_keys($statuses));
        $labels = array_column($statuses, 'label');
        $colors = array_column($statuses, 'color');

        return [
            'datasets' => [[
                'data'            => $counts,
                'backgroundColor' => $colors,
                'borderWidth'     => 0,
                'hoverOffset'     => 6,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'right'],
            ],
            'cutout' => '65%',
        ];
    }
}
