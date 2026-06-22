<?php

namespace App\Http\Requests\Procurement\Invoices;

use App\Models\Procurement\PurchaseOrders\PurchaseOrderItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('invoices.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'purchase_order_ids' => ['required', 'array', 'min:1'],
            'purchase_order_ids.*' => ['integer', 'distinct', 'exists:purchase_orders,id'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'purchase_order_item_ids' => ['required', 'array', 'min:1'],
            'purchase_order_item_ids.*' => ['integer', 'distinct'],
            'currency_code' => ['nullable', 'string', 'size:3', 'alpha'],
            'transport_fees' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'supervision_fees' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'administrative_fees' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'logistics_fees' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
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
