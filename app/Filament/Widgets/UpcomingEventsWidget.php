<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\QuoteResource;
use App\Models\Quote;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingEventsWidget extends BaseWidget
{
    protected static ?int    $sort     = 4;
    protected static ?string $heading  = 'Upcoming Events';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Quote::query()
                    ->whereNotNull('event_date')
                    ->whereDate('event_date', '>=', now()->startOfDay())
                    ->whereNotIn('status', ['rejected', 'expired'])
                    ->with(['customer', 'business'])
                    ->orderBy('event_date')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('event_date')
                    ->label('Date')
                    ->date('D, d M Y')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn (Quote $record) => $record->event_date->isToday() ? 'primary' : null),

                Tables\Columns\TextColumn::make('customer_display_name')
                    ->label('Customer')
                    ->searchable(query: fn ($query, $search) => $query->where('customer_first_name', 'like', "%{$search}%")
                        ->orWhere('customer_last_name', 'like', "%{$search}%")
                        ->orWhere('customer_company', 'like', "%{$search}%"))
                    ->limit(32),

                Tables\Columns\TextColumn::make('business.name')
                    ->label('Venue')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('event_type')
                    ->label('Type')
                    ->placeholder('—')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('attendee_count')
                    ->label('Guests')
                    ->placeholder('—')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'    => 'gray',
                        'sent'     => 'info',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'expired'  => 'warning',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('quote_number')
                    ->label('Quote #')
                    ->color('gray')
                    ->size('sm'),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->url(fn (Quote $record) => QuoteResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-m-pencil-square')
                    ->color('gray'),
            ])
            ->paginated(false)
            ->striped();
    }
}
