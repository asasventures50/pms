<?php

namespace App\Http\Requests\Procurement\Rfqs\Concerns;

use App\Models\Procurement\Rfqs\RfqItem;
use Illuminate\Validation\Validator;

trait ValidatesRfqProcurementRequestItems
{
    protected function validateRfqProcurementRequestItems(Validator $validator): void
    {
        $exceptRfqId = $this->route('rfq')?->id;
        $seen = [];

        foreach ($this->input('items', []) as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $prItemId = (int) ($row['procurement_request_item_id'] ?? 0);
            if ($prItemId <= 0) {
                $validator->errors()->add(
                    "items.{$index}.procurement_request_item_id",
                    'Select a procurement request item.',
                );

                continue;
            }

            if (in_array($prItemId, $seen, true)) {
                $validator->errors()->add(
                    "items.{$index}.procurement_request_item_id",
                    'Each procurement request item can only be selected once.',
                );

                continue;
            }

            $seen[] = $prItemId;

            $alreadyAssigned = RfqItem::query()
                ->where('procurement_request_item_id', $prItemId)
                ->when($exceptRfqId, fn ($q) => $q->where('rfq_id', '!=', $exceptRfqId))
                ->exists();

            if ($alreadyAssigned) {
                $validator->errors()->add(
                    "items.{$index}.procurement_request_item_id",
                    'This procurement request item is already on another RFQ.',
                );
            }
        }
    }
}
