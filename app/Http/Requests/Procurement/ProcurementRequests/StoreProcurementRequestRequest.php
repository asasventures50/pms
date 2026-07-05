<?php

namespace App\Http\Requests\Procurement\ProcurementRequests;

use App\Enums\Procurement\PrCompany;
use App\Enums\Procurement\ProcurementRequests\CompliancePrequalificationLevel;
use App\Enums\Procurement\ProcurementRequests\GeographicScope;
use App\Enums\Procurement\ProcurementRequests\ProcurementApprovalRole;
use App\Enums\Procurement\ProcurementRequests\ProcurementRequestStatus;
use App\Enums\Procurement\ProcurementRequests\ProcurementTimelineActivity;
use App\Enums\Procurement\ProcurementRequests\ProcurementType;
use App\Enums\Procurement\ProcurementRequests\ProcurementVendorType;
use App\Http\Requests\Procurement\ProcurementRequests\Concerns\NormalizesProcurementCheckboxFields;
use App\Http\Requests\Procurement\ProcurementRequests\Concerns\PreparesHeaderSupportingDocuments;
use App\Http\Requests\Procurement\ProcurementRequests\Concerns\ValidatesProcurementRequestHeader;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProcurementRequestRequest extends FormRequest
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
        return $this->user()?->hasPermission('procurement-requests.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'request_number' => ['nullable', 'string', 'max:100', Rule::unique('procurement_requests', 'request_number')],
            'classification' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'string', Rule::in(ProcurementRequestStatus::values())],
            'company_key' => ['required', 'string', Rule::in(PrCompany::values())],
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'package' => ['nullable', 'string', 'max:500'],
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
            'scope_of_work' => ['required', 'string', 'max:50000'],
            'nda_required' => ['nullable', 'boolean'],
            'after_sale_service_applicable' => ['nullable', 'boolean'],
            'compliance_verification_required' => ['nullable', 'boolean'],
            'compliance_prequalification_required' => ['nullable', 'boolean'],
            'compliance_prequalification_level' => ['nullable', 'string', Rule::in(CompliancePrequalificationLevel::values())],
            'conflict_of_interest_required' => ['nullable', 'boolean'],
            'commitment_compliance_required' => ['nullable', 'boolean'],
            'warranty_years' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'warranty_coverage' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['nullable', 'string', 'max:255'],
            'items.*.zone_id' => ['nullable', 'integer', 'exists:zones,id'],
            'items.*.description' => ['required', 'string', 'max:5000'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.total_price' => ['nullable', 'numeric', 'min:0'],
            'payment_terms' => ['nullable', 'array'],
            'payment_terms.*.milestone' => ['nullable', 'string', 'max:255'],
            'payment_terms.*.amount' => ['nullable', 'string', 'max:255'],
            'payment_terms.*.percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_terms.*.due_upon' => ['nullable', 'string', 'max:500'],
            'retentions' => ['nullable', 'array'],
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
        ];
    }
}
