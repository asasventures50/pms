<?php

namespace App\Http\Requests\Procurement\ScheduleOfWorks;

use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use App\Enums\Procurement\ScheduleOfWorks\ScheduleOfWorkScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleOfWorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('schedule-of-works.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:255'],
            'project_manager_name' => ['nullable', 'string', 'max:255'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'procurement_request_id' => ['nullable', 'integer', 'exists:procurement_requests,id'],
            'vendor_company_name' => ['nullable', 'string', 'max:255'],
            'currency_code' => ['nullable', 'string', 'size:3', 'alpha'],
            'print_locale' => ['required', 'string', Rule::in(RfqTermsLocale::values())],
            'scope_types' => ['required', 'array', 'min:1'],
            'scope_types.*' => ['string', Rule::in(ScheduleOfWorkScope::values())],
            'scope_of_work' => ['nullable', 'string', 'max:10000'],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string', 'max:2000'],
            'pr_sections' => ['nullable', 'array'],
            'pr_sections.pr_info' => ['nullable', 'array'],
            'pr_sections.delivery' => ['nullable', 'array'],
            'pr_sections.supporting_documents' => ['nullable', 'array'],
            'pr_sections.payment_terms' => ['nullable', 'array'],
            'pr_sections.retentions' => ['nullable', 'array'],
            'pr_sections.maintenance' => ['nullable', 'array'],
            'pr_sections.timeline' => ['nullable', 'array'],
            'pr_sections.compliance' => ['nullable', 'array'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.project_zone' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['required', 'string', 'max:5000'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001', 'max:999999999.999'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
        ];
    }
}
