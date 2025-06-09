<?php

namespace App\Helpers;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;

trait S3MultipartUploader
{
    public function uploadAsParts($stream, string $s3Path): bool
    {
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

        $fileHandle = $stream;
        $partSize = 5 * 1024 * 1024;
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

            return true;
        } catch (\Exception $e) {
            $s3->abortMultipartUpload([
                'Bucket'   => $bucket,
                'Key'      => $s3Path,
                'UploadId' => $upload['UploadId'],
            ]);

            Log::error("Multipart upload failed: " . $e->getMessage());

            return false;
        }
    }
}
