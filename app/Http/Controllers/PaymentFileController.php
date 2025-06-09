<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\PaymentUploadRepositoryInterface;
use App\Jobs\ProcessPaymentFileJob;
use App\Helpers\S3MultipartUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentFileController extends Controller
{
    use S3MultipartUploader;

    private PaymentUploadRepositoryInterface $paymentUploadRepository;

    public function __construct(PaymentUploadRepositoryInterface $paymentUploadRepository)
    {
        $this->paymentUploadRepository = $paymentUploadRepository;
    }

    public function upload(Request $request): RedirectResponse
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized.');
        }

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
            return redirect()
                ->back()
                ->withErrors(['file' => 'Upload failed. Please try again.']);
        }

        $paymentFile = $this->paymentUploadRepository->create([
            'file_name' => $uniqueName,
            'user_id' => Auth::id(),
            'path' => $s3Path,
            'status' => 'uploaded',
            'uploaded_at' => now(),
        ]);

        ProcessPaymentFileJob::dispatch($paymentFile->id);

        return redirect()
            ->route('filament.admin.resources.payment-files.index')
            ->with('success', 'File is uploaded and being processed.');
    }
}
