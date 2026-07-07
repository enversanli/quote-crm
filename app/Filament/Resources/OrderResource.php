<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\OrderItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Bestelldetails')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Bestellname')
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\DatePicker::make('date')
                            ->label('Datum')
                            ->nullable(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft'     => 'Entwurf',
                                'confirmed' => 'Bestätigt',
                                'completed' => 'Abgeschlossen',
                            ])
                            ->default('draft')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Anmerkungen')
                            ->nullable()
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('grand_total_display')
                            ->label('Gesamtbetrag')
                            ->content(function (Forms\Get $get): string {
                                $lines = $get('orderLines') ?? [];
                                $total = 0;
                                foreach ($lines as $line) {
                                    if (! ($line['include_in_total'] ?? true)) {
                                        continue;
                                    }
                                    $item = OrderItem::find($line['order_item_id'] ?? null);
                                    $total += ($item?->price ?? 0) * (float) ($line['quantity'] ?? 0);
                                }
                                return $total > 0 ? '€ ' . number_format($total, 2, ',', '.') : '—';
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Bestellpositionen')
                    ->schema([
                        Forms\Components\Repeater::make('orderLines')
                            ->relationship()
                            ->label('')
                            ->schema([
                                Forms\Components\Select::make('order_item_id')
                                    ->label('Artikel')
                                    ->options(
                                        OrderItem::with('itemGroup')
                                            ->get()
                                            ->groupBy(fn ($item) => $item->itemGroup->name)
                                            ->map(fn ($items) => $items->pluck('name', 'id'))
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                        $item = OrderItem::find($state);
                                        $set('unit_price_display', $item?->price !== null ? number_format($item->price, 2, ',', '.') : null);
                                        $qty = (float) ($get('quantity') ?? 1);
                                        $total = ($item?->price ?? 0) * $qty;
                                        $set('line_total_display', $total > 0 ? number_format($total, 2, ',', '.') : null);
                                    })
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Menge')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(0)
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                        $item = OrderItem::find($get('order_item_id'));
                                        $total = ($item?->price ?? 0) * (float) ($state ?? 0);
                                        $set('line_total_display', $total > 0 ? number_format($total, 2, ',', '.') : null);
                                    }),
                                Forms\Components\TextInput::make('unit_price_display')
                                    ->label('Einzelpreis')
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('—')
                                    ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get) {
                                        $item = OrderItem::find($get('order_item_id'));
                                        $set('unit_price_display', $item?->price !== null ? number_format($item->price, 2, ',', '.') : null);
                                    }),
                                Forms\Components\TextInput::make('line_total_display')
                                    ->label('Zeilensumme')
                                    ->prefix('€')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('—')
                                    ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get) {
                                        $item = OrderItem::find($get('order_item_id'));
                                        $qty = (float) ($get('quantity') ?? 0);
                                        $total = ($item?->price ?? 0) * $qty;
                                        $set('line_total_display', $total > 0 ? number_format($total, 2, ',', '.') : null);
                                    }),
                                Forms\Components\Toggle::make('include_in_total')
                                    ->label('Zum Gesamtbetrag')
                                    ->default(true)
                                    ->live()
                                    ->onIcon('heroicon-m-calculator')
                                    ->offIcon('heroicon-m-eye')
                                    ->onColor('success')
                                    ->offColor('warning')
                                    ->helperText(fn (Forms\Get $get) => ($get('include_in_total') ?? true)
                                        ? 'Im Gesamtbetrag enthalten'
                                        : 'Nur zur Anzeige — nicht im Gesamtbetrag'
                                    ),
                                Forms\Components\TextInput::make('notes')
                                    ->label('Anmerkung')
                                    ->nullable()
                                    ->columnSpan(2),
                            ])
                            ->columns(7)
                            ->addActionLabel('Artikel hinzufügen')
                            ->reorderable()
                            ->cloneable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Bestellname')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray'    => 'draft',
                        'success' => 'confirmed',
                        'info'    => 'completed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft'     => 'Entwurf',
                        'confirmed' => 'Bestätigt',
                        'completed' => 'Abgeschlossen',
                        default     => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_lines_count')
                    ->counts('orderLines')
                    ->label('Positionen')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'     => 'Entwurf',
                        'confirmed' => 'Bestätigt',
                        'completed' => 'Abgeschlossen',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        Forms\Components\Toggle::make('show_prices')
                            ->label('Preise im PDF anzeigen')
                            ->default(false),
                    ])
                    ->modalHeading('Bestellung als PDF herunterladen')
                    ->modalSubmitActionLabel('Herunterladen')
                    ->action(function (Order $record, array $data) {
                        $order = $record->load([
                            'orderLines.orderItem.itemGroup',
                        ]);

                        $grouped = $order->orderLines
                            ->filter(fn ($line) => (bool) $line->include_in_total)
                            ->groupBy(fn ($line) => $line->orderItem->itemGroup->name)
                            ->sortKeys();

                        $infoGrouped = $order->orderLines
                            ->filter(fn ($line) => ! (bool) $line->include_in_total)
                            ->groupBy(fn ($line) => $line->orderItem->itemGroup->name)
                            ->sortKeys();

                        $pdf = Pdf::loadView('pdf.order', [
                            'order'       => $order,
                            'grouped'     => $grouped,
                            'infoGrouped' => $infoGrouped,
                            'showPrices'  => $data['show_prices'],
                        ])->setPaper('a4');

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            "bestellung-{$order->id}-{$order->name}.pdf"
                        );
                    }),
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
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}