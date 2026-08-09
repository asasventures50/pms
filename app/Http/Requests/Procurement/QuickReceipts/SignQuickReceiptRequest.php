<?php

namespace App\Http\Requests\Procurement\QuickReceipts;

use App\Models\Procurement\QuickReceipts\QuickReceipt;
use Illuminate\Foundation\Http\FormRequest;

class SignQuickReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var QuickReceipt|null $receipt */
        $receipt = $this->route('quick_receipt');
        $user = $this->user();

        if ($user === null || ! $receipt instanceof QuickReceipt) {
            return false;
        }

        return $user->canSignQuickReceipt($receipt);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'attachment' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attachment.required' => 'Upload the signed document to mark this receipt as signed.',
            'attachment.mimes' => 'Attachment must be a JPG, PNG, WEBP, or PDF file.',
            'attachment.max' => 'Attachment may not be larger than 10 MB.',
        ];
    }
}
