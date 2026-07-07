<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Filament\Exports\LeadExporter;
use App\Filament\Resources\LeadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Imports\LeadImporter; // Import the class

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(LeadExporter::class)
                ->label('Export Event Leads'),
            Actions\ImportAction::make()
                ->importer(LeadImporter::class)
                ->label('Upload Event Leads'),
            Actions\CreateAction::make(),
        ];
    }
}
