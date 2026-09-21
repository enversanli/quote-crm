<?php

namespace App\Filament\Resources\QuoteResource\Widgets;

use App\Filament\Resources\QuoteResource\Pages\ListQuotes;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QuoteStatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListQuotes::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();

        $accepted      = (clone $query)->whereIn('status', ['accepted', 'completed'])->get();
        $acceptedCount = $accepted->count();
        $acceptedTotal = $accepted->sum(fn ($q) => (float) $q->total_gross);

        $paidTotal = (clone $query)
            ->where('payment_status', 'paid')
            ->get()
            ->sum(fn ($q) => (float) $q->total_gross);

        return [
            Stat::make('Accepted Quotes', $acceptedCount)
                ->description('Accepted & completed quotes')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Paid', '€ ' . number_format($paidTotal, 2, ',', '.'))
                ->description('Quotes marked as paid')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Total Accepted Value', '€ ' . number_format($acceptedTotal, 2, ',', '.'))
                ->description('Sum of accepted & completed quotes')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('info'),
        ];
    }
}
