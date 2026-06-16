<?php

namespace App\Services\Procurement\VendorQuotations;

use App\Models\Procurement\VendorQuotations\VendorQuotation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VendorQuotationSignatureStorage
{
    private const DISK = 's3';

    public function store(VendorQuotation $quotation, UploadedFile $file): string
    {
        if ($quotation->vendor_rep_signature_path) {
            Storage::disk(self::DISK)->delete($quotation->vendor_rep_signature_path);
        }

        $directory = 'vendor-quotations/'.$quotation->id.'/signatures';
        $path = Storage::disk(self::DISK)->putFileAs(
            $directory,
            $file,
            'signature-'.$file->hashName(),
            ['visibility' => 'public'],
        );

        if ($path === false) {
            throw new \RuntimeException('Failed to upload vendor signature.');
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
