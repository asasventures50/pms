<?php

namespace App\Http\Requests\Procurement\ProcurementRequests\Concerns;

use Illuminate\Http\UploadedFile;

trait PreparesSupportingDocuments
{
    protected function prepareSupportingDocumentsForValidation(): void
    {
        $files = $this->file('supporting_documents');

        if (! is_array($files)) {
            return;
        }

        $filtered = array_values(array_filter(
            $files,
            static fn ($file) => $file instanceof UploadedFile && $file->isValid()
        ));

        $this->files->set('supporting_documents', $filtered);
    }
}
