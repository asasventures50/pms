<?php

namespace App\Services\Procurement\ProcurementRequests;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProcurementRequestSupportingDocumentStorage
{
    private const DISK = 'public';

    public function store(ProcurementRequest $request, UploadedFile $file): void
    {
        $this->deleteFile($request);

        $path = $file->store('procurement-requests/'.$request->id, self::DISK);

        if ($path === false) {
            throw new \RuntimeException(
                "Failed to upload supporting document '{$file->getClientOriginalName()}'."
            );
        }

        $request->update([
            'supporting_document_path' => $path,
            'supporting_document_name' => $file->getClientOriginalName(),
        ]);
    }

    public function remove(ProcurementRequest $request): void
    {
        $this->deleteFile($request);

        $request->update([
            'supporting_document_path' => null,
            'supporting_document_name' => null,
        ]);
    }

    private function deleteFile(ProcurementRequest $request): void
    {
        $path = $request->supporting_document_path;

        if ($path !== null && $path !== '' && Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
