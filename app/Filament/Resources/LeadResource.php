<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Filament\Resources\LeadResource\RelationManagers;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('category'),
                Forms\Components\TextInput::make('rating'),
                Forms\Components\TextInput::make('reviews_count'),
                Forms\Components\TextInput::make('address'),
                Forms\Components\TextInput::make('address_2'),
                Forms\Components\TextInput::make('email'),
                Forms\Components\TextInput::make('phone'),
                Forms\Components\TextInput::make('status_now'),
                Forms\Components\TextInput::make('website'),
                Forms\Components\TextInput::make('google_maps_link'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                //Tables\Columns\TextColumn::make('category')
                //    ->searchable(),

                Tables\Columns\TextInputColumn::make('email')
                    ->rules(['email', 'max:255'])
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('address')
                    ->searchable(),

                Tables\Columns\TextColumn::make('address_2')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('visit_website')
                    ->label('Visit Website')
                    ->icon('heroicon-m-globe-alt')
                    ->url(fn ($record) => $record->website)
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('open_maps')
                    ->label('View on Maps')
                    ->icon('heroicon-m-map-pin')
                    ->color('gray')
                    ->url(fn ($record) => $record->google_maps_link)
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
