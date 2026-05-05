<?php

namespace App\Http\Requests\Procurement\PurchaseOrders;

use App\Enums\Procurement\PurchaseOrders\PaymentStatus;
use App\Enums\Procurement\PurchaseOrders\PurchaseOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseOrderRequest extends FormRequest
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
            'po_number'       => ['nullable', 'string', 'max:100', Rule::unique('purchase_orders', 'po_number')],
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'notes'           => ['nullable', 'string'],
            'vendor_id'       => ['nullable', 'integer', Rule::exists('vendors', 'id')->whereNull('deleted_at')],
            'total_price'     => ['nullable', 'numeric', 'min:0'],
            'status'          => ['nullable', 'string', Rule::in(PurchaseOrderStatus::values())],
            'payment_status'  => ['nullable', 'string', Rule::in(PaymentStatus::values())],
            'ordered_at'      => ['nullable', 'date'],
            'delivered_at'    => ['nullable', 'date'],
        ];
    }
}
