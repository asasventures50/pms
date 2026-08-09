<?php

namespace App\Services\Procurement\ProcurementRequests;

use App\Enums\Procurement\ProcurementRequests\ProcurementVendorType;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Support\Procurement\ProcurementCheckboxGroup;

class ProcurementRequestLegacyItemSync
{
    public static function applyToItem(ProcurementRequest $request, ProcurementRequestItem $item): void
    {
        $vendorTypes = ProcurementCheckboxGroup::selectedValues(
            $request->vendor_types,
            ProcurementVendorType::values()
        );

        $scopeLabels = array_map(
            static fn (string $value) => ProcurementVendorType::from($value)->legacyScopeLabel(),
            $vendorTypes
        );

        $item->fill([
            'project_id' => $request->project_id,
            'scope_type' => $scopeLabels === [] ? $item->scope_type : implode(', ', $scopeLabels),
            'justification' => $request->justification,
            'scope_of_work' => $request->scope_of_work,
            'delivery_location' => $request->delivery_location,
            'flexible_delivery_date' => $request->flexible_delivery_date ?? true,
        ]);
    }
}
