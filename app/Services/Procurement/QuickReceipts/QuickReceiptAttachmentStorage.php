<?php

namespace App\Services\Procurement\QuickReceipts;

use App\Models\Procurement\QuickReceipts\QuickReceipt;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class QuickReceiptAttachmentStorage
{
    private const DISK = 's3';

    public function store(QuickReceipt $receipt, UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new \RuntimeException('The uploaded receipt attachment is not valid.');
        }

        set_time_limit(max(120, (int) ini_get('max_execution_time')));

        $this->deleteStoredFile($receipt->attachment_path);

        $directory = 'quick-receipts/'.$receipt->id;
        $path = Storage::disk(self::DISK)->putFileAs(
            $directory,
            $file,
            $file->hashName(),
            ['visibility' => 'public'],
        );

        if ($path === false) {
            throw new \RuntimeException(
                "Failed to upload receipt attachment '{$file->getClientOriginalName()}' to S3."
            );
        }

        $receipt->forceFill([
            'attachment_path' => $path,
            'attachment_original_name' => $file->getClientOriginalName(),
        ])->save();
    }

    public function delete(QuickReceipt $receipt): void
    {
        $this->deleteStoredFile($receipt->attachment_path);

        $receipt->forceFill([
            'attachment_path' => null,
            'attachment_original_name' => null,
        ])->save();
    }

    public function purge(QuickReceipt $receipt): void
    {
        $this->deleteStoredFile($receipt->attachment_path);
    }

    public function deleteStoredFile(?string $path): void
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
