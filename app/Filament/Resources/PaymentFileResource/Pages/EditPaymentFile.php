<?php

namespace App\Filament\Resources\PaymentFileResource\Pages;

use App\Filament\Resources\PaymentFileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentFile extends EditRecord
{
    protected static string $resource = PaymentFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
