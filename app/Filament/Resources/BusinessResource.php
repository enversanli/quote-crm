<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessResource\Pages;
use App\Models\Business;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Contacts';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Business Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Business Name')
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->nullable()
                            ->rows(2)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('contact_person')
                            ->label('Contact Person')
                            ->nullable(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->nullable(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->nullable(),
                        Forms\Components\TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->nullable(),
                    ])
                    ->columns    (2),

                Forms\Components\Section::make('Venue / Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('default_hourly_rate')
                            ->label('Default Hourly Rate (€)')
                            ->numeric()
                            ->prefix('€')
                            ->nullable()
                            ->helperText('This rate is pre-filled when this business is selected on a quote.'),
                        Forms\Components\TextInput::make('capacity')
                            ->label('Capacity (persons)')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('address')
                            ->label('Address')
                            ->nullable(),
                        Forms\Components\TextInput::make('city')
                            ->label('City')
                            ->nullable(),
                        Forms\Components\TextInput::make('postal_code')
                            ->label('Postal Code')
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('')
                            ->nullable()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Business Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_person')
                    ->label('Contact')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('default_hourly_rate')
                    ->label('Hourly Rate')
                    ->money('EUR')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacity')
                    ->suffix(' persons')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('quotes_count')
                    ->counts('quotes')
                    ->label('Quotes')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBusinesses::route('/'),
            'create' => Pages\CreateBusiness::route('/create'),
            'edit'   => Pages\EditBusiness::route('/{record}/edit'),
        ];
    }
}
