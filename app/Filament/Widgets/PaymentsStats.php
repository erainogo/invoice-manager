<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Payment;
use App\Models\User;
use App\Models\PaymentFile;

class PaymentsStats extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total Payments Count', Payment::count())
                ->description('Total payments')
                ->icon('heroicon-o-currency-dollar'),

            Card::make('Processed Payments Count', Payment::where('status','processed')->count())
                ->description('Total processed payments')
                ->icon('heroicon-o-currency-dollar'),

            Card::make('Unprocessed Payments Count', Payment::where('status','unprocessed')->count())
                ->description('Total unprocessed payments')
                ->icon('heroicon-o-currency-dollar'),

            Card::make('Failed Payments Count', Payment::where('status','failed')->count())
                ->description('Total failed payments')
                ->icon('heroicon-o-currency-dollar'),

            Card::make('API users', User::where('role', 'user')->count())
                ->description('API users')
                ->icon('heroicon-o-users'),

            Card::make('Admin Users', User::where('role', 'admin')->count())
                ->description('Admin users')
                ->icon('heroicon-o-users'),

            Card::make('Payment Files', PaymentFile::count())
                ->description('Uploaded payment files')
                ->icon('heroicon-o-document-text'),

            Card::make('Invoices sent', Invoice::count())
                ->description('Invoices sent')
                ->icon('heroicon-o-document-text'),
        ];
    }
}

