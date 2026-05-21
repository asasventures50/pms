<?php

namespace App\Http\Requests\Procurement\ProcurementRequests;

use App\Enums\Procurement\ProcurementRequests\ProcurementRequestStatus;
use App\Http\Requests\Procurement\ProcurementRequests\Concerns\NormalizesProcurementDeliveryDate;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProcurementRequestRequest extends FormRequest
{
    use NormalizesProcurementDeliveryDate;
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
            'delivery_location' => ['nullable', 'string', 'max:500'],
            'classification' => ['nullable', 'string', 'max:500'],
            'supporting_documents' => ['nullable', 'array'],
            'supporting_documents.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp'],
            'remove_supporting_document_ids' => ['nullable', 'array'],
            'remove_supporting_document_ids.*' => ['integer'],
            'status' => ['nullable', 'string', Rule::in(ProcurementRequestStatus::values())],
            'items' => ['required', 'array', 'min:1'],
            'items.*.project' => ['nullable', 'string', 'max:255'],
            'items.*.zone' => ['nullable', 'string', 'max:100'],
            'items.*.category' => ['nullable', 'string', 'max:255'],
            'items.*.subcategory' => ['nullable', 'string', 'max:255'],
            'items.*.scope_type' => ['nullable', 'string', 'max:100'],
            'items.*.description' => ['required', 'string', 'max:5000'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.justification' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
