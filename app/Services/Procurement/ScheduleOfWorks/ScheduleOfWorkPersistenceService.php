<?php

namespace App\Services\Procurement\ScheduleOfWorks;

use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use App\Enums\Procurement\ScheduleOfWorks\ScheduleOfWorkScope;
use App\Models\Procurement\ScheduleOfWorks\ScheduleOfWork;
use App\Models\Procurement\ScheduleOfWorks\ScheduleOfWorkItem;
use App\Models\Procurement\Vendors\Vendor;
use App\Services\Procurement\Invoices\InvoiceCurrencyResolver;
use App\Services\Procurement\PurchaseOrders\VendorPurchaseOrderSnapshot;
use Illuminate\Support\Facades\DB;

class ScheduleOfWorkPersistenceService
{
    public function __construct(
        private readonly ScheduleOfWorkCodeGenerator $codeGenerator,
    ) {}

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $lines
     */
    public function create(array $header, array $lines): ScheduleOfWork
    {
        return DB::transaction(function () use ($header, $lines) {
            $header['document_number'] = $header['document_number'] ?? $this->codeGenerator->next();
            $header['total_price'] = $this->calculateTotalPrice($header, $lines);

            $schedule = ScheduleOfWork::query()->create($header);
            $this->syncItems($schedule, $lines);

            return $schedule->load(['items', 'creator']);
        });
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $lines
     */
    public function update(ScheduleOfWork $schedule, array $header, array $lines): ScheduleOfWork
    {
        return DB::transaction(function () use ($schedule, $header, $lines) {
            unset($header['document_number'], $header['created_by']);
            $header['total_price'] = $this->calculateTotalPrice($header, $lines);

            $schedule->update($header);
            $schedule->items()->delete();
            $this->syncItems($schedule, $lines);

            return $schedule->load(['items', 'creator']);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $rawLines
     * @return list<array<string, mixed>>
     */
    public static function normalizeLines(array $rawLines): array
    {
        $lines = [];
        $lineNumber = 1;

        foreach (array_values($rawLines) as $row) {
            if (! is_array($row)) {
                continue;
            }

            $description = trim((string) ($row['description'] ?? ''));
            $quantity = round((float) ($row['quantity'] ?? 0), 3);
            $unitPrice = round((float) ($row['unit_price'] ?? 0), 2);

            if ($description === '' || $quantity <= 0) {
                continue;
            }

            $lineTotal = round($quantity * $unitPrice, 2);
            if ($lineTotal <= 0) {
                continue;
            }

            $unit = trim((string) ($row['unit'] ?? ''));
            $projectZone = trim((string) ($row['project_zone'] ?? ''));

            $lines[] = [
                'line_number' => $lineNumber++,
                'project_zone' => $projectZone !== '' ? $projectZone : null,
                'description' => $description,
                'quantity' => $quantity,
                'unit' => $unit !== '' ? $unit : null,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        return $lines;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function headerFromValidated(array $validated, int $createdBy): array
    {
        $cleanNotes = collect($validated['notes'] ?? [])
            ->map(static fn (mixed $note): string => trim((string) $note))
            ->filter()
            ->values()
            ->all();

        $locale = RfqTermsLocale::tryFrom(strtolower(trim((string) ($validated['print_locale'] ?? 'en'))))
            ?? RfqTermsLocale::En;

        $vendorId = filled($validated['vendor_id'] ?? null) ? (int) $validated['vendor_id'] : null;
        $procurementRequestId = filled($validated['procurement_request_id'] ?? null)
            ? (int) $validated['procurement_request_id']
            : null;
        $vendorName = trim((string) ($validated['vendor_company_name'] ?? ''));

        if ($vendorId !== null) {
            $vendor = Vendor::query()->find($vendorId);
            if ($vendor !== null) {
                $vendorName = VendorPurchaseOrderSnapshot::fromVendor($vendor)['vendor_company_name'] ?? $vendor->name;
            }
        }

        $projectManager = trim((string) ($validated['project_manager_name'] ?? ''));

        return [
            'created_by' => $createdBy,
            'recipient_name' => trim((string) $validated['recipient_name']),
            'project_manager_name' => $projectManager !== '' ? $projectManager : null,
            'documented_at' => now()->toDateString(),
            'po_reference' => null,
            'vendor_id' => $vendorId,
            'vendor_company_name' => $vendorName !== '' ? $vendorName : null,
            'procurement_request_id' => $procurementRequestId,
            'currency_code' => InvoiceCurrencyResolver::normalizeCode($validated['currency_code'] ?? null)
                ?? InvoiceCurrencyResolver::DEFAULT,
            'scope_types' => ScheduleOfWorkScope::encode($validated['scope_types'] ?? []),
            'print_locale' => $locale->value,
            'notes' => $cleanNotes !== [] ? $cleanNotes : null,
            'custom_fees' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $lines
     */
    private function calculateTotalPrice(array $header, array $lines): float
    {
        return round((float) collect($lines)->sum('line_total'), 2);
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function syncItems(ScheduleOfWork $schedule, array $lines): void
    {
        foreach (array_values($lines) as $index => $row) {
            ScheduleOfWorkItem::query()->create([
                'schedule_of_work_id' => $schedule->id,
                'sort_order' => $index,
                'line_number' => $row['line_number'],
                'project_zone' => $row['project_zone'] ?? null,
                'description' => $row['description'],
                'quantity' => $row['quantity'],
                'unit' => $row['unit'] ?? null,
                'unit_price' => $row['unit_price'],
                'line_total' => $row['line_total'],
            ]);
        }
    }
}
