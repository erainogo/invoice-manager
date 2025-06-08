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
    }

    /**
     * Bootstrap any application Services.
     */
    public function boot(): void
    {
        //
    }
}
