<?php

namespace App\Http\Requests\Procurement\ProcurementRequests;

use App\Enums\Procurement\ProcurementRequests\ProcurementRequestStatus;
use App\Support\Procurement\ProcurementScopeType;
use App\Http\Requests\Procurement\ProcurementRequests\Concerns\NormalizesProcurementDeliveryDate;
use App\Http\Requests\Procurement\ProcurementRequests\Concerns\PreparesSupportingDocuments;
use App\Http\Requests\Procurement\ProcurementRequests\Concerns\ValidatesProcurementRequestLineItems;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProcurementRequestRequest extends FormRequest
{
    use NormalizesProcurementDeliveryDate {
        prepareForValidation as normalizeDeliveryDateFields;
    }
    use PreparesSupportingDocuments;
    use ValidatesProcurementRequestLineItems;

    protected function prepareForValidation(): void
    {
        $this->normalizeDeliveryDateFields();
        $this->prepareSupportingDocumentsForValidation();
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
            'required_delivery_date' => ['nullable', 'date', 'required_unless:flexible_delivery_date,1,true'],
            'flexible_delivery_date' => ['nullable', 'boolean'],
            'delivery_location' => ['required', 'string', 'max:500'],
            'classification' => ['nullable', 'string', 'max:500'],
            'supporting_documents' => ['nullable', 'array'],
            'supporting_documents.*' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp,zip,rar'],
            'remove_supporting_document_ids' => ['nullable', 'array'],
            'remove_supporting_document_ids.*' => ['integer'],
            'status' => ['nullable', 'string', Rule::in(ProcurementRequestStatus::values())],
            'items' => ['required', 'array', 'min:1'],
            'items.*.project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'items.*.zone_id' => ['nullable', 'integer', 'exists:zones,id'],
            'items.*.category' => ['nullable', 'string', 'max:255'],
            'items.*.subcategory' => ['nullable', 'string', 'max:255'],
            'items.*.scope_type' => ['nullable', 'array'],
            'items.*.scope_type.*' => ['string', Rule::in(ProcurementScopeType::values())],
            'items.*.description' => ['required', 'string', 'max:5000'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.justification' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
