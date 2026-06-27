<?php

namespace App\Services\Procurement\ScheduleOfWorks;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Services\Procurement\Invoices\InvoiceProjectZoneResolver;

class ScheduleOfWorkPrItemsPresenter
{
    public function __construct(
        private readonly ScheduleOfWorkPrFormMapper $formMapper,
    ) {}

    /**
     * @return array{
     *     request_number: string,
     *     form: array{
     *         recipient_name: string|null,
     *         project_manager_name: string|null,
     *         scope_of_work: string|null,
     *         currency_code: string|null,
     *         scope_types: list<string>,
     *         notes: list<string>
     *     },
     *     currency_code: string|null,
     *     scope_types: list<string>,
     *     notes: list<string>,
     *     recipient_name: string|null,
     *     project_manager_name: string|null,
     *     scope_of_work: string|null,
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
            ->values();

        $form = $this->formMapper->map($procurementRequest);

        return [
            'request_number' => trim((string) ($procurementRequest->request_number ?? '')),
            'form' => $form,
            'currency_code' => $form['currency_code'],
            'scope_types' => $form['scope_types'],
            'notes' => $form['notes'],
            'recipient_name' => $form['recipient_name'],
            'project_manager_name' => $form['project_manager_name'],
            'scope_of_work' => $form['scope_of_work'],
            'pr_sections' => $form['pr_sections'] ?? null,
            'items' => $items
                ->map(fn (ProcurementRequestItem $line) => $this->toLine($procurementRequest, $line))
                ->all(),
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
