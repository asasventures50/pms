<?php

namespace App\Services\Procurement\ProcurementRequests;

use App\Enums\Procurement\PrCompany;
use App\Enums\Procurement\ProcurementRequests\GeographicScope;
use App\Enums\Procurement\ProcurementRequests\ProcurementApprovalRole;
use App\Enums\Procurement\ProcurementRequests\ProcurementTimelineActivity;
use App\Enums\Procurement\ProcurementRequests\ProcurementType;
use App\Enums\Procurement\ProcurementRequests\ProcurementVendorType;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestApproval;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestTimelineEntry;
use App\Support\Procurement\ProcurementCheckboxGroup;
use App\Support\Procurement\ProcurementScopeType;

class ProcurementRequestFormDataResolver
{
    /**
     * @return array<string, mixed>
     */
    public function resolve(ProcurementRequest $procurementRequest): array
    {
        $procurementRequest->loadMissing([
            'items.project',
            'items.zone',
            'items.documents',
            'project',
            'zone',
            'category',
            'subcategory',
            'paymentTerms',
            'retentions',
            'timelineEntries',
            'approvals',
            'headerDocuments',
        ]);

        $firstItem = $procurementRequest->items->first();

        return [
            'company_key' => $procurementRequest->company_key ?? PrCompany::AsasVentures->value,
            'project_id' => $procurementRequest->project_id ?? $firstItem?->project_id,
            'category_id' => $procurementRequest->category_id,
            'subcategory_id' => $procurementRequest->subcategory_id,
            'legacy_category' => $firstItem?->category,
            'legacy_subcategory' => $firstItem?->subcategory,
            'procurement_types' => ProcurementCheckboxGroup::selectedValues(
                $procurementRequest->procurement_types,
                ProcurementType::values()
            ),
            'geographic_scopes' => GeographicScope::formSelectedValues($procurementRequest->geographic_scopes),
            'vendor_types' => $this->resolveVendorTypes($procurementRequest, $firstItem),
            'justification' => $procurementRequest->justification ?? $firstItem?->justification,
            'delivery_lead_time_days' => $procurementRequest->delivery_lead_time_days,
            'delivery_location' => $procurementRequest->delivery_location ?? $firstItem?->delivery_location,
            'flexible_delivery_date' => $procurementRequest->flexible_delivery_date
                ?? $firstItem?->flexible_delivery_date
                ?? true,
            'currency_code' => $procurementRequest->currency_code,
            'samples_required' => $procurementRequest->samples_required,
            'scope_of_work' => $procurementRequest->scope_of_work ?? $firstItem?->scope_of_work,
            'nda_required' => $procurementRequest->nda_required,
            'after_sale_service_applicable' => $procurementRequest->after_sale_service_applicable,
            'compliance_verification_required' => $procurementRequest->compliance_verification_required,
            'compliance_prequalification_required' => $procurementRequest->compliance_prequalification_required,
            'compliance_prequalification_level' => $procurementRequest->compliance_prequalification_level?->value,
            'conflict_of_interest_required' => $procurementRequest->conflict_of_interest_required,
            'commitment_compliance_required' => $procurementRequest->commitment_compliance_required,
            'primary_insurance_applicable' => $procurementRequest->primary_insurance_applicable,
            'primary_insurance_requirements' => $procurementRequest->primary_insurance_requirements,
            'final_insurance_applicable' => $procurementRequest->final_insurance_applicable,
            'final_insurance_requirements' => $procurementRequest->final_insurance_requirements,
            'warranty_years' => $procurementRequest->warranty_years,
            'warranty_coverage' => $procurementRequest->warranty_coverage,
            'items' => $this->resolveBoqItems($procurementRequest),
            'payment_terms' => $this->resolvePaymentTerms($procurementRequest),
            'retentions' => $this->resolveRetentions($procurementRequest),
            'timeline' => $this->resolveTimeline($procurementRequest),
            'approvals' => $this->resolveApprovals($procurementRequest),
            'header_documents' => $procurementRequest->headerDocuments,
            'legacy_item_documents' => $procurementRequest->items->flatMap(fn ($item) => $item->documents),
        ];
    }

