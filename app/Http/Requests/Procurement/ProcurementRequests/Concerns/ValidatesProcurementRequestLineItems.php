<?php

namespace App\Http\Requests\Procurement\ProcurementRequests\Concerns;

use App\Models\Procurement\Projects\Zone;
use Illuminate\Contracts\Validation\Validator;

trait ValidatesProcurementRequestLineItems
{
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $items = (array) $this->input('items', []);

            foreach ($items as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $projectId = $row['project_id'] ?? null;
                $zoneId = $row['zone_id'] ?? null;

                if ($zoneId && ! $projectId) {
                    $validator->errors()->add(
                        "items.$index.zone_id",
                        'Select a project before choosing a zone.'
                    );

                    continue;
                }

                if ($zoneId && $projectId) {
                    $valid = Zone::query()
                        ->whereKey($zoneId)
                        ->where('project_id', $projectId)
                        ->exists();

                    if (! $valid) {
                        $validator->errors()->add(
                            "items.$index.zone_id",
                            'The selected zone does not belong to this project.'
                        );
                    }
                }
            }
        });
    }
}
