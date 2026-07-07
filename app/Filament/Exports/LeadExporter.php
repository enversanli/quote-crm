<?php

namespace App\Filament\Exports;

use App\Models\Lead;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class LeadExporter extends Exporter
{
    protected static ?string $model = Lead::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('name')->label('Name'),
            ExportColumn::make('category')->label('Category'),
            //ExportColumn::make('rating')->label('Rating'),
            ExportColumn::make('email')->label('Email'),
            ExportColumn::make('address')->label('Address'),
            ExportColumn::make('address_2')->label('Address 2'),
            //ExportColumn::make('status_now')->label('Status Now'),
            ExportColumn::make('email')->label('mobile'),
            ExportColumn::make('mobile')->label('mobile'),
            ExportColumn::make('phone')->label('phone'),
            ExportColumn::make('website')->label('Website'),
            ExportColumn::make('google_maps_link')->label('Google Maps Link'),
            ExportColumn::make('created_at')->label('Created At'),
            ExportColumn::make('updated_at')->label('Updated At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your lead export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
