<?php

namespace App\Services\Procurement\VendorQuotations;

use App\Enums\Procurement\VendorQuotations\VendorQuotationDocumentType;
use App\Models\Procurement\VendorQuotations\VendorQuotation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VendorQuotationDocumentStorage
{
    private const DISK = 's3';

    /**
     * @param  array<string, UploadedFile|null>  $uploads
     * @return array<string, array{file_name: string, file_path: string}>
     */
    public function mergeUploads(VendorQuotation $quotation, array $uploads, ?array $existing = null): array
    {
        $documents = $existing ?? $quotation->documents_attached ?? [];

        foreach (VendorQuotationDocumentType::cases() as $type) {
            $key = $type->value;
            $file = $uploads[$key] ?? null;

            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            if (isset($documents[$key]['file_path'])) {
                Storage::disk(self::DISK)->delete($documents[$key]['file_path']);
            }

            $directory = 'vendor-quotations/'.$quotation->id;
            $path = Storage::disk(self::DISK)->putFileAs(
                $directory,
                $file,
                $key.'-'.$file->hashName(),
                ['visibility' => 'public'],
            );

            if ($path === false) {
                throw new \RuntimeException(
                    "Failed to upload document '{$file->getClientOriginalName()}'."
                );
            }

            $documents[$key] = [
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
            ];
        }

        return $documents;
    }

    /**
     * @param  list<string>  $keys
     */
    public function removeKeys(VendorQuotation $quotation, array $keys, ?array $existing = null): array
    {
        $documents = $existing ?? $quotation->documents_attached ?? [];

        foreach ($keys as $key) {
            if (! isset($documents[$key])) {
                continue;
            }

            if (! empty($documents[$key]['file_path'])) {
                Storage::disk(self::DISK)->delete($documents[$key]['file_path']);
            }

            unset($documents[$key]);
        }

        return $documents;
    }
}
