<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\PaymentUploadRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Jobs\ProcessPaymentFileJob;
use Illuminate\Support\Facades\Auth;

class PaymentFileController extends Controller
{
    private PaymentUploadRepositoryInterface $paymentUploadRepository;

    function __construct(PaymentUploadRepositoryInterface $paymentUploadRepository)
    {
        $this->paymentUploadRepository = $paymentUploadRepository;
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('payments', 's3');

        $paymentFile = $this->paymentUploadRepository->create([
            'file_name' => $file->getClientOriginalName(),
            'user_id' => Auth::user()->getAuthIdentifier(),
            'path' => $path,
            'status' => 'uploaded',
            'uploaded_at' => now(),
        ]);

        // Dispatch to queue for background processing
        ProcessPaymentFileJob::dispatch($paymentFile->id)
            ->onConnection('redis')
            ->onQueue('payment-file-upload-queue');

        return response()->json(['message' => 'File uploaded successfully and is being processed.']);
    }
}

