<?php

namespace App\Http\Requests\Procurement\PurchaseOrders;

use App\Enums\Procurement\PurchaseOrders\PaymentStatus;
use App\Enums\Procurement\PurchaseOrders\PurchaseOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'po_number'       => ['sometimes', 'nullable', 'string', 'max:100', Rule::unique('purchase_orders', 'po_number')->ignore($this->route('purchase_order'))],
            'title'           => ['sometimes', 'required', 'string', 'max:255'],
            'description'     => ['sometimes', 'nullable', 'string'],
            'notes'           => ['sometimes', 'nullable', 'string'],
            'vendor_id'       => ['sometimes', 'nullable', 'integer', Rule::exists('vendors', 'id')->whereNull('deleted_at')],
            'total_price'     => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status'          => ['sometimes', 'string', Rule::in(PurchaseOrderStatus::values())],
            'payment_status'  => ['sometimes', 'string', Rule::in(PaymentStatus::values())],
            'ordered_at'      => ['sometimes', 'nullable', 'date'],
            'delivered_at'    => ['sometimes', 'nullable', 'date'],
        ];
    }
}
