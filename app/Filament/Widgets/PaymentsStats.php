<?php

namespace App\Filament\Widgets;

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
            Card::make('Payments Count', Payment::count())
                ->description('Total processed payments')
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
        ];
    }
}

