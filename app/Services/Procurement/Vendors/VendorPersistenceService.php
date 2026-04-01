<?php

namespace App\Services\Procurement\Vendors;

use App\Models\Procurement\Vendors\Vendor;
use App\Models\Procurement\Vendors\VendorBrochure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VendorPersistenceService
{
    /**
     * Replace all vendor category pivot rows.
     *
     * One row per selected subcategory; category-only rows use null subcategory_id.
     *
     * @param  array<int, array{category_id?: int|string|null, subcategory_ids?: list<mixed>|null, is_primary?: bool|string|null}>|null  $categories
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

            $categoryId = (int) $categoryId;
            $isPrimaryRow = filter_var($assignment['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $rawSubs = $assignment['subcategory_ids'] ?? [];
            if (! is_array($rawSubs)) {
                $rawSubs = [];
            }

            $subIds = array_values(array_unique(array_filter(
                array_map(static fn ($v) => (int) $v, $rawSubs),
                static fn (int $id) => $id > 0
            )));

            if ($subIds === []) {
                $vendor->vendorCategories()->create([
                    'category_id' => $categoryId,
                    'subcategory_id' => null,
                    'is_primary' => $isPrimaryRow,
                ]);

                continue;
            }

            foreach ($subIds as $subId) {
                $vendor->vendorCategories()->create([
                    'category_id' => $categoryId,
                    'subcategory_id' => $subId,
                    'is_primary' => $isPrimaryRow,
                ]);
            }
        }
    }

    /**
     * Replace all vendor location rows.
     *
     * @param  array<int, array<string, mixed>>|null  $locations
     */
    public function replaceLocations(Vendor $vendor, ?array $locations): void
    {
        $vendor->locations()->delete();

        if (! is_array($locations)) {
            return;
        }

        foreach ($locations as $row) {
            if (! is_array($row)) {
                continue;
            }

            $countryId = $row['country_id'] ?? null;
            if ($countryId === null || $countryId === '') {
                continue;
            }

            $vendor->locations()->create([
                'country_id' => (int) $countryId,
                'city_id' => isset($row['city_id']) && $row['city_id'] !== '' && $row['city_id'] !== null
                    ? (int) $row['city_id']
                    : null,
                'address' => isset($row['address']) && trim((string) $row['address']) !== ''
                    ? (string) $row['address']
                    : null,
                'phone' => isset($row['phone']) && trim((string) $row['phone']) !== ''
                    ? trim((string) $row['phone'])
                    : null,
                'whatsapp' => isset($row['whatsapp']) && trim((string) $row['whatsapp']) !== ''
                    ? trim((string) $row['whatsapp'])
                    : null,
                'notes' => isset($row['notes']) && trim((string) $row['notes']) !== ''
                    ? trim((string) $row['notes'])
                    : null,
                'is_primary' => filter_var($row['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
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
     * Delete vendor brochures by id (scoped to vendor). Removes public disk files when present.
     *
     * @param  list<int>  $ids
     */
    public function removeBrochuresByIds(Vendor $vendor, array $ids): void
    {
        $ids = array_values(array_unique(array_filter(
            array_map(static fn ($v) => (int) $v, $ids),
            static fn (int $id) => $id > 0
        )));

        if ($ids === []) {
            return;
        }

        $brochures = VendorBrochure::query()
            ->where('vendor_id', $vendor->id)
            ->whereIn('id', $ids)
            ->get();

        foreach ($brochures as $brochure) {
            if ($brochure->file_path) {
                Storage::disk('public')->delete($brochure->file_path);
            }
            $brochure->delete();
        }
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
