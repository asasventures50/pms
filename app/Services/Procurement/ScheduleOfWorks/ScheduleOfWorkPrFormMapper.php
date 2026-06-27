<?php

namespace App\Services\Procurement\ScheduleOfWorks;

use App\Enums\Procurement\ProcurementRequests\GeographicScope;
use App\Enums\Procurement\ScheduleOfWorks\ScheduleOfWorkScope;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Services\Procurement\PurchaseOrders\PurchaseOrderProcurementRequestContext;
use Illuminate\Support\Collection;

class ScheduleOfWorkPrFormMapper
{
    public function __construct(
        private readonly ScheduleOfWorkPrSectionsMapper $sectionsMapper,
    ) {}
    /**
     * Maps procurement request header fields to schedule-of-works form values.
     *
     * @return array{
     *     recipient_name: string|null,
     *     project_manager_name: string|null,
     *     scope_of_work: string|null,
     *     currency_code: string|null,
     *     scope_types: list<string>,
     *     notes: list<string>,
     *     pr_sections: array<string, mixed>|null
     * }
     */
    public function map(ProcurementRequest $procurementRequest): array
    {
        $procurementRequest->loadMissing([
            'items',
            'project',
        ]);

        /** @var Collection<int, \App\Models\Procurement\ProcurementRequests\ProcurementRequestItem> $items */
        $items = $procurementRequest->items
            ->sortBy(['sort_order', 'id'])
            ->values();

        $scopeTypeKeys = PurchaseOrderProcurementRequestContext::scopeTypeKeysFromRequest($procurementRequest, $items);
        $hasGeographicScope = GeographicScope::selectedValues($procurementRequest->geographic_scopes) !== [];

        return [
            'recipient_name' => $this->recipientName($procurementRequest),
            'project_manager_name' => $this->projectManagerName($procurementRequest),
            'scope_of_work' => $this->nullableTrim($procurementRequest->scope_of_work),
            'currency_code' => filled($procurementRequest->currency_code)
                ? strtoupper(trim((string) $procurementRequest->currency_code))
                : null,
            'scope_types' => ScheduleOfWorkScope::fromProcurementScopeTypeKeys($scopeTypeKeys, $hasGeographicScope),
            'notes' => $this->notes($procurementRequest),
            'pr_sections' => $this->sectionsMapper->map($procurementRequest),
        ];
    }

    private function recipientName(ProcurementRequest $procurementRequest): ?string
    {
        $requestor = $this->nullableTrim($procurementRequest->requestor_name);
        if ($requestor !== null) {
            return $requestor;
        }

        return $this->nullableTrim($procurementRequest->received_by);
    }

    private function projectManagerName(ProcurementRequest $procurementRequest): ?string
    {
        $project = $procurementRequest->project;
        if ($project === null) {
            return null;
        }

        $code = trim((string) ($project->code ?? ''));
        $name = trim((string) ($project->name ?? ''));

        return match (true) {
            $code !== '' && $name !== '' => $code.' — '.$name,
            $name !== '' => $name,
            $code !== '' => $code,
            default => null,
        };
    }

    /**
     * @return list<string>
     */
    private function notes(ProcurementRequest $procurementRequest): array
    {
        $notes = [];

        $justification = $this->nullableTrim($procurementRequest->justification);
        if ($justification !== null) {
            $notes[] = $justification;
        }

        $procurementNote = $this->nullableTrim($procurementRequest->procurement_note);
        if ($procurementNote !== null) {
            $notes[] = $procurementNote;
        }

        return $notes;
    }

    private function nullableTrim(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : null;
    }
}
