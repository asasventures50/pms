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
        return $this->user()?->hasPermission('purchase-orders.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->headerRules(), [
            'items' => ['required', 'array', 'min:1'],
            'items.*.item' => ['nullable', 'string', 'max:100'],
            'items.*.description' => ['required', 'string', 'max:5000'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function headerRules(): array
    {
        return [
            'po_number' => ['nullable', 'string', 'max:100', Rule::unique('purchase_orders', 'po_number')],
            'title' => ['nullable', 'string', 'max:255'],
            'ordered_at' => ['nullable', 'date'],
            'vendor_id' => ['nullable', 'integer', Rule::exists('vendors', 'id')->whereNull('deleted_at')],
            'vendor_company_name' => ['nullable', 'string', 'max:255'],
            'vendor_contact' => ['nullable', 'string', 'max:255'],
            'vendor_email' => ['nullable', 'string', 'email', 'max:255'],
            'vendor_phone' => ['nullable', 'string', 'max:50'],
            'vendor_address' => ['nullable', 'string', 'max:2000'],
            'currency_code' => ['nullable', 'string', 'size:3', 'alpha'],
            'payment_terms' => ['nullable', 'string', 'max:5000'],
            'delivery_time' => ['nullable', 'string', 'max:255'],
            'delivery_location' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'status' => ['nullable', 'string', Rule::in(PurchaseOrderStatus::values())],
            'payment_status' => ['nullable', 'string', Rule::in(PaymentStatus::values())],
            'procurement_signature' => ['nullable', 'string', 'max:255'],
            'procurement_signed_at' => ['nullable', 'date'],
            'finance_signature' => ['nullable', 'string', 'max:255'],
            'finance_signed_at' => ['nullable', 'date'],
            'ceo_signature' => ['nullable', 'string', 'max:255'],
            'ceo_signed_at' => ['nullable', 'date'],
        ];
    }
}
