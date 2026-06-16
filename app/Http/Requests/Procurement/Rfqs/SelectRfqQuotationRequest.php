<?php

namespace App\Http\Requests\Procurement\Rfqs;

use App\Models\Procurement\Rfqs\Rfq;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SelectRfqQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $rfq = $this->route('rfq');

        return $rfq instanceof Rfq
            && ($this->user()?->canSelectQuotationForRfq($rfq) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Rfq $rfq */
        $rfq = $this->route('rfq');

        return [
            'vendor_quotation_id' => [
                'required',
                'integer',
                Rule::exists('vendor_quotations', 'id')
                    ->where(fn ($query) => $query->where('rfq_id', $rfq->id)->whereNull('deleted_at')),
            ],
        ];
    }
}
