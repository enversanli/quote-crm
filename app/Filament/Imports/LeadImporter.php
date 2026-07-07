<?php

namespace App\Filament\Imports;

use App\Models\Lead;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;

class LeadImporter extends Importer
{
    protected static ?string $model = Lead::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required']),

            ImportColumn::make('category')
                ->label('Category'),

            ImportColumn::make('rating')
                ->numeric(),

            ImportColumn::make('address'),

            ImportColumn::make('address_2'),

            ImportColumn::make('website')
                ->label('Website URL'),

            ImportColumn::make('phone')
                ->label('Phone Number'),

            ImportColumn::make('mobile')
                ->label('Mobile Number'),

            ImportColumn::make('google_maps_link')
                ->label('Google Maps Link'),
        ];
    }

    // SADECE BİR TANE resolveRecord metodu olmalı:
    public function resolveRecord(): ?Lead
    {
        return Lead::firstOrNew([
            'name' => $this->data['name'],
        ]);
    }

    protected function beforeSave(): void
    {
        if (empty($this->record->address) || $this->record->address === '.') {
            $this->record->address = $this->record->address_2;
        }
    }

    public static function getCompletedNotificationBody(\Filament\Actions\Imports\Models\Import $import): string
    {
        $body = 'Your lead import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' were imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
