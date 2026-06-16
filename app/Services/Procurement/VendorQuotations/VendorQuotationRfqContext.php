<?php

namespace App\Services\Procurement\VendorQuotations;

use App\Enums\Procurement\ProcurementRequests\GeographicScope;
use App\Enums\Procurement\ProcurementRequests\ProcurementType;
use App\Enums\Procurement\ProcurementRequests\ProcurementVendorType;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestDocument;
use App\Models\Procurement\Rfqs\Rfq;
use App\Support\Procurement\ProcurementCheckboxGroup;
use Carbon\Carbon;

class VendorQuotationRfqContext
{
    /**
     * @return array<string, mixed>
     */
    public static function resolve(Rfq $rfq): array
    {
        $rfq = self::ensureRfqRelations($rfq);
        $procurementRequest = self::procurementRequestFromRfq($rfq);
        $requestLines = self::requestLines($rfq);

        return [
            'procurement_request' => $procurementRequest,
            'pr_number' => $procurementRequest?->request_number,
            'revision_number' => (int) ($rfq->revision_number ?? 0),
            'department' => $procurementRequest?->requestor_department
                ?? $procurementRequest?->project?->name,
            'procurement_mode' => $procurementRequest
                ? (ProcurementCheckboxGroup::display(
                    $procurementRequest->procurement_types,
                    ProcurementType::values(),
                    fn (string $value) => ProcurementType::from($value)->label(),
                ) ?: '—')
                : '—',
            'sourcing_type' => $procurementRequest
                ? (GeographicScope::display($procurementRequest->geographic_scopes) ?: '—')
                : '—',
            'vendor_category' => $procurementRequest
                ? (ProcurementCheckboxGroup::display(
                    $procurementRequest->vendor_types,
                    ProcurementVendorType::values(),
                    fn (string $value) => ProcurementVendorType::from($value)->label(),
                ) ?: '—')
                : '—',
            'delivery_location' => $procurementRequest?->delivery_location,
            'required_delivery_date' => self::headerRequiredDeliveryDate($procurementRequest, $requestLines),
            'required_lead_time' => $procurementRequest?->delivery_lead_time_days !== null
                ? (string) $procurementRequest->delivery_lead_time_days
                : null,
            'samples_required' => match ($procurementRequest?->samples_required) {
                true => 'Yes',
                false => 'No',
                default => '—',
            },
            'procurement_officer' => $rfq->creator?->name,
            'buyer_contact_person' => $rfq->creator?->name,
            'submission_deadline_display' => self::formatSubmissionDeadline($rfq),
            'submission_timezone' => $rfq->submission_timezone ?? config('app.timezone'),
            'request_lines' => $requestLines,
            'supporting_documents' => self::supportingDocuments($rfq),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $requestLines
     */
    private static function headerRequiredDeliveryDate(?ProcurementRequest $procurementRequest, array $requestLines): ?string
    {
        if ($procurementRequest?->flexible_delivery_date) {
            return 'Flexible';
        }

        foreach ($requestLines as $line) {
            $date = $line['required_delivery_date'] ?? null;
            if ($date && $date !== 'Flexible') {
                return $date;
            }
        }

        return null;
    }

    private static function ensureRfqRelations(Rfq $rfq): Rfq
    {
        if (! $rfq->relationLoaded('creator')) {
            $rfq->load('creator');
        }

        if (! $rfq->relationLoaded('items')) {
            $rfq->load([
                'items.procurementRequestItem.documents',
                'items.procurementRequestItem.procurementRequest.project',
            ]);
        } else {
            $rfq->loadMissing([
                'items.procurementRequestItem.documents',
                'items.procurementRequestItem.procurementRequest.project',
            ]);
        }

        return $rfq;
    }

    private static function procurementRequestFromRfq(Rfq $rfq): ?ProcurementRequest
    {
        $procurementRequest = $rfq->items->first()?->procurementRequestItem?->procurementRequest;

        if ($procurementRequest && ! $procurementRequest->relationLoaded('project')) {
            $procurementRequest->load('project');
        }

        return $procurementRequest;
    }

    private static function formatSubmissionDeadline(Rfq $rfq): string
    {
        if ($rfq->submission_deadline_at) {
            $tz = $rfq->submission_timezone ?? config('app.timezone');
            $at = $rfq->submission_deadline_at instanceof Carbon
                ? $rfq->submission_deadline_at
                : Carbon::parse($rfq->submission_deadline_at);

            return $at->timezone($tz)->format('Y-m-d H:i').' ('.$tz.')';
        }

        if ($rfq->submission_deadline) {
            $date = $rfq->submission_deadline->format('Y-m-d');
            $tz = $rfq->submission_timezone;

            return $tz ? $date.' ('.$tz.')' : $date;
        }

        return '—';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function requestLines(Rfq $rfq): array
    {
        return $rfq->items->map(function ($line) {
            $prItem = $line->procurementRequestItem;

            return [
                'line_number' => $line->item,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit' => $line->unit,
                'delivery_location' => $prItem?->delivery_location,
                'required_delivery_date' => $prItem?->required_delivery_date?->format('Y-m-d')
                    ?? ($prItem?->flexible_delivery_date ? 'Flexible' : null)
                    ?? $line->request_lead_time,
                'required_lead_time' => $line->request_lead_time,
                'scope_reference' => trim((string) ($prItem?->scope_of_work ?: $prItem?->justification ?: '')) ?: null,
            ];
        })->values()->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function supportingDocuments(Rfq $rfq): array
    {
        $rows = [];
        $index = 0;

        foreach ($rfq->items as $line) {
            $documents = $line->procurementRequestItem?->documents ?? collect();

            /** @var ProcurementRequestDocument $document */
            foreach ($documents as $document) {
                $index++;
                $rows[] = [
                    'number' => $index,
                    'line_number' => $line->item,
                    'document_type' => $document->document_type ?: '—',
                    'file_name' => $document->file_name,
                    'file_description' => $document->file_description,
                    'url' => $document->url,
                    'is_link' => ProcurementRequestDocument::isExternalUrl($document->file_path),
                ];
            }
        }

        return $rows;
    }
}
