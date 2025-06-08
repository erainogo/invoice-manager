<?php

namespace App\Filament\Resources\PaymentFileResource\Pages;

use App\Filament\Resources\PaymentFileResource;
use Filament\Resources\Pages\Page;

class CreatePaymentFile extends Page
{
    protected static string $resource = PaymentFileResource::class;

    protected static string $view = 'filament.pages.upload-payment-file';

    protected static ?string $title = 'Upload Payment File';
    protected static ?string $slug = 'upload-payment-file';
}
