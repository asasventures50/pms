<?php

namespace App\Http\Requests\Procurement\ProcurementRequests;

use App\Enums\Procurement\ProcurementRequests\GeographicScope;
use App\Enums\Procurement\ProcurementRequests\ProcurementApprovalRole;
use App\Enums\Procurement\ProcurementRequests\ProcurementRequestStatus;
use App\Enums\Procurement\ProcurementRequests\ProcurementTimelineActivity;
use App\Enums\Procurement\ProcurementRequests\ProcurementType;
use App\Enums\Procurement\ProcurementRequests\ProcurementVendorType;
use App\Http\Requests\Procurement\ProcurementRequests\Concerns\NormalizesProcurementCheckboxFields;
use App\Http\Requests\Procurement\ProcurementRequests\Concerns\PreparesHeaderSupportingDocuments;
use App\Http\Requests\Procurement\ProcurementRequests\Concerns\ValidatesProcurementRequestHeader;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProcurementRequestRequest extends FormRequest
{
    use NormalizesProcurementCheckboxFields;
    use PreparesHeaderSupportingDocuments;
    use ValidatesProcurementRequestHeader;

    protected function prepareForValidation(): void
    {
        $this->normalizeProcurementCheckboxFields();
        $this->prepareHeaderSupportingDocumentsForValidation();
    }

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('procurement-requests.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var ProcurementRequest|null $procurementRequest */
        $procurementRequest = $this->route('procurement_request');

        return [
            'request_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('procurement_requests', 'request_number')->ignore($procurementRequest?->id),
            ],
            'classification' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'string', Rule::in(ProcurementRequestStatus::values())],
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'zone_id' => ['nullable', 'integer', 'exists:zones,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'integer', 'exists:subcategories,id'],
            'procurement_types' => ['nullable', 'array'],
            'procurement_types.*' => ['required', 'string', Rule::in(ProcurementType::values())],
            'geographic_scopes' => ['required', 'array', 'min:1'],
            'geographic_scopes.*' => ['required', 'string', Rule::in(GeographicScope::values())],
            'vendor_types' => ['nullable', 'array'],
            'vendor_types.*' => ['required', 'string', Rule::in(ProcurementVendorType::values())],
            'justification' => ['nullable', 'string', 'max:10000'],
            'delivery_lead_time_days' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'delivery_location' => ['required', 'string', 'max:500'],
            'flexible_delivery_date' => ['nullable', 'boolean'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'samples_required' => ['nullable', 'boolean'],
            'scope_of_work' => ['nullable', 'string'],
            'nda_required' => ['nullable', 'boolean'],
            'primary_insurance_applicable' => ['nullable', 'boolean'],
            'final_insurance_applicable' => ['nullable', 'boolean'],
            'warranty_years' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'warranty_coverage' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => [
                'nullable',
                'integer',
                Rule::exists('procurement_request_items', 'id')->where(
                    'procurement_request_id',
                    $procurementRequest?->id
                ),
            ],
            'items.*.item_name' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['required', 'string', 'max:5000'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.total_price' => ['nullable', 'numeric', 'min:0'],
            'payment_terms' => ['nullable', 'array'],
            'payment_terms.*.id' => [
                'nullable',
                'integer',
                Rule::exists('procurement_request_payment_terms', 'id')->where(
                    'procurement_request_id',
                    $procurementRequest?->id
                ),
            ],
            'payment_terms.*.milestone' => ['nullable', 'string', 'max:255'],
            'payment_terms.*.amount' => ['nullable', 'string', 'max:255'],
            'payment_terms.*.percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_terms.*.due_upon' => ['nullable', 'string', 'max:500'],
            'retentions' => ['nullable', 'array'],
            'retentions.*.id' => [
                'nullable',
                'integer',
                Rule::exists('procurement_request_retentions', 'id')->where(
                    'procurement_request_id',
                    $procurementRequest?->id
                ),
            ],
            'retentions.*.retention_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'retentions.*.release_period' => ['nullable', 'string', 'max:255'],
            'timeline' => ['nullable', 'array'],
            'timeline.*.activity' => ['required', 'string', Rule::in(ProcurementTimelineActivity::values())],
            'timeline.*.duration_days' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'approvals' => ['nullable', 'array'],
            'approvals.*.role' => ['required', 'string', Rule::in(ProcurementApprovalRole::values())],
            'approvals.*.name' => ['nullable', 'string', 'max:255'],
            'approvals.*.signature' => ['nullable', 'string', 'max:255'],
            'approvals.*.signed_at' => ['nullable', 'date'],
            'supporting_document_rows' => ['nullable', 'array'],
            'supporting_document_rows.*.file' => ['nullable', 'file', 'max:204800', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp,zip,rar'],
            'supporting_document_rows.*.document_type' => ['nullable', 'string', 'max:255'],
            'supporting_document_rows.*.file_description' => ['nullable', 'string', 'max:2000'],
            'supporting_document_rows.*.url' => ['nullable', 'string', 'url', 'max:2000'],
            'supporting_document_rows.*.name' => ['nullable', 'string', 'max:255'],
            'remove_supporting_document_ids' => ['nullable', 'array'],
            'remove_supporting_document_ids.*' => ['integer'],
        ];
    }
}
