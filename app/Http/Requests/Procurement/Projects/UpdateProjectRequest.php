<?php

namespace App\Http\Requests\Procurement\Projects;

use App\Http\Requests\Procurement\Projects\Concerns\PreparesProjectZoneRows;
use App\Models\Procurement\Projects\Zone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProjectRequest extends FormRequest
{
    use PreparesProjectZoneRows;

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('projects.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareZoneRowsForValidation();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'zones' => ['nullable', 'array'],
            'zones.*.id' => ['nullable', 'integer', 'exists:zones,id'],
            'zones.*.name' => ['required', 'string', 'max:255'],
            'zones.*.status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var \App\Models\Procurement\Projects\Project $project */
            $project = $this->route('project');
            $zones = (array) $this->input('zones', []);

            foreach ($zones as $index => $zone) {
                if (! is_array($zone) || empty($zone['id'])) {
                    continue;
                }

                $zoneId = (int) $zone['id'];
                $owned = Zone::query()
                    ->where('project_id', $project->id)
                    ->whereKey($zoneId)
                    ->exists();

                if (! $owned) {
                    $validator->errors()->add(
                        "zones.$index.id",
                        'Invalid zone for this project.'
                    );
                }
            }
        });
    }
}
