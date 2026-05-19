<?php

namespace App\Http\Requests\Procurement\ProcurementRequests;

use App\Enums\Procurement\ProcurementRequests\ProcurementRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProcurementRequestRequest extends FormRequest
{
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
            'requestor_name' => ['nullable', 'string', 'max:255'],
            'requested_at' => ['nullable', 'date'],
            'requestor_department' => ['nullable', 'string', 'max:255'],
            'required_delivery_date' => ['nullable', 'date'],
            'delivery_location' => ['nullable', 'string', 'max:500'],
            'classification' => ['nullable', 'string', 'max:5000'],
            'supporting_documents' => ['nullable', 'string', 'max:5000'],
            'received_by' => ['nullable', 'string', 'max:255'],
            'procurement_note' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', 'string', Rule::in(ProcurementRequestStatus::values())],
            'items' => ['required', 'array', 'min:1'],
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
