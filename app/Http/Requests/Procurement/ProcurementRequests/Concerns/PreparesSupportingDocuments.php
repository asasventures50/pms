<?php

namespace App\Http\Requests\Procurement\ProcurementRequests\Concerns;

use Illuminate\Http\UploadedFile;

trait PreparesSupportingDocuments
{
    protected function prepareSupportingDocumentsForValidation(): void
    {
        $items = $this->input('items', []);

        if (! is_array($items)) {
            return;
        }

        foreach ($items as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $files = $this->file("items.$index.supporting_documents");

            if (is_array($files)) {
                $filtered = array_values(array_filter(
                    $files,
                    static fn ($file) => $file instanceof UploadedFile && $file->isValid()
                ));

                $this->files->set("items.$index.supporting_documents", $filtered);
            }

            $links = $row['supporting_document_links'] ?? null;

            if (! is_array($links)) {
                continue;
            }

            $items[$index]['supporting_document_links'] = array_values(array_filter(
                $links,
                static function ($link): bool {
                    if (! is_array($link)) {
                        return false;
                    }

                    return trim((string) ($link['url'] ?? '')) !== '';
                }
            ));
        }

        $this->merge(['items' => $items]);
    }
}
