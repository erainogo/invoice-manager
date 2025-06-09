<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\PaymentUploadRepositoryInterface;
use Aws\S3\S3Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Jobs\ProcessPaymentFileJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentFileController extends Controller
{
    private PaymentUploadRepositoryInterface $paymentUploadRepository;

    function __construct(PaymentUploadRepositoryInterface $paymentUploadRepository)
    {
        $this->paymentUploadRepository = $paymentUploadRepository;
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $localPath = $file->store('/temp'); // stored locally
        $filename = $file->getClientOriginalName();
        $uniqueName = uniqid() . '-' . $filename;

        $s3Path = 'payments/' . $uniqueName;

        // we can't really use separate worker for upload the file to s3 because,
        // we can't access the request file from the worker server,
        // but we can do multipart upload using aws sdk.
        $paymentFile = $this->paymentUploadRepository->create([
            'file_name' => $uniqueName,
            'user_id' => Auth::id(),
            'path' => $s3Path,
            'status' => 'uploaded',
            'uploaded_at' => now(),
        ]);

        // === Multipart Upload Inline ===
        $absolutePath = storage_path("app/private/{$localPath}");
        $bucket = config('filesystems.disks.s3.bucket');

        $s3 = new S3Client([
            'version' => 'latest',
            'region' => config('filesystems.disks.s3.region'),
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);

        $upload = $s3->createMultipartUpload([
            'Bucket' => $bucket,
            'Key'    => $s3Path,
        ]);

        $fileHandle = fopen($absolutePath, 'rb');
        $partSize = 5 * 1024 * 1024; // 5 MB
        $partNumber = 1;
        $parts = [];

        try {
            while (!feof($fileHandle)) {
                $data = fread($fileHandle, $partSize);

                $result = $s3->uploadPart([
                    'Bucket'     => $bucket,
                    'Key'        => $s3Path,
                    'UploadId'   => $upload['UploadId'],
                    'PartNumber' => $partNumber,
                    'Body'       => $data,
                ]);

                $parts['Parts'][] = [
                    'PartNumber' => $partNumber,
                    'ETag'       => $result['ETag'],
                ];

                $partNumber++;
            }

            fclose($fileHandle);

            $s3->completeMultipartUpload([
                'Bucket'          => $bucket,
                'Key'             => $s3Path,
                'UploadId'        => $upload['UploadId'],
                'MultipartUpload' => $parts,
            ]);

            // Dispatch processing job after upload
            ProcessPaymentFileJob::dispatch($paymentFile->id);
        } catch (\Exception $e) {
            $s3->abortMultipartUpload([
                'Bucket'   => $bucket,
                'Key'      => $s3Path,
                'UploadId' => $upload['UploadId'],
            ]);

            Log::error("Multipart upload failed: " . $e->getMessage());

            return redirect()
                ->back()
                ->withErrors(['file' => 'Upload failed. Please try again.']);
        }

        return redirect()
            ->route('filament.admin.resources.payment-files.index')
            ->with('success', 'File is uploaded and being processed.');
    }
}

