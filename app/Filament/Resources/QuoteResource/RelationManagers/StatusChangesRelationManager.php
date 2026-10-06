<?php

namespace App\Filament\Resources\QuoteResource\RelationManagers;

use App\Filament\Resources\QuoteResource;
use App\Models\QuoteStatusChange;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Attributes\On;

/**
 * Status timeline below the quote form. Rows are written by QuoteObserver;
 * only the comment is editable here.
 */
class StatusChangesRelationManager extends RelationManager
{
    protected static string $relationship = 'statusChanges';

    protected static ?string $title = 'Status history';

    protected static ?string $icon = 'heroicon-o-clock';

    /** Show new steps right after the quote form is saved. */
    #[On('quotes-changed')]
    public function refreshHistory(): void
    {
        // Re-render only.
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('comment')
                ->label('Comment')
                ->rows(4)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->paginated(false)
            ->emptyStateHeading('No status changes yet')
            ->columns([
                Tables\Columns\TextColumn::make('step')
                    ->label('#')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('d.m.Y H:i'),

                Tables\Columns\TextColumn::make('from_status')
                    ->label('From')
                    ->badge()
                    ->color(fn (?string $state) => QuoteResource::statusColor($state))
                    ->formatStateUsing(fn (?string $state) => ucfirst((string) $state))
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('to_status')
                    ->label('To')
                    ->badge()
                    ->color(fn (?string $state) => QuoteResource::statusColor($state))
                    ->formatStateUsing(fn (?string $state) => ucfirst((string) $state)),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('By')
                    ->placeholder('System'),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Comment')
                    ->wrap()
                    ->placeholder('—'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(fn (QuoteStatusChange $record) => filled($record->comment) ? 'Edit comment' : 'Add comment')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->modalHeading(fn (QuoteStatusChange $record) => 'Comment — '
                        . ($record->from_status ? ucfirst($record->from_status) . ' → ' : '')
                        . ucfirst($record->to_status)
                        . ' (' . $record->created_at->format('d.m.Y H:i') . ')'),
            ]);
    }
}
