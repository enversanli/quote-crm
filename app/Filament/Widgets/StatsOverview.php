<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\Quote;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now()->startOfDay();

        $upcomingEvents = Quote::whereNotNull('event_date')
            ->whereDate('event_date', '>=', $today)
            ->whereNotIn('status', ['rejected', 'expired'])
            ->count();

        $openQuotes = Quote::whereIn('status', ['draft', 'sent'])->count();

        // Sparkline data: last 7 months of accepted count
        $acceptedSparkline = collect(range(6, 0))
            ->map(fn ($i) => Quote::where('status', 'accepted')
                ->whereMonth('created_at', now()->subMonths($i)->month)
                ->whereYear('created_at', now()->subMonths($i)->year)
                ->count()
            )->values()->toArray();

        // Sparkline data: last 7 months of collected (paid) count
        $collectedSparkline = collect(range(6, 0))
            ->map(fn ($i) => Quote::where('status', 'completed')
                ->where('payment_status', 'paid')
                ->whereMonth('updated_at', now()->subMonths($i)->month)
                ->whereYear('updated_at', now()->subMonths($i)->year)
                ->count()
            )->values()->toArray();

        $acceptedThisMonth = Quote::where('status', 'accepted')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get();

        $acceptedRevenue      = $acceptedThisMonth->sum(fn ($q) => $q->total_gross);
        $acceptedCount        = $acceptedThisMonth->count();

        $acceptedLastMonthCount = Quote::where('status', 'accepted')
            ->whereMonth('created_at', now()->subMonthNoOverflow()->month)
            ->whereYear('created_at', now()->subMonthNoOverflow()->year)
            ->count();

        $acceptedTrend = $acceptedCount >= $acceptedLastMonthCount ? 'up' : 'down';

        // All-time outstanding: completed but not fully paid (accounts receivable)
        $outstandingQuotes  = Quote::where('status', 'completed')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->get();
        $outstandingRevenue = $outstandingQuotes->sum(fn ($q) => $q->total_gross);
        $outstandingCount   = $outstandingQuotes->count();

        // Cash collected this month: completed + paid
        $collectedThisMonth = Quote::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->get();
        $collectedRevenue   = $collectedThisMonth->sum(fn ($q) => $q->total_gross);
        $collectedCount     = $collectedThisMonth->count();

        $newLeadsThisMonth = Lead::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            Stat::make('Upcoming Events', $upcomingEvents)
                ->description('Events scheduled from today')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Open Quotes', $openQuotes)
                ->description('Draft & sent, awaiting response')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),

            Stat::make('Accepted This Month', '€ ' . number_format($acceptedRevenue, 2, ',', '.'))
                ->description($acceptedCount . ' quote' . ($acceptedCount === 1 ? '' : 's') . ' · ' . ($acceptedTrend === 'up' ? '↑' : '↓') . ' vs last month')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($acceptedTrend === 'up' ? 'success' : 'gray')
                ->chart($acceptedSparkline),

            Stat::make('Outstanding (not paid)', '€ ' . number_format($outstandingRevenue, 2, ',', '.'))
                ->description($outstandingCount . ' completed quote' . ($outstandingCount === 1 ? '' : 's') . ' awaiting payment')
                ->descriptionIcon('heroicon-m-clock')
                ->color($outstandingRevenue > 0 ? 'warning' : 'success'),

            Stat::make('Collected This Month', '€ ' . number_format($collectedRevenue, 2, ',', '.'))
                ->description($collectedCount . ' paid quote' . ($collectedCount === 1 ? '' : 's') . ' in ' . now()->format('F'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($collectedSparkline),

            Stat::make('New Leads This Month', $newLeadsThisMonth)
                ->description('Leads added in ' . now()->format('F'))
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),
        ];
    }
}
