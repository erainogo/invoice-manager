<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentFileApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->post('/upload', [
    PaymentFileApiController::class, 'upload'
])->name('api.payment-files.upload');