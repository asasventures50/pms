<?php

namespace App\Http\Requests\Procurement\Invoices;

use App\Models\Procurement\Invoices\Invoice;
use App\Models\Procurement\PurchaseOrders\PurchaseOrderItem;
use App\Models\Procurement\PurchaseOrders\PurchaseOrderPaymentTerm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('invoices.create') ?? false;
    }

    public function isManualSource(): bool
    {
        return $this->input('source') === Invoice::SOURCE_MANUAL;
    }

    public function isPaymentTermSource(): bool
    {
        return $this->input('source') === Invoice::SOURCE_PO_PAYMENT_TERM;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'source' => ['required', 'string', Rule::in([
                Invoice::SOURCE_PURCHASE_ORDER,
                Invoice::SOURCE_PO_PAYMENT_TERM,
                Invoice::SOURCE_MANUAL,
            ])],
            'purchase_order_ids' => [
                'required_if:source,'.Invoice::SOURCE_PURCHASE_ORDER,
                'required_if:source,'.Invoice::SOURCE_PO_PAYMENT_TERM,
                'array',
                'min:1',
            ],
            'purchase_order_ids.*' => ['integer', 'distinct', 'exists:purchase_orders,id'],
            'manual_po_number' => ['nullable', 'string', 'max:500'],
            'manual_vendor_name' => ['nullable', 'string', 'max:255'],
            'manual_project_name' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'project_manager_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string', 'max:2000'],
            'purchase_order_item_ids' => [
                Rule::requiredIf(fn () => $this->input('source') === Invoice::SOURCE_PURCHASE_ORDER),
                'array',
                'min:1',
            ],
            'purchase_order_item_ids.*' => ['integer', 'distinct'],
            'po_payment_term_ids' => [
                'required_if:source,'.Invoice::SOURCE_PO_PAYMENT_TERM,
                'nullable',
                'array',
                'max:1',
            ],
            'po_payment_term_ids.*' => ['integer', 'distinct', 'exists:purchase_order_payment_terms,id'],
            'purchase_order_item_zones' => ['nullable', 'array'],
            'purchase_order_item_zones.*' => ['nullable', 'string', 'max:255'],
            'purchase_order_item_margins' => ['nullable', 'array'],
            'purchase_order_item_margins.*' => ['nullable', 'numeric', 'min:-100', 'max:1000'],
            'manual_lines' => ['required_if:source,'.Invoice::SOURCE_MANUAL, 'array', 'min:1'],
            'manual_lines.*.zone' => ['nullable', 'string', 'max:255'],
            'manual_lines.*.project_zone' => ['nullable', 'string', 'max:255'],
            'manual_lines.*.description' => ['required', 'string', 'max:2000'],
            'manual_lines.*.quantity' => ['required', 'numeric', 'min:0.001', 'max:999999999.999'],
            'manual_lines.*.unit' => ['nullable', 'string', 'max:50'],
            'manual_lines.*.unit_price' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'manual_lines.*.margin_percentage' => ['nullable', 'numeric', 'min:-100', 'max:1000'],
            'currency_code' => ['nullable', 'string', 'size:3', 'alpha'],
            'custom_fees' => ['nullable', 'array'],
            'custom_fees.*.project_zone' => ['nullable', 'string', 'max:255'],
            'custom_fees.*.description' => ['nullable', 'string', 'max:2000'],
            'custom_fees.*.quantity' => ['nullable', 'numeric', 'min:0', 'max:999999999.999'],
            'custom_fees.*.unit' => ['nullable', 'string', 'max:50'],
            'custom_fees.*.unit_price' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'custom_fees.*.margin_percentage' => ['nullable', 'numeric', 'min:-100', 'max:1000'],
            'merge_groups' => ['nullable', 'array'],
            'merge_groups.*.description' => ['required', 'string', 'max:2000'],
            'merge_groups.*.item_ids' => ['required', 'array', 'min:2'],
            'merge_groups.*.item_ids.*' => ['integer', 'distinct'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->isManualSource()) {
                return;
            }

            if ($this->isPaymentTermSource()) {
                $termIds = collect($this->input('po_payment_term_ids', []))
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->unique()
                    ->values();
                $purchaseOrderIds = collect($this->input('purchase_order_ids', []))
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                if ($termIds->isEmpty()) {
                    $validator->errors()->add('po_payment_term_ids', 'Select a payment term.');

                    return;
                }

                if ($termIds->count() !== 1) {
                    $validator->errors()->add('po_payment_term_ids', 'Each invoice must be for a single payment term.');

                    return;
                }

                $terms = PurchaseOrderPaymentTerm::query()
                    ->whereIn('id', $termIds)
                    ->get();

                if ($terms->count() !== $termIds->count()) {
                    $validator->errors()->add('po_payment_term_ids', 'One or more payment terms were not found.');

                    return;
                }

                if ($terms->contains(fn ($term) => $term->invoice_id !== null)) {
                    $validator->errors()->add('po_payment_term_ids', 'A selected payment term is already linked to an invoice.');

                    return;
                }

                $termPoIds = $terms->pluck('purchase_order_id')->unique()->values();
                if ($termPoIds->count() !== 1 || $purchaseOrderIds->diff($termPoIds)->isNotEmpty() || $termPoIds->diff($purchaseOrderIds)->isNotEmpty()) {
                    $validator->errors()->add('po_payment_term_ids', 'Payment terms must belong to the selected purchase order.');
                }

                return;
            }

            $purchaseOrderIds = collect($this->input('purchase_order_ids', []))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $selectedIds = collect($this->input('purchase_order_item_ids', []))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $poItemIds = PurchaseOrderItem::query()
                ->whereIn('purchase_order_id', $purchaseOrderIds)
                ->pluck('id');

            $invalid = $selectedIds->diff($poItemIds);
            if ($invalid->isNotEmpty()) {
                $validator->errors()->add('purchase_order_item_ids', 'One or more selected lines do not belong to the selected purchase orders.');

                return;
            }

            $mergeGroups = $this->input('merge_groups', []);
            if (! is_array($mergeGroups) || $mergeGroups === []) {
                return;
            }

            $seenItemIds = [];

            foreach ($mergeGroups as $index => $group) {
                $groupItemIds = collect($group['item_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                $outsideSelection = $groupItemIds->diff($selectedIds);
                if ($outsideSelection->isNotEmpty()) {
                    $validator->errors()->add("merge_groups.{$index}.item_ids", 'Merged lines must be selected for the invoice.');

                    return;
                }

                foreach ($groupItemIds as $itemId) {
                    if (in_array($itemId, $seenItemIds, true)) {
                        $validator->errors()->add('merge_groups', 'Each line can belong to only one merge group.');

                        return;
                    }
                    $seenItemIds[] = $itemId;
                }
            }
        });
    }
}
