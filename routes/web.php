<?php

use App\Http\Controllers\PaymentFileController;
use Illuminate\Support\Facades\Route;

Route::post('/admin/upload-payment-file', [PaymentFileController::class, 'upload'])
    ->name('admin.upload-payment-file')
    ->middleware(['auth']);
