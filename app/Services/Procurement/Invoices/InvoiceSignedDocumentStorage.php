<?php

namespace App\Services\Procurement\Invoices;

use App\Models\Procurement\Invoices\Invoice;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class InvoiceSignedDocumentStorage
{
    private const DISK = 's3';

    public function store(Invoice $invoice, UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new \RuntimeException('The uploaded invoice document is not valid.');
        }

        set_time_limit(max(120, (int) ini_get('max_execution_time')));

        $this->deleteStoredFile($invoice->signed_document_path);

        $directory = 'invoices/'.$invoice->id.'/signed';
        $path = Storage::disk(self::DISK)->putFileAs(
            $directory,
            $file,
            $file->hashName(),
            ['visibility' => 'public'],
        );

        if ($path === false) {
            throw new \RuntimeException(
                "Failed to upload invoice document '{$file->getClientOriginalName()}' to S3."
            );
        }

        $invoice->forceFill([
            'signed_document_path' => $path,
            'signed_document_original_name' => $file->getClientOriginalName(),
        ])->save();
    }

    public function purge(Invoice $invoice): void
    {
        $this->deleteStoredFile($invoice->signed_document_path);
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