    /**
     * @return list<string>
     */
    private function resolveVendorTypes(ProcurementRequest $procurementRequest, ?ProcurementRequestItem $firstItem): array
    {
        $fromHeader = ProcurementCheckboxGroup::selectedValues(
            $procurementRequest->vendor_types,
            ProcurementVendorType::values()
        );

        if ($fromHeader !== []) {
            return $fromHeader;
        }

        if ($firstItem === null) {
            return [];
        }

        $legacy = ProcurementScopeType::selectedValues($firstItem->scope_type);
        $map = [
            'Contractor' => ProcurementVendorType::Contractor->value,
            'Supplier' => ProcurementVendorType::Supplier->value,
            'Studies' => ProcurementVendorType::Studies->value,
        ];

        $values = [];
        foreach ($legacy as $label) {
            if (isset($map[$label])) {
                $values[] = $map[$label];
            }
        }

        return $values;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function resolveBoqItems(ProcurementRequest $procurementRequest): array
    {
        return $procurementRequest->items->map(fn (ProcurementRequestItem $row) => [
            'id' => $row->id,
            'line_number' => $row->line_number,
            'item_name' => $row->item_name ?? $row->line_number,
            'zone_id' => $row->zone_id ?? $procurementRequest->zone_id,
            'description' => $row->description,
            'quantity' => $row->quantity,
            'unit' => $row->unit,
            'unit_price' => $row->unit_price,
            'total_price' => $row->total_price,
        ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function resolvePaymentTerms(ProcurementRequest $procurementRequest): array
    {
        $rows = $procurementRequest->paymentTerms->sortBy('sort_order')->values();

        if ($rows->isEmpty()) {
            return [['milestone' => '', 'amount' => '', 'percentage' => '', 'due_upon' => '']];
        }

        return $rows->map(fn ($row) => [
            'id' => $row->id,
            'milestone' => $row->milestone,
            'amount' => $row->amount,
            'percentage' => $row->percentage,
            'due_upon' => $row->due_upon,
        ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function resolveRetentions(ProcurementRequest $procurementRequest): array
    {
        $rows = $procurementRequest->retentions->sortBy('sort_order')->values();

        if ($rows->isEmpty()) {
            return [['retention_percent' => '', 'release_period' => '']];
        }

        return $rows->map(fn ($row) => [
            'id' => $row->id,
            'retention_percent' => $row->retention_percent,
            'release_period' => $row->release_period,
        ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function resolveTimeline(ProcurementRequest $procurementRequest): array
    {
        $byActivity = $procurementRequest->timelineEntries->keyBy(
            fn (ProcurementRequestTimelineEntry $entry) => $entry->activity->value
        );

        $rows = [];
        foreach (ProcurementTimelineActivity::cases() as $activity) {
            $entry = $byActivity->get($activity->value);
            $rows[] = [
                'activity' => $activity->value,
                'label' => $activity->label(),
                'duration_days' => $entry?->duration_days,
            ];
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function resolveApprovals(ProcurementRequest $procurementRequest): array
    {
        $byRole = $procurementRequest->approvals->keyBy(
            fn (ProcurementRequestApproval $row) => $row->role->value
        );

        $requestorName = trim((string) ($procurementRequest->requestor_name ?? $procurementRequest->creator?->name ?? ''));

        $rows = [];
        foreach (ProcurementApprovalRole::cases() as $role) {
            $row = $byRole->get($role->value);
            $name = trim((string) ($row?->name ?? ''));

            if ($name === '' && $role === ProcurementApprovalRole::Requester) {
                $name = $requestorName;
            }

            if ($name === '' && $role === ProcurementApprovalRole::ReceivedBy) {
                $name = trim((string) ($procurementRequest->received_by ?? ''));
            }

            $signedAt = $row?->signed_at?->format('Y-m-d');
            if ($signedAt === null
                && $role === ProcurementApprovalRole::Requester
                && $procurementRequest->requested_at !== null) {
                $signedAt = $procurementRequest->requested_at->format('Y-m-d');
            }

            $rows[] = [
                'role' => $role->value,
                'label' => $role->label(),
                'name' => $name !== '' ? $name : null,
                'signature' => $row?->signature,
                'signed_at' => $signedAt,
            ];
        }

        return $rows;
    }
}
