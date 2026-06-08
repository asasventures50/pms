<?php

namespace App\Services\Procurement\ProcurementRequests;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestDocument;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProcurementRequestSupportingDocumentStorage
{
    private const DISK = 's3';

    /**
     * @param  list<UploadedFile>  $files
     */
    public function append(ProcurementRequestItem $item, array $files): int
    {
        if ($files !== []) {
            set_time_limit(max(120, (int) ini_get('max_execution_time')));
        }

        $stored = 0;
        $directory = 'procurement-requests/'.$item->procurement_request_id.'/items/'.$item->id;

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

            $item->documents()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
            ]);

            $stored++;
        }

        return $stored;
    }

    /**
     * @param  list<array{url?: string, name?: string}>  $links
     */
    public function appendLinks(ProcurementRequestItem $item, array $links): int
    {
        $stored = 0;

        foreach ($links as $link) {
            if (! is_array($link)) {
                continue;
            }

            $url = trim((string) ($link['url'] ?? ''));

            if ($url === '' || ! ProcurementRequestDocument::isExternalUrl($url)) {
                continue;
            }

            $name = trim((string) ($link['name'] ?? ''));

            if ($name === '') {
                $name = $this->labelFromUrl($url);
            }

            $item->documents()->create([
                'file_name' => $name,
                'file_path' => $url,
            ]);

            $stored++;
        }

        return $stored;
    }

    /**
     * @param  list<UploadedFile>  $files
     */
    public function appendToRequest(ProcurementRequest $request, array $files, ?string $documentType = null, ?string $description = null): int
    {
        if ($files !== []) {
            set_time_limit(max(120, (int) ini_get('max_execution_time')));
        }

        $stored = 0;
        $directory = 'procurement-requests/'.$request->id.'/documents';

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

            $request->headerDocuments()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'document_type' => $documentType,
                'file_description' => $description,
            ]);

            $stored++;
        }

        return $stored;
    }

    /**
     * @param  list<array{url?: string, name?: string, document_type?: string, file_description?: string}>  $links
     */
    public function appendLinksToRequest(ProcurementRequest $request, array $links): int
    {
        $stored = 0;

        foreach ($links as $link) {
            if (! is_array($link)) {
                continue;
            }

            $url = trim((string) ($link['url'] ?? ''));

            if ($url === '' || ! ProcurementRequestDocument::isExternalUrl($url)) {
                continue;
            }

            $name = trim((string) ($link['name'] ?? ''));

            if ($name === '') {
                $name = $this->labelFromUrl($url);
            }

            $request->headerDocuments()->create([
                'file_name' => $name,
                'file_path' => $url,
                'document_type' => isset($link['document_type']) ? trim((string) $link['document_type']) : null,
                'file_description' => isset($link['file_description']) ? trim((string) $link['file_description']) : null,
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
            ->whereIn('id', $ids)
            ->where(function ($query) use ($request) {
                $query->where('procurement_request_id', $request->id)
                    ->orWhereHas('item', fn ($itemQuery) => $itemQuery->where('procurement_request_id', $request->id));
            })
            ->get();

        foreach ($documents as $document) {
            $this->deleteFile($document->file_path);
            $document->delete();
        }
    }

    public function deleteFile(?string $path): void
    {
        if ($path === null || $path === '' || ProcurementRequestDocument::isExternalUrl($path)) {
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

    private function labelFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $basename = is_string($path) ? basename($path) : '';

        if ($basename !== '' && $basename !== '/') {
            return $basename;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : $url;
    }
}
