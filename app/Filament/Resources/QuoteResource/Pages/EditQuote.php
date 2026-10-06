<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Resources\QuoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuote extends EditRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview_pdf')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn () => route('quotes.preview', $this->record))
                ->openUrlInNewTab(),

            QuoteResource::downloadPdfAction(),

            QuoteResource::sendEmailAction(),
            QuoteResource::lexwareQuotationAction(),
            QuoteResource::lexwareDraftInvoiceAction(),
            QuoteResource::lexwareInvoiceAction(),

            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $this->record->statusChangeComment = $this->data['status_comment'] ?? null;
    }

    protected function afterSave(): void
    {
        $this->record->statusChangeComment = null;
        $this->data['status_comment'] = null;

        QuoteResource::notifySameDayBookings($this->record);
        $this->dispatch('quotes-changed');
    }
}
