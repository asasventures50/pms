<?php

namespace App\Services\Procurement\ProcurementRequests;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProcurementRequestSupportingDocumentStorage
{
    private const DISK = 'public';

    /**
     * @param  list<UploadedFile>  $files
     */
    public function append(ProcurementRequest $request, array $files): int
    {
        $stored = 0;
        $directory = 'procurement-requests/'.$request->id;

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $path = $file->store($directory, self::DISK);

            if ($path === false) {
                throw new \RuntimeException(
                    "Failed to upload supporting document '{$file->getClientOriginalName()}'."
                );
            }

            $request->documents()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
            ]);

            $stored++;
        }

        return $stored;
    }

    /**
     * @param  list<int>  $ids
     */
    public function removeByIds(ProcurementRequest $request, array $ids): void
    {
        $ids = array_values(array_unique(array_filter(
            array_map(static fn ($v) => (int) $v, $ids),
            static fn (int $id) => $id > 0
        )));

        if ($ids === []) {
            return;
        }

        $documents = ProcurementRequestDocument::query()
            ->where('procurement_request_id', $request->id)
            ->whereIn('id', $ids)
            ->get();

        foreach ($documents as $document) {
            if ($document->file_path && Storage::disk(self::DISK)->exists($document->file_path)) {
                Storage::disk(self::DISK)->delete($document->file_path);
            }
            $document->delete();
        }
    }
}
