<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPaymentFileJob;
use App\Repositories\Contracts\PaymentUploadRepositoryInterface;
use App\Helpers\S3MultipartUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentFileApiController extends Controller
{
    use S3MultipartUploader;

    private PaymentUploadRepositoryInterface $paymentUploadRepository;

    function __construct(PaymentUploadRepositoryInterface $paymentUploadRepository)
    {
        $this->paymentUploadRepository = $paymentUploadRepository;
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        // opens the uploaded file as a stream
        // no need to write the file to disk
        // less I/O overhead, faster and more memory-efficient
        $stream = fopen($file->getRealPath(), 'rb');

        $filename = $file->getClientOriginalName();
        $uniqueName = uniqid() . '-' . $filename;
        $s3Path = 'payments/' . $uniqueName;

        $success = $this->uploadAsParts($stream, $s3Path);

        if (! $success) {
            return response()->json([
                'message' => 'Upload failed. Internal server error.',
            ], 500);
        }

        $paymentFile = $this->paymentUploadRepository->create([
            'file_name' => $uniqueName,
            'user_id' => $request->user()->id,
            'path' => $s3Path,
            'status' => 'uploaded',
            'uploaded_at' => now(),
        ]);

        ProcessPaymentFileJob::dispatch($paymentFile->id);

        return response()->json([
            'message' => 'File uploaded successfully and is being processed.',
            'file_id' => $paymentFile->id,
        ]);
    }
}