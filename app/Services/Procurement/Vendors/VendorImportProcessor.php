<?php

namespace App\Services\Procurement\Vendors;

use App\DataTransferObjects\Procurement\VendorsImportResult;
use App\Models\Geo\City;
use App\Models\Geo\Country;
use App\Models\Procurement\Vendors\Vendor;
use App\Models\Procurement\Vendors\VendorLocation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VendorImportProcessor
{
    public function __construct(
        private VendorCodeGenerator $codeGenerator
    ) {}

    public function process(Collection $rows): VendorsImportResult
    {
        $result = new VendorsImportResult;

        if ($rows->isEmpty()) {
            return $result;
        }

        $headerRowIndex = $this->detectHeaderRowIndex($rows);
        if ($headerRowIndex === null) {
            $result->failedRows = 1;
            $result->errors[] = 'Could not detect header row. Expected columns like: الاسم, المهنة, العنوان, الرقم.';
            return $result;
        }

        $headerRow = $rows->get($headerRowIndex);
        $semanticIndexes = $this->buildSemanticColumnIndexes($headerRow);
        $headerMap = $this->buildHeaderMap($headerRow, $semanticIndexes);

        $country = Country::query()->active()->where('iso_code', 'SY')->first();
        $city = $country
            ? City::query()->active()->where('country_id', $country->id)->where('name_en', 'Damascus')->first()
            : null;

        foreach ($rows->slice($headerRowIndex + 1)->values() as $index => $row) {
            $line = $headerRowIndex + $index + 2;
            $data = $this->extractRowData($row, $semanticIndexes, $headerMap);

            if ($this->isRowEmpty($data)) {
                continue;
            }

            $name = trim((string) ($data['name'] ?? ''));
            if ($name === '') {
                $result->failedRows++;
                $result->errors[] = "Row {$line}: Vendor name is required.";
                continue;
            }

            try {
                DB::transaction(function () use ($name, $data, $country, $city, $result) {
                    $exists = Vendor::query()
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                        ->exists();

                    if ($exists) {
                        $result->vendorsSkipped++;
                        return;
                    }

                    $vendor = Vendor::query()->create([
                        'created_by' => auth()->id(),
                        'vendor_code' => $this->codeGenerator->next(),
                        'name' => $name,
                        'language' => 'ar',
                        'description' => $data['description'] ?: null,
                        'phone' => $data['phone'] ?: null,
                        'status' => 'active',
                    ]);

                    VendorLocation::query()->create([
                        'vendor_id' => $vendor->id,
                        'country_id' => $country?->id,
                        'city_id' => $city?->id,
                        'address' => $data['address'] ?: null,
                        'phone' => $data['phone'] ?: null,
                        'is_primary' => true,
                    ]);

                    $result->vendorsCreated++;
                });
            } catch (\Throwable $e) {
                $result->failedRows++;
                $result->errors[] = "Row {$line}: ".$e->getMessage();
            }
        }

        return $result;
    }

    private function detectHeaderRowIndex(Collection $rows): ?int
    {
        $maxRowsToScan = min($rows->count(), 30);
        $bestIndex = null;
        $bestScore = -1;

        for ($i = 0; $i < $maxRowsToScan; $i++) {
            $row = $rows->get($i);
            $indexes = $this->buildSemanticColumnIndexes($row);
            $map = $this->buildHeaderMap($row, $indexes);

            $score = 0;
            if (($indexes['name'] ?? null) !== null) {
                $score += 3;
            } elseif ($this->hasAnyHeader($map, ['vendor_name', 'name', 'الاسم', 'الإسم', 'اسم'])) {
                $score += 3;
            }
            if (($indexes['description'] ?? null) !== null || $this->hasAnyHeader($map, ['description', 'profession', 'المهنة'])) {
                $score++;
            }
            if (($indexes['address'] ?? null) !== null || $this->hasAnyHeader($map, ['address', 'العنوان'])) {
                $score++;
            }
            if (($indexes['phone'] ?? null) !== null || $this->hasAnyHeader($map, ['phone', 'number', 'الرقم'])) {
                $score++;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestIndex = $i;
            }
        }

        return $bestScore >= 3 ? $bestIndex : null;
    }

    /**
     * @param  array<string, int>  $headerMap
     * @param  list<string>  $candidates
     */
    private function hasAnyHeader(array $headerMap, array $candidates): bool
    {
        foreach ($candidates as $candidate) {
            $key = $this->normalizeHeader($candidate);
            if (array_key_exists($key, $headerMap)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Infer column indexes from header labels (Arabic/English / mixed text).
     *
     * @return array{name:?int, description:?int, address:?int, phone:?int}
     */
    private function buildSemanticColumnIndexes(mixed $headerRow): array
    {
        $headers = $headerRow instanceof Collection ? $headerRow->toArray() : (array) $headerRow;
        /** @var array{name:?int, description:?int, address:?int, phone:?int} */
        $out = [
            'name' => null,
            'description' => null,
            'address' => null,
            'phone' => null,
        ];

        foreach ($headers as $idx => $title) {
            $titleStr = $this->cellAsText($title);
            $field = $this->matchHeaderFieldLabel($titleStr);
            if ($field === null) {
                continue;
            }

            $colIdx = $this->columnIndex($idx);
            if ($colIdx === null) {
                continue;
            }

            if ($out[$field] === null) {
                $out[$field] = $colIdx;
            }
        }

        return $out;
    }

    private function matchHeaderFieldLabel(string $title): ?string
    {
        $t = $title;
        if ($t === '') {
            return null;
        }

        $n = str_replace('_', '', $this->normalizeHeader($t));

        if ($this->looksLikeVendorNameColumn($t, $n)) {
            return 'name';
        }
        if (
            preg_match('/\b(desc|descr|professional|trade|sector|sector|مهنة)\b/ui', $t) === 1
            || preg_match('/المهنه|مهنه|مهنة/ui', $t) === 1
            || str_contains($n, 'مهنه')
            || str_contains($n, 'مهنة')
        ) {
            return 'description';
        }
        if (preg_match('/العنوان|عنوان|address\b/ui', $t) === 1) {
            return 'address';
        }
        if (
            preg_match('/\b(phone|mobile|fax|gsm|gsm|تليفون|telephone)\b/ui', $t) === 1
            || preg_match('/الهاتف|هاتف|الجوال|جوال/ui', $t) === 1
            || preg_match('/الرقم|رقم_الهاتف|رقم_التابع/ui', $t) === 1
            || preg_match('/\b(no\.?|num|#\b)/ui', $t) === 1
            || preg_match('/^رقم\s*$/u', trim($title)) === 1
            || preg_match('/^الرقم/ui', trim($title)) === 1
        ) {
            return 'phone';
        }

        return null;
    }

    /**
     * Disambiguate "اسم"-like headers vs other columns containing "اسم" as substring.
     */
    private function looksLikeVendorNameColumn(string $original, string $normalizedNoUnderscores): bool
    {
        if (preg_match('/\b(vendor|supplier|company|trade\s*name)\b/ui', $original) === 1) {
            return true;
        }

        if (
            preg_match('/\b(name)\b/ui', $original) === 1
            && preg_match('/\b(vendor|company|supplier|trade|business)\b/ui', $original) === 1
        ) {
            return true;
        }

        foreach (['/الاسم/u', '/^الإسم$/u', '/^اسم$/u', '/supplier_name/ui'] as $pattern) {
            if (preg_match($pattern, $original) === 1) {
                return true;
            }
        }

        if (str_contains($normalizedNoUnderscores, 'الاسم') || str_contains($normalizedNoUnderscores, 'الإسم')) {
            return true;
        }

        $key = $this->normalizeHeader($original);
        foreach (['vendor_name', 'name', 'الاسم', 'الإسم', 'اسم'] as $c) {
            if ($key === $this->normalizeHeader($c)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  mixed  $headerRow
     * @param  array{name:?int, description:?int, address:?int, phone:?int}  $semanticIndexes
     * @return array<string, int>
     */
    private function buildHeaderMap(mixed $headerRow, array $semanticIndexes = []): array
    {
        $headers = $headerRow instanceof Collection ? $headerRow->toArray() : (array) $headerRow;

        $map = [];
        foreach ($headers as $idx => $title) {
            $colIdx = $this->columnIndex($idx);
            if ($colIdx === null) {
                continue;
            }
            $normalized = $this->normalizeHeader($this->cellAsText($title));
            if ($normalized !== '') {
                $map[$normalized] = $colIdx;
            }
        }

        foreach ($semanticIndexes as $field => $colIdx) {
            if ($colIdx === null) {
                continue;
            }
            $map[(string) $field] = $colIdx;
        }

        return $map;
    }

    /**
     * @param  mixed  $row
     * @param  array{name:?int, description:?int, address:?int, phone:?int}  $semanticIndexes
     * @param  array<string, int>  $headerMap
     * @return array{name: string, description: string, address: string, phone: string}
     */
    private function extractRowData(mixed $row, array $semanticIndexes, array $headerMap): array
    {
        $values = $row instanceof Collection ? $row->toArray() : (array) $row;

        return [
            'name' => $this->getCellBySemantic($values, $semanticIndexes, $headerMap, 'name', ['vendor_name', 'name', 'الاسم', 'الإسم', 'اسم']),
            'description' => $this->getCellBySemantic($values, $semanticIndexes, $headerMap, 'description', ['description', 'profession', 'المهنة']),
            'address' => $this->getCellBySemantic($values, $semanticIndexes, $headerMap, 'address', ['address', 'العنوان']),
            'phone' => $this->getCellBySemantic($values, $semanticIndexes, $headerMap, 'phone', ['phone', 'number', 'الرقم']),
        ];
    }

    /**
     * @param  array<int|string, mixed>  $values
     * @param  array{name:?int, description:?int, address:?int, phone:?int}  $semanticIndexes
     * @param  array<string, int>  $headerMap
     * @param  'name'|'description'|'address'|'phone'  $field
     * @param  list<string>  $candidates
     */
    private function getCellBySemantic(array $values, array $semanticIndexes, array $headerMap, string $field, array $candidates): string
    {
        $idx = $semanticIndexes[$field] ?? null;
        if ($idx !== null) {
            $raw = $this->valueAtColumn($values, $idx);
            $text = $this->cellAsText($raw);

            return trim($text);
        }

        if (array_key_exists($field, $headerMap)) {
            $raw = $this->valueAtColumn($values, $headerMap[$field]);
            return trim($this->cellAsText($raw));
        }

        foreach ($candidates as $candidate) {
            $key = $this->normalizeHeader($candidate);
            if (array_key_exists($key, $headerMap)) {
                $raw = $this->valueAtColumn($values, $headerMap[$key]);

                return trim($this->cellAsText($raw));
            }
        }

        return '';
    }

    /**
     * @param  array<int|string, mixed>  $values
     */
    private function valueAtColumn(array $values, int $zeroBasedIndex): mixed
    {
        if (array_key_exists($zeroBasedIndex, $values)) {
            return $values[$zeroBasedIndex];
        }

        foreach ($values as $key => $cell) {
            $col = $this->columnIndex($key);
            if ($col === $zeroBasedIndex) {
                return $cell;
            }
        }

        return null;
    }

    private function columnIndex(mixed $idx): ?int
    {
        if (is_int($idx)) {
            return $idx;
        }
        if (is_numeric($idx)) {
            return (int) $idx;
        }
        if (is_string($idx) && preg_match('/^[A-Za-z]+$/', $idx) === 1) {
            $letters = strtoupper($idx);
            $n = 0;
            $len = strlen($letters);
            for ($i = 0; $i < $len; $i++) {
                $n = $n * 26 + (ord($letters[$i]) - ord('A') + 1);
            }

            return $n - 1;
        }

        return null;
    }

    private function cellAsText(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_object($value) && class_exists(\PhpOffice\PhpSpreadsheet\RichText\RichText::class)
            && $value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
            return $this->normalizeUnicodeCell($value->getPlainText());
        }

        if ($value instanceof \Stringable) {
            return $this->normalizeUnicodeCell((string) $value);
        }

        if (is_scalar($value)) {
            return $this->normalizeUnicodeCell((string) $value);
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return $this->normalizeUnicodeCell((string) $value);
        }

        return '';
    }

    private function normalizeUnicodeCell(string $value): string
    {
        $value = str_replace("\xEF\xBB\xBF", '', $value);
        $value = str_replace("\xc2\xa0", ' ', $value);
        $value = preg_replace('/\x{00A0}/u', ' ', $value) ?? $value;
        $value = preg_replace('/\x{2007}/u', ' ', $value) ?? $value;
        $value = preg_replace('/\x{202F}/u', ' ', $value) ?? $value;
        $value = preg_replace('/[\x{200B}\x{200C}\x{200D}\x{200E}\x{200F}\x{061C}]/u', '', $value) ?? $value;

        return trim($value);
    }

    private function normalizeHeader(string $value): string
    {
        $value = $this->normalizeUnicodeCell($value);
        // Remove BOM and Arabic tatweel/diacritics to avoid header mismatch.
        $value = str_replace("\xEF\xBB\xBF", '', $value);
        $value = preg_replace('/[ـًٌٍَُِّْ]/u', '', $value) ?? $value;
        $value = strtr($value, [
            'أ' => 'ا',
            'إ' => 'ا',
            'آ' => 'ا',
            'ى' => 'ي',
            'ة' => 'ه',
        ]);
        $value = str_replace(['*', '->', '=>', ':'], '', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;
        $value = mb_strtolower($value);
        $value = str_replace(' ', '_', $value);

        return trim($value, '_');
    }

    /**
     * @param  array{name: string, description: string, address: string, phone: string}  $data
     */
    private function isRowEmpty(array $data): bool
    {
        foreach ($data as $value) {
            if (trim($value) !== '') {
                return false;
            }
        }

        return true;
    }
}
