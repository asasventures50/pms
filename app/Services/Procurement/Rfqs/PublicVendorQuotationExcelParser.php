<?php

namespace App\Services\Procurement\Rfqs;

use App\Models\Procurement\Rfqs\RfqVendorQuotationInvite;
use App\Support\Procurement\Rfqs\PublicVendorQuotationExcelSchema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PublicVendorQuotationExcelParser
{
    /**
     * Parse an uploaded Excel template into a payload for submitQuotation().
     *
     * @return array{
     *     vendor_rep_name: string|null,
     *     vendor_rep_email: string|null,
     *     vendor_rep_phone: string|null,
     *     notes: string|null,
     *     items: list<array<string, mixed>>
     * }
     *
     * @throws ValidationException
     */
    public function parse(RfqVendorQuotationInvite $invite, UploadedFile $file): array
    {
        $invite->loadMissing('rfq.items');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'excel_file' => __('vendor_quotation_invite.excel.errors.unreadable'),
            ]);
        }

        $itemsSheet = $spreadsheet->getSheetByName(PublicVendorQuotationExcelSchema::SHEET_ITEMS);
        $contactSheet = $spreadsheet->getSheetByName(PublicVendorQuotationExcelSchema::SHEET_CONTACT);

        if ($itemsSheet === null || $contactSheet === null) {
            throw ValidationException::withMessages([
                'excel_file' => __('vendor_quotation_invite.excel.errors.missing_sheets'),
            ]);
        }

        $expectedIds = $invite->rfq->items->pluck('id')->map(fn ($id) => (int) $id)->all();
        $items = $this->parseItemsSheet($itemsSheet->toArray(null, true, true, false), $expectedIds);
        $contact = $this->parseContactSheet($contactSheet->toArray(null, true, true, false));

        $payload = array_merge($contact, ['items' => $items]);

        $this->validatePayload($invite, $payload);

        return $payload;
    }

    /**
     * @param  list<list<mixed>>  $rows
     * @param  list<int>  $expectedIds
     * @return list<array<string, mixed>>
     */
    private function parseItemsSheet(array $rows, array $expectedIds): array
    {
        if ($rows === []) {
            throw ValidationException::withMessages([
                'excel_file' => __('vendor_quotation_invite.excel.errors.empty_items'),
            ]);
        }

        $headingMap = null;
        $headerRowIndex = null;

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $candidate = $this->mapItemHeadings($row);
            if (isset($candidate['rfq_item_id'], $candidate['unit_price'], $candidate['remarks'])) {
                $headingMap = $candidate;
                $headerRowIndex = $index;
                break;
            }
        }

        if ($headingMap === null || $headerRowIndex === null) {
            throw ValidationException::withMessages([
                'excel_file' => __('vendor_quotation_invite.excel.errors.bad_headers', [
                    'column' => 'rfq_item_id',
                ]),
            ]);
        }

        foreach (PublicVendorQuotationExcelSchema::itemHeadings() as $required) {
            if (! array_key_exists($required, $headingMap)) {
                throw ValidationException::withMessages([
                    'excel_file' => __('vendor_quotation_invite.excel.errors.bad_headers', [
                        'column' => $required,
                    ]),
                ]);
            }
        }

        $items = [];
        $seenIds = [];

        foreach ($rows as $index => $row) {
            if ($index <= $headerRowIndex) {
                continue;
            }

            $excelRowNumber = $index + 1;

            if (! is_array($row) || $this->rowIsEmpty($row)) {
                continue;
            }

            $rawId = $row[$headingMap['rfq_item_id']] ?? null;
            if ($rawId === null || $rawId === '') {
                continue;
            }

            $rfqItemId = $this->cellInt($rawId);
            if ($rfqItemId === null) {
                continue;
            }

            if (isset($seenIds[$rfqItemId])) {
                throw ValidationException::withMessages([
                    'excel_file' => __('vendor_quotation_invite.excel.errors.duplicate_id', [
                        'row' => $excelRowNumber,
                        'id' => $rfqItemId,
                    ]),
                ]);
            }
            $seenIds[$rfqItemId] = true;

            if (! in_array($rfqItemId, $expectedIds, true)) {
                throw ValidationException::withMessages([
                    'excel_file' => __('vendor_quotation_invite.excel.errors.unknown_id', [
                        'row' => $excelRowNumber,
                        'id' => $rfqItemId,
                    ]),
                ]);
            }

            $numericFields = [
                'quantity_quoted',
                'unit_price',
                'discount',
                'installation',
                'delivery_charges',
            ];

            $parsedNumerics = [];
            foreach ($numericFields as $field) {
                try {
                    $parsedNumerics[$field] = $this->cellNumeric($row[$headingMap[$field]] ?? null);
                } catch (\InvalidArgumentException) {
                    throw ValidationException::withMessages([
                        'excel_file' => __('vendor_quotation_invite.excel.errors.row_not_numeric', [
                            'row' => $excelRowNumber,
                            'column' => $field,
                        ]),
                    ]);
                }
            }

            $items[] = [
                'rfq_item_id' => $rfqItemId,
                'quantity_quoted' => $parsedNumerics['quantity_quoted'],
                'currency' => $this->cellString($row[$headingMap['currency']] ?? null, 10),
                'brand' => $this->cellString($row[$headingMap['brand']] ?? null, 255),
                'model' => $this->cellString($row[$headingMap['model']] ?? null, 255),
                'unit_price' => $parsedNumerics['unit_price'],
                'discount' => $parsedNumerics['discount'],
                'installation' => $parsedNumerics['installation'],
                'delivery_charges' => $parsedNumerics['delivery_charges'],
                'remarks' => $this->cellString($row[$headingMap['remarks']] ?? null, 2000),
                '_excel_row' => $excelRowNumber,
            ];
        }

        $foundIds = array_column($items, 'rfq_item_id');
        $missing = array_values(array_diff($expectedIds, $foundIds));
        if ($missing !== []) {
            throw ValidationException::withMessages([
                'excel_file' => __('vendor_quotation_invite.excel.errors.missing_rows', [
                    'ids' => implode(', ', $missing),
                ]),
            ]);
        }

        return $items;
    }

    /**
     * @param  list<list<mixed>>  $rows
     * @return array{
     *     vendor_rep_name: string|null,
     *     vendor_rep_email: string|null,
     *     vendor_rep_phone: string|null,
     *     notes: string|null
     * }
     */
    private function parseContactSheet(array $rows): array
    {
        if ($rows === []) {
            throw ValidationException::withMessages([
                'excel_file' => __('vendor_quotation_invite.excel.errors.missing_contact'),
            ]);
        }

        $headingRow = array_shift($rows);
        $keyIndex = 0;
        $valueIndex = 2;

        if (is_array($headingRow)) {
            foreach ($headingRow as $index => $heading) {
                $normalized = $this->normalizeHeading($heading);
                if ($normalized === 'key') {
                    $keyIndex = (int) $index;
                }
                if (in_array($normalized, ['value', 'enter_here', 'your_answer', 'ادخل_هنا', 'أدخل_هنا'], true)
                    || str_contains($normalized, 'enter')
                    || str_contains($normalized, 'value')
                    || str_contains($normalized, 'answer')) {
                    $valueIndex = (int) $index;
                }
            }
        }

        // Prefer the yellow "answer" column: if headers are localized, column C (index 2) is the value.
        if ($valueIndex === 0 && is_array($headingRow) && count($headingRow) >= 3) {
            $valueIndex = 2;
        }

        $contact = [
            'vendor_rep_name' => null,
            'vendor_rep_email' => null,
            'vendor_rep_phone' => null,
            'notes' => null,
        ];

        foreach ($rows as $row) {
            if (! is_array($row) || $this->rowIsEmpty($row)) {
                continue;
            }

            $key = $this->normalizeHeading($row[$keyIndex] ?? null);
            if ($key === '' || ! array_key_exists($key, $contact)) {
                continue;
            }

            $max = match ($key) {
                'vendor_rep_phone' => 50,
                'notes' => 5000,
                default => 255,
            };

            $contact[$key] = $this->cellString($row[$valueIndex] ?? null, $max);
        }

        return $contact;
    }

    /**
     * @param  array{
     *     vendor_rep_name: string|null,
     *     vendor_rep_email: string|null,
     *     vendor_rep_phone: string|null,
     *     notes: string|null,
     *     items: list<array<string, mixed>>
     * }  $payload
     */
    private function validatePayload(RfqVendorQuotationInvite $invite, array $payload): void
    {
        $rules = [
            'vendor_rep_name' => ['required', 'string', 'max:255'],
            'vendor_rep_email' => ['nullable', 'email', 'max:255'],
            'vendor_rep_phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.rfq_item_id' => [
                'required',
                'integer',
                Rule::exists('rfq_items', 'id')->where('rfq_id', $invite->rfq_id),
            ],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.currency' => ['nullable', 'string', 'max:10'],
            'items.*.quantity_quoted' => ['nullable', 'numeric', 'min:0'],
            'items.*.brand' => ['nullable', 'string', 'max:255'],
            'items.*.model' => ['nullable', 'string', 'max:255'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.installation' => ['nullable', 'numeric', 'min:0'],
            'items.*.delivery_charges' => ['nullable', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string', 'max:2000'],
        ];

        $validator = Validator::make($payload, $rules);

        $validator->after(function ($validator) use ($payload): void {
            $hasPrice = false;
            foreach ($payload['items'] as $row) {
                if ((float) ($row['unit_price'] ?? 0) > 0) {
                    $hasPrice = true;
                    break;
                }
            }

            if (! $hasPrice) {
                $validator->errors()->add('excel_file', __('vendor_quotation_invite.errors.price_required'));
            }

            foreach ($payload['items'] as $index => $row) {
                $excelRow = $row['_excel_row'] ?? ($index + 2);
                foreach (['unit_price', 'quantity_quoted', 'discount', 'installation', 'delivery_charges'] as $key) {
                    if (! array_key_exists($key, $row) || $row[$key] === null || $row[$key] === '') {
                        continue;
                    }
                    if ((float) $row[$key] < 0) {
                        $validator->errors()->add(
                            'excel_file',
                            __('vendor_quotation_invite.excel.errors.row_negative', [
                                'row' => $excelRow,
                                'column' => $key,
                            ])
                        );
                    }
                }
            }
        });

        if ($validator->fails()) {
            $messages = [];
            foreach ($validator->errors()->all() as $message) {
                $messages[] = $message;
            }

            throw ValidationException::withMessages([
                'excel_file' => array_values(array_unique($messages)),
            ]);
        }
    }

    /**
     * @param  list<mixed>  $headingRow
     * @return array<string, int>
     */
    private function mapItemHeadings(array $headingRow): array
    {
        $map = [];
        foreach ($headingRow as $index => $heading) {
            $normalized = $this->normalizeHeading($heading);
            if ($normalized === '') {
                continue;
            }
            $map[$normalized] = (int) $index;
        }

        return $map;
    }

    private function normalizeHeading(mixed $value): string
    {
        $raw = strtolower(trim((string) $value));
        $raw = str_replace([' ', '-'], '_', $raw);

        return preg_replace('/[^a-z0-9_]/', '', $raw) ?? '';
    }

    /**
     * @param  list<mixed>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if ($cell === null || $cell === '') {
                continue;
            }
            if (is_string($cell) && trim($cell) === '') {
                continue;
            }

            return false;
        }

        return true;
    }

    private function cellString(mixed $value, int $max): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        return mb_substr($text, 0, $max);
    }

    private function cellNumeric(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $value = trim(str_replace([',', ' '], '', $value));
            if ($value === '') {
                return null;
            }
        }

        if (! is_numeric($value)) {
            throw new \InvalidArgumentException('Not numeric.');
        }

        return (float) $value;
    }

    private function cellInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '' || ! is_numeric($value)) {
                return null;
            }
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}
