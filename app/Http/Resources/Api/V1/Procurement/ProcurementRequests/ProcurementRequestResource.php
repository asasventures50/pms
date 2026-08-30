<?php

namespace App\Http\Resources\Api\V1\Procurement\ProcurementRequests;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProcurementRequest
 */
class ProcurementRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->status;
        $prequal = $this->compliance_prequalification_level;

        return [
            'id' => $this->id,
            'request_number' => $this->request_number,
            'status' => $status instanceof \BackedEnum ? $status->value : $status,
            'classification' => $this->classification,
            'requestor_name' => $this->requestor_name,
            'requestor_department' => $this->requestor_department,
            'requested_at' => $this->requested_at?->toDateString(),
            'company_key' => $this->company_key,
            'package' => $this->package,
            'project_id' => $this->project_id,
            'zone_id' => $this->zone_id,
            'procurement_types' => $this->procurement_types,
            'geographic_scopes' => $this->geographic_scopes,
            'vendor_types' => $this->vendor_types,
            'justification' => $this->justification,
            'delivery_lead_time_days' => $this->delivery_lead_time_days,
            'delivery_location' => $this->delivery_location,
            'flexible_delivery_date' => $this->flexible_delivery_date,
            'currency_code' => $this->currency_code,
            'samples_required' => $this->samples_required,
            'scope_of_work' => $this->scope_of_work,
            'nda_required' => $this->nda_required,
            'after_sale_service_applicable' => $this->after_sale_service_applicable,
            'compliance_verification_required' => $this->compliance_verification_required,
            'compliance_prequalification_required' => $this->compliance_prequalification_required,
            'compliance_prequalification_level' => $prequal instanceof \BackedEnum ? $prequal->value : $prequal,
            'conflict_of_interest_required' => $this->conflict_of_interest_required,
            'commitment_compliance_required' => $this->commitment_compliance_required,
            'primary_insurance_applicable' => $this->primary_insurance_applicable,
            'primary_insurance_requirements' => $this->primary_insurance_requirements,
            'final_insurance_applicable' => $this->final_insurance_applicable,
            'final_insurance_requirements' => $this->final_insurance_requirements,
            'warranty_years' => $this->warranty_years,
            'warranty_coverage' => $this->warranty_coverage,
            'received_by' => $this->received_by,
            'procurement_note' => $this->procurement_note,
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
                'email' => $this->creator?->email,
            ]),
            'project' => $this->whenLoaded('project', fn () => $this->project ? [
                'id' => $this->project->id,
                'code' => $this->project->code,
                'name' => $this->project->name,
            ] : null),
            'zone' => $this->whenLoaded('zone', fn () => $this->zone ? [
                'id' => $this->zone->id,
                'name' => $this->zone->name,
            ] : null),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'line_number' => $item->line_number,
                'item_name' => $item->item_name,
                'project_id' => $item->project_id,
                'zone_id' => $item->zone_id,
                'category_id' => $item->category_id,
                'subcategory_id' => $item->subcategory_id,
                'description' => $item->description,
                'unit' => $item->unit,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'project' => $item->project ? ['id' => $item->project->id, 'name' => $item->project->name] : null,
                'zone' => $item->zone ? ['id' => $item->zone->id, 'name' => $item->zone->name] : null,
                'catalog_category' => $item->catalogCategory ? [
                    'id' => $item->catalogCategory->id,
                    'name_en' => $item->catalogCategory->name_en ?? null,
                    'name_ar' => $item->catalogCategory->name_ar ?? null,
                ] : null,
                'catalog_subcategory' => $item->catalogSubcategory ? [
                    'id' => $item->catalogSubcategory->id,
                    'name_en' => $item->catalogSubcategory->name_en ?? null,
                    'name_ar' => $item->catalogSubcategory->name_ar ?? null,
                ] : null,
                'documents' => $item->documents->map(fn ($doc) => [
                    'id' => $doc->id,
                    'document_type' => $doc->document_type,
                    'file_name' => $doc->file_name,
                    'file_description' => $doc->file_description,
                    'url' => $doc->url,
                ])->values(),
            ])->values()),
            'payment_terms' => $this->whenLoaded('paymentTerms', fn () => $this->paymentTerms),
            'retentions' => $this->whenLoaded('retentions', fn () => $this->retentions),
            'timeline_entries' => $this->whenLoaded('timelineEntries', fn () => $this->timelineEntries),
            'approvals' => $this->whenLoaded('approvals', fn () => $this->approvals),
            'header_documents' => $this->whenLoaded('headerDocuments', fn () => $this->headerDocuments->map(fn ($doc) => [
                'id' => $doc->id,
                'document_type' => $doc->document_type,
                'file_name' => $doc->file_name,
                'file_description' => $doc->file_description,
                'url' => $doc->url,
            ])->values()),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
