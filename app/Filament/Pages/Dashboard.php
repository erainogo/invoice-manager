<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\PaymentsStats;

class Dashboard extends Page
{
    protected static string $view = 'filament.pages.dashboard';
    protected function getHeaderWidgets(): array
    {
        return [
            PaymentsStats::class,
        ];
    }
}

