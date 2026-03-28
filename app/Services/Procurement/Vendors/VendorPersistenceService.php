<?php

namespace App\Services\Procurement\Vendors;

use App\Models\Procurement\Vendors\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class VendorPersistenceService
{
    /**
     * Replace all vendor category pivot rows.
     *
     * @param  array<int, array{category_id?: int|string|null, subcategory_id?: int|string|null, is_primary?: bool|string|null}>|null  $categories
     */
    public function replaceCategories(Vendor $vendor, ?array $categories): void
    {
        $vendor->vendorCategories()->delete();

        if (! is_array($categories)) {
            return;
        }

        foreach ($categories as $assignment) {
            $categoryId = $assignment['category_id'] ?? null;
            if ($categoryId === null || $categoryId === '') {
                continue;
            }

            $vendor->vendorCategories()->create([
                'category_id' => (int) $categoryId,
                'subcategory_id' => isset($assignment['subcategory_id']) && $assignment['subcategory_id'] !== '' && $assignment['subcategory_id'] !== null
                    ? (int) $assignment['subcategory_id']
                    : null,
                'is_primary' => filter_var($assignment['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    /**
     * Replace all vendor business type rows.
     *
     * @param  list<string>|null  $businessTypes
     */
    public function replaceBusinessTypes(Vendor $vendor, ?array $businessTypes): void
    {
        $vendor->businessTypes()->delete();

        if (! is_array($businessTypes)) {
            return;
        }

        foreach ($businessTypes as $businessType) {
            if ($businessType === null || $businessType === '') {
                continue;
            }

            $vendor->businessTypes()->create([
                'business_type' => $businessType,
            ]);
        }
    }

    /**
     * Store brochure rows with optional metadata (notes, category, subcategory).
     *
     * @param  array<int, array<string, mixed>>|null  $rows
     * @return int Number of files stored
     */
    public function appendBrochureRows(Vendor $vendor, ?array $rows): int
    {
        if (! is_array($rows)) {
            return 0;
        }

        $stored = 0;
        $directory = 'vendors/brochures/'.$vendor->id;

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $file = $row['file'] ?? null;
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $path = $file->store($directory, 'public');

            $vendor->brochures()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
                'notes' => isset($row['notes']) && $row['notes'] !== '' ? (string) $row['notes'] : null,
                'category_id' => ! empty($row['category_id']) ? (int) $row['category_id'] : null,
                'subcategory_id' => ! empty($row['subcategory_id']) ? (int) $row['subcategory_id'] : null,
            ]);

            $stored++;
        }

        return $stored;
    }

    /**
     * Legacy: flat array of brochure files (API / older clients).
     *
     * @return int Number of files stored
     */
    public function appendBrochures(Vendor $vendor, Request $request): int
    {
        if (! $request->hasFile('brochures')) {
            return 0;
        }

        /** @var array<int, UploadedFile> $files */
        $files = $request->file('brochures', []);
        $stored = 0;
        $directory = 'vendors/brochures/'.$vendor->id;

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $path = $file->store($directory, 'public');

            $vendor->brochures()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
            ]);

            $stored++;
        }

        return $stored;
    }
}
