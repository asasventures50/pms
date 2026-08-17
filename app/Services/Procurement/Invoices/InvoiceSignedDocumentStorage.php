<?php

namespace App\Services\Procurement\Invoices;

use App\Models\Procurement\Invoices\Invoice;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoiceSignedDocumentStorage
{
    private const DISK = 's3';

    public function store(Invoice $invoice, UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new \RuntimeException('The uploaded invoice document is not valid.');
        }

        $realPath = $file->getRealPath();
        if ($realPath === false || ! is_readable($realPath)) {
            throw new \RuntimeException('The uploaded invoice document could not be read.');
        }

        set_time_limit(max(120, (int) ini_get('max_execution_time')));

        $this->deleteStoredFile($invoice->signed_document_path);

        $key = 'invoices/'.$invoice->id.'/signed/'.$file->hashName();
        $this->putObject($key, $realPath, $file->getClientOriginalName());

        $invoice->forceFill([
            'signed_document_path' => $key,
            'signed_document_original_name' => $file->getClientOriginalName(),
        ])->save();
    }

    public function purge(Invoice $invoice): void
    {
        $this->deleteStoredFile($invoice->signed_document_path);
    }

    private function putObject(string $key, string $realPath, string $originalName): void
    {
        $disk = Storage::build(array_merge(config('filesystems.disks.s3', []), [
            'throw' => true,
        ]));

        $attempts = [
            ['visibility' => 'public'],
            [],
        ];

        $lastMessage = null;

        foreach ($attempts as $options) {
            $stream = fopen($realPath, 'r');
            if ($stream === false) {
                throw new \RuntimeException('The uploaded invoice document could not be read.');
            }

            try {
                $disk->put($key, $stream, $options);

                return;
            } catch (\Throwable $exception) {
                $lastMessage = $exception->getMessage();
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }

        Log::error('Invoice signed document S3 upload failed', [
            'key' => $key,
            'original_name' => $originalName,
            'error' => $lastMessage,
        ]);

        throw new \RuntimeException(
            'Could not upload the signed invoice. Use a JPG, PNG, WEBP, or PDF up to 2 MB and try again.'
        );
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        try {
            Storage::disk(self::DISK)->delete($path);
        } catch (\Throwable) {
            // Best-effort; object may already be gone or S3 unreachable.
        }
    }
}
