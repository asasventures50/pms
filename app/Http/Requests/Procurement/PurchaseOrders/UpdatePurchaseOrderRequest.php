<?php

namespace App\Http\Requests\Procurement\PurchaseOrders;

use App\Enums\Procurement\PurchaseOrders\PaymentStatus;
use App\Enums\Procurement\PurchaseOrders\PurchaseOrderStatus;
use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('purchase-orders.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'po_number' => ['sometimes', 'nullable', 'string', 'max:100', Rule::unique('purchase_orders', 'po_number')->ignore($this->route('purchase_order'))],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ordered_at' => ['sometimes', 'nullable', 'date'],
            'vendor_id' => ['sometimes', 'nullable', 'integer', Rule::exists('vendors', 'id')->whereNull('deleted_at')],
            'vendor_company_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'vendor_contact' => ['sometimes', 'nullable', 'string', 'max:255'],
            'vendor_email' => ['sometimes', 'nullable', 'string', 'email', 'max:255'],
            'vendor_phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'vendor_address' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'delivery_contact_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'delivery_contact_phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'delivery_contact_email' => ['sometimes', 'nullable', 'string', 'email', 'max:255'],
            'delivery_location' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'currency_code' => ['sometimes', 'nullable', 'string', 'size:3', 'alpha'],
            'delivery_fee' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'discount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'payment_terms' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'handover_at' => ['sometimes', 'nullable', 'date'],
            'dismantling_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:handover_at'],
            'terms_locale' => ['sometimes', 'nullable', 'string', Rule::in(RfqTermsLocale::values())],
            'terms_custom' => ['sometimes', 'nullable', 'array'],
            'terms_custom.*' => ['sometimes', 'nullable'],
            'terms_custom.*.key' => ['sometimes', 'nullable', 'string', 'max:255'],
            'terms_custom.*.value' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'status' => ['sometimes', 'string', Rule::in(PurchaseOrderStatus::values())],
            'payment_status' => ['sometimes', 'string', Rule::in(PaymentStatus::values())],
            'vendor_signature' => ['sometimes', 'nullable', 'string', 'max:255'],
            'vendor_signed_at' => ['sometimes', 'nullable', 'date'],
            'procurement_signature' => ['sometimes', 'nullable', 'string', 'max:255'],
            'procurement_signed_at' => ['sometimes', 'nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item' => ['nullable', 'string', 'max:100'],
            'items.*.description' => ['required', 'string', 'max:5000'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
