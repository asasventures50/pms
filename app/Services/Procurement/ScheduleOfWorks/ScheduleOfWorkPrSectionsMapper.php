<?php

namespace App\Services\Procurement\ScheduleOfWorks;

use App\Enums\Procurement\ProcurementRequests\CompliancePrequalificationLevel;
use App\Enums\Procurement\ProcurementRequests\ProcurementTimelineActivity;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestDocument;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestPaymentTerm;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestRetention;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestTimelineEntry;
use App\Services\Procurement\PurchaseOrders\PurchaseOrderProcurementRequestContext;
use Illuminate\Support\Collection;

class ScheduleOfWorkPrSectionsMapper
{
    /**
     * @return array<string, mixed>
     */
    public function map(ProcurementRequest $procurementRequest): array
    {
        $procurementRequest->loadMissing([
            'items',
            'project',
            'zone',
            'category',
            'subcategory',
            'paymentTerms',
            'retentions',
            'timelineEntries',
            'headerDocuments',
        ]);

        /** @var Collection<int, \App\Models\Procurement\ProcurementRequests\ProcurementRequestItem> $items */
        $items = $procurementRequest->items
            ->sortBy(['sort_order', 'id'])
            ->values();

        $context = PurchaseOrderProcurementRequestContext::aggregateFromRequest($procurementRequest, $items);

        return ScheduleOfWorkPrSectionsNormalizer::normalize([
            'pr_info' => [
                'project' => $context['project'] ?? '',
                'zone' => $this->zoneLabel($procurementRequest),
                'category' => $this->categoryLabel($procurementRequest),
                'subcategory' => $this->subcategoryLabel($procurementRequest),
                'procurement_type' => $context['procurement_type'] ?? '',
                'geographic_scope' => $context['geographic_scope'] ?? '',
                'vendor_type' => $context['scope_type'] ?? '',
                'samples_required' => $procurementRequest->samples_required,
            ],
            'delivery' => [
                'lead_time_days' => $procurementRequest->delivery_lead_time_days,
                'location' => trim((string) ($procurementRequest->delivery_location ?? '')),
                'flexible_delivery_date' => $procurementRequest->flexible_delivery_date,
            ],
            'supporting_documents' => $this->documents($procurementRequest),
            'payment_terms' => $procurementRequest->paymentTerms
                ->sortBy(['sort_order', 'id'])
                ->values()
                ->map(fn (ProcurementRequestPaymentTerm $row) => [
                    'milestone' => trim((string) ($row->milestone ?? '')),
                    'amount' => trim((string) ($row->amount ?? '')),
                    'percentage' => $row->percentage,
                    'due_upon' => trim((string) ($row->due_upon ?? '')),
                ])
                ->all(),
            'retentions' => $procurementRequest->retentions
                ->sortBy(['sort_order', 'id'])
                ->values()
                ->map(fn (ProcurementRequestRetention $row) => [
                    'retention_percent' => $row->retention_percent,
                    'release_period' => trim((string) ($row->release_period ?? '')),
                ])
                ->all(),
            'maintenance' => [
                'after_sale_service_applicable' => $procurementRequest->after_sale_service_applicable,
                'warranty_years' => $procurementRequest->warranty_years,
                'warranty_coverage' => trim((string) ($procurementRequest->warranty_coverage ?? '')),
            ],
            'timeline' => $this->timelineRows($procurementRequest),
            'compliance' => [
                'verification_required' => $procurementRequest->compliance_verification_required,
                'prequalification_required' => $procurementRequest->compliance_prequalification_required,
                'prequalification_level' => $procurementRequest->compliance_prequalification_level instanceof CompliancePrequalificationLevel
                    ? $procurementRequest->compliance_prequalification_level->value
                    : (is_string($procurementRequest->compliance_prequalification_level)
                        ? $procurementRequest->compliance_prequalification_level
                        : ''),
                'nda_required' => $procurementRequest->nda_required,
                'conflict_of_interest_required' => $procurementRequest->conflict_of_interest_required,
                'commitment_compliance_required' => $procurementRequest->commitment_compliance_required,
            ],
        ]);
    }

    /**
     * @return list<array{document_type: string, file_name: string, file_description: string, file_url: string}>
     */
    private function documents(ProcurementRequest $procurementRequest): array
    {
        $procurementRequest->loadMissing(['headerDocuments', 'items.documents']);

        $rows = [];
        $seen = [];

        foreach ($procurementRequest->headerDocuments as $document) {
            $row = $this->documentRow($document);
            if ($row['file_name'] === '' && $row['file_url'] === '') {
                continue;
            }
            $key = $row['file_name'].'|'.$row['file_url'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $rows[] = $row;
        }

        foreach ($procurementRequest->items as $item) {
            foreach ($item->documents as $document) {
                $row = $this->documentRow($document);
                if ($row['file_name'] === '' && $row['file_url'] === '') {
                    continue;
                }
                $key = $row['file_name'].'|'.$row['file_url'];
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @return array{document_type: string, file_name: string, file_description: string, file_url: string}
     */
    private function documentRow(ProcurementRequestDocument $document): array
    {
        return [
            'document_type' => trim((string) ($document->document_type ?? '')),
            'file_name' => trim((string) ($document->file_name ?? '')),
            'file_description' => trim((string) ($document->file_description ?? '')),
            'file_url' => trim((string) ($document->url ?? '')),
        ];
    }

    /**
     * @return list<array{activity: string, label: string, duration_days: mixed}>
     */
    private function timelineRows(ProcurementRequest $procurementRequest): array
    {
        $byActivity = $procurementRequest->timelineEntries->keyBy(
            fn (ProcurementRequestTimelineEntry $entry) => $entry->activity->value
        );

        $rows = [];
        foreach (ProcurementTimelineActivity::cases() as $activity) {
            $entry = $byActivity->get($activity->value);
            $duration = $entry?->duration_days;
            if ($duration === null || $duration === '') {
                continue;
            }

            $rows[] = [
                'activity' => $activity->value,
                'label' => $activity->label(),
                'duration_days' => $duration,
            ];
        }

        return $rows;
    }

    private function zoneLabel(ProcurementRequest $procurementRequest): string
    {
        $zone = $procurementRequest->zone;
        if ($zone === null) {
            return '';
        }

        $code = trim((string) ($zone->code ?? ''));
        $name = trim((string) ($zone->name ?? ''));

        return match (true) {
            $code !== '' && $name !== '' => $code.' — '.$name,
            $name !== '' => $name,
            $code !== '' => $code,
            default => '',
        };
    }

    private function categoryLabel(ProcurementRequest $procurementRequest): string
    {
        $category = $procurementRequest->category;
        if ($category === null) {
            return '';
        }

        $en = trim((string) ($category->name_en ?? ''));
        $ar = trim((string) ($category->name_ar ?? ''));

        return match (true) {
            $en !== '' && $ar !== '' => $ar.' — '.$en,
            $en !== '' => $en,
            $ar !== '' => $ar,
            default => '',
        };
    }

    private function subcategoryLabel(ProcurementRequest $procurementRequest): string
    {
        $subcategory = $procurementRequest->subcategory;
        if ($subcategory === null) {
            return '';
        }

        $en = trim((string) ($subcategory->name_en ?? ''));
        $ar = trim((string) ($subcategory->name_ar ?? ''));

        return match (true) {
            $en !== '' && $ar !== '' => $ar.' — '.$en,
            $en !== '' => $en,
            $ar !== '' => $ar,
            default => '',
        };
    }
}
