<?php

namespace App\Services\Procurement\ScheduleOfWorks;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Services\Procurement\Invoices\InvoiceProjectZoneResolver;

class ScheduleOfWorkPrItemsPresenter
{
    /**
     * @return array{
     *     request_number: string,
     *     currency_code: string|null,
     *     items: list<array{
     *         id: int,
     *         line_code: string,
     *         project_zone: string|null,
     *         description: string,
     *         quantity: float,
     *         unit: string,
     *         unit_price: float,
     *         line_total: float
     *     }>
     * }
     */
    public function present(ProcurementRequest $procurementRequest): array
    {
        $procurementRequest->loadMissing([
            'items.project',
            'items.zone',
            'project',
            'zone',
        ]);

        $items = $procurementRequest->items
            ->sortBy(['sort_order', 'id'])
            ->values()
            ->map(fn (ProcurementRequestItem $line) => $this->toLine($procurementRequest, $line))
            ->all();

        return [
            'request_number' => trim((string) ($procurementRequest->request_number ?? '')),
            'currency_code' => filled($procurementRequest->currency_code)
                ? strtoupper(trim((string) $procurementRequest->currency_code))
                : null,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toLine(ProcurementRequest $request, ProcurementRequestItem $line): array
    {
        $quantity = round((float) $line->quantity, 3);
        $unitPrice = round((float) ($line->unit_price ?? 0), 2);
        $lineTotal = round($quantity * $unitPrice, 2);

        return [
            'id' => (int) $line->id,
            'line_code' => trim((string) ($line->line_number ?? '')),
            'project_zone' => InvoiceProjectZoneResolver::formatLabel($line, $request),
            'description' => trim((string) ($line->description ?? '')),
            'quantity' => $quantity,
            'unit' => trim((string) ($line->unit ?? '')),
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
        ];
    }
}
