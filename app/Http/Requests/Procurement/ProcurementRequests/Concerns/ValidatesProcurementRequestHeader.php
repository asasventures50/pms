<?php

namespace App\Http\Requests\Procurement\ProcurementRequests\Concerns;

use App\Models\Procurement\Projects\Zone;
use Illuminate\Contracts\Validation\Validator;

trait ValidatesProcurementRequestHeader
{
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $projectId = $this->input('project_id');
            $zoneId = $this->input('zone_id');

            if ($zoneId && ! $projectId) {
                $validator->errors()->add('zone_id', 'Select a project before choosing a zone.');
            }

            if ($zoneId && $projectId) {
                $valid = Zone::query()
                    ->whereKey($zoneId)
                    ->where('project_id', $projectId)
                    ->exists();

                if (! $valid) {
                    $validator->errors()->add('zone_id', 'The selected zone does not belong to this project.');
                }
            }

            $categoryId = $this->input('category_id');
            $subcategoryId = $this->input('subcategory_id');

            if ($subcategoryId && ! $categoryId) {
                $validator->errors()->add('subcategory_id', 'Select a category before choosing a subcategory.');
            }

            if ($subcategoryId && $categoryId) {
                $valid = \App\Models\Procurement\Vendors\Subcategory::query()
                    ->whereKey($subcategoryId)
                    ->where('category_id', $categoryId)
                    ->exists();

                if (! $valid) {
                    $validator->errors()->add('subcategory_id', 'The selected subcategory does not belong to this category.');
                }
            }

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
        });
    }
}
