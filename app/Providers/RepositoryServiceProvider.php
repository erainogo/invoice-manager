<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application Services.
     */
    public function register(): void
    {
        $this->app->bind(
            'App\Repositories\Contracts\PaymentUploadRepositoryInterface',
            'App\Repositories\DbPaymentUploadRepository'
        );

        $this->app->bind(
            'App\Repositories\Contracts\PaymentRepositoryInterface',
            'App\Repositories\DbPaymentRepository'
        );

        $this->app->bind(
            'App\Repositories\Contracts\InvoiceRepositoryInterface',
            'App\Repositories\DbInvoiceRepository'
        );

        $this->app->bind(
            'App\Repositories\Contracts\InvoicePaymentRepositoryInterface',
            'App\Repositories\DbInvoicePaymentRepository'
        );
    }

    /**
     * Bootstrap any application Services.
     */
    public function boot(): void
    {
        //
    }
}
