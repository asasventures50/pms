<?php

namespace App\Http\Requests\Procurement\ProcurementRequests\Concerns;

use Illuminate\Http\UploadedFile;

trait PreparesHeaderSupportingDocuments
{
    protected function prepareHeaderSupportingDocumentsForValidation(): void
    {
        $rows = $this->input('supporting_document_rows', []);

        if (! is_array($rows)) {
            return;
        }

        $normalized = [];

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $url = trim((string) ($row['url'] ?? ''));
            $file = $this->file("supporting_document_rows.$index.file");
            $hasFile = $file instanceof UploadedFile && $file->isValid();
            $documentType = trim((string) ($row['document_type'] ?? ''));
            $description = trim((string) ($row['file_description'] ?? ''));

            if ($url === '' && ! $hasFile && $documentType === '' && $description === '') {
                continue;
            }

            $normalized[] = array_filter([
                'document_type' => $documentType !== '' ? $documentType : null,
                'file_description' => $description !== '' ? $description : null,
                'url' => $url !== '' ? $url : null,
                'name' => isset($row['name']) ? trim((string) $row['name']) : null,
                'has_file' => $hasFile,
            ], static fn ($v) => $v !== null && $v !== '' && $v !== false);
        }

        $this->merge(['supporting_document_rows' => $normalized]);
    }
}
