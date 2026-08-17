<?php

namespace App\Http\Requests\Procurement\PurchaseOrders;

use App\Enums\Procurement\PurchaseOrders\PaymentStatus;
use App\Enums\Procurement\PurchaseOrders\PurchaseOrderStatus;
use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use App\Http\Requests\Procurement\PurchaseOrders\Concerns\ValidatesPurchaseOrderPaymentTermRows;
use App\Http\Requests\Procurement\PurchaseOrders\Concerns\ValidatesPurchaseOrderVendorFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseOrderRequest extends FormRequest
{
    use ValidatesPurchaseOrderVendorFields;
    use ValidatesPurchaseOrderPaymentTermRows;
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
            'items.*.unit' => ['nullable', 'string', 'max:50'],
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
            'procurement_request_id' => ['nullable', 'integer', Rule::exists('procurement_requests', 'id')->whereNull('deleted_at')],
            'package' => ['nullable', 'string', 'max:500'],
            ...$this->vendorFieldRules(),
            'delivery_contact_name' => ['nullable', 'string', 'max:255'],
            'delivery_contact_phone' => ['nullable', 'string', 'max:50'],
            'delivery_contact_email' => ['nullable', 'string', 'email', 'max:255'],
            'delivery_location' => ['nullable', 'string', 'max:2000'],
            'currency_code' => ['nullable', 'string', 'size:3', 'alpha'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_terms' => ['nullable', 'string', 'max:5000'],
            'payment_term_rows' => ['nullable', 'array'],
            'payment_term_rows.*.id' => ['nullable', 'integer'],
            'payment_term_rows.*.milestone' => ['nullable', 'string', 'max:2000'],
            'payment_term_rows.*.percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_term_rows.*.amount' => ['nullable', 'numeric', 'min:0'],
            'payment_term_rows.*.notes' => ['nullable', 'string', 'max:5000'],
            'show_payment_terms' => ['nullable', 'boolean'],
            'retentions' => ['nullable', 'array'],
            'retentions.*.retention_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'retentions.*.release_period' => ['nullable', 'string', 'max:255'],
            'show_retention' => ['nullable', 'boolean'],
            'after_sale_service_applicable' => ['nullable', 'boolean'],
            'warranty_years' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'warranty_coverage' => ['nullable', 'string', 'max:5000'],
            'show_maintenance' => ['nullable', 'boolean'],
            'handover_at' => ['nullable', 'date'],
            'dismantling_at' => ['nullable', 'date', 'after_or_equal:handover_at'],
            'execution_delivery_days' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'terms_locale' => ['nullable', 'string', Rule::in(RfqTermsLocale::values())],
            'terms_custom' => ['nullable', 'array'],
            'terms_custom.*' => ['nullable'],
            'terms_custom.*.key' => ['nullable', 'string', 'max:255'],
            'terms_custom.*.value' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'status' => ['nullable', 'string', Rule::in(PurchaseOrderStatus::values())],
            'payment_status' => ['nullable', 'string', Rule::in(PaymentStatus::values())],
            'vendor_signature' => ['nullable', 'string', 'max:255'],
            'vendor_signed_at' => ['nullable', 'date'],
            'procurement_signature' => ['nullable', 'string', 'max:255'],
            'procurement_signed_at' => ['nullable', 'date'],
        ];
    }
}
