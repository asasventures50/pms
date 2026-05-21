<?php

namespace App\Services\Procurement\ProcurementRequests;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProcurementRequestSupportingDocumentStorage
{
    private const DISK = 's3';

    /**
     * @param  list<UploadedFile>  $files
     */
    public function append(ProcurementRequest $request, array $files): int
    {
        if ($files !== []) {
            set_time_limit(max(120, (int) ini_get('max_execution_time')));
        }

        $stored = 0;
        $directory = 'procurement-requests/'.$request->id;

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $path = Storage::disk(self::DISK)->putFileAs(
                $directory,
                $file,
                $file->hashName(),
                ['visibility' => 'public'],
            );

            if ($path === false) {
                throw new \RuntimeException(
                    "Failed to upload supporting document '{$file->getClientOriginalName()}' to S3."
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
            $this->deleteFile($document->file_path);
            $document->delete();
        }
    }

    public function deleteFile(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        try {
            Storage::disk(self::DISK)->delete($path);
        } catch (\Throwable) {
            // Best-effort; object may already be gone or S3 unreachable.
        }

        try {
            Storage::disk('public')->delete($path);
        } catch (\Throwable) {
            // Best-effort for legacy local files.
        }
    }
}
