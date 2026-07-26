<?php

namespace App\Http\Requests\Procurement\QuickReceipts;

use App\Enums\Procurement\PrCompany;
use App\Models\Procurement\QuickReceipts\QuickReceipt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuickReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var QuickReceipt|null $receipt */
        $receipt = $this->route('quick_receipt');

        if ($user === null || $receipt === null) {
            return false;
        }

        if (! $user->hasPermission('quick-receipts.update') && ! $user->hasPermission('quick-receipts.create')) {
            return false;
        }

        return $user->canUpdateQuickReceipt($receipt);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'currency_code' => ['nullable', 'string', 'size:3', 'alpha'],
            'expense_date' => ['required', 'date'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'company_key' => ['required', 'string', Rule::in(PrCompany::values())],
            'provider_name' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Select a category.',
            'category_id.exists' => 'The selected category is invalid.',
            'company_key.required' => 'Select a company.',
            'attachment.mimes' => 'Attachment must be a JPG, PNG, WEBP, or PDF file.',
            'attachment.max' => 'Attachment may not be larger than 10 MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('currency_code')) {
            $this->merge([
                'currency_code' => strtoupper(trim((string) $this->input('currency_code'))),
            ]);
        }
    }
}
