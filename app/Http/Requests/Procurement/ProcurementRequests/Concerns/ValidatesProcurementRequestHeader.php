<?php

namespace App\Http\Requests\Procurement\ProcurementRequests\Concerns;

use App\Models\Procurement\Projects\Zone;
use App\Models\Procurement\Vendors\Subcategory;
use Illuminate\Contracts\Validation\Validator;

trait ValidatesProcurementRequestHeader
{
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $flexible = filter_var($this->input('flexible_delivery_date', false), FILTER_VALIDATE_BOOLEAN);
            $leadTime = $this->input('delivery_lead_time_days');

            if (! $flexible && ($leadTime === null || $leadTime === '')) {
                $validator->errors()->add(
                    'delivery_lead_time_days',
                    'Required delivery lead time is needed when flexible delivery date is disabled.'
                );
            }

            $prequalRequired = filter_var(
                $this->input('compliance_prequalification_required'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
            $prequalLevel = trim((string) $this->input('compliance_prequalification_level', ''));

            if ($prequalRequired === true && $prequalLevel === '') {
                $validator->errors()->add(
                    'compliance_prequalification_level',
                    'Select a prequalification level when prequalification is required.'
                );
            }

            $headerProjectId = $this->input('project_id');

            foreach ((array) $this->input('items', []) as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $zoneId = $row['zone_id'] ?? null;

                if ($zoneId && ! $headerProjectId) {
                    $validator->errors()->add(
                        "items.$index.zone_id",
                        'Select a project before choosing a zone.'
                    );
                } elseif ($zoneId && $headerProjectId) {
                    $valid = Zone::query()
                        ->whereKey($zoneId)
                        ->where('project_id', $headerProjectId)
                        ->exists();

                    if (! $valid) {
                        $validator->errors()->add(
                            "items.$index.zone_id",
                            'The selected zone does not belong to this project.'
                        );
                    }
                }

                $categoryId = $row['category_id'] ?? null;
                $subcategoryId = $row['subcategory_id'] ?? null;

                if ($subcategoryId && ! $categoryId) {
                    $validator->errors()->add(
                        "items.$index.subcategory_id",
                        'Select a category before choosing a subcategory.'
                    );
                }

                if ($subcategoryId && $categoryId) {
                    $valid = Subcategory::query()
                        ->whereKey($subcategoryId)
                        ->where('category_id', $categoryId)
                        ->exists();

                    if (! $valid) {
                        $validator->errors()->add(
                            "items.$index.subcategory_id",
                            'The selected subcategory does not belong to this category.'
                        );
                    }
                }
            }
        });
    }
}
