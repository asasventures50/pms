<?php

namespace App\Http\Requests\Procurement\Projects\Concerns;

trait PreparesProjectZoneRows
{
    protected function prepareZoneRowsForValidation(): void
    {
        $zones = (array) $this->input('zones', []);
        $filtered = [];

        foreach ($zones as $row) {
            if (! is_array($row)) {
                continue;
            }

            $name = isset($row['name']) ? trim((string) $row['name']) : '';

            if ($name === '') {
                continue;
            }

            $filtered[] = $row;
        }

        $this->merge(['zones' => array_values($filtered)]);
    }
}
